<?php
namespace App\Http\Controllers\Customer;
use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Models\ShippingZone;
use App\Services\CartService;
use App\Services\CouponService;
use App\Services\ShippingService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private CartService $carts,private CouponService $coupons,private ShippingService $shipping){}
    public function index(Request $request)
    {
        $base=$this->carts->summary($request->user()); $discount=0; $coupon=null;
        if($code=$request->session()->get('cart_coupon')) { try{$result=$this->coupons->apply($code,$base['subtotal'],$request->user());$discount=$result['discount'];$coupon=$result['coupon'];}catch(\Throwable){$request->session()->forget('cart_coupon');} }
        $shipping=$this->shipping->calculate($request->session()->get('shipping_zone_id'),$base['subtotal']-$discount);
        $summary=$this->carts->summary($request->user(),$discount,$shipping); $zones=ShippingZone::where('status','active')->with(['rates'=>fn($q)=>$q->where('status','active')])->get();
        return view('cart.index',compact('summary','zones','coupon'));
    }
    public function store(Request $request)
    {
        $data=$request->validate(['variant_id'=>'required|exists:product_variants,id','quantity'=>'required|integer|min:1']);
        $variant=ProductVariant::with('product')->findOrFail($data['variant_id']);
        abort_unless($variant->product->status==='active'&&!$variant->product->trashed(),404);
        $this->carts->add($request->user(),$variant,$data['quantity']);

        if ($request->wantsJson() || $request->ajax()) {
            $cartCount = (int) ($request->user()->cart?->items()->sum('quantity') ?? 0);
            return response()->json([
                'success'      => true,
                'message'      => 'Đã thêm vào giỏ hàng thành công.',
                'cart_count'   => $cartCount,
                'redirect_url' => $request->boolean('buy_now') ? route('cart.index') : null,
            ]);
        }

        return redirect()->route($request->boolean('buy_now')?'cart.index':'products.show',$request->boolean('buy_now')?[]:$variant->product->slug)->with('success','Đã thêm vào giỏ hàng.');
    }
    public function update(Request $request,CartItem $item){$data=$request->validate(['quantity'=>'required|integer|min:1']);$this->carts->update($request->user(),$item,$data['quantity']);return back()->with('success','Đã cập nhật giỏ hàng.');}
    public function destroy(Request $request,CartItem $item){abort_unless($item->cart->user_id===$request->user()->id,404);$item->delete();return back()->with('success','Đã xóa sản phẩm.');}
    public function clear(Request $request){$this->carts->cart($request->user())->items()->delete();$request->session()->forget(['cart_coupon']);return back()->with('success','Đã xóa toàn bộ giỏ hàng.');}
    public function coupon(Request $request){$data=$request->validate(['coupon'=>'required|string|max:50']);$subtotal=$this->carts->summary($request->user())['subtotal'];$this->coupons->apply($data['coupon'],$subtotal,$request->user());$request->session()->put('cart_coupon',strtoupper($data['coupon']));return back()->with('success','Đã áp dụng mã giảm giá.');}
    public function removeCoupon(Request $request){$request->session()->forget('cart_coupon');return back();}
    public function shipping(Request $request){$data=$request->validate(['shipping_zone_id'=>'nullable|exists:shipping_zones,id']);$request->session()->put('shipping_zone_id',$data['shipping_zone_id']??null);return back();}
}
