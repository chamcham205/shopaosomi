@extends('layouts.app')
@section('title', 'Trang chủ')
@section('content')
@if($banners->isNotEmpty())
<section id="homeBanners" class="carousel slide carousel-fade hero-banner-section" data-bs-ride="carousel" data-bs-interval="6500" aria-label="Khuyến mãi nổi bật">
    <div class="carousel-inner">
        @foreach($banners as $banner)
            <div class="carousel-item @if($loop->first) active @endif">
                <div class="hero-banner-item">
                    {{-- Ảnh nền chuyển động nhẹ nhàng, điện ảnh --}}
                    <div class="hero-banner-bg" style="background-image: url('{{ $banner->image_url }}');"></div>
                    <div class="hero-banner-overlay"></div>

                    <div class="hero-banner-content">
                        <span class="hero-banner-tag">Bộ sưu tập 2026</span>
                        <h1 class="hero-banner-title">{{ $banner->title }}</h1>

                        @if($banner->subtitle)
                            <p class="hero-banner-subtitle">{{ $banner->subtitle }}</p>
                        @endif

                        <div class="hero-banner-actions">
                            <a class="btn-hero-explore" href="{{ $banner->target_url ?: route('products.index') }}">
                                <span>Khám phá bộ sưu tập</span>
                                <i class="fa-solid fa-arrow-right ms-2 explore-arrow"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if($banners->count() > 1)
        <button class="carousel-control-prev hero-carousel-arrow position-absolute start-0 ms-4" type="button" data-bs-target="#homeBanners" data-bs-slide="prev" aria-label="Banner trước">
            <i class="fa-solid fa-chevron-left"></i>
        </button>
        <button class="carousel-control-next hero-carousel-arrow position-absolute end-0 me-4" type="button" data-bs-target="#homeBanners" data-bs-slide="next" aria-label="Banner tiếp theo">
            <i class="fa-solid fa-chevron-right"></i>
        </button>
    @endif
</section>
@else
<section class="hero-banner-section">
    <div class="hero-banner-item">
        <div class="hero-banner-bg" style="background-image: url('{{ asset('assets/images/products/no-image.png') }}');"></div>
        <div class="hero-banner-overlay"></div>

        <div class="hero-banner-content">
            <span class="hero-banner-tag">Bộ sưu tập mới</span>
            <h1 class="hero-banner-title">Phong thái bắt đầu từ chiếc áo vừa vặn</h1>
            <p class="hero-banner-subtitle">Sơ mi hiện đại, chất liệu thoáng nhẹ và phom dáng được chăm chút cho người đàn ông Việt.</p>
            <div class="hero-banner-actions">
                <a class="btn-hero-explore" href="{{ route('products.index') }}">
                    <span>Khám phá bộ sưu tập</span>
                    <i class="fa-solid fa-arrow-right ms-2 explore-arrow"></i>
                </a>
            </div>
        </div>
    </div>
</section>
@endif
<div class="container shop-shell py-5">
    <section class="mb-5"><div class="d-flex justify-content-between align-items-end mb-4"><div><p class="eyebrow mb-1">Mặc theo phong cách</p><h2 class="section-title mb-0">Danh mục nổi bật</h2></div></div>
        <div class="row g-3">@forelse($categories as $category)<div class="col-6 col-md-4 col-lg-2"><a href="{{ route('products.index',['category'=>$category->slug]) }}" class="category-card d-block p-4 text-center text-decoration-none text-dark h-100"><i class="fa-solid fa-shirt fa-2x mb-3" style="color:var(--gold)"></i><strong class="d-block">{{ $category->name }}</strong><small class="text-muted">{{ $category->products_count }} sản phẩm</small></a></div>@empty<p class="text-muted">Danh mục đang được cập nhật.</p>@endforelse</div>
    </section>
    @foreach([['Sản phẩm mới',$newProducts,'newest'],['Lựa chọn nổi bật',$featuredProducts,'newest'],['Ưu đãi hiện tại',$saleProducts,'discount']] as [$title,$items,$sort])
    <section class="mb-5"><div class="d-flex justify-content-between align-items-end mb-4"><h2 class="section-title mb-0">{{ $title }}</h2><a href="{{ route('products.index',['sort'=>$sort]) }}" class="text-dark">Xem tất cả</a></div><div class="row g-4">@forelse($items as $product)<div class="col-6 col-lg-3"><x-product-card :product="$product" /></div>@empty<p class="text-muted">Sản phẩm đang được cập nhật.</p>@endforelse</div></section>
    @endforeach
</div>
@endsection
