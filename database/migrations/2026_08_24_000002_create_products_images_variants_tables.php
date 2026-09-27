<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 2 – Migration: products, product_images, product_variants
 *
 * Thứ tự tạo quan trọng vì FK:
 *   categories → products → product_images
 *                         → product_variants (cần colors, sizes)
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── products ───────────────────────────────────────────────
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();

            $table->string('name', 191)->notNull();
            $table->string('slug', 191)->unique()->notNull();
            $table->string('sku', 100)->unique()->notNull();      // products.sku UNIQUE (Phase 0)
            $table->string('short_description', 500)->nullable(); // Mô tả ngắn cho card sản phẩm
            $table->longText('description')->nullable();          // Mô tả chi tiết (HTML)
            $table->string('material', 191)->nullable();          // Cotton 100%, Linen, Oxford...
            $table->string('fit', 100)->nullable();               // Slim Fit, Regular, Oversize
            $table->string('brand', 100)->nullable();

            // Giá – logic sale price nằm trong Product Model method
            $table->decimal('price', 12, 2)->notNull();
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->dateTime('sale_start')->nullable();
            $table->dateTime('sale_end')->nullable();

            $table->boolean('featured')->default(false);
            $table->enum('status', ['active', 'inactive', 'draft'])->default('active');

            $table->timestamps();
            $table->softDeletes(); // deleted_at – Soft Delete theo Phase 0

            $table->index(['status', 'featured', 'category_id']);
        });

        // ── product_images ─────────────────────────────────────────
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();

            // Lưu đường dẫn tương đối, VD: products/abc123.jpg
            // KHÔNG lưu C:\... hay URL tuyệt đối
            $table->string('image_path', 255)->notNull();

            // Theo schema Phase 0: tên cột là is_main
            $table->boolean('is_main')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // ── product_variants ⭐ QUAN TRỌNG ─────────────────────────
        // Mỗi dòng = 1 tổ hợp Product + Color + Size
        // stock nằm ở đây – KHÔNG phải products.stock
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('color_id')->constrained('colors')->restrictOnDelete();
            $table->foreignId('size_id')->constrained('sizes')->restrictOnDelete();

            // product_variants.sku UNIQUE (Phase 0)
            $table->string('sku', 100)->unique()->notNull();

            // Tên cột tồn kho CHÍNH THỨC: stock (Phase 0, Rule INV-02)
            // UNSIGNED → không thể âm ở DB level
            $table->unsignedInteger('stock')->default(0);

            $table->timestamps();

            // Không được trùng tổ hợp Product + Color + Size (Phase 0)
            $table->unique(['product_id', 'color_id', 'size_id'], 'uq_variant');

            // Index để query low stock nhanh
            $table->index(['product_id', 'stock']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('products');
    }
};
