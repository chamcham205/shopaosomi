<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Coupon extends Model { protected $fillable=['code','type','value','minimum_order','max_discount','start_at','end_at','usage_limit','per_user_limit','used_count','status']; protected function casts():array{return ['value'=>'decimal:2','minimum_order'=>'decimal:2','max_discount'=>'decimal:2','start_at'=>'datetime','end_at'=>'datetime'];} public function usages(){return $this->hasMany(CouponUsage::class);} public function setCodeAttribute($value):void{$this->attributes['code']=strtoupper(trim($value));} }
