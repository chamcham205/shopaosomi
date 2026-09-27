<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\InventoryService;
use Illuminate\Http\Request;

/**
 * Admin\InventoryController
 *
 * Quản lý tồn kho dạng ma trận: Color × Size.
 * Hiển thị dạng bảng dễ nhìn, Admin cập nhật stock nhanh.
 */
class InventoryController extends Controller
{
    public function __construct(private InventoryService $inventoryService) {}

    /**
     * Tổng quan tồn kho – danh sách sản phẩm và cảnh báo low stock.
     */
    public function index(Request $request)
    {
        $threshold = $this->inventoryService->getLowStockThreshold();

        // Sản phẩm có ít nhất 1 variant sắp hết hoặc hết hàng
        $lowStockVariants = $this->inventoryService->getLowStockVariants();

        $query = Product::with(['category'])
            ->withSum('variants', 'stock')
            ->withCount('variants');

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $products = $query->latest()->paginate(20)->withQueryString();

        return view('admin.inventory.index', compact(
            'products', 'lowStockVariants', 'threshold'
        ));
    }

    /**
     * Ma trận tồn kho của 1 sản phẩm.
     * Hiển thị dạng bảng: rows = màu, columns = size.
     */
    public function show(Product $product)
    {
        $product->load(['variants.color', 'variants.size']);

        // Lấy matrix ['color_id']['size_id'] = variant
        $matrix = $this->inventoryService->getInventoryMatrix($product);

        // Danh sách màu và size đang có trong product
        $colors = $product->variants->pluck('color')->unique('id')->sortBy('name');
        $sizes  = $product->variants->pluck('size')->unique('id')->sortBy('sort_order');

        $threshold = $this->inventoryService->getLowStockThreshold();

        return view('admin.inventory.show', compact(
            'product', 'matrix', 'colors', 'sizes', 'threshold'
        ));
    }

    /**
     * Cập nhật stock nhiều variants cùng lúc.
     * Validation: stock >= 0, không trust client.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'stock'   => ['required', 'array'],
            'stock.*' => ['required', 'integer', 'min:0'],
        ], [
            'stock.*.min'     => 'Tồn kho không được âm.',
            'stock.*.integer' => 'Tồn kho phải là số nguyên.',
        ]);

        // Đảm bảo các variant_id thuộc về product này (security check)
        $variantIds = ProductVariant::where('product_id', $product->id)
            ->pluck('id')
            ->toArray();

        $stockData = [];
        foreach ($validated['stock'] as $variantId => $stock) {
            if (in_array((int) $variantId, $variantIds)) {
                $stockData[$variantId] = $stock;
            }
        }

        $updated = $this->inventoryService->bulkUpdateStock($stockData);

        return redirect()->route('admin.inventory.show', $product)
            ->with('success', "Đã cập nhật tồn kho cho {$updated} biến thể.");
    }
}
