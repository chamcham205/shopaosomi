<?php
namespace App\Services;
use App\Models\BannedWord;use App\Models\OrderItem;use App\Models\Product;use App\Models\Review;use App\Models\User;use Illuminate\Validation\ValidationException;
class ReviewModerationService
{
 public const TRANSITIONS=['pending'=>['approved','rejected'],'approved'=>['hidden'],'hidden'=>['approved'],'rejected'=>[]];
 public function eligibleOrderItem(User $user,Product $product):?OrderItem{return OrderItem::query()->whereHas('order',fn($q)=>$q->where('user_id',$user->id)->where('status','completed'))->whereHas('variant',fn($q)=>$q->where('product_id',$product->id))->latest('id')->first();}
 public function canReview(User $user,Product $product):bool{return $user->isCustomer()&&!Review::where('user_id',$user->id)->where('product_id',$product->id)->exists()&&$this->eligibleOrderItem($user,$product)!==null;}
 public function create(User $user,Product $product,array $data):Review
 {
  if(!$user->isCustomer()||Review::where('user_id',$user->id)->where('product_id',$product->id)->exists())$this->fail('Bạn đã đánh giá sản phẩm này hoặc không có quyền đánh giá.');
  $item=$this->eligibleOrderItem($user,$product);if(!$item)$this->fail('Chỉ khách hàng đã nhận sản phẩm mới được đánh giá.');
  if($word=$this->detectBannedWord(($data['title']??'').' '.$data['content']))$this->fail("Nội dung chứa từ không được phép: {$word}.",'content');
  return Review::create(['user_id'=>$user->id,'product_id'=>$product->id,'order_item_id'=>$item->id,'rating'=>$data['rating'],'title'=>$data['title']??null,'content'=>$data['content'],'status'=>'pending','verified_purchase'=>true]);
 }
 public function detectBannedWord(string $text):?string{$normalized=$this->normalize($text);foreach(BannedWord::where('status','active')->pluck('word') as $word)if(str_contains($normalized,$this->normalize($word)))return $word;return null;}
 public function transition(Review $review,string $to,User $admin,?string $note=null):Review{if(!in_array($to,self::TRANSITIONS[$review->status]??[],true))$this->fail('Chuyển trạng thái review không hợp lệ.','status');$review->update(['status'=>$to,'moderation_note'=>$note,'moderated_by'=>$admin->id,'moderated_at'=>now()]);return $review->fresh();}
 private function normalize(string $text):string{return preg_replace('/\s+/u',' ',mb_strtolower(strip_tags(trim($text))));}
 private function fail(string $message,string $key='review'):never{throw ValidationException::withMessages([$key=>$message]);}
}
