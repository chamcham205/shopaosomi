@extends('layouts.admin')

@section('title', 'Chỉnh sửa người dùng')

@section('content')
<div class="container-fluid" style="max-width: 720px;">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h2 class="h3 mb-0">Chỉnh sửa người dùng</h2>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>Quay lại danh sách
        </a>
    </div>

    <div class="card shadow-sm border-0 rounded-4 p-4">
        <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label fw-semibold">Họ và tên <span class="text-danger">*</span></label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Địa chỉ Email <span class="text-danger">*</span></label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Vai trò phân quyền <span class="text-danger">*</span></label>
                <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                    <option value="user" @selected($user->role === 'customer' || $user->role === 'user')>Người dùng (Khách hàng)</option>
                    <option value="admin" @selected(in_array($user->role, ['admin', 'super_admin']))>Quản trị viên (Admin)</option>
                </select>
                @error('role')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fa-solid fa-check me-1"></i>Cập nhật
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-light px-3">Hủy</a>
            </div>
        </form>
    </div>
</div>
@endsection
