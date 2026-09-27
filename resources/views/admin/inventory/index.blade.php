@extends('layouts.admin')
@section('title', 'Tồn kho')
@section('page-title', 'Quản lý Tồn kho')

@section('content')

{{-- Cảnh báo low stock --}}
@if($lowStockVariants->count() > 0)
<div class="alert alert-warning mb-4">
    <h6><i class="fa-solid fa-triangle-exclamation me-2"></i>Cảnh báo tồn kho thấp ({{ $lowStockVariants->count() }} biến thể)</h6>
    <div class="d-flex flex-wrap gap-2 mt-2">
        @foreach($lowStockVariants->take(10) as $v)
        <span class="badge {{ $v->stock === 0 ? 'badge-out' : 'badge-low' }} rounded-pill px-2">
            {{ $v->product->name }} / {{ $v->color->name }} / {{ $v->size->name }}: {{ $v->stock }}
        </span>
        @endforeach
        @if($lowStockVariants->count() > 10)
            <span class="text-muted small">+{{ $lowStockVariants->count() - 10 }} khác...</span>
        @endif
    </div>
</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0 fw-700">Tổng quan tồn kho sản phẩm</h5>
</div>

{{-- Filter --}}
<div class="card mb-4">
    <div class="card-body py-2">
        <form method="GET" class="d-flex gap-2 align-items-center">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                   placeholder="Tìm tên sản phẩm..." style="max-width:300px">
            <button type="submit" class="btn btn-accent"><i class="fa-solid fa-search"></i></button>
            @if(request('search'))<a href="{{ route('admin.inventory.index') }}" class="btn btn-outline-secondary">Xóa</a>@endif
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead style="background:#f9fafb">
                <tr>
                    <th>Sản phẩm</th>
                    <th>Danh mục</th>
                    <th class="text-center">Biến thể</th>
                    <th class="text-center">Tổng tồn kho</th>
                    <th class="text-center">Tình trạng</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                @php $total = $product->variants_sum_stock ?? 0; @endphp
                <tr>
                    <td>
                        <div class="fw-600 small">{{ $product->name }}</div>
                        <code class="text-muted" style="font-size:.7rem">{{ $product->sku }}</code>
                    </td>
                    <td class="small text-muted">{{ $product->category?->name }}</td>
                    <td class="text-center">{{ $product->variants_count }}</td>
                    <td class="text-center">
                        <span class="fw-700 {{ $total == 0 ? 'text-danger' : ($total <= $threshold ? 'text-warning' : 'text-success') }}">
                            {{ $total }}
                        </span>
                    </td>
                    <td class="text-center">
                        @if($total === 0)
                            <span class="badge badge-out rounded-pill px-2">Hết hàng</span>
                        @elseif($total <= $threshold)
                            <span class="badge badge-low rounded-pill px-2">Sắp hết</span>
                        @else
                            <span class="badge badge-ok rounded-pill px-2">Còn hàng</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.inventory.show', $product) }}" class="btn btn-sm btn-accent">
                            <i class="fa-solid fa-boxes-stacked me-1"></i>Cập nhật
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-4 text-muted">Chưa có sản phẩm nào.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($products->hasPages())
    <div class="card-footer bg-transparent">{{ $products->links() }}</div>
    @endif
</div>
@endsection
