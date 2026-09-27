<?php
namespace App\Services;
use App\Models\ShippingRate;
use App\Models\ShippingZone;
class ShippingService
{
    public function calculate(?int $zoneId,float $subtotal): float
    {
        return $this->quote($zoneId,$subtotal)['fee'];
    }
    public function quote(?int $zoneId,float $subtotal):array
    {
        if(!$zoneId) return ['zone'=>null,'rate'=>null,'fee'=>0.0];
        $rate=ShippingRate::where('shipping_zone_id',$zoneId)->where('status','active')->whereHas('zone',fn($q)=>$q->where('status','active'))->with('zone')->orderBy('fee')->first();
        if(!$rate) return ['zone'=>null,'rate'=>null,'fee'=>0.0];
        $fee=$rate->free_shipping_threshold!==null && $subtotal >= (float)$rate->free_shipping_threshold ? 0.0 : (float)$rate->fee;
        return ['zone'=>$rate->zone,'rate'=>$rate,'fee'=>$fee];
    }
}
