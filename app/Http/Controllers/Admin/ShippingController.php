<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\ShippingRate;
use App\Models\ShippingZone;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class ShippingController extends Controller
{
    public function index(){return view('admin.shipping.index',['zones'=>ShippingZone::with('rates')->orderBy('name')->get()]);}
    public function store(Request $r){$d=$r->validate(['name'=>'required|string|max:100','code'=>'required|string|max:50|unique:shipping_zones,code','status'=>'required|in:active,inactive','rate_name'=>'required|string|max:100','fee'=>'required|numeric|min:0','free_shipping_threshold'=>'nullable|numeric|min:0']);$zone=ShippingZone::create(['name'=>$d['name'],'code'=>strtoupper($d['code']),'status'=>$d['status']]);$zone->rates()->create(['name'=>$d['rate_name'],'fee'=>$d['fee'],'free_shipping_threshold'=>$d['free_shipping_threshold'],'status'=>'active']);return back()->with('success','Đã tạo khu vực.');}
    public function update(Request $r,ShippingZone $zone){$d=$r->validate(['name'=>'required|string|max:100','code'=>['required','string','max:50',Rule::unique('shipping_zones')->ignore($zone)],'status'=>'required|in:active,inactive','rate_name'=>'required|string|max:100','fee'=>'required|numeric|min:0','free_shipping_threshold'=>'nullable|numeric|min:0','rate_status'=>'required|in:active,inactive']);$zone->update(['name'=>$d['name'],'code'=>strtoupper($d['code']),'status'=>$d['status']]);$zone->rates()->updateOrCreate(['id'=>$zone->rates()->value('id')],['name'=>$d['rate_name'],'fee'=>$d['fee'],'free_shipping_threshold'=>$d['free_shipping_threshold'],'status'=>$d['rate_status']]);return back()->with('success','Đã cập nhật vận chuyển.');}
    public function destroy(ShippingZone $zone){$zone->delete();return back()->with('success','Đã xóa khu vực.');}
}
