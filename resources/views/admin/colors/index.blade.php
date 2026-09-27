@extends('layouts.admin')
@section('title', 'Màu sắc')
@section('page-title', 'Quản lý Màu sắc')

@section('content')
<div class="row g-4">
    {{-- Form thêm/sửa --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">Thêm màu sắc mới</div>
            <div class="card-body">
                @if($errors->any())
                <div class="alert alert-danger small"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                @endif
                <form method="POST" action="{{ route('admin.colors.store') }}">
                @csrf
                    <div class="mb-3">
                        <label class="form-label">Tên màu <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" placeholder="VD: Navy, Trắng...">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mã màu HEX <span class="text-danger">*</span></label>
                        <div class="d-flex gap-2">
                            <input type="color" id="colorPicker" value="#000000"
                                   class="form-control form-control-color" style="width:50px">
                            <input type="text" name="hex_code" id="hexInput"
                                   class="form-control @error('hex_code') is-invalid @enderror"
                                   value="{{ old('hex_code', '#000000') }}" placeholder="#FFFFFF" maxlength="7">
                            @error('hex_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-text">Định dạng: #RRGGBB hoặc #RGB</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Trạng thái</label>
                        <select name="status" class="form-select">
                            <option value="active">Hoạt động</option>
                            <option value="inactive">Tắt</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-accent w-100">
                        <i class="fa-solid fa-plus me-2"></i>Thêm màu
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Danh sách màu --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">Danh sách màu sắc ({{ $colors->total() }})</div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead style="background:#f9fafb">
                        <tr>
                            <th>Màu</th>
                            <th>Tên</th>
                            <th>Mã HEX</th>
                            <th class="text-center">Đang dùng</th>
                            <th class="text-center">Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($colors as $color)
                        <tr>
                            <td>
                                <div class="rounded" style="width:32px;height:32px;background:{{ $color->hex_code }};border:2px solid #e5e7eb"></div>
                            </td>
                            <td class="fw-600 small">{{ $color->name }}</td>
                            <td><code>{{ $color->hex_code }}</code></td>
                            <td class="text-center"><span class="badge bg-secondary">{{ $color->variants_count }}</span></td>
                            <td class="text-center">
                                @if($color->status === 'active')
                                    <span class="badge badge-active rounded-pill px-2">Hoạt động</span>
                                @else
                                    <span class="badge badge-inactive rounded-pill px-2">Tắt</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                            data-bs-target="#editModal{{ $color->id }}" title="Sửa">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <form method="POST" action="{{ route('admin.colors.toggle-status', $color) }}">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-sm btn-outline-warning">
                                            <i class="fa-solid fa-toggle-{{ $color->status === 'active' ? 'on' : 'off' }}"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.colors.destroy', $color) }}"
                                          onsubmit="return confirm('Xóa màu {{ $color->name }}?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        {{-- Edit modal --}}
                        <div class="modal fade" id="editModal{{ $color->id }}" tabindex="-1">
                            <div class="modal-dialog"><div class="modal-content">
                                <div class="modal-header"><h5 class="modal-title">Sửa màu: {{ $color->name }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST" action="{{ route('admin.colors.update', $color) }}">
                                    @csrf @method('PUT')
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Tên màu</label>
                                            <input type="text" name="name" class="form-control" value="{{ $color->name }}">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Mã HEX</label>
                                            <input type="text" name="hex_code" class="form-control" value="{{ $color->hex_code }}">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Trạng thái</label>
                                            <select name="status" class="form-select">
                                                <option value="active" {{ $color->status === 'active' ? 'selected' : '' }}>Hoạt động</option>
                                                <option value="inactive" {{ $color->status === 'inactive' ? 'selected' : '' }}>Tắt</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                        <button type="submit" class="btn btn-accent">Lưu</button>
                                    </div>
                                </form>
                            </div></div>
                        </div>
                        @empty
                        <tr><td colspan="6" class="text-center py-4 text-muted">Chưa có màu nào.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($colors->hasPages())
            <div class="card-footer bg-transparent">{{ $colors->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Đồng bộ color picker với text input
document.getElementById('colorPicker').addEventListener('input', function() {
    document.getElementById('hexInput').value = this.value.toUpperCase();
});
document.getElementById('hexInput').addEventListener('input', function() {
    if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
        document.getElementById('colorPicker').value = this.value;
    }
});
</script>
@endpush
