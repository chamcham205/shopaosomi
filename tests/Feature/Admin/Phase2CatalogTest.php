<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Size;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Phase 2 Feature Tests – Product Catalog
 *
 * Bao phủ 18 test cases theo yêu cầu Phase 2.
 * Dùng môi trường testing an toàn (RefreshDatabase + storage fake).
 */
class Phase2CatalogTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ─────────────────────────────────────────────────────────

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'status' => 'active']);
    }

    private function customer(): User
    {
        return User::factory()->create(['role' => 'customer', 'status' => 'active']);
    }

    private function category(): Category
    {
        return Category::create([
            'name'       => 'Sơ mi Oxford Test',
            'slug'       => 'so-mi-oxford-test',
            'status'     => 'active',
            'sort_order' => 1,
        ]);
    }

    private function color(string $name = 'Trắng Test', string $hex = '#FFFFFF'): Color
    {
        return Color::create(['name' => $name, 'hex_code' => $hex, 'status' => 'active']);
    }

    private function makeSize(string $name = 'M'): Size
    {
        return Size::create(['name' => $name, 'sort_order' => 2, 'status' => 'active']);
    }

    private function product(?Category $cat = null): Product
    {
        $cat ??= $this->category();
        return Product::create([
            'category_id' => $cat->id,
            'name'        => 'Sơ mi Test Product',
            'slug'        => 'so-mi-test-product',
            'sku'         => 'TEST-SKU-001',
            'price'       => 500000,
            'status'      => 'active',
        ]);
    }

    // ══════════════════════════════════════════════════════════════════
    // 1. Admin truy cập products được
    // ══════════════════════════════════════════════════════════════════
    public function test_admin_can_access_products_index(): void
    {
        $this->actingAs($this->admin())
             ->get(route('admin.products.index'))
             ->assertStatus(200)
             ->assertSee('Danh sách Sản phẩm');
    }

    // ══════════════════════════════════════════════════════════════════
    // 2. Customer truy cập admin products bị 403
    // ══════════════════════════════════════════════════════════════════
    public function test_customer_cannot_access_admin_products(): void
    {
        $this->actingAs($this->customer())
             ->get(route('admin.products.index'))
             ->assertStatus(403);
    }

    // ══════════════════════════════════════════════════════════════════
    // 3. Admin tạo Category thành công
    // ══════════════════════════════════════════════════════════════════
    public function test_admin_can_create_category(): void
    {
        $this->actingAs($this->admin())
             ->post(route('admin.categories.store'), [
                 'name'       => 'Sơ mi Công sở Test',
                 'status'     => 'active',
                 'sort_order' => 1,
             ])
             ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', ['name' => 'Sơ mi Công sở Test']);
    }

    // ══════════════════════════════════════════════════════════════════
    // 4. Admin tạo Color thành công
    // ══════════════════════════════════════════════════════════════════
    public function test_admin_can_create_color(): void
    {
        $this->actingAs($this->admin())
             ->post(route('admin.colors.store'), [
                 'name'     => 'Navy Test',
                 'hex_code' => '#172554',
                 'status'   => 'active',
             ])
             ->assertRedirect(route('admin.colors.index'));

        $this->assertDatabaseHas('colors', ['name' => 'Navy Test', 'hex_code' => '#172554']);
    }

    // ══════════════════════════════════════════════════════════════════
    // 5. Hex Color validation – sai định dạng bị chặn
    // ══════════════════════════════════════════════════════════════════
    public function test_invalid_hex_color_is_rejected(): void
    {
        $this->actingAs($this->admin())
             ->post(route('admin.colors.store'), [
                 'name'     => 'Bad Color',
                 'hex_code' => 'NOTAHEX',  // Sai format
                 'status'   => 'active',
             ])
             ->assertSessionHasErrors(['hex_code']);

        $this->assertDatabaseMissing('colors', ['name' => 'Bad Color']);
    }

    // ══════════════════════════════════════════════════════════════════
    // 6. Admin tạo Size thành công
    // ══════════════════════════════════════════════════════════════════
    public function test_admin_can_create_size(): void
    {
        $this->actingAs($this->admin())
             ->post(route('admin.sizes.store'), [
                 'name'       => 'XXL',
                 'sort_order' => 5,
                 'status'     => 'active',
             ])
             ->assertRedirect(route('admin.sizes.index'));

        $this->assertDatabaseHas('sizes', ['name' => 'XXL']);
    }

    // ══════════════════════════════════════════════════════════════════
    // 7. Admin tạo Product thành công
    // ══════════════════════════════════════════════════════════════════
    public function test_admin_can_create_product(): void
    {
        $cat = $this->category();

        $this->actingAs($this->admin())
             ->post(route('admin.products.store'), [
                 'category_id' => $cat->id,
                 'name'        => 'Sơ mi Oxford Premium',
                 'sku'         => 'SM-OXF-TEST-01',
                 'price'       => 699000,
                 'status'      => 'active',
                 'featured'    => false,
             ])
             ->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', [
            'name' => 'Sơ mi Oxford Premium',
            'sku'  => 'SM-OXF-TEST-01',
        ]);
    }

    // ══════════════════════════════════════════════════════════════════
    // 8. Product SKU duplicate bị chặn
    // ══════════════════════════════════════════════════════════════════
    public function test_duplicate_product_sku_is_rejected(): void
    {
        $cat = $this->category();
        $this->product($cat); // SKU: TEST-SKU-001

        $this->actingAs($this->admin())
             ->post(route('admin.products.store'), [
                 'category_id' => $cat->id,
                 'name'        => 'Sản phẩm khác',
                 'sku'         => 'TEST-SKU-001', // Trùng SKU
                 'price'       => 399000,
                 'status'      => 'active',
                 'featured'    => false,
             ])
             ->assertSessionHasErrors(['sku']);
    }

    // ══════════════════════════════════════════════════════════════════
    // 9. Product có nhiều Images (DB level)
    // ══════════════════════════════════════════════════════════════════
    public function test_product_can_have_multiple_images(): void
    {
        $product = $this->product();

        $product->images()->createMany([
            ['image_path' => 'products/img1.jpg', 'is_main' => true,  'sort_order' => 0],
            ['image_path' => 'products/img2.jpg', 'is_main' => false, 'sort_order' => 1],
            ['image_path' => 'products/img3.jpg', 'is_main' => false, 'sort_order' => 2],
        ]);

        $this->assertCount(3, $product->fresh()->images);
        $this->assertEquals(1, $product->images()->where('is_main', true)->count());
    }

    // ══════════════════════════════════════════════════════════════════
    // 10. Product tạo Variant đúng Color + Size
    // ══════════════════════════════════════════════════════════════════
    public function test_product_creates_correct_variants(): void
    {
        $product = $this->product();
        $color   = $this->color();
        $size    = $this->makeSize();

        ProductVariant::create([
            'product_id' => $product->id,
            'color_id'   => $color->id,
            'size_id'    => $size->id,
            'sku'        => 'TEST-SKU-001-WHT-M',
            'stock'      => 10,
        ]);

        $variant = $product->variants->first();
        $this->assertEquals($color->id, $variant->color_id);
        $this->assertEquals($size->id, $variant->size_id);
        $this->assertEquals(10, $variant->stock);
    }

    // ══════════════════════════════════════════════════════════════════
    // 11. Duplicate Variant bị chặn bởi DB unique constraint
    // ══════════════════════════════════════════════════════════════════
    public function test_duplicate_variant_is_rejected(): void
    {
        $product = $this->product();
        $color   = $this->color();
        $size    = $this->makeSize();

        ProductVariant::create([
            'product_id' => $product->id, 'color_id' => $color->id,
            'size_id' => $size->id, 'sku' => 'VAR-001', 'stock' => 5,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        ProductVariant::create([
            'product_id' => $product->id, 'color_id' => $color->id,
            'size_id' => $size->id, 'sku' => 'VAR-002', 'stock' => 3,
        ]);
    }

    // ══════════════════════════════════════════════════════════════════
    // 12. Stock không thể âm – kiểm tra qua Application Validation
    //     (MariaDB strict mode không throw cho UNSIGNED trong SQLite test,
    //      nhưng Application validate trước khi xuống DB)
    // ══════════════════════════════════════════════════════════════════
    public function test_stock_cannot_be_negative(): void
    {
        $product = $this->product();
        $color   = $this->color();
        $size    = $this->makeSize();

        ProductVariant::create([
            'product_id' => $product->id, 'color_id' => $color->id,
            'size_id' => $size->id, 'sku' => 'NEG-001', 'stock' => 5,
        ]);
        $variant = $product->variants->first();

        // Cập nhật tồn kho với giá trị âm qua Admin route → bị chặn bởi validation
        $this->actingAs($this->admin())
             ->post(route('admin.inventory.update', $product), [
                 'stock' => [$variant->id => -10],
             ])
             ->assertSessionHasErrors(['stock.' . $variant->id]);

        // Stock vẫn giữ nguyên giá trị cũ
        $this->assertEquals(5, $variant->fresh()->stock);
    }

    // ══════════════════════════════════════════════════════════════════
    // 13. Update Inventory hoạt động
    // ══════════════════════════════════════════════════════════════════
    public function test_admin_can_update_inventory(): void
    {
        $product = $this->product();
        $color   = $this->color();
        $size    = $this->makeSize();

        $variant = ProductVariant::create([
            'product_id' => $product->id, 'color_id' => $color->id,
            'size_id' => $size->id, 'sku' => 'INV-001', 'stock' => 5,
        ]);

        $this->actingAs($this->admin())
             ->post(route('admin.inventory.update', $product), [
                 'stock' => [$variant->id => 20],
             ])
             ->assertRedirect();

        $this->assertEquals(20, $variant->fresh()->stock);
    }

    // ══════════════════════════════════════════════════════════════════
    // 14. Low Stock detection
    // ══════════════════════════════════════════════════════════════════
    public function test_low_stock_detection(): void
    {
        $product = $this->product();
        $color   = $this->color();
        $size    = $this->makeSize();

        // stock = 3 → sắp hết (threshold = 5)
        $variant = ProductVariant::create([
            'product_id' => $product->id, 'color_id' => $color->id,
            'size_id' => $size->id, 'sku' => 'LOW-001', 'stock' => 3,
        ]);

        $this->assertTrue($variant->is_low_stock);
        $this->assertFalse($variant->is_out_of_stock);
        $this->assertEquals('low', $variant->stock_status);

        // stock = 0 → hết hàng
        $variant->update(['stock' => 0]);
        $this->assertTrue($variant->fresh()->is_out_of_stock);
        $this->assertEquals('out', $variant->fresh()->stock_status);
    }

    // ══════════════════════════════════════════════════════════════════
    // 15. Sale current price đúng theo thời gian
    // ══════════════════════════════════════════════════════════════════
    public function test_sale_price_logic_is_time_aware(): void
    {
        $cat = $this->category();

        // Đang trong sale
        $onSale = Product::create([
            'category_id' => $cat->id, 'name' => 'On Sale',
            'slug' => 'on-sale', 'sku' => 'SALE-001',
            'price' => 699000, 'sale_price' => 549000,
            'sale_start' => now()->subHour(), 'sale_end' => now()->addHour(),
            'status' => 'active',
        ]);
        $this->assertEquals(549000, $onSale->current_price);
        $this->assertTrue($onSale->is_on_sale);
        $this->assertEquals(21, $onSale->discount_percent);

        // Sale đã hết hạn
        $expired = Product::create([
            'category_id' => $cat->id, 'name' => 'Expired Sale',
            'slug' => 'expired-sale', 'sku' => 'SALE-002',
            'price' => 699000, 'sale_price' => 549000,
            'sale_start' => now()->subDays(2), 'sale_end' => now()->subDay(),
            'status' => 'active',
        ]);
        $this->assertEquals(699000, $expired->current_price);
        $this->assertFalse($expired->is_on_sale);

        // Không có sale
        $noSale = Product::create([
            'category_id' => $cat->id, 'name' => 'No Sale',
            'slug' => 'no-sale', 'sku' => 'SALE-003',
            'price' => 500000, 'status' => 'active',
        ]);
        $this->assertEquals(500000, $noSale->current_price);
    }

    // ══════════════════════════════════════════════════════════════════
    // 16. Product Soft Delete
    // ══════════════════════════════════════════════════════════════════
    public function test_product_soft_delete(): void
    {
        $product = $this->product();

        $this->actingAs($this->admin())
             ->delete(route('admin.products.destroy', $product))
             ->assertRedirect(route('admin.products.index'));

        // Soft deleted: không tìm thấy qua query bình thường
        $this->assertNull(Product::find($product->id));

        // Nhưng vẫn tồn tại với withTrashed
        $this->assertNotNull(Product::withTrashed()->find($product->id));
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    // ══════════════════════════════════════════════════════════════════
    // 17. Product Restore sau soft delete
    // ══════════════════════════════════════════════════════════════════
    public function test_product_restore(): void
    {
        $product = $this->product();
        $product->delete(); // Soft delete

        $this->assertNull(Product::find($product->id));

        $this->actingAs($this->admin())
             ->patch(route('admin.products.restore', $product->id))
             ->assertRedirect(route('admin.products.index'));

        $this->assertNotNull(Product::find($product->id));
    }

    // ══════════════════════════════════════════════════════════════════
    // 18. Upload invalid image bị chặn
    // ══════════════════════════════════════════════════════════════════
    public function test_upload_invalid_image_is_rejected(): void
    {
        Storage::fake('public');
        $cat = $this->category();

        $this->actingAs($this->admin())
             ->post(route('admin.products.store'), [
                 'category_id' => $cat->id,
                 'name'        => 'Test Upload Fail',
                 'sku'         => 'UPL-001',
                 'price'       => 500000,
                 'status'      => 'active',
                 'featured'    => false,
                 'images'      => [
                     UploadedFile::fake()->create('virus.exe', 100, 'application/octet-stream'),
                 ],
             ])
             ->assertSessionHasErrors(['images.0']);
    }
}
