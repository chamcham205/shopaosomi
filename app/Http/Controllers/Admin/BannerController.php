<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BannerController extends Controller
{
    public function index(){return view('admin.banners.index',['banners'=>Banner::orderBy('sort_order')->latest()->get()]);}
    public function store(Request $request){$data=$this->validated($request);if($request->hasFile('image'))$data['image_path']=$request->file('image')->storeAs('banners',Str::uuid().'.'.$request->file('image')->extension(),'public');unset($data['image']);Banner::create($data);return back()->with('success','Đã tạo banner.');}
    public function update(Request $request,Banner $banner){$data=$this->validated($request,true);$old=$banner->image_path;if($request->hasFile('image'))$data['image_path']=$request->file('image')->storeAs('banners',Str::uuid().'.'.$request->file('image')->extension(),'public');unset($data['image']);$banner->update($data);if(isset($data['image_path'])&&$old)Storage::disk('public')->delete($old);return back()->with('success','Đã cập nhật banner.');}
    public function destroy(Banner $banner){if($banner->image_path)Storage::disk('public')->delete($banner->image_path);$banner->delete();return back()->with('success','Đã xóa banner.');}
    public function toggle(Banner $banner){$banner->update(['status'=>$banner->status==='active'?'inactive':'active']);return back()->with('success','Đã đổi trạng thái banner.');}
    private function validated(Request $request,bool $updating=false):array{return $request->validate(['title'=>'required|string|max:191','subtitle'=>'nullable|string|max:1000','image'=>[$updating?'nullable':'required','image','mimes:jpg,jpeg,png,webp','max:4096'],'button_text'=>'nullable|string|max:100','target_url'=>['nullable','string','max:2048',function($attribute,$value,$fail){if($value!==null&&!str_starts_with($value,'/')&&!filter_var($value,FILTER_VALIDATE_URL))$fail('URL đích không hợp lệ.');}],'sort_order'=>'required|integer|min:0|max:100000','status'=>['required',Rule::in(['active','inactive'])],'starts_at'=>'nullable|date','ends_at'=>'nullable|date|after_or_equal:starts_at']);}
}
