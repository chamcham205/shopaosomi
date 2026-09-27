@extends('layouts.admin')
@section('title', 'Danh mục')
@section('page-title', 'Quản lý Danh mục')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div><h5 class="mb-0 fw-700">Danh mục sản phẩm</h5></div>
    <a href="{{ route('admin.categories.create') }}" class="btn btn-accent">
        <i class="fa-solid fa-plus me-2"></i>Thêm danh mục
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead style="background:#f9fafb">
                    <tr>
                        <th>Tên danh mục</th>
                        <th class="text-center">Sản phẩm</th>
                        <th class="text-center">Thứ tự</th>
                        <th class="text-center">Trạng thái</th>
                        <th style="width:120px">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $cat)
                    <tr>
                        <td>
                            <div class="fw-600 small">{{ $cat->name }}</div>
                            <code class="text-muted" style="font-size:.7rem">{{ $cat->slug }}</code>
                        </td>
                        <td class="text-center"><span class="badge bg-secondary">{{ $cat->products_count }}</span></td>
                        <td class="text-center">{{ $cat->sort_order }}</td>
                        <td class="text-center">
                            @if($cat->status === 'active')
                                <span class="badge badge-active rounded-pill px-2">Hoạt động</span>
                            @else
                                <span class="badge badge-inactive rounded-pill px-2">Ẩn</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.categories.edit', $cat) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.categories.toggle-status', $cat) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-outline-warning">
                                        <i class="fa-solid fa-toggle-{{ $cat->status === 'active' ? 'on' : 'off' }}"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.categories.destroy', $cat) }}"
                                      onsubmit="return confirm('Xóa danh mục {{ $cat->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-4 text-muted">Chưa có danh mục nào.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($categories->hasPages())
    <div class="card-footer bg-transparent">{{ $categories->links() }}</div>
    @endif
</div>
@endsection
