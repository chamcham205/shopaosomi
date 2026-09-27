<?php
namespace App\Http\Controllers\Customer;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ShippingZone;
use App\Services\CartService;
use App\Services\CouponService;
use App\Services\OrderService;
use App\Services\ShippingService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function __construct(private CartService $carts,private CouponService $coupons,private ShippingService $shipping,private OrderService $orders){}
    public function index(Request $request)
    {
        $summary=$this->summary($request); if($summary['items']->isEmpty())return redirect()->route('cart.index')->with('error','Giỏ hàng đang trống.');
        $token=(string)Str::uuid();$request->session()->put('checkout_token',$token);
        return view('checkout.index',['summary'=>$summary,'token'=>$token,'zones'=>ShippingZone::where('status','active')->get()]);
    }
    public function store(Request $request)
    {
        $data=$request->validate(['checkout_token'=>'required|uuid','recipient_name'=>'required|string|max:191','email'=>'required|email|max:191','phone'=>'required|string|max:30','province'=>'required|string|max:191','district'=>'required|string|max:191','ward'=>'required|string|max:191','address'=>'required|string|max:255','note'=>'nullable|string|max:1000','shipping_zone_id'=>'required|exists:shipping_zones,id']);
        $token=$data['checkout_token'];$existing=Order::where('checkout_token',$token)->where('user_id',$request->user()->id)->first();if($existing)return redirect()->route('checkout.success',$existing);
        if(!hash_equals((string)$request->session()->get('checkout_token'),$token))return back()->withErrors(['order'=>'Phiên checkout không hợp lệ hoặc đã được sử dụng.']);
        $address=collect($data)->only(['recipient_name','email','phone','province','district','ward','address','note'])->all();
        $order=$this->orders->placeOrder($request->user(),$address,$token,$request->session()->get('cart_coupon'),(int)$data['shipping_zone_id']);
        $request->session()->forget(['checkout_token','cart_coupon']);return redirect()->route('checkout.success',$order);
    }
    public function success(Request $request,Order $order){abort_unless($order->user_id===$request->user()->id,404);return view('checkout.success',compact('order'));}
    private function summary(Request $request):array{$base=$this->carts->summary($request->user());$discount=0;if($code=$request->session()->get('cart_coupon')){$discount=$this->coupons->apply($code,$base['subtotal'],$request->user())['discount'];}$shipping=$this->shipping->calculate($request->session()->get('shipping_zone_id'),$base['subtotal']-$discount);return $this->carts->summary($request->user(),$discount,$shipping);}
}
