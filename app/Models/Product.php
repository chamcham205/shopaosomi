<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Product Model – Sản phẩm áo sơ mi
 *
 * Dùng SoftDeletes: xóa sản phẩm không mất lịch sử đơn hàng.
 *
 * Relationships:
 *   Product belongsTo  Category
 *   Product hasMany    ProductImage
 *   Product hasMany    ProductVariant → chứa stock
 *
 * Tại sao stock KHÔNG nằm ở Product mà nằm ở ProductVariant?
 *   Vì một áo sơ mi Oxford có thể có 6 màu × 5 size = 30 variants,
 *   mỗi variant có tồn kho riêng. Tổng tồn kho = SUM(variants.stock).
 */
class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id', 'name', 'slug', 'sku',
        'short_description', 'description',
        'material', 'fit', 'brand',
        'price', 'sale_price', 'sale_start', 'sale_end',
        'featured', 'status',
    ];

    protected function casts(): array
    {
        return [
            'price'      => 'decimal:2',
            'sale_price' => 'decimal:2',
            'sale_start' => 'datetime',
            'sale_end'   => 'datetime',
            'featured'   => 'boolean',
        ];
    }

    // ── Relationships ───────────────────────────────────────────────

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Tất cả ảnh của sản phẩm, sắp xếp theo sort_order.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    /**
     * Ảnh chính (is_main = true). Dùng first() để lấy một ảnh.
     */
    public function mainImage(): HasMany
    {
        return $this->hasMany(ProductImage::class)->where('is_main', true);
    }

    /**
     * Tất cả biến thể (Product + Color + Size) – chứa stock.
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function wishlistItems() { return $this->hasMany(Wishlist::class); }
    public function reviews() { return $this->hasMany(Review::class); }
    public function approvedReviews() { return $this->hasMany(Review::class)->where('status','approved'); }
    public function getAverageRatingAttribute(): float { return round((float)$this->approvedReviews()->avg('rating'),1); }
    public function getApprovedReviewsCountAttribute(): int { return $this->approvedReviews()->count(); }

    // ── Sale Price Logic (BUSINESS_RULES §PRICE-01) ─────────────────

    /**
     * Giá hiện tại áp dụng cho sản phẩm.
     *
     * Logic:
     *   IF sale_price != null AND now BETWEEN sale_start AND sale_end
     *     → giá = sale_price
     *   ELSE
     *     → giá = price
     *
     * sale_start/sale_end có thể NULL:
     *   - Nếu cả hai NULL → sale áp dụng ngay (không giới hạn thời gian)
     *   - Nếu chỉ sale_start NULL → áp dụng đến sale_end
     *   - Nếu chỉ sale_end NULL → áp dụng từ sale_start vô thời hạn
     */
    public function getCurrentPriceAttribute(): float
    {
        if (! $this->sale_price) {
            return (float) $this->price;
        }

        $now = now();

        $afterStart  = $this->sale_start ? $now->gte($this->sale_start) : true;
        $beforeEnd   = $this->sale_end   ? $now->lte($this->sale_end)   : true;

        return ($afterStart && $beforeEnd)
            ? (float) $this->sale_price
            : (float) $this->price;
    }

    /**
     * Kiểm tra sản phẩm có đang sale không.
     */
    public function getIsOnSaleAttribute(): bool
    {
        return $this->current_price < (float) $this->price;
    }

    /**
     * Phần trăm giảm giá.
     * VD: price=699000, sale_price=549000 → 21%
     */
    public function getDiscountPercentAttribute(): int
    {
        if (! $this->is_on_sale || ! $this->price) {
            return 0;
        }
        return (int) round((1 - $this->current_price / $this->price) * 100);
    }

    // ── Inventory Helpers ───────────────────────────────────────────

    /**
     * Tổng tồn kho = SUM của tất cả variants.stock.
     * Không cache ở đây để luôn có dữ liệu fresh.
     */
    public function getTotalStockAttribute(): int
    {
        return $this->variants->sum('stock');
    }

    /**
     * Kiểm tra sản phẩm có còn hàng không (ít nhất 1 variant > 0).
     */
    public function getInStockAttribute(): bool
    {
        return $this->variants->where('stock', '>', 0)->isNotEmpty();
    }

    // ── Image Helpers ───────────────────────────────────────────────

    /**
     * Lấy đường dẫn ảnh chính. Fallback về ảnh đầu tiên nếu không có is_main.
     */
    public function getPrimaryImagePathAttribute(): ?string
    {
        $main = $this->images->firstWhere('is_main', true);
        return $main ? $main->image_path : $this->images->first()?->image_path;
    }

    public function getPrimaryImageUrlAttribute(): string
    {
        $main = $this->images->firstWhere('is_main', true) ?? $this->images->first();

        return $main?->url ?? asset('assets/images/product-fallback.svg');
    }

    // ── Slug Helper ─────────────────────────────────────────────────

    public static function generateSlug(string $name): string
    {
        return Str::slug($name);
    }
}
