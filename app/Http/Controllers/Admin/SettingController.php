<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Services\SettingService;
use Illuminate\Http\Request;
class SettingController extends Controller
{
    public function index(SettingService $settings){return view('admin.settings.index',['settings'=>$settings->all()]);}
    public function update(Request $request,SettingService $settings){$data=$request->validate(['site_name'=>'required|string|max:100','site_description'=>'nullable|string|max:500','contact_email'=>'nullable|email|max:191','contact_phone'=>'nullable|string|max:30','address'=>'nullable|string|max:500','low_stock_threshold'=>'required|integer|min:0|max:100000','default_theme'=>'required|in:light,dark,system','announcement'=>'nullable|string|max:255']);$settings->put($data);return back()->with('success','Đã lưu cài đặt hệ thống.');}
}
