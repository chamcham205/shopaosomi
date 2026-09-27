<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\BannedWord;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusLog;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Review;
use App\Models\Setting;
use App\Models\ShippingRate;
use App\Models\ShippingZone;
use App\Models\User;
use App\Support\PublicImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class Phase10DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCoupons();
        $this->seedShipping();
        $this->seedSettings();
        $this->seedBanners();
        $this->seedBannedWords();
        $this->repairDemoProductImages();
        $this->seedOrdersAndReviews();

        $this->command->info('✅ Phase10DemoSeeder: Đã tạo dữ liệu vận hành và lịch sử demo.');
    }

    private function seedCoupons(): void
    {
        foreach ([
            ['code' => 'WELCOME10', 'type' => 'percent', 'value' => 10, 'minimum_order' => 300000, 'max_discount' => 100000, 'usage_limit' => 500, 'per_user_limit' => 1],
            ['code' => 'GIAM100K', 'type' => 'fixed', 'value' => 100000, 'minimum_order' => 800000, 'max_discount' => null, 'usage_limit' => 200, 'per_user_limit' => 2],
        ] as $data) {
            Coupon::updateOrCreate(['code' => $data['code']], $data + [
                'start_at' => now()->subMonth(),
                'end_at' => now()->addYear(),
                'status' => 'active',
            ]);
        }
    }

    private function seedShipping(): void
    {
        foreach ([
            ['name' => 'Nội thành TP.HCM', 'code' => 'HCM', 'rate' => 'Giao tiêu chuẩn', 'fee' => 25000, 'free' => 1000000],
            ['name' => 'Miền Nam', 'code' => 'SOUTH', 'rate' => 'Giao liên tỉnh', 'fee' => 35000, 'free' => 1200000],
            ['name' => 'Toàn quốc', 'code' => 'NATIONWIDE', 'rate' => 'Giao toàn quốc', 'fee' => 45000, 'free' => 1500000],
        ] as $data) {
            $zone = ShippingZone::updateOrCreate(
                ['code' => $data['code']],
                ['name' => $data['name'], 'status' => 'active']
            );
            ShippingRate::updateOrCreate(
                ['shipping_zone_id' => $zone->id, 'name' => $data['rate']],
                ['fee' => $data['fee'], 'free_shipping_threshold' => $data['free'], 'status' => 'active']
            );
        }
    }

    private function seedSettings(): void
    {
        $settings = [
            'site_name' => 'SHIRT STORE',
            'site_description' => 'Áo sơ mi nam chỉn chu cho mọi ngày.',
            'contact_email' => 'hello@shirtstore.local',
            'contact_phone' => '0901 000 001',
            'address' => 'TP. Hồ Chí Minh, Việt Nam',
            'low_stock_threshold' => '5',
            'default_theme' => 'system',
            'announcement' => 'Miễn phí vận chuyển cho đơn hàng đủ điều kiện.',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }

    private function seedBanners(): void
    {
        $path = 'banners/demo-hero.png';

        $banner = Banner::firstOrNew(['title' => 'Sơ mi chuẩn phom, tự tin mỗi ngày']);
        $banner->fill([
            'subtitle' => 'Khám phá bộ sưu tập công sở và casual dành cho người đàn ông hiện đại.',
            'button_text' => 'Mua ngay',
            'target_url' => '/products',
            'sort_order' => 1,
            'status' => 'active',
            'starts_at' => now()->subMonth(),
            'ends_at' => now()->addYear(),
        ]);

        $currentPath = PublicImage::relativePath($banner->image_path);
        if (! $currentPath || ! Storage::disk('public')->exists($currentPath)) {
            $banner->image_path = $path;
        }
        $banner->save();
    }

    private function seedBannedWords(): void
    {
        foreach (['lừa đảo', 'hàng giả', 'spam'] as $word) {
            BannedWord::updateOrCreate(['word' => $word], ['status' => 'active']);
        }
    }

    private function repairDemoProductImages(): void
    {
        Product::query()->where('sku', 'like', 'SM-%')->orderBy('id')->get()->each(function (Product $product, int $index): void {
            $path = 'products/demo/shirt-'.(($index % 5) + 1).'.png';

            $image = $product->images()->where('is_main', true)->first();
            $currentPath = PublicImage::relativePath($image?->image_path);
            if (! $image || ! $currentPath || ! Storage::disk('public')->exists($currentPath)) {
                ProductImage::updateOrCreate(
                    ['product_id' => $product->id, 'is_main' => true],
                    ['image_path' => $path, 'sort_order' => 0]
                );
            }
        });
    }

    private function seedOrdersAndReviews(): void
    {
        $customer = User::where('email', 'customer@shirtstore.local')->first();
        $secondCustomer = User::where('email', 'minhanh@shirtstore.local')->first();
        $thirdCustomer = User::where('email', 'hoangnam@shirtstore.local')->first();
        $admin = User::where('email', 'admin@shirtstore.local')->first();
        $products = Product::with(['variants.color', 'variants.size'])->where('status', 'active')->orderBy('id')->take(3)->get();

        if (! $customer || ! $secondCustomer || ! $thirdCustomer || $products->count() < 3) {
            return;
        }

        $completed = $this->seedOrder($customer, $products[0], '11111111-1111-4111-8111-000000000001', 'completed', 699000);
        $reviewOrder = $this->seedOrder($secondCustomer, $products[1], '11111111-1111-4111-8111-000000000002', 'completed', 499000);
        $this->seedOrder($customer, $products[2], '11111111-1111-4111-8111-000000000003', 'cancelled', 550000);
        $this->seedOrder($thirdCustomer, $products[2], '11111111-1111-4111-8111-000000000004', 'pending', 550000);

        $completedItem = $completed->items()->first();
        if ($completedItem) {
            Review::updateOrCreate(
                ['user_id' => $customer->id, 'product_id' => $products[0]->id],
                [
                    'order_item_id' => $completedItem->id,
                    'rating' => 5,
                    'title' => 'Phom áo đẹp, dễ phối đồ',
                    'content' => 'Chất vải thoáng, đường may chắc chắn và giao hàng đúng hẹn.',
                    'status' => 'approved',
                    'verified_purchase' => true,
                    'moderated_by' => $admin?->id,
                    'moderated_at' => now()->subDay(),
                ]
            );
        }

        $pendingItem = $reviewOrder->items()->first();
        if ($pendingItem) {
            Review::updateOrCreate(
                ['user_id' => $secondCustomer->id, 'product_id' => $products[1]->id],
                [
                    'order_item_id' => $pendingItem->id,
                    'rating' => 4,
                    'title' => 'Màu sắc trang nhã',
                    'content' => 'Áo đúng mô tả, đóng gói cẩn thận và mặc khá thoải mái.',
                    'status' => 'pending',
                    'verified_purchase' => true,
                ]
            );
        }
    }

    private function seedOrder(User $user, Product $product, string $token, string $status, int $subtotal): Order
    {
        $shippingFee = 25000;
        $order = Order::updateOrCreate(
            ['checkout_token' => $token],
            [
                'user_id' => $user->id,
                'recipient_name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'province' => 'TP. Hồ Chí Minh',
                'district' => 'Quận 1',
                'ward' => 'Phường Bến Nghé',
                'address' => '123 Nguyễn Huệ',
                'shipping_zone_name' => 'Nội thành TP.HCM',
                'subtotal' => $subtotal,
                'discount' => 0,
                'shipping_fee' => $shippingFee,
                'total' => $subtotal + $shippingFee,
                'payment_method' => 'cod',
                'status' => $status,
                'cancelled_reason' => $status === 'cancelled' ? 'Khách hàng đổi nhu cầu.' : null,
                'cancelled_at' => $status === 'cancelled' ? now()->subDays(2) : null,
            ]
        );

        $order->update(['order_code' => 'ORD'.$order->created_at->format('Ymd').str_pad((string) $order->id, 8, '0', STR_PAD_LEFT)]);
        $variant = $product->variants->first();

        OrderItem::updateOrCreate(
            ['order_id' => $order->id, 'product_sku' => $product->sku],
            [
                'product_variant_id' => $variant?->id,
                'product_name' => $product->name,
                'color_name' => $variant?->color?->name ?? 'N/A',
                'size_name' => $variant?->size?->name ?? 'N/A',
                'price' => $subtotal,
                'quantity' => 1,
                'subtotal' => $subtotal,
            ]
        );

        $history = match ($status) {
            'completed' => [
                [null, 'pending'], ['pending', 'confirmed'], ['confirmed', 'packing'],
                ['packing', 'shipping'], ['shipping', 'completed'],
            ],
            'cancelled' => [[null, 'pending'], ['pending', 'cancelled']],
            default => [[null, 'pending']],
        };

        foreach ($history as [$from, $to]) {
            OrderStatusLog::updateOrCreate(
                ['order_id' => $order->id, 'to_status' => $to],
                ['from_status' => $from, 'note' => 'Dữ liệu lịch sử demo.']
            );
        }

        return $order;
    }
}
