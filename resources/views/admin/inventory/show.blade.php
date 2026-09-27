@extends('layouts.admin')
@section('title', 'Tồn kho: ' . $product->name)
@section('page-title', 'Cập nhật Tồn kho')

@push('styles')
<style>
    .stock-cell input { width:80px; text-align:center; }
    .stock-0  { background:#fee2e2; }
    .stock-low { background:#fef3c7; }
</style>
@endpush

@section('content')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('admin.inventory.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-arrow-left"></i></a>
    <div>
        <h5 class="mb-0 fw-700">{{ $product->name }}</h5>
        <small class="text-muted">SKU: {{ $product->sku }} | Ngưỡng cảnh báo: ≤ {{ $threshold }}</small>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        Ma trận Tồn kho (Màu × Kích cỡ)
        <div>
            <span class="badge badge-out me-1">Hết hàng = 0</span>
            <span class="badge badge-low me-1">Sắp hết ≤ {{ $threshold }}</span>
            <span class="badge badge-ok">Còn hàng</span>
        </div>
    </div>
    <div class="card-body">
        @if($colors->isEmpty())
            <div class="text-center py-4 text-muted">
                <i class="fa-solid fa-info-circle me-2"></i>
                Sản phẩm này chưa có biến thể nào.
                <a href="{{ route('admin.products.edit', $product) }}">Thêm biến thể tại đây</a>.
            </div>
        @else
        <form method="POST" action="{{ route('admin.inventory.update', $product) }}">
            @csrf
            <div class="table-responsive">
                <table class="table table-bordered" style="min-width:400px">
                    <thead class="table-dark">
                        <tr>
                            <th>Màu \ Kích cỡ</th>
                            @foreach($sizes as $size)
                            <th class="text-center">{{ $size->name }}</th>
                            @endforeach
                            <th class="text-center">Tổng</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($colors as $color)
                        @php $rowTotal = 0; @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle" style="width:14px;height:14px;background:{{ $color->hex_code }};border:1px solid #ccc"></div>
                                    <span class="fw-600 small">{{ $color->name }}</span>
                                </div>
                            </td>
                            @foreach($sizes as $size)
                            @php
                                $variant = $matrix[$color->id][$size->id] ?? null;
                                $stock = $variant ? $variant->stock : null;
                                $cellClass = is_null($stock) ? '' : ($stock === 0 ? 'stock-0' : ($stock <= $threshold ? 'stock-low' : ''));
                                if (!is_null($stock)) $rowTotal += $stock;
                            @endphp
                            <td class="text-center stock-cell {{ $cellClass }}">
                                @if($variant)
                                    <input type="number"
                                           name="stock[{{ $variant->id }}]"
                                           value="{{ $stock }}"
                                           min="0"
                                           class="form-control form-control-sm d-inline-block">
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            @endforeach
                            <td class="text-center fw-700">{{ $rowTotal }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th>Tổng theo size</th>
                            @foreach($sizes as $size)
                            @php
                                $colTotal = collect($colors)->sum(fn($c) => $matrix[$c->id][$size->id]->stock ?? 0);
                            @endphp
                            <th class="text-center">{{ $colTotal }}</th>
                            @endforeach
                            @php
                                $grandTotal = collect($colors)->sum(fn($c) => collect($sizes)->sum(fn($s) => $matrix[$c->id][$s->id]->stock ?? 0));
                            @endphp
                            <th class="text-center text-success">{{ $grandTotal }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="mt-3 d-flex gap-2">
                <button type="submit" class="btn btn-accent">
                    <i class="fa-solid fa-floppy-disk me-2"></i>Lưu tồn kho
                </button>
                <a href="{{ route('admin.inventory.index') }}" class="btn btn-outline-secondary">Hủy</a>
            </div>
        </form>
        @endif
    </div>
</div>
@endsection
