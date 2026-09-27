@extends('layouts.admin')
@section('title', $product->name)
@section('page-title', 'Chi tiết Sản phẩm')

@section('content')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-arrow-left"></i></a>
    <h5 class="mb-0 fw-700">{{ $product->name }}</h5>
    <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-accent ms-auto">
        <i class="fa-solid fa-pen me-1"></i>Sửa
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">Thông tin sản phẩm</div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tbody>
                        <tr><th width="180">SKU</th><td><code>{{ $product->sku }}</code></td></tr>
                        <tr><th>Danh mục</th><td>{{ $product->category?->name }}</td></tr>
                        <tr><th>Chất liệu</th><td>{{ $product->material ?? '–' }}</td></tr>
                        <tr><th>Kiểu dáng</th><td>{{ $product->fit ?? '–' }}</td></tr>
                        <tr><th>Thương hiệu</th><td>{{ $product->brand ?? '–' }}</td></tr>
                        <tr><th>Giá</th><td>{{ number_format($product->price) }}đ</td></tr>
                        @if($product->sale_price)
                        <tr><th>Giá sale</th><td class="text-danger fw-700">{{ number_format($product->sale_price) }}đ ({{ $product->discount_percent }}%)</td></tr>
                        @endif
                        <tr><th>Trạng thái</th><td>
                            <span class="badge badge-{{ $product->status }} rounded-pill px-2">{{ $product->status }}</span>
                        </td></tr>
                        <tr><th>Nổi bật</th><td>{{ $product->featured ? '✅' : '–' }}</td></tr>
                        <tr><th>Tổng tồn kho</th><td class="fw-700">{{ $product->total_stock }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Variants table --}}
        <div class="card">
            <div class="card-header">Biến thể & Tồn kho</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead style="background:#f9fafb">
                        <tr><th>SKU Variant</th><th>Màu</th><th>Size</th><th class="text-center">Tồn kho</th><th>Tình trạng</th></tr>
                    </thead>
                    <tbody>
                        @forelse($product->variants as $v)
                        <tr>
                            <td><code style="font-size:.75rem">{{ $v->sku }}</code></td>
                            <td>
                                <div class="d-flex align-items-center gap-1">
                                    <div class="rounded-circle" style="width:12px;height:12px;background:{{ $v->color?->hex_code }};border:1px solid #ccc"></div>
                                    {{ $v->color?->name }}
                                </div>
                            </td>
                            <td><span class="badge bg-dark">{{ $v->size?->name }}</span></td>
                            <td class="text-center fw-700">{{ $v->stock }}</td>
                            <td>
                                @if($v->stock === 0)
                                    <span class="badge badge-out rounded-pill px-2">Hết hàng</span>
                                @elseif($v->is_low_stock)
                                    <span class="badge badge-low rounded-pill px-2">Sắp hết</span>
                                @else
                                    <span class="badge badge-ok rounded-pill px-2">OK</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-3 text-muted">Chưa có biến thể.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Ảnh --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">Hình ảnh ({{ $product->images->count() }})</div>
            <div class="card-body">
                @forelse($product->images as $img)
                <div class="mb-2 position-relative">
                    <img src="{{ $img->url }}"
                         onerror="this.onerror=null;this.src='{{ asset('assets/images/product-fallback.svg') }}'"
                         class="img-fluid rounded w-100" style="object-fit:cover;max-height:200px">
                    @if($img->is_main)
                    <span class="position-absolute top-0 start-0 m-1 badge" style="background:#c8a96e">Ảnh chính</span>
                    @endif
                </div>
                @empty
                <p class="text-muted text-center">Chưa có ảnh.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
