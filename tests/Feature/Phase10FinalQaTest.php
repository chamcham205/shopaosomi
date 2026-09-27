<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Banner;
use App\Models\Order;
use App\Models\ProductImage;
use App\Models\Review;
use App\Models\ShippingZone;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Phase10FinalQaTest extends TestCase
{
    use RefreshDatabase;

    public function test_missing_seeded_product_image_uses_local_fallback(): void
    {
        Storage::fake('public');

        $image = new ProductImage(['image_path' => 'products/demo/missing.png']);
        $banner = new Banner(['image_path' => 'banners/missing.png']);

        $this->assertStringEndsWith('/assets/images/product-fallback.svg', $image->url);
        $this->assertStringEndsWith('/assets/images/banner-fallback.svg', $banner->image_url);

        Storage::disk('public')->put('products/existing.svg', file_get_contents(public_path('assets/images/product-fallback.svg')));
        Storage::disk('public')->put('banners/existing.svg', file_get_contents(public_path('assets/images/banner-fallback.svg')));
        $storedProduct = new ProductImage(['image_path' => 'storage/products/existing.svg']);
        $storedBanner = new Banner(['image_path' => '/storage/banners/existing.svg']);

        $this->assertStringEndsWith('/storage/products/existing.svg', $storedProduct->url);
        $this->assertStringEndsWith('/storage/banners/existing.svg', $storedBanner->image_url);
        $this->assertStringNotContainsString('/storage/storage/', $storedProduct->url.$storedBanner->image_url);
    }

    public function test_final_demo_seeding_is_idempotent_and_reviews_have_completed_purchases(): void
    {
        Storage::fake('public');

        $this->seed(DatabaseSeeder::class);

        $missingImage = ProductImage::query()->orderBy('id')->firstOrFail();
        $missingImage->update(['image_path' => 'products/demo/shirt-oxford.jpg']);
        $validImage = ProductImage::query()->orderBy('id')->skip(1)->firstOrFail();
        Storage::disk('public')->put('products/uploaded.svg', file_get_contents(public_path('assets/images/product-fallback.svg')));
        $validImage->update(['image_path' => 'storage/products/uploaded.svg']);
        Banner::query()->firstOrFail()->update(['image_path' => 'banners/missing.jpg']);

        $this->seed(DatabaseSeeder::class);

        $this->assertSame('products/demo/shirt-1.png', $missingImage->fresh()->image_path);
        $this->assertSame('storage/products/uploaded.svg', $validImage->fresh()->image_path);
        $this->assertSame('banners/demo-hero.png', Banner::query()->firstOrFail()->image_path);

        $this->assertSame(15, \App\Models\Product::count());
        $this->assertSame(2, Coupon::count());
        $this->assertSame(3, ShippingZone::count());
        $this->assertSame(4, Order::count());
        $this->assertSame(2, Review::count());
        $this->assertFalse(Review::whereHas('orderItem.order', fn ($query) => $query->where('status', '!=', 'completed'))->exists());
        $this->assertSame(Order::count(), Order::pluck('order_code')->unique()->count());

        $admin = \App\Models\User::where('email', 'admin@shirtstore.local')->firstOrFail();
        $this->actingAs($admin)->get('/admin/reviews')->assertOk()->assertSee('Chất vải thoáng');
    }

    public function test_seeded_customer_and_admin_demo_pages_render(): void
    {
        Storage::fake('public');
        $this->seed(DatabaseSeeder::class);

        $product = \App\Models\Product::firstOrFail();
        foreach (['/', '/products', '/products/'.$product->slug, '/login', '/register', '/forgot-password'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get('/')->assertDontSee('Miễn phí vận chuyển cho đơn hàng đủ điều kiện.');

        $customer = \App\Models\User::where('email', 'customer@shirtstore.local')->firstOrFail();
        $customerOrder = Order::where('user_id', $customer->id)->firstOrFail();
        $this->actingAs($customer);
        foreach (['/cart', '/wishlist', '/account/orders', '/account/orders/'.$customerOrder->id, '/account/reviews'] as $url) {
            $this->get($url)->assertOk();
        }

        $admin = \App\Models\User::where('email', 'admin@shirtstore.local')->firstOrFail();
        $review = Review::firstOrFail();
        $this->actingAs($admin);
        foreach ([
            '/admin/dashboard', '/admin/products', '/admin/categories', '/admin/colors', '/admin/sizes',
            '/admin/inventory', '/admin/orders', '/admin/orders/'.$customerOrder->id, '/admin/customers',
            '/admin/customers/'.$customer->id, '/admin/coupons', '/admin/shipping', '/admin/reports',
            '/admin/reviews', '/admin/reviews/'.$review->id, '/admin/banned-words', '/admin/settings', '/admin/banners',
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }
}
