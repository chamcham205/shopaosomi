<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Size;
use App\Services\ProductService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Phase2Seeder
 *
 * Tạo dữ liệu demo đủ để hệ thống hoạt động và nhìn thấy được:
 * - 8 Categories
 * - 6 Colors
 * - 5 Sizes (S, M, L, XL, XXL)
 * - 15 Products với variants và stock thực tế
 *
 * An toàn: KHÔNG xóa User Phase 1, chỉ thêm dữ liệu mới.
 */
class Phase2Seeder extends Seeder
{
    public function __construct(private ProductService $productService) {}

    public function run(): void
    {
        $this->seedCategories();
        $this->seedColors();
        $this->seedSizes();
        $this->seedProducts();

        $this->command->info('✅ Phase2Seeder: Đã tạo categories, colors, sizes, products với variants và stock.');
    }

    private function seedCategories(): void
    {
        $categories = [
            ['name' => 'Sơ mi Công sở', 'sort_order' => 1],
            ['name' => 'Sơ mi Casual',  'sort_order' => 2],
            ['name' => 'Sơ mi Oxford',  'sort_order' => 3],
            ['name' => 'Sơ mi Linen',   'sort_order' => 4],
            ['name' => 'Sơ mi Dài tay', 'sort_order' => 5],
            ['name' => 'Sơ mi Ngắn tay','sort_order' => 6],
            ['name' => 'Sơ mi Slim Fit','sort_order' => 7],
            ['name' => 'Sơ mi Oversize','sort_order' => 8],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(
                ['slug' => Str::slug($cat['name'])],
                ['name' => $cat['name'], 'status' => 'active', 'sort_order' => $cat['sort_order']]
            );
        }
    }

    private function seedColors(): void
    {
        $colors = [
            ['name' => 'Trắng',    'hex_code' => '#FFFFFF'],
            ['name' => 'Đen',      'hex_code' => '#000000'],
            ['name' => 'Navy',     'hex_code' => '#172554'],
            ['name' => 'Xanh nhạt','hex_code' => '#ADD8E6'],
            ['name' => 'Be',       'hex_code' => '#D6C3A5'],
            ['name' => 'Xám',      'hex_code' => '#9CA3AF'],
        ];

        foreach ($colors as $color) {
            Color::firstOrCreate(['name' => $color['name']], array_merge($color, ['status' => 'active']));
        }
    }

    private function seedSizes(): void
    {
        $sizes = [
            ['name' => 'S',   'sort_order' => 1],
            ['name' => 'M',   'sort_order' => 2],
            ['name' => 'L',   'sort_order' => 3],
            ['name' => 'XL',  'sort_order' => 4],
            ['name' => 'XXL', 'sort_order' => 5],
        ];

        foreach ($sizes as $size) {
            Size::firstOrCreate(['name' => $size['name']], array_merge($size, ['status' => 'active']));
        }
    }

