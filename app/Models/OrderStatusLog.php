<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class OrderStatusLog extends Model { protected $fillable=['order_id','from_status','to_status','note','changed_by']; public function order(){return $this->belongsTo(Order::class);} }
