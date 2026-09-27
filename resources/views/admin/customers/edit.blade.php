@extends('layouts.admin')

@section('title', 'Chỉnh sửa khách hàng – ' . $user->name)

@section('content')
<div class="container-fluid" style="max-width: 720px;">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="h3 mb-1 fw-bold">Chỉnh sửa khách hàng</h2>
            <p class="text-muted mb-0 small">Cập nhật thông tin tài khoản khách hàng #{{ $user->id }}</p>
        </div>
        <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>Quay lại danh sách
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-3" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i><strong>Vui lòng kiểm tra lại thông tin:</strong>
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0 rounded-4 p-4">
        <form action="{{ route('admin.customers.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label fw-semibold">Họ và tên <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Địa chỉ Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Số điện thoại</label>
                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}" placeholder="Ví dụ: 0901234567">
                @error('phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Đổi mật khẩu mới</label>
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Bỏ trống nếu muốn giữ nguyên mật khẩu cũ">
                <small class="text-muted">Chỉ nhập nếu bạn muốn thay đổi mật khẩu của khách hàng này (tối thiểu 6 ký tự).</small>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Trạng thái tài khoản <span class="text-danger">*</span></label>
                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                    <option value="active" @selected(old('status', $user->status) === 'active')>Hoạt động (Active)</option>
                    <option value="locked" @selected(old('status', $user->status) === 'locked')>Khóa tài khoản (Locked)</option>
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fa-solid fa-check me-1"></i>Lưu thay đổi
                </button>
                <a href="{{ route('admin.customers.index') }}" class="btn btn-light px-3">Hủy</a>
            </div>
        </form>
    </div>
</div>
@endsection