    private function seedProducts(): void
    {
        $catMap   = Category::pluck('id', 'slug');
        $colorMap = Color::pluck('id', 'name');
        $sizeMap  = Size::pluck('id', 'name');

        $products = [
            [
                'name'     => 'Sơ mi Oxford Premium Trắng',
                'sku'      => 'SM-OXF-001',
                'category' => 'so-mi-oxford',
                'price'    => 699000,
                'material' => 'Cotton Oxford 100%', 'fit' => 'Regular Fit', 'brand' => 'ShirtStore',
                'colors'   => ['Trắng', 'Navy', 'Xám'],
                'sizes'    => ['M', 'L', 'XL'],
                'status'   => 'active', 'featured' => true,
                'short_description' => 'Sơ mi Oxford cao cấp, chất liệu cotton mềm mại, phù hợp công sở.',
            ],
            [
                'name'     => 'Sơ mi Slim Fit Business Navy',
                'sku'      => 'SM-SLM-002',
                'category' => 'so-mi-cong-so',
                'price'    => 599000, 'sale_price' => 499000,
                'sale_start' => now()->subDay(), 'sale_end' => now()->addDays(7),
                'material' => 'Cotton 95% Elastane 5%', 'fit' => 'Slim Fit', 'brand' => 'ShirtStore',
                'colors'   => ['Navy', 'Đen'],
                'sizes'    => ['S', 'M', 'L', 'XL'],
                'status'   => 'active', 'featured' => true,
                'short_description' => 'Sơ mi slim fit thanh lịch, co giãn nhẹ, tôn dáng hoàn hảo.',
            ],
            [
                'name'     => 'Sơ mi Linen Summer Be',
                'sku'      => 'SM-LIN-003',
                'category' => 'so-mi-linen',
                'price'    => 550000,
                'material' => 'Linen 100%', 'fit' => 'Regular Fit', 'brand' => 'ShirtStore',
                'colors'   => ['Be', 'Trắng'],
                'sizes'    => ['M', 'L', 'XL', 'XXL'],
                'status'   => 'active', 'featured' => false,
                'short_description' => 'Sơ mi Linen thoáng mát, lý tưởng cho mùa hè.',
            ],
            [
                'name'     => 'Sơ mi Casual Stripe Xanh nhạt',
                'sku'      => 'SM-CAS-004',
                'category' => 'so-mi-casual',
                'price'    => 450000,
                'material' => 'Cotton 100%', 'fit' => 'Relaxed Fit', 'brand' => 'ShirtStore',
                'colors'   => ['Xanh nhạt', 'Trắng'],
                'sizes'    => ['S', 'M', 'L'],
                'status'   => 'active', 'featured' => false,
                'short_description' => 'Sơ mi casual sọc nhẹ nhàng, năng động, phù hợp dạo phố.',
            ],
            [
                'name'     => 'Sơ mi Đen Formal Công sở',
                'sku'      => 'SM-FOR-005',
                'category' => 'so-mi-cong-so',
                'price'    => 629000, 'sale_price' => 529000,
                'sale_start' => now()->subDay(), 'sale_end' => now()->addDays(14),
                'material' => 'Cotton Twill 98%', 'fit' => 'Slim Fit', 'brand' => 'ShirtStore',
                'colors'   => ['Đen', 'Navy'],
                'sizes'    => ['S', 'M', 'L', 'XL', 'XXL'],
                'status'   => 'active', 'featured' => true,
                'short_description' => 'Sơ mi đen công sở thanh lịch, phù hợp với mọi trang phục formal.',
            ],
            [
                'name'     => 'Sơ mi Oxford Oversize Xám',
                'sku'      => 'SM-OVS-006',
                'category' => 'so-mi-oversize',
                'price'    => 480000,
                'material' => 'Cotton Oxford', 'fit' => 'Oversize', 'brand' => 'ShirtStore',
                'colors'   => ['Xám', 'Trắng', 'Be'],
                'sizes'    => ['M', 'L', 'XL', 'XXL'],
                'status'   => 'active', 'featured' => false,
                'short_description' => 'Sơ mi Oxford oversize phong cách streetwear, thoải mái.',
            ],
            [
                'name'     => 'Sơ mi Dài tay Kẻ caro Navy',
                'sku'      => 'SM-DTA-007',
                'category' => 'so-mi-dai-tay',
                'price'    => 520000,
                'material' => 'Cotton Flannel', 'fit' => 'Regular Fit', 'brand' => 'ShirtStore',
                'colors'   => ['Navy', 'Be'],
                'sizes'    => ['M', 'L', 'XL'],
                'status'   => 'active', 'featured' => false,
                'short_description' => 'Sơ mi flannel kẻ caro dài tay ấm áp, phù hợp thời tiết se lạnh.',
            ],
            [
                'name'     => 'Sơ mi Ngắn tay Linen Trắng',
                'sku'      => 'SM-NTA-008',
                'category' => 'so-mi-ngan-tay',
                'price'    => 399000,
                'material' => 'Linen 100%', 'fit' => 'Regular Fit', 'brand' => 'ShirtStore',
                'colors'   => ['Trắng', 'Be', 'Xanh nhạt'],
                'sizes'    => ['S', 'M', 'L', 'XL'],
                'status'   => 'active', 'featured' => false,
                'short_description' => 'Sơ mi ngắn tay linen nhẹ nhàng cho ngày hè.',
            ],
            [
                'name'     => 'Sơ mi Bamboo Office Xám',
                'sku'      => 'SM-BAM-009',
                'category' => 'so-mi-cong-so',
                'price'    => 750000, 'sale_price' => 650000,
                'sale_start' => now()->subHour(), 'sale_end' => now()->addDays(3),
                'material' => 'Bamboo Cotton 60/40', 'fit' => 'Slim Fit', 'brand' => 'EcoShirt',
                'colors'   => ['Xám', 'Trắng'],
                'sizes'    => ['S', 'M', 'L', 'XL'],
                'status'   => 'active', 'featured' => true,
                'short_description' => 'Sơ mi từ sợi tre thân thiện môi trường, kháng khuẩn tự nhiên.',
            ],
            [
                'name'     => 'Sơ mi Business Classic Blue',
                'sku'      => 'SM-BCB-010',
                'category' => 'so-mi-cong-so',
                'price'    => 680000,
                'material' => 'Cotton 100%', 'fit' => 'Classic Fit', 'brand' => 'ShirtStore',
                'colors'   => ['Xanh nhạt', 'Navy'],
                'sizes'    => ['M', 'L', 'XL', 'XXL'],
                'status'   => 'active', 'featured' => false,
                'short_description' => 'Sơ mi classic fit sang trọng, bền màu sau nhiều lần giặt.',
            ],
            [
                'name'     => 'Sơ mi Polo Casual Đen',
                'sku'      => 'SM-POL-011',
                'category' => 'so-mi-casual',
                'price'    => 420000,
                'material' => 'Pique Cotton', 'fit' => 'Regular Fit', 'brand' => 'ShirtStore',
                'colors'   => ['Đen', 'Navy', 'Xám'],
                'sizes'    => ['S', 'M', 'L', 'XL'],
                'status'   => 'active', 'featured' => false,
                'short_description' => 'Sơ mi polo casual, vải pique thoáng khí, phong cách trẻ trung.',
            ],
            [
                'name'     => 'Sơ mi Oxford Slim Fit Navy',
                'sku'      => 'SM-OSN-012',
                'category' => 'so-mi-oxford',
                'price'    => 620000, 'sale_price' => 520000,
                'sale_start' => now()->subDays(2), 'sale_end' => now()->addDays(5),
                'material' => 'Oxford Cotton', 'fit' => 'Slim Fit', 'brand' => 'ShirtStore',
                'colors'   => ['Navy', 'Đen'],
                'sizes'    => ['S', 'M', 'L', 'XL'],
                'status'   => 'active', 'featured' => true,
                'short_description' => 'Sơ mi Oxford slim fit phong cách, thích hợp cả công sở lẫn casual.',
            ],
            [
                'name'     => 'Sơ mi Ngắn tay Casual Stripe Navy',
                'sku'      => 'SM-STR-013',
                'category' => 'so-mi-ngan-tay',
                'price'    => 380000,
                'material' => 'Cotton 100%', 'fit' => 'Regular Fit', 'brand' => 'ShirtStore',
                'colors'   => ['Trắng', 'Be'],
                'sizes'    => ['S', 'M', 'L'],
                'status'   => 'active', 'featured' => false,
                'short_description' => 'Sơ mi kẻ sọc năng động, phù hợp mặc đi chơi cuối tuần.',
            ],
            [
                'name'     => 'Sơ mi Linen Oversize Be',
                'sku'      => 'SM-LOV-014',
                'category' => 'so-mi-oversize',
                'price'    => 560000,
                'material' => 'Linen 100%', 'fit' => 'Oversize', 'brand' => 'ShirtStore',
                'colors'   => ['Be', 'Trắng'],
                'sizes'    => ['M', 'L', 'XL', 'XXL'],
                'status'   => 'active', 'featured' => false,
                'short_description' => 'Sơ mi linen oversize trendy, phong cách tối giản.',
            ],
            [
                'name'     => 'Sơ mi Dài tay Công sở Trắng Premium',
                'sku'      => 'SM-DWP-015',
                'category' => 'so-mi-dai-tay',
                'price'    => 790000, 'sale_price' => 680000,
                'sale_start' => now()->subDay(), 'sale_end' => now()->addDays(10),
                'material' => 'Cotton Supima 100%', 'fit' => 'Slim Fit', 'brand' => 'PremiumShirt',
                'colors'   => ['Trắng', 'Xám'],
                'sizes'    => ['S', 'M', 'L', 'XL', 'XXL'],
                'status'   => 'active', 'featured' => true,
                'short_description' => 'Sơ mi premium Supima cotton siêu mềm, không nhăn sau cả ngày mặc.',
            ],
        ];

        // Stock demo theo vị trí trong mảng (ngẫu nhiên có chủ ý)
        $stockTemplates = [
            [12, 18, 15, 8, 4],  // Đủ hàng
            [5, 3, 2, 1, 0],     // Sắp hết + hết hàng 1 size
            [20, 25, 20, 15, 10],// Nhiều hàng
            [0, 2, 4, 1, 0],     // Gần hết tất cả
            [8, 12, 10, 6, 3],   // Trung bình
        ];

        foreach ($products as $idx => $data) {
            // Bỏ qua nếu đã có SKU này
            if (Product::where('sku', $data['sku'])->exists()) continue;

            $categoryId = $catMap[$data['category']] ?? $catMap->first();

            $product = Product::create([
                'category_id'       => $categoryId,
                'name'              => $data['name'],
                'slug'              => $this->productService->generateUniqueSlug($data['name']),
                'sku'               => $data['sku'],
                'short_description' => $data['short_description'] ?? null,
                'material'          => $data['material'] ?? null,
                'fit'               => $data['fit'] ?? null,
                'brand'             => $data['brand'] ?? null,
                'price'             => $data['price'],
                'sale_price'        => $data['sale_price'] ?? null,
                'sale_start'        => $data['sale_start'] ?? null,
                'sale_end'          => $data['sale_end'] ?? null,
                'featured'          => $data['featured'] ?? false,
                'status'            => $data['status'] ?? 'active',
            ]);

            // Tạo ảnh placeholder (dùng ảnh demo local)
            $this->createDemoImage($product, $idx);

            // Tạo variants
            $stockTpl = $stockTemplates[$idx % count($stockTemplates)];
            $sizeList = ['S', 'M', 'L', 'XL', 'XXL'];

            foreach ($data['colors'] as $colorName) {
                $colorId = $colorMap[$colorName] ?? null;
                if (! $colorId) continue;

                foreach ($data['sizes'] as $sIdx => $sizeName) {
                    $sizeId = $sizeMap[$sizeName] ?? null;
                    if (! $sizeId) continue;

                    $stockVal = $stockTpl[array_search($sizeName, $sizeList)] ?? 5;

                    ProductVariant::firstOrCreate(
                        [
                            'product_id' => $product->id,
                            'color_id'   => $colorId,
                            'size_id'    => $sizeId,
                        ],
                        [
                            'sku'   => $this->productService->generateVariantSku($product, $colorId, $sizeId),
                            'stock' => $stockVal,
                        ]
                    );
                }
            }
        }
    }

    /**
     * Tạo record ảnh demo (trỏ đến ảnh no-image local).
     * Không fetch ảnh Internet để tránh phụ thuộc mạng.
     */
    private function createDemoImage(Product $product, int $idx): void
    {
        // Ảnh demo local tại public/assets/images/products/demo/
        // Các file này sẽ được tạo trong bước assets
        $demoImages = [
            'products/demo/shirt-1.png',
            'products/demo/shirt-2.png',
            'products/demo/shirt-3.png',
            'products/demo/shirt-4.png',
            'products/demo/shirt-5.png',
        ];

        $imagePath = $demoImages[$idx % count($demoImages)];

        ProductImage::firstOrCreate(
            ['product_id' => $product->id, 'is_main' => true],
            [
                'image_path' => $imagePath,
                'is_main'    => true,
                'sort_order' => 0,
            ]
        );
    }
}
