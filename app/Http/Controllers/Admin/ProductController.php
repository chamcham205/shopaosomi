<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Size;
use App\Services\InventoryService;
use App\Services\ProductImageService;
use App\Services\ProductService;
use Illuminate\Http\Request;

/**
 * Admin\ProductController
 *
 * CRUD sản phẩm. Logic phức tạp delegate sang Services.
 * Không có SQL raw, không trust price/stock từ JS.
 */
class ProductController extends Controller
{
    public function __construct(
        private ProductService      $productService,
        private ProductImageService $imageService,
        private InventoryService    $inventoryService,
    ) {}

    /**
     * Danh sách sản phẩm với search + filter + pagination.
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'images'])
            ->withCount('variants')
            ->withSum('variants', 'stock'); // Tổng tồn kho qua DB aggregate

        // Search theo tên hoặc SKU
        if ($search = $request->input('search')) {
            $query->where(fn($q) =>
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
            );
        }

        // Filter theo danh mục
        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        // Filter theo trạng thái
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Filter sắp hết hàng
        if ($request->input('low_stock')) {
            $threshold = $this->inventoryService->getLowStockThreshold();
            $query->whereHas('variants', fn($q) =>
                $q->where('stock', '<=', $threshold)->where('stock', '>', 0)
            );
        }

        $products   = $query->latest()->paginate(20)->withQueryString();
        $categories = Category::where('status', 'active')->orderBy('name')->get();
        $threshold = $this->inventoryService->getLowStockThreshold();

        return view('admin.products.index', compact('products', 'categories','threshold'));
    }

    public function create()
    {
        $categories = Category::where('status', 'active')->orderBy('sort_order')->get();
        $colors     = Color::where('status', 'active')->orderBy('name')->get();
        $sizes      = Size::where('status', 'active')->orderBy('sort_order')->get();

        return view('admin.products.create', compact('categories', 'colors', 'sizes'));
    }

    public function store(StoreProductRequest $request)
    {
        $data    = $request->validated();
        $product = $this->productService->create($data);

        // Upload ảnh nếu có
        if ($request->hasFile('images')) {
            $this->imageService->uploadMany($product, $request->file('images'));
        }

        // Tạo variants từ color + size được chọn
        if (! empty($data['color_ids']) && ! empty($data['size_ids'])) {
            $this->productService->syncVariants(
                $product,
                $data['color_ids'],
                $data['size_ids'],
                $data['stock'] ?? []
            );
        }

        return redirect()->route('admin.products.index')
            ->with('success', "Sản phẩm \"{$product->name}\" đã được tạo thành công.");
    }

    public function show(Product $product)
    {
        $product->load(['category', 'images', 'variants.color', 'variants.size']);
        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $product->load(['images', 'variants.color', 'variants.size']);
        $categories = Category::where('status', 'active')->orderBy('sort_order')->get();
        $colors     = Color::where('status', 'active')->orderBy('name')->get();
        $sizes      = Size::where('status', 'active')->orderBy('sort_order')->get();

        // Danh sách color/size hiện tại của product
        $selectedColorIds = $product->variants->pluck('color_id')->unique()->values();
        $selectedSizeIds  = $product->variants->pluck('size_id')->unique()->values();

        return view('admin.products.edit', compact(
            'product', 'categories', 'colors', 'sizes',
            'selectedColorIds', 'selectedSizeIds'
        ));
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->validated();
        $this->productService->update($product, $data);

        // Upload ảnh mới nếu có
        if ($request->hasFile('images')) {
            $this->imageService->uploadMany($product, $request->file('images'));
        }

        // Sync variants
        if (! empty($data['color_ids']) && ! empty($data['size_ids'])) {
            $this->productService->syncVariants(
                $product,
                $data['color_ids'],
                $data['size_ids'],
                $data['stock'] ?? []
            );
        }

        return redirect()->route('admin.products.edit', $product)
            ->with('success', 'Sản phẩm đã được cập nhật.');
    }

    /**
     * Xóa mềm sản phẩm (SoftDelete).
     */
    public function destroy(Product $product)
    {
        $name = $product->name;
        $product->delete(); // SoftDelete – không xóa variants/images
        return redirect()->route('admin.products.index')
            ->with('success', "Sản phẩm \"{$name}\" đã được xóa.");
    }

    /**
     * Khôi phục sản phẩm đã xóa mềm.
     */
    public function restore(int $id)
    {
        $product = Product::withTrashed()->findOrFail($id);
        $product->restore();
        return redirect()->route('admin.products.index')
            ->with('success', "Sản phẩm \"{$product->name}\" đã được khôi phục.");
    }

    /**
     * Bật/Tắt trạng thái sản phẩm nhanh.
     */
    public function toggleStatus(Product $product)
    {
        $product->status = $product->status === 'active' ? 'inactive' : 'active';
        $product->save();
        return back()->with('success', 'Đã cập nhật trạng thái sản phẩm.');
    }

    /**
     * Đặt ảnh làm ảnh chính.
     */
    public function setMainImage(Product $product, ProductImage $image)
    {
        abort_unless($image->product_id === $product->id, 404);
        $this->imageService->setMain($image);
        return back()->with('success', 'Đã đặt làm ảnh chính.');
    }

    /**
     * Xóa ảnh sản phẩm.
     */
    public function destroyImage(Product $product, ProductImage $image)
    {
        abort_unless($image->product_id === $product->id, 404);
        $this->imageService->delete($image);
        return back()->with('success', 'Đã xóa ảnh sản phẩm.');
    }
}
