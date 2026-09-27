<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CouponUsage extends Model { public $timestamps=false; protected $fillable=['coupon_id','user_id','order_id','discount_amount','used_at']; protected function casts():array{return ['used_at'=>'datetime'];} public function coupon(){return $this->belongsTo(Coupon::class);} public function order(){return $this->belongsTo(Order::class);} }
