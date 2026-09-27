<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class CouponController extends Controller
{
    public function index(){return view('admin.coupons.index',['coupons'=>Coupon::withCount('usages')->latest()->paginate(20)]);}
    public function create(){return view('admin.coupons.form',['coupon'=>new Coupon]);}
    public function store(Request $r){Coupon::create($this->data($r));return redirect()->route('admin.coupons.index')->with('success','Đã tạo coupon.');}
    public function edit(Coupon $coupon){return view('admin.coupons.form',compact('coupon'));}
    public function update(Request $r,Coupon $coupon){$coupon->update($this->data($r,$coupon));return redirect()->route('admin.coupons.index')->with('success','Đã cập nhật coupon.');}
    public function destroy(Coupon $coupon){abort_if($coupon->usages()->exists(),422);$coupon->delete();return back()->with('success','Đã xóa coupon.');}
    public function toggle(Coupon $coupon){$coupon->update(['status'=>$coupon->status==='active'?'inactive':'active']);return back();}
    private function data(Request $r,?Coupon $coupon=null):array{$d=$r->validate(['code'=>['required','string','max:50',Rule::unique('coupons')->ignore($coupon)],'type'=>'required|in:percent,fixed','value'=>'required|numeric|min:0','minimum_order'=>'nullable|numeric|min:0','max_discount'=>'nullable|numeric|min:0','start_at'=>'nullable|date','end_at'=>'nullable|date|after_or_equal:start_at','usage_limit'=>'nullable|integer|min:1','per_user_limit'=>'nullable|integer|min:1','status'=>'required|in:active,inactive']);$d['code']=strtoupper(trim($d['code']));$d['minimum_order']=$d['minimum_order']??0;return $d;}
}
