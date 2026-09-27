@extends('layouts.admin')
@section('title', 'Kích cỡ')
@section('page-title', 'Quản lý Kích cỡ')

@section('content')
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">Thêm kích cỡ mới</div>
            <div class="card-body">
                @if($errors->any())
                <div class="alert alert-danger small"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                @endif
                <form method="POST" action="{{ route('admin.sizes.store') }}">
                @csrf
                    <div class="mb-3">
                        <label class="form-label">Tên kích cỡ <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" placeholder="VD: S, M, L, XL, XXL" maxlength="20">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Thứ tự hiển thị</label>
                        <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}" min="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Trạng thái</label>
                        <select name="status" class="form-select">
                            <option value="active">Hoạt động</option>
                            <option value="inactive">Tắt</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-accent w-100"><i class="fa-solid fa-plus me-2"></i>Thêm kích cỡ</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">Danh sách kích cỡ</div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead style="background:#f9fafb">
                        <tr>
                            <th>Tên</th>
                            <th class="text-center">Thứ tự</th>
                            <th class="text-center">Đang dùng</th>
                            <th class="text-center">Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sizes as $size)
                        <tr>
                            <td><span class="badge bg-dark fs-6 px-3">{{ $size->name }}</span></td>
                            <td class="text-center text-muted">{{ $size->sort_order }}</td>
                            <td class="text-center"><span class="badge bg-secondary">{{ $size->variants_count }}</span></td>
                            <td class="text-center">
                                @if($size->status === 'active')
                                    <span class="badge badge-active rounded-pill px-2">Hoạt động</span>
                                @else
                                    <span class="badge badge-inactive rounded-pill px-2">Tắt</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#sizeModal{{ $size->id }}">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <form method="POST" action="{{ route('admin.sizes.destroy', $size) }}"
                                          onsubmit="return confirm('Xóa kích cỡ {{ $size->name }}?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        {{-- Edit modal --}}
                        <div class="modal fade" id="sizeModal{{ $size->id }}" tabindex="-1">
                            <div class="modal-dialog"><div class="modal-content">
                                <div class="modal-header"><h5 class="modal-title">Sửa: {{ $size->name }}</h5>
                                    <button class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST" action="{{ route('admin.sizes.update', $size) }}">
                                    @csrf @method('PUT')
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Tên kích cỡ</label>
                                            <input type="text" name="name" class="form-control" value="{{ $size->name }}">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Thứ tự</label>
                                            <input type="number" name="sort_order" class="form-control" value="{{ $size->sort_order }}">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Trạng thái</label>
                                            <select name="status" class="form-select">
                                                <option value="active" {{ $size->status === 'active' ? 'selected' : '' }}>Hoạt động</option>
                                                <option value="inactive" {{ $size->status === 'inactive' ? 'selected' : '' }}>Tắt</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                        <button type="submit" class="btn btn-accent">Lưu</button>
                                    </div>
                                </form>
                            </div></div>
                        </div>
                        @empty
                        <tr><td colspan="5" class="text-center py-4 text-muted">Chưa có kích cỡ nào.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
