<?php
namespace App\Http\Controllers\Customer;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
class OrderController extends Controller
{
    public function __construct(private OrderService $orders){}
    public function index(Request $request){$orders=$request->user()->orders()->with(['items','paymentTransactions'])->latest()->paginate(15);return view('user.orders.index',compact('orders'));}
    public function show(Request $request,Order $order){abort_unless($order->user_id===$request->user()->id,404);$order->load(['items','statusLogs','paymentTransactions']);return view('customer.orders.show',compact('order'));}
    public function cancel(Request $request,Order $order){$data=$request->validate(['reason'=>'required|string|max:500']);$this->orders->cancelOrder($request->user(),$order,$data['reason']);return back()->with('success','Đơn hàng đã được hủy và hoàn kho.');}

}
