<?php
namespace App\Services;
use App\Models\Order;use App\Models\OrderItem;use App\Models\Product;use App\Models\ProductVariant;use App\Models\User;use Illuminate\Support\Carbon;
class AdminMetricsService
{
    public function __construct(private SettingService $settings){}
    public function overview(?Carbon $from=null,?Carbon $to=null):array
    {
        $completed=Order::where('status','completed');if($from)$completed->whereDate('created_at','>=',$from);if($to)$completed->whereDate('created_at','<=',$to);
        $statusCounts=Order::selectRaw('status, COUNT(*) total')->groupBy('status')->pluck('total','status');
        return ['totalRevenue'=>(clone $completed)->sum('total'),'todayRevenue'=>(clone $completed)->whereDate('created_at',today())->sum('total'),'monthRevenue'=>(clone $completed)->whereBetween('created_at',[now()->startOfMonth(),now()->endOfMonth()])->sum('total'),'totalOrders'=>Order::count(),'statusCounts'=>$statusCounts,'totalCustomers'=>User::where('role','customer')->count(),'activeProducts'=>Product::where('status','active')->count(),'lowStockCount'=>ProductVariant::where('stock','>',0)->where('stock','<=',$this->settings->lowStockThreshold())->count()];
    }
    public function topProducts(int $limit=10){return OrderItem::query()->join('orders','orders.id','=','order_items.order_id')->where('orders.status','completed')->selectRaw('product_sku, product_name, SUM(quantity) quantity_sold, SUM(order_items.subtotal) revenue')->groupBy('product_sku','product_name')->orderByDesc('quantity_sold')->limit($limit)->get();}
    public function revenue7Days():array{$from=today()->subDays(6);$rows=Order::where('status','completed')->whereDate('created_at','>=',$from)->selectRaw('DATE(created_at) day, SUM(total) revenue')->groupBy('day')->pluck('revenue','day');$labels=[];$values=[];for($i=0;$i<7;$i++){$day=$from->copy()->addDays($i);$labels[]=$day->format('d/m');$values[]=(float)($rows[$day->toDateString()]??0);}return compact('labels','values');}
    public function lowStock(){return ProductVariant::with(['product','color','size'])->where('stock','<=',$this->settings->lowStockThreshold())->orderBy('stock')->limit(30)->get();}
    public function bestCustomers(){return User::where('role','customer')->withCount(['orders as completed_orders_count'=>fn($q)=>$q->where('status','completed')])->withSum(['orders as completed_revenue'=>fn($q)=>$q->where('status','completed')],'total')->orderByDesc('completed_revenue')->limit(10)->get();}
}
