<?php
namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public const TRANSITIONS=[
        'pending'     => ['confirmed', 'cancelled'],
        'cod_ordered' => ['confirmed','packing','shipping','completed','cancelled'],
        'paid'        => ['confirmed','packing','shipping','completed','cancelled'],
        'confirmed'   => ['packing','shipping','completed','cancelled'],
        'packing'     => ['shipping','completed','cancelled'],
        'shipping'    => ['completed','cancelled'],
        'completed'   => [],
        'cancelled'   => []
    ];
    public function __construct(private CartService $carts,private CouponService $coupons,private ShippingService $shipping,private OrderNotificationService $notifications){}

    public function placeOrder(User $user,array $address,string $token,?string $couponCode,?int $zoneId):Order
    {
        if($existing=Order::where('checkout_token',$token)->where('user_id',$user->id)->first()) return $existing;
        $created=true;
        try {
        $order=DB::transaction(function() use($user,$address,$token,$couponCode,$zoneId){
            $cart=$this->carts->cart($user); $cartItems=$cart->items()->get();
            if($cartItems->isEmpty()) $this->fail('Giỏ hàng đang trống.');

            // Khóa variants theo ID cố định để hai checkout không thể cùng tiêu thụ một tồn kho.
            $variants=ProductVariant::whereIn('id',$cartItems->pluck('product_variant_id'))->orderBy('id')->lockForUpdate()->with(['product'=>fn($q)=>$q->withTrashed(),'color','size'])->get()->keyBy('id');
            $lines=[]; $subtotal=0.0;
            foreach($cartItems as $item){$variant=$variants->get($item->product_variant_id);if(!$variant||!$variant->product||$variant->stock<$item->quantity||$variant->product->status!=='active'||$variant->product->trashed())$this->fail('Sản phẩm vừa hết hàng hoặc không đủ số lượng.');$price=$variant->product->current_price;$line=$price*$item->quantity;$subtotal+=$line;$lines[]=compact('variant','item','price','line');}

            $coupon=null;$discount=0.0;
            if($couponCode){
                // Coupon cũng phải lock để usage_limit không bị vượt bởi hai transaction đồng thời.
                $coupon=Coupon::where('code',strtoupper(trim($couponCode)))->lockForUpdate()->first(); if(!$coupon)$this->fail('Mã giảm giá không hợp lệ.');
                $result=$this->coupons->validateCoupon($coupon,$subtotal,$user);$discount=$result['discount'];
            }
            $quote=$this->shipping->quote($zoneId,$subtotal-$discount); if(!$quote['zone'])$this->fail('Vui lòng chọn khu vực giao hàng hợp lệ.');
            $order=Order::create(array_merge($address,['user_id'=>$user->id,'coupon_id'=>$coupon?->id,'shipping_zone_id'=>$quote['zone']->id,'checkout_token'=>$token,'coupon_code'=>$coupon?->code,'shipping_zone_name'=>$quote['zone']->name,'subtotal'=>$subtotal,'discount'=>$discount,'shipping_fee'=>$quote['fee'],'total'=>max(0,$subtotal-$discount)+$quote['fee'],'payment_method'=>'cod','status'=>'pending']));
            $order->update(['order_code'=>'ORD'.$order->created_at->format('Ymd').str_pad((string)$order->id,8,'0',STR_PAD_LEFT)]);
            foreach($lines as $entry){$v=$entry['variant'];$item=$entry['item'];$order->items()->create(['product_variant_id'=>$v->id,'product_name'=>$v->product->name,'product_sku'=>$v->product->sku,'color_name'=>$v->color->name,'size_name'=>$v->size->name,'price'=>$entry['price'],'quantity'=>$item->quantity,'subtotal'=>$entry['line']]);$v->decrement('stock',$item->quantity);}
            if($coupon){CouponUsage::create(['coupon_id'=>$coupon->id,'user_id'=>$user->id,'order_id'=>$order->id,'discount_amount'=>$discount]);$coupon->increment('used_count');}
            $order->statusLogs()->create(['from_status'=>null,'to_status'=>'pending','note'=>'Đơn hàng được tạo','changed_by'=>$user->id]);
            return $order;
        },3);
        } catch (QueryException $exception) {
            // UNIQUE(checkout_token) là hàng rào cuối cho hai POST đồng thời.
            $order=Order::where('checkout_token',$token)->where('user_id',$user->id)->first();
            if(!$order) throw $exception;
            $created=false;
        }
        // Chỉ xóa cart sau khi transaction order đã commit thành công.
        $this->carts->cart($user)->items()->delete();
        $order=$order->fresh(['items','statusLogs']);
        if($created) $this->notifications->placed($order);
        return $order;
    }

    public function cancelOrder(User $user,Order $order,string $reason):Order
    {
        return $this->cancel($user,$order,$reason,true,['pending']);
    }
    public function cancelByAdmin(User $admin,Order $order,string $reason):Order
    {
        return $this->cancel($admin,$order,$reason,false,['pending','confirmed','paid','cod_ordered','packing','shipping']);
    }
    private function cancel(User $actor,Order $order,string $reason,bool $checkOwner,array $allowed):Order
    {
        $cancelled=DB::transaction(function() use($actor,$order,$reason,$checkOwner,$allowed){
            $locked=Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if($checkOwner) abort_unless($locked->user_id===$actor->id,404);
            if(!in_array($locked->status,$allowed,true)) $this->fail('Đơn hàng không còn ở trạng thái có thể hủy.');
            $from=$locked->status;
            $items=$locked->items()->orderBy('product_variant_id')->get();$variants=ProductVariant::whereIn('id',$items->pluck('product_variant_id')->filter())->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            foreach($items as $item) if($variant=$variants->get($item->product_variant_id)) $variant->increment('stock',$item->quantity);
            if($locked->coupon_id){$coupon=Coupon::whereKey($locked->coupon_id)->lockForUpdate()->first();$deleted=CouponUsage::where('order_id',$locked->id)->delete();if($coupon&&$deleted)$coupon->update(['used_count'=>max(0,$coupon->used_count-$deleted)]);}
            $locked->update(['status'=>'cancelled','cancelled_reason'=>$reason,'cancelled_at'=>now()]);$locked->statusLogs()->create(['from_status'=>$from,'to_status'=>'cancelled','note'=>$reason,'changed_by'=>$actor->id]);return $locked->fresh(['items','statusLogs']);
        },3);
        $this->notifications->cancelled($cancelled);
        return $cancelled;
    }
    public function transitionByAdmin(User $admin,Order $order,string $to,?string $note=null):Order
    {
        if($to==='cancelled') return $this->cancelByAdmin($admin,$order,$note?:'Admin hủy đơn');
        [$changed,$from]=DB::transaction(function() use($admin,$order,$to,$note){$locked=Order::whereKey($order->id)->lockForUpdate()->firstOrFail();$from=$locked->status;if(!$this->canTransition($from,$to))$this->fail('Chuyển trạng thái không hợp lệ.');$locked->update(['status'=>$to]);$locked->statusLogs()->create(['from_status'=>$from,'to_status'=>$to,'note'=>$note,'changed_by'=>$admin->id]);return [$locked->fresh(['items','statusLogs']),$from];},3);
        $this->notifications->statusChanged($changed,$from,$to);
        return $changed;
    }
    public function canTransition(string $from,string $to):bool{return in_array($to,self::TRANSITIONS[$from]??[],true);}
    private function fail(string $message):never{throw ValidationException::withMessages(['order'=>$message]);}
}
