<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Banner;

/**
 * HomeController (Customer)
 *
 * Trang chủ placeholder cho Phase 1.
 * Sẽ được hoàn thiện ở Phase 3 với Banner, Product, Flash Sale...
 */
class HomeController extends Controller
{
    public function index()
    {
        $base = fn () => Product::query()->where('status', 'active')
            ->with(['category', 'images', 'variants.color', 'variants.size']);

        $categories = Category::where('status', 'active')->withCount([
            'products' => fn ($query) => $query->where('status', 'active'),
        ])->orderBy('sort_order')->limit(6)->get();
        $newProducts = $base()->latest()->limit(8)->get();
        $featuredProducts = $base()->where('featured', true)->latest()->limit(8)->get();
        $saleProducts = $base()->whereNotNull('sale_price')
            ->where(fn ($query) => $query->whereNull('sale_start')->orWhere('sale_start', '<=', now()))
            ->where(fn ($query) => $query->whereNull('sale_end')->orWhere('sale_end', '>=', now()))
            ->latest()->limit(8)->get();
        $banners = Banner::visible()->orderBy('sort_order')->orderBy('id')->get();

        return view('home.index', compact('categories', 'newProducts', 'featuredProducts', 'saleProducts','banners'));
    }
}
