<?php

namespace App\Models;

use App\Support\PublicImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ProductImage Model – Ảnh sản phẩm
 *
 * Mỗi Product có nhiều ảnh.
 * Chỉ một ảnh được là_main = true (ảnh chính).
 * Đường dẫn lưu tương đối: products/abc123.webp
 */
class ProductImage extends Model
{
    protected $fillable = [
        'product_id', 'image_path', 'is_main', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['is_main' => 'boolean'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Trả về URL đầy đủ của ảnh.
     * Dùng Storage::url() để tương thích với Laravel storage.
     */
    public function getUrlAttribute(): string
    {
        return PublicImage::url($this->image_path, 'assets/images/product-fallback.svg');
    }
}
