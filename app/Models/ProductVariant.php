<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ProductVariant Model ⭐ QUAN TRỌNG NHẤT PHASE 2
 *
 * Mỗi dòng = 1 tổ hợp Product + Color + Size.
 *
 * Tại sao stock nằm ở đây?
 *   Áo sơ mi Oxford / Trắng / M → có thể còn 10 chiếc
 *   Áo sơ mi Oxford / Navy / L  → có thể còn 2 chiếc
 *   → Phải quản lý stock ở từng variant, không thể gộp chung.
 *
 * Rule INV-02: Tên cột tồn kho CHÍNH THỨC là: stock
 * Rule INV-07: stock không được âm (UNSIGNED INT ở migration)
 *
 * Khi checkout: phải lockForUpdate() trên bảng này (Phase 5).
 */
class ProductVariant extends Model
{
    protected $fillable = [
        'product_id', 'color_id', 'size_id', 'sku', 'stock',
    ];

    protected function casts(): array
    {
        return ['stock' => 'integer'];
    }

    // ── Relationships ───────────────────────────────────────────────

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class);
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(Size::class);
    }

    // ── Stock Status Helpers ────────────────────────────────────────

    /**
     * Lấy ngưỡng low stock từ settings hoặc dùng mặc định 5.
     * Dễ dàng thay bằng Setting::get('low_stock_threshold', 5) ở Phase sau.
     */
    public static function getLowStockThreshold(): int
    {
        return app(\App\Services\SettingService::class)->lowStockThreshold();
    }

    /**
     * Variant này có hết hàng không?
     */
    public function getIsOutOfStockAttribute(): bool
    {
        return $this->stock === 0;
    }

    /**
     * Variant này có sắp hết hàng không (0 < stock <= threshold)?
     */
    public function getIsLowStockAttribute(): bool
    {
        $threshold = self::getLowStockThreshold();
        return $this->stock > 0 && $this->stock <= $threshold;
    }

    /**
     * Nhãn badge tồn kho cho Admin UI.
     * Trả về: 'out', 'low', 'ok'
     */
    public function getStockStatusAttribute(): string
    {
        if ($this->is_out_of_stock) return 'out';
        if ($this->is_low_stock)    return 'low';
        return 'ok';
    }
}
