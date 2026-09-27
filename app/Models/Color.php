<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Color Model – Màu sắc
 */
class Color extends Model
{
    protected $fillable = ['name', 'hex_code', 'status'];

    /**
     * Một màu có thể có trong nhiều biến thể sản phẩm.
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * Kiểm tra màu đang được dùng trong variant nào không.
     * Dùng để chặn xóa an toàn.
     */
    public function isInUse(): bool
    {
        return $this->variants()->exists();
    }
}
