<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\Size;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Services\ReviewModerationService;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::query()->where('status', 'active')
            ->with(['category', 'images', 'variants.color', 'variants.size'])
            ->when($request->filled('q'), function (Builder $query) use ($request) {
                $term = '%'.trim($request->string('q')).'%';
                $query->where(fn (Builder $q) => $q->where('name', 'like', $term)
                    ->orWhere('sku', 'like', $term)->orWhere('brand', 'like', $term)
                    ->orWhere('material', 'like', $term));
            })
            ->when($request->filled('category'), fn (Builder $q) => $q->whereHas(
                'category', fn (Builder $category) => $category->where('slug', $request->string('category'))
            ))
            ->when($request->filled('color'), fn (Builder $q) => $q->whereHas(
                'variants.color', fn (Builder $color) => $color->where('id', $request->integer('color'))->where('status', 'active')
            ))
            ->when($request->filled('size'), fn (Builder $q) => $q->whereHas(
                'variants.size', fn (Builder $size) => $size->where('id', $request->integer('size'))->where('status', 'active')
            ))
            ->when($request->filled('material'), fn (Builder $q) => $q->where('material', $request->string('material')))
            ->when($request->filled('fit'), fn (Builder $q) => $q->where('fit', $request->string('fit')))
            ->when($request->filled('brand'), fn (Builder $q) => $q->where('brand', $request->string('brand')))
            ->when($request->boolean('in_stock'), fn (Builder $q) => $q->whereHas('variants', fn (Builder $v) => $v->where('stock', '>', 0)))
            ->when($request->filled('min_price'), fn (Builder $q) => $q->where('price', '>=', max(0, $request->integer('min_price'))))
            ->when($request->filled('max_price'), fn (Builder $q) => $q->where('price', '<=', max(0, $request->integer('max_price'))));

        match ($request->input('sort', 'newest')) {
            'price_asc' => $products->orderBy('price'),
            'price_desc' => $products->orderByDesc('price'),
            'discount' => $products->orderByRaw(
                'CASE WHEN sale_price IS NOT NULL AND price > 0 AND (sale_start IS NULL OR sale_start <= ?) AND (sale_end IS NULL OR sale_end >= ?) THEN (price - sale_price) / price ELSE 0 END DESC',
                [now(), now()]
            )->latest(),
            default => $products->latest(),
        };

        return view('products.index', [
            'products' => $products->paginate(12)->withQueryString(),
            'categories' => Category::where('status', 'active')->orderBy('sort_order')->get(),
            'colors' => Color::where('status', 'active')->orderBy('name')->get(),
            'sizes' => Size::where('status', 'active')->orderBy('sort_order')->get(),
            'materials' => $this->options('material'),
            'fits' => $this->options('fit'),
            'brands' => $this->options('brand'),
        ]);
    }

    public function show(string $slug, ReviewModerationService $reviewService)
    {
        $product = Product::where('status', 'active')->where('slug', $slug)
            ->with(['category', 'images', 'variants.color', 'variants.size'])->firstOrFail();
        $relatedProducts = Product::where('status', 'active')->where('category_id', $product->category_id)
            ->whereKeyNot($product->id)->with(['images', 'variants'])->latest()->limit(4)->get();
        $colors = $product->variants->pluck('color')->unique('id')->values();
        $sizes = $product->variants->pluck('size')->unique('id')->sortBy('sort_order')->values();
        $variantData = $product->variants->map(fn ($variant) => [
            'color' => (string) $variant->color_id,
            'size' => (string) $variant->size_id,
            'stock' => $variant->stock,
            'sku' => $variant->sku,
            'id' => $variant->id,
        ])->values();
        $reviews = $product->approvedReviews()->with('user')->latest()->paginate(10, ['*'], 'reviews_page');
        $averageRating = round((float)$product->approvedReviews()->avg('rating'), 1);
        $approvedReviewsCount = $product->approvedReviews()->count();
        $canReview = auth()->check() && auth()->user()->isCustomer() && $reviewService->canReview(auth()->user(), $product);

        return view('products.show', compact('product', 'relatedProducts', 'colors', 'sizes', 'variantData', 'reviews', 'averageRating', 'approvedReviewsCount', 'canReview'));
    }

    private function options(string $column)
    {
        return Product::where('status', 'active')->whereNotNull($column)->where($column, '!=', '')
            ->distinct()->orderBy($column)->pluck($column);
    }
}
