<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * ProductImageService
 *
 * Tập trung logic upload và quản lý ảnh sản phẩm.
 * Không để upload logic rải rác trong Controller.
 *
 * Quy tắc:
 * - Tên file ngẫu nhiên (UUID) để tránh trùng và XSS từ filename
 * - Lưu tại storage/app/public/products/ (→ public/storage/products/)
 * - DB chỉ lưu đường dẫn tương đối: products/abc123.jpg
 */
class ProductImageService
{
    /**
     * Upload một ảnh và tạo record ProductImage.
     *
     * @param  Product       $product
     * @param  UploadedFile  $file
     * @param  bool          $isMain    Có phải ảnh chính không
     * @param  int           $sortOrder Thứ tự trong gallery
     */
    public function upload(
        Product $product,
        UploadedFile $file,
        bool $isMain = false,
        int $sortOrder = 0
    ): ProductImage {
        // Sinh tên file ngẫu nhiên – tránh trùng và tránh path traversal
        $extension = $file->getClientOriginalExtension();
        $filename  = Str::uuid() . '.' . strtolower($extension);

        // Lưu vào storage/app/public/products/
        $path = $file->storeAs('products', $filename, 'public');

        // Nếu đây là ảnh chính → bỏ is_main của ảnh cũ trước
        if ($isMain) {
            $product->images()->update(['is_main' => false]);
        }

        return ProductImage::create([
            'product_id' => $product->id,
            'image_path' => $path,          // Đường dẫn tương đối: products/uuid.jpg
            'is_main'    => $isMain,
            'sort_order' => $sortOrder,
        ]);
    }

    /**
     * Upload nhiều ảnh cùng lúc.
     * Ảnh đầu tiên tự động là is_main nếu product chưa có ảnh nào.
     */
    public function uploadMany(Product $product, array $files): void
    {
        $hasMain = $product->images()->where('is_main', true)->exists();
        $order   = $product->images()->max('sort_order') ?? 0;

        foreach ($files as $index => $file) {
            $isMain = ! $hasMain && $index === 0; // Ảnh đầu tiên làm primary nếu chưa có
            if ($isMain) $hasMain = true;

            $this->upload($product, $file, $isMain, $order + $index + 1);
        }
    }

    /**
     * Xóa ảnh: xóa file trên disk VÀ record DB.
     */
    public function delete(ProductImage $image): void
    {
        // Xóa file vật lý
        Storage::disk('public')->delete($image->image_path);
        $image->delete();
    }

    /**
     * Đặt ảnh làm primary, bỏ primary của ảnh khác.
     */
    public function setMain(ProductImage $image): void
    {
        // Bỏ is_main tất cả ảnh của product này
        $image->product->images()->update(['is_main' => false]);
        // Đặt ảnh được chọn làm primary
        $image->update(['is_main' => true]);
    }

    /**
     * URL fallback khi product chưa có ảnh.
     */
    public static function fallbackUrl(): string
    {
        return asset('assets/images/product-fallback.svg');
    }
}
