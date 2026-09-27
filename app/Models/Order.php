<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'coupon_id',
        'shipping_zone_id',
        'order_code',
        'checkout_token',
        'recipient_name',
        'name',
        'email',
        'phone',
        'province',
        'district',
        'ward',
        'address',
        'note',
        'coupon_code',
        'shipping_zone_name',
        'subtotal',
        'discount',
        'shipping_fee',
        'total',
        'total_price',
        'payment_method',
        'status',
        'cancelled_reason',
        'cancelled_at',
        'to_district_id',
        'to_ward_code',
        'ghn_total_fee',
        'shipping_status',
        'ghn_order_code',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'shipping_fee' => 'decimal:2',
            'total' => 'decimal:2',
            'total_price' => 'decimal:2',
            'ghn_total_fee' => 'decimal:2',
            'cancelled_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($order) {
            if (empty($order->checkout_token)) {
                $order->checkout_token = (string) Str::uuid();
            }
            if (empty($order->recipient_name) && !empty($order->name)) {
                $order->recipient_name = $order->name;
            }
            if (empty($order->name) && !empty($order->recipient_name)) {
                $order->name = $order->recipient_name;
            }
            if (!isset($order->total) && isset($order->total_price)) {
                $order->total = $order->total_price;
            }
            if (!isset($order->total_price) && isset($order->total)) {
                $order->total_price = $order->total;
            }
            if (!isset($order->subtotal)) {
                $order->subtotal = $order->total ?? 0;
            }
            if (empty($order->email) && $order->user) {
                $order->email = $order->user->email;
            }
        });

        static::created(function ($order) {
            if (empty($order->order_code)) {
                $order->updateQuietly([
                    'order_code' => 'ORD' . $order->created_at->format('Ymd') . str_pad((string) $order->id, 8, '0', STR_PAD_LEFT),
                ]);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusLogs()
    {
        return $this->hasMany(OrderStatusLog::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function paymentTransactions()
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function getNameAttribute(): ?string
    {
        return $this->attributes['name'] ?? $this->attributes['recipient_name'] ?? null;
    }

    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = $value;
        if (empty($this->attributes['recipient_name'])) {
            $this->attributes['recipient_name'] = $value;
        }
    }

    public function getTotalPriceAttribute(): ?float
    {
        $val = $this->attributes['total_price'] ?? $this->attributes['total'] ?? null;
        return $val !== null ? (float) $val : null;
    }

    public function setTotalPriceAttribute($value): void
    {
        $this->attributes['total_price'] = $value;
        if (!isset($this->attributes['total'])) {
            $this->attributes['total'] = $value;
        }
    }

    public function getStatusLabelAttribute(): string
    {
        return [
            'pending' => 'Chờ xác nhận / thanh toán',
            'cod_ordered' => 'Chờ giao hàng (COD)',
            'paid' => 'Đã thanh toán (MoMo)',
            'confirmed' => 'Đã xác nhận',
            'packing' => 'Đang đóng gói',
            'shipping' => 'Đang giao',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
        ][$this->status] ?? $this->status;
    }

    public function getWorkflowInfoAttribute(): array
    {
        $status = $this->status;
        $ship = $this->shipping_status ?? 'pending';
        $paymentStatus = $this->payment_status ?? ($status === 'paid' ? 'paid' : ($status === 'cod_ordered' ? 'pending' : $status));
        $gateway = $this->gateway ?? ($this->payment_method ?? 'unknown');
        $hasGhn = !empty($this->ghn_order_code);
        $isPaid = in_array($paymentStatus, ['paid', 'cod_paid']) || in_array($status, ['paid', 'paid_momo', 'completed']);

        // 1. Đã hủy: những đơn giao ko thành công hoặc ng bán hủy
        if ($status === 'cancelled' || in_array($ship, ['cancelled', 'cancel', 'delivery_fail']) || $paymentStatus === 'cancelled') {
            return ['key' => 'cancelled', 'label' => 'Đã hủy', 'color' => '#ef4444'];
        }

        // 2. Hoàn hàng: giao thành công bị người mua hoàn lại
        if ($status === 'return' || in_array($ship, ['return', 'returning', 'returned', 'return_transporting', 'return_sorting'])) {
            return ['key' => 'return', 'label' => 'Hoàn hàng', 'color' => '#f97316'];
        }

        // 3. Giao thành công (API của giao hàng nhanh trả về)
        if ($ship === 'delivered' || $status === 'completed') {
            return ['key' => 'delivered', 'label' => 'Thành công', 'color' => '#22c55e'];
        }

        // 4. Đang giao (API của giao hàng nhanh trả về)
        if (in_array($ship, ['delivering', 'picked', 'storing', 'transporting', 'sorting', 'shipping'])) {
            return ['key' => 'delivering', 'label' => 'Đang giao', 'color' => '#f59e0b'];
        }

        // 5. Đang lấy hàng:
        // - thanh toán bằng MoMo thành công và có mã vận đơn
        // - thanh toán COD (trực tiếp về đang lấy hàng)
        if (
            ($hasGhn && $isPaid) ||
            ($gateway === 'cod' || $this->payment_method === 'cod') ||
            ($hasGhn && in_array($ship, ['ready_to_pick', 'picking']))
        ) {
            return ['key' => 'picking', 'label' => 'Đang lấy hàng', 'color' => '#06b6d4'];
        }

        // 6. Chờ lấy hàng: thanh toán bằng MoMo thành công nhưng chưa có mã vận đơn
        if (
            (($gateway === 'momo' || $this->payment_method === 'momo') && $isPaid && !$hasGhn) ||
            ($ship === 'ready_to_pick' && !$hasGhn)
        ) {
            return ['key' => 'ready', 'label' => 'Chờ lấy hàng', 'color' => '#0ea5e9'];
        }

        // 7. Chờ xử lý: thanh toán bằng MoMo thất bại
        return ['key' => 'pending', 'label' => 'Chờ xử lý', 'color' => '#94a3b8'];
    }
}
