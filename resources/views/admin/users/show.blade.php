@extends('layouts.admin')

@section('title', 'Thông tin người dùng')

@section('content')
<div class="container-fluid" style="max-width: 720px;">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h2 class="h3 mb-0">Thông tin người dùng</h2>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>Quay lại
        </a>
    </div>

    <div class="card shadow-sm border-0 rounded-4 p-4 mb-3">
        <div class="d-flex align-items-center gap-3 mb-4">
            <div class="rounded-circle bg-dark text-warning fw-bold d-flex align-items-center justify-content-center" style="width: 56px; height: 56px; font-size: 1.5rem;">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div>
                <h4 class="mb-1 fw-bold">{{ $user->name }}</h4>
                <p class="text-muted mb-0">{{ $user->email }}</p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered mb-0">
                <tbody>
                    <tr>
                        <th style="width: 30%;" class="bg-light">ID Người dùng</th>
                        <td>#{{ $user->id }}</td>
                    </tr>
                    <tr>
                        <th class="bg-light">Họ và tên</th>
                        <td>{{ $user->name }}</td>
                    </tr>
                    <tr>
                        <th class="bg-light">Email</th>
                        <td>{{ $user->email }}</td>
                    </tr>
                    <tr>
                        <th class="bg-light">Vai trò</th>
                        <td>
                            @if(in_array($user->role, ['admin', 'super_admin']))
                                <span class="badge bg-danger">{{ $user->role === 'super_admin' ? 'Super Admin' : 'Quản trị (Admin)' }}</span>
                            @else
                                <span class="badge bg-primary">Người dùng</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th class="bg-light">Ngày tạo tài khoản</th>
                        <td>{{ $user->created_at ? $user->created_at->format('d/m/Y H:i') : '---' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="d-flex gap-2 mt-4">
            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary px-3">
                <i class="fa-solid fa-pen-to-square me-1"></i>Chỉnh sửa
            </a>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary px-3">
                Quay lại danh sách
            </a>
        </div>
    </div>
</div>
@endsection
