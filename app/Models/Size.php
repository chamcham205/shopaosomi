<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Size Model – Kích cỡ
 */
class Size extends Model
{
    protected $fillable = ['name', 'sort_order', 'status'];

    /**
     * Một kích cỡ có thể có trong nhiều biến thể sản phẩm.
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * Kiểm tra size đang được dùng trong variant không.
     */
    public function isInUse(): bool
    {
        return $this->variants()->exists();
    }
}
