<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ShippingRate extends Model { protected $fillable=['shipping_zone_id','name','fee','free_shipping_threshold','status']; protected function casts():array{return ['fee'=>'decimal:2','free_shipping_threshold'=>'decimal:2'];} public function zone(){return $this->belongsTo(ShippingZone::class,'shipping_zone_id');} }
