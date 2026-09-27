@extends('layouts.app')
@section('title', 'Cửa hàng')
@section('content')
<div class="container shop-shell py-5">
    <div class="mb-4"><p class="eyebrow mb-1">Bộ sưu tập</p><h1 class="section-title">Cửa hàng</h1><p class="text-muted">{{ $products->total() }} sản phẩm phù hợp</p></div>
    <div class="row">
        <aside class="col-lg-3"><form class="filter-panel p-3 p-xl-4" method="GET" action="{{ route('products.index') }}">
            <div class="d-flex justify-content-between"><h2 class="h5">Bộ lọc</h2><a class="small text-muted" href="{{ route('products.index') }}">Xóa lọc</a></div>
            <label class="form-label small mt-3" for="filter-q">Tìm kiếm</label><input id="filter-q" class="form-control" name="q" value="{{ request('q') }}" placeholder="Tên, SKU, thương hiệu...">
            <label class="form-label small mt-3" for="category">Danh mục</label><select id="category" class="form-select" name="category"><option value="">Tất cả</option>@foreach($categories as $item)<option value="{{ $item->slug }}" @selected(request('category')==$item->slug)>{{ $item->name }}</option>@endforeach</select>
            <div class="row g-2"><div class="col-6"><label class="form-label small mt-3" for="min-price">Giá từ</label><input id="min-price" class="form-control" type="number" min="0" name="min_price" value="{{ request('min_price') }}"></div><div class="col-6"><label class="form-label small mt-3" for="max-price">Đến</label><input id="max-price" class="form-control" type="number" min="0" name="max_price" value="{{ request('max_price') }}"></div></div>
            @foreach([['color','Màu sắc',$colors,'id'],['size','Kích thước',$sizes,'id'],['material','Chất liệu',$materials,null],['fit','Phom dáng',$fits,null],['brand','Thương hiệu',$brands,null]] as [$name,$label,$options,$key])
            <label class="form-label small mt-3" for="{{ $name }}">{{ $label }}</label><select id="{{ $name }}" class="form-select" name="{{ $name }}"><option value="">Tất cả</option>@foreach($options as $option)@php($value=$key ? $option->{$key} : $option)<option value="{{ $value }}" @selected((string)request($name)===(string)$value)>{{ $key ? $option->name : $option }}</option>@endforeach</select>
            @endforeach
            <div class="form-check mt-3"><input class="form-check-input" type="checkbox" value="1" name="in_stock" id="in-stock" @checked(request()->boolean('in_stock'))><label class="form-check-label" for="in-stock">Chỉ sản phẩm còn hàng</label></div>
            <input type="hidden" name="sort" value="{{ request('sort','newest') }}"><button class="btn btn-dark w-100 mt-4">Áp dụng</button>
        </form></aside>
        <div class="col-lg-9"><form class="d-flex justify-content-end mb-3" method="GET">@foreach(request()->except(['sort','page']) as $key=>$value)<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endforeach<label class="visually-hidden" for="sort">Sắp xếp</label><select id="sort" name="sort" class="form-select w-auto" onchange="this.form.submit()"><option value="newest" @selected(request('sort')==='newest')>Mới nhất</option><option value="price_asc" @selected(request('sort')==='price_asc')>Giá tăng dần</option><option value="price_desc" @selected(request('sort')==='price_desc')>Giá giảm dần</option><option value="discount" @selected(request('sort')==='discount')>Giảm nhiều nhất</option></select></form>
            <div class="row g-4">@forelse($products as $product)<div class="col-6 col-xl-4"><x-product-card :product="$product" /></div>@empty<div class="col-12 text-center py-5"><i class="fa-solid fa-magnifying-glass fa-2x text-muted mb-3"></i><h2 class="h5">Không tìm thấy sản phẩm</h2><a href="{{ route('products.index') }}">Xóa bộ lọc</a></div>@endforelse</div>
            <div class="mt-5">{{ $products->links() }}</div>
        </div>
    </div>
</div>
@endsection
