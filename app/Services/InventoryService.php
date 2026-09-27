<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;

/**
 * InventoryService
 *
 * Tập trung logic tồn kho để Phase Checkout sau dùng lại.
 * Phase 2: chỉ hỗ trợ Admin update stock.
 * Phase 5: sẽ thêm decrementStock() / incrementStock() với lockForUpdate().
 */
class InventoryService
{
    /**
     * Lấy ngưỡng sắp hết hàng.
     * TODO Phase 6: lấy từ Settings table.
     */
    public function getLowStockThreshold(): int
    {
        return ProductVariant::getLowStockThreshold();
    }

    /**
     * Cập nhật stock nhiều variants cùng lúc (từ màn hình Inventory Admin).
     * Validation stock >= 0 đã được Form Request xử lý trước.
     *
     * @param array $stockData ['variant_id' => stock_value, ...]
     */
    public function bulkUpdateStock(array $stockData): int
    {
        $updated = 0;
        foreach ($stockData as $variantId => $stock) {
            $rows = ProductVariant::where('id', $variantId)
                ->update(['stock' => max(0, (int) $stock)]);
            $updated += $rows;
        }
        return $updated;
    }

    /**
     * Lấy ma trận tồn kho của 1 sản phẩm.
     * Trả về dạng: ['color_id' => ['size_id' => variant]]
     */
    public function getInventoryMatrix(Product $product): array
    {
        $variants = $product->variants()->with(['color', 'size'])->get();
        $matrix   = [];

        foreach ($variants as $variant) {
            $matrix[$variant->color_id][$variant->size_id] = $variant;
        }

        return $matrix;
    }

    /**
     * Lấy danh sách sản phẩm đang low stock để hiển thị cảnh báo.
     */
    public function getLowStockVariants(): Collection
    {
        $threshold = $this->getLowStockThreshold();

        return ProductVariant::with(['product', 'color', 'size'])
            ->where('stock', '<=', $threshold)
            ->orderBy('stock')
            ->get();
    }

    /**
     * Tổng tồn kho theo product_id.
     * Dùng để hiển thị nhanh trong danh sách sản phẩm Admin.
     */
    public function getTotalStockByProduct(int $productId): int
    {
        return ProductVariant::where('product_id', $productId)->sum('stock');
    }
}
