@php($image = $product->images->firstWhere('is_main', true) ?? $product->images->first())
@php($variantsPayload = $product->variants->map(fn($v) => [
    'id'         => $v->id,
    'color_id'   => $v->color_id,
    'color_name' => $v->color?->name ?? 'Mặc định',
    'color_hex'  => $v->color?->hex_code ?? '#cccccc',
    'size_id'    => $v->size_id,
    'size_name'  => $v->size?->name ?? 'Tiêu chuẩn',
    'stock'      => (int) $v->stock,
    'sku'        => $v->sku,
]))
<article class="product-card h-100 d-flex flex-column">
    <div class="product-image-wrap">
        <a href="{{ route('products.show', $product->slug) }}" class="d-block w-100 h-100">
            <img src="{{ $image?->url ?? asset('assets/images/product-fallback.svg') }}" onerror="this.onerror=null;this.src='{{ asset('assets/images/product-fallback.svg') }}'" alt="{{ $product->name }}" class="product-image" loading="lazy">
        </a>
        @if($product->is_on_sale)<span class="sale-badge">-{{ $product->discount_percent }}%</span>@endif
        @if(!$product->in_stock)<span class="stock-badge">Hết hàng</span>@endif
        @if($product->in_stock)
        <button type="button" class="btn-quick-cart-float btn-open-quick-add"
                title="Thêm nhanh vào giỏ hàng"
                aria-label="Thêm vào giỏ hàng"
                data-product-id="{{ $product->id }}"
                data-product-name="{{ $product->name }}"
                data-product-price="{{ number_format($product->current_price, 0, ',', '.') }}₫"
                data-product-original-price="{{ $product->is_on_sale ? number_format($product->price, 0, ',', '.') . '₫' : '' }}"
                data-product-discount="{{ $product->is_on_sale ? '-' . $product->discount_percent . '%' : '' }}"
                data-product-image="{{ $image?->url ?? asset('assets/images/product-fallback.svg') }}"
                data-product-url="{{ route('products.show', $product->slug) }}"
                data-variants='@json($variantsPayload)'>
            <i class="fa-solid fa-cart-plus"></i>
        </button>
        @endif
    </div>
    <div class="p-3">
        <div class="small text-uppercase text-muted mb-1">{{ $product->brand ?: $product->category?->name }}</div>
        <h3 class="h6 mb-2"><a class="product-name" href="{{ route('products.show', $product->slug) }}">{{ $product->name }}</a></h3>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <strong class="price">{{ number_format($product->current_price, 0, ',', '.') }}₫</strong>
            @if($product->is_on_sale)<del class="small text-muted">{{ number_format($product->price, 0, ',', '.') }}₫</del>@endif
        </div>
    </div>
</article>
