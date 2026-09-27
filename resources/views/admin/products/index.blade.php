@extends('layouts.admin')

@section('title', 'Sản phẩm')
@section('page-title', 'Quản lý Sản phẩm')

@section('content')
{{-- Toolbar --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="mb-0 fw-700">Danh sách Sản phẩm</h5>
        <p class="text-muted small mb-0">Quản lý toàn bộ sản phẩm áo sơ mi</p>
    </div>
    <a href="{{ route('admin.products.create') }}" class="btn btn-accent">
        <i class="fa-solid fa-plus me-2"></i>Thêm sản phẩm
    </a>
</div>

{{-- Filter / Search --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Tìm kiếm</label>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                       placeholder="Tên sản phẩm hoặc SKU...">
            </div>
            <div class="col-md-3">
                <label class="form-label">Danh mục</label>
                <select name="category_id" class="form-select">
                    <option value="">Tất cả danh mục</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="">Tất cả</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Đang bán</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Ẩn</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Nháp</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Tồn kho</label>
                <select name="low_stock" class="form-select">
                    <option value="">Tất cả</option>
                    <option value="1" {{ request('low_stock') ? 'selected' : '' }}>Sắp hết hàng</option>
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-accent w-100"><i class="fa-solid fa-search"></i></button>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead style="background:#f9fafb">
                    <tr>
                        <th style="width:60px">Ảnh</th>
                        <th>Tên / SKU</th>
                        <th>Danh mục</th>
                        <th>Giá</th>
                        <th class="text-center">Tồn kho</th>
                        <th class="text-center">Trạng thái</th>
                        <th class="text-center">Nổi bật</th>
                        <th style="width:140px">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
                        {{-- Ảnh --}}
                        <td>
                            @if($product->primaryImagePath)
                                <img src="{{ $product->primary_image_url }}"
                                     onerror="this.onerror=null;this.src='{{ asset('assets/images/product-fallback.svg') }}'"
                                     class="rounded" width="48" height="48" style="object-fit:cover">
                            @else
                                <div class="rounded bg-light d-flex align-items-center justify-content-center"
                                     style="width:48px;height:48px">
                                    <i class="fa-solid fa-shirt text-muted" style="font-size:1.2rem"></i>
                                </div>
                            @endif
                        </td>

                        {{-- Tên + SKU --}}
                        <td>
                            <div class="fw-600 small">{{ $product->name }}</div>
                            <code class="text-muted" style="font-size:.75rem">{{ $product->sku }}</code>
                        </td>

                        {{-- Danh mục --}}
                        <td><span class="small text-muted">{{ $product->category?->name ?? '–' }}</span></td>

                        {{-- Giá --}}
                        <td>
                            @if($product->is_on_sale)
                                <div class="fw-600 text-danger small">{{ number_format($product->current_price) }}đ</div>
                                <del class="text-muted" style="font-size:.75rem">{{ number_format($product->price) }}đ</del>
                            @else
                                <div class="fw-600 small">{{ number_format($product->price) }}đ</div>
                            @endif
                        </td>

                        {{-- Tồn kho --}}
                        <td class="text-center">
                            @php $totalStock = $product->variants_sum_stock ?? 0; @endphp
                            @if($totalStock == 0)
                                <span class="badge badge-out rounded-pill px-2">Hết hàng</span>
                            @elseif($totalStock <= $threshold)
                                <span class="badge badge-low rounded-pill px-2">{{ $totalStock }} – Sắp hết</span>
                            @else
                                <span class="badge badge-ok rounded-pill px-2">{{ $totalStock }}</span>
                            @endif
                        </td>

                        {{-- Trạng thái --}}
                        <td class="text-center">
                            @if($product->status === 'active')
                                <span class="badge badge-active rounded-pill px-2">Đang bán</span>
                            @elseif($product->status === 'inactive')
                                <span class="badge badge-inactive rounded-pill px-2">Ẩn</span>
                            @else
                                <span class="badge badge-draft rounded-pill px-2">Nháp</span>
                            @endif
                        </td>

                        {{-- Featured --}}
                        <td class="text-center">
                            @if($product->featured)
                                <i class="fa-solid fa-star text-warning"></i>
                            @else
                                <i class="fa-regular fa-star text-muted"></i>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.products.edit', $product) }}"
                                   class="btn btn-sm btn-outline-primary" title="Sửa">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <a href="{{ route('admin.inventory.show', $product) }}"
                                   class="btn btn-sm btn-outline-secondary" title="Tồn kho">
                                    <i class="fa-solid fa-boxes-stacked"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.products.toggle-status', $product) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-outline-warning" title="Bật/Tắt">
                                        <i class="fa-solid fa-toggle-{{ $product->status === 'active' ? 'on' : 'off' }}"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.products.destroy', $product) }}"
                                      onsubmit="return confirm('Xóa sản phẩm này?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-box-open fa-2x mb-2 d-block"></i>
                            Chưa có sản phẩm nào. <a href="{{ route('admin.products.create') }}">Thêm ngay</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($products->hasPages())
    <div class="card-footer bg-transparent">{{ $products->links() }}</div>
    @endif
</div>
@endsection
