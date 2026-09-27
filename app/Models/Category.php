<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Category Model
 *
 * Danh mục sản phẩm. Không hỗ trợ đa cấp (V1).
 *
 * Relationship:
 *   Category hasMany Product
 */
class Category extends Model
{
    protected $fillable = [
        'name', 'slug', 'image', 'description', 'status', 'sort_order',
    ];

    // ── Relationships ───────────────────────────────────────────────

    /**
     * Một danh mục có nhiều sản phẩm.
     * Dùng withCount('products') để đếm SP nhanh trong admin.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    // ── Helpers ─────────────────────────────────────────────────────

    /**
     * Tự động sinh slug từ name nếu chưa có.
     */
    public static function generateSlug(string $name): string
    {
        return Str::slug($name);
    }

    /**
     * Kiểm tra danh mục có đang có sản phẩm không.
     * Dùng để chặn xóa khi còn sản phẩm (kể cả soft-deleted).
     */
    public function hasProducts(): bool
    {
        return $this->products()->withTrashed()->exists();
    }
}
