<?php
namespace App\Services;
use App\Models\Coupon;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CouponService
{
    public function apply(string $code, float $subtotal, User $user): array
    {
        $coupon=Coupon::where('code',strtoupper(trim($code)))->first();
        if(!$coupon) $this->fail('Mã giảm giá không hợp lệ.');
        return $this->validateCoupon($coupon,$subtotal,$user);
    }
    public function validateCoupon(Coupon $coupon,float $subtotal,User $user):array
    {
        if($coupon->status!=='active') $this->fail('Mã giảm giá không hợp lệ.');
        if(($coupon->start_at && now()->lt($coupon->start_at)) || ($coupon->end_at && now()->gt($coupon->end_at))) $this->fail('Mã giảm giá đã hết hạn hoặc chưa bắt đầu.');
        if($subtotal<(float)$coupon->minimum_order) $this->fail('Đơn hàng chưa đạt giá trị tối thiểu.');
        if($coupon->usage_limit!==null && max($coupon->used_count,$coupon->usages()->count()) >= $coupon->usage_limit) $this->fail('Mã giảm giá đã hết lượt sử dụng.');
        if($coupon->per_user_limit!==null && $coupon->usages()->where('user_id',$user->id)->count() >= $coupon->per_user_limit) $this->fail('Bạn đã dùng hết lượt cho mã này.');
        $discount=$coupon->type==='percent' ? $subtotal*((float)$coupon->value/100) : (float)$coupon->value;
        if($coupon->max_discount!==null) $discount=min($discount,(float)$coupon->max_discount);
        return ['coupon'=>$coupon,'discount'=>min($discount,$subtotal)];
    }
    private function fail(string $message): never { throw ValidationException::withMessages(['coupon'=>$message]); }
}
