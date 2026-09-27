<?php

namespace App\Services;

use App\Models\Color;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Size;
use Illuminate\Support\Str;

/**
 * ProductService
 *
 * Xử lý logic tạo/sửa sản phẩm phức tạp.
 * Đặc biệt: tạo Variants từ danh sách Color + Size.
 */
class ProductService
{
    /**
     * Tạo product mới từ validated data.
     * Không xử lý ảnh ở đây (ProductImageService lo).
     */
    public function create(array $data): Product
    {
        // Auto-generate slug nếu chưa có
        if (empty($data['slug'])) {
            $data['slug'] = $this->generateUniqueSlug($data['name']);
        }

        return Product::create($data);
    }

    /**
     * Cập nhật product.
     */
    public function update(Product $product, array $data): Product
    {
        if (! empty($data['name']) && empty($data['slug'])) {
            // Chỉ re-generate slug nếu tên thay đổi và không có slug override
            if ($data['name'] !== $product->name) {
                $data['slug'] = $this->generateUniqueSlug($data['name'], $product->id);
            }
        }

        $product->update($data);
        return $product->fresh();
    }

    /**
     * Tạo/đồng bộ variants từ danh sách color_ids + size_ids.
     *
     * Logic:
     *   Mỗi combination (color × size) → 1 ProductVariant.
     *   Nếu variant đã tồn tại → bỏ qua (không duplicate).
     *   Nếu variant mới → tạo với stock = 0, SKU tự sinh.
     *
     * @param  Product  $product
     * @param  array    $colorIds   Mảng color_id được chọn
     * @param  array    $sizeIds    Mảng size_id được chọn
     * @param  array    $stockMap   ['colorId_sizeId' => stock] – từ form
     */
    public function syncVariants(
        Product $product,
        array $colorIds,
        array $sizeIds,
        array $stockMap = []
    ): void {
        foreach ($colorIds as $colorId) {
            foreach ($sizeIds as $sizeId) {
                $stockKey = "{$colorId}_{$sizeId}";
                $stock    = max(0, (int) ($stockMap[$stockKey] ?? 0));

                // Tạo nếu chưa tồn tại (tránh duplicate – unique constraint giữ an toàn)
                $variant = ProductVariant::firstOrCreate(
                    [
                        'product_id' => $product->id,
                        'color_id'   => $colorId,
                        'size_id'    => $sizeId,
                    ],
                    [
                        'sku'   => $this->generateVariantSku($product, $colorId, $sizeId),
                        'stock' => $stock,
                    ]
                );

                // Nếu variant đã tồn tại và form truyền stock mới → update
                if (! $variant->wasRecentlyCreated && isset($stockMap[$stockKey])) {
                    $variant->update(['stock' => $stock]);
                }
            }
        }
    }

    /**
     * Sinh SKU cho variant theo format: {PRODUCT_SKU}-{COLOR_CODE}-{SIZE}
     * VD: OXF-001-WHT-M
     */
    public function generateVariantSku(Product $product, int $colorId, int $sizeId): string
    {
        $color = Color::find($colorId);
        $size  = Size::find($sizeId);

        $colorCode = $color ? strtoupper(substr(Str::slug($color->name, ''), 0, 3)) : 'C' . $colorId;
        $sizeName  = $size  ? strtoupper($size->name)                                : 'S' . $sizeId;

        $sku = "{$product->sku}-{$colorCode}-{$sizeName}";

        // Đảm bảo unique
        $count = 0;
        $base  = $sku;
        while (ProductVariant::where('sku', $sku)->exists()) {
            $count++;
            $sku = "{$base}-{$count}";
        }

        return $sku;
    }

    /**
     * Sinh slug unique cho product.
     * Nếu bị trùng → thêm số cuối: so-mi-oxford-2, so-mi-oxford-3...
     */
    public function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $slug  = Str::slug($name);
        $base  = $slug;
        $count = 1;

        while (true) {
            $query = Product::withTrashed()->where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
            if (! $query->exists()) break;
            $slug = "{$base}-" . (++$count);
        }

        return $slug;
    }
}
