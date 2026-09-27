<?php
namespace App\Http\Controllers\Customer;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;
class WishlistController extends Controller
{
    public function index(Request $request){$items=$request->user()->wishlistItems()->with(['product.images','product.variants'])->latest()->paginate(12);return view('wishlist.index',compact('items'));}
    public function store(Request $request,Product $product){abort_unless($product->status==='active'&&!$product->trashed(),404);Wishlist::firstOrCreate(['user_id'=>$request->user()->id,'product_id'=>$product->id]);return back()->with('success','Đã thêm vào yêu thích.');}
    public function destroy(Request $request,Product $product){Wishlist::where('user_id',$request->user()->id)->where('product_id',$product->id)->delete();return back()->with('success','Đã xóa khỏi yêu thích.');}
}
