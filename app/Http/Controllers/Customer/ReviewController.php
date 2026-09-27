<?php
namespace App\Http\Controllers\Customer;use App\Http\Controllers\Controller;use App\Models\Product;use App\Services\ReviewModerationService;use Illuminate\Http\Request;
class ReviewController extends Controller
{public function store(Request $r,Product $product,ReviewModerationService $service){$d=$r->validate(['rating'=>'required|integer|between:1,5','title'=>'nullable|string|max:191','content'=>'required|string|min:10|max:3000']);$service->create($r->user(),$product,$d);return back()->with('success','Đánh giá đã được gửi và đang chờ duyệt.');}public function index(Request $r){$reviews=$r->user()->reviews()->with('product')->latest()->paginate(15);return view('customer.reviews.index',compact('reviews'));}}
