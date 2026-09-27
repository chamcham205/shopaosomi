@extends('layouts.admin')

@section('title', 'Khách hàng')

@section('content')
<div class="container-fluid">
    {{-- HEADER & NÚT THÊM KHÁCH HÀNG --}}
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h1 class="h3 mb-1 fw-bold">Khách hàng</h1>
            <p class="text-muted mb-0 small">Quản lý danh sách khách hàng, thông tin tài khoản và lịch sử đặt hàng</p>
        </div>
        <button type="button" class="btn btn-success rounded-pill px-3 shadow-sm d-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#createCustomerModal">
            <i class="fa-solid fa-user-plus"></i>
            <span>Thêm khách hàng</span>
        </button>
    </div>

    {{-- FLASH MESSAGES --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-3" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-3" role="alert">
            <i class="fa-solid fa-circle-xmark me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
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

    {{-- BỘ LỌC TÌM KIẾM --}}
    <form class="card card-body mb-3 shadow-sm border-0 rounded-3" method="GET" action="{{ route('admin.customers.index') }}">
        <div class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Tên, email, điện thoại">
                </div>
            </div>
            <div class="col-md-4">
                <select class="form-select" name="status">
                    <option value="">Mọi trạng thái</option>
                    <option value="active" @selected(request('status')==='active')>Active</option>
                    <option value="locked" @selected(request('status')==='locked')>Locked</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-primary flex-fill"><i class="fa-solid fa-filter me-1"></i>Lọc</button>
                @if(request('q') || request('status'))
                    <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary" title="Đặt lại bộ lọc">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </div>
    </form>

    {{-- BẢNG DANH SÁCH KHÁCH HÀNG --}}
    <div class="card shadow-sm border-0 rounded-3 overflow-hidden mb-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.875rem;">
                <thead class="table-light text-muted small">
                    <tr>
                        <th>TÊN</th>
                        <th>EMAIL/SĐT</th>
                        <th>TRẠNG THÁI</th>
                        <th>ĐĂNG KÝ</th>
                        <th class="text-center">ĐƠN</th>
                        <th class="text-center">HOÀN THÀNH</th>
                        <th class="text-end">ĐÃ CHI</th>
                        <th class="text-center" style="width: 140px;">HÀNH ĐỘNG</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $user)
                        <tr>
                            <td>
                                <div class="fw-semibold text-dark">{{ $user->name }}</div>
                            </td>
                            <td>
                                <div>{{ $user->email }}</div>
                                <small class="text-muted">{{ $user->phone ?? '---' }}</small>
                            </td>
                            <td>
                                @if($user->status === 'active')
                                    <span class="badge bg-success-subtle text-success border border-success">active</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger">locked</span>
                                @endif
                            </td>
                            <td class="text-muted small">
                                {{ $user->created_at ? $user->created_at->format('d/m/Y') : '---' }}
                            </td>
                            <td class="text-center fw-medium">{{ $user->orders_count }}</td>
                            <td class="text-center fw-medium">{{ $user->completed_orders_count }}</td>
                            <td class="text-end fw-bold text-dark">
                                {{ number_format($user->total_spent ?? 0, 0, ',', '.') }}₫
                            </td>
                            <td class="text-center">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('admin.customers.show', $user) }}" class="btn btn-sm btn-info text-white px-2.5 py-1" title="Xem chi tiết">
                                        <i class="fa-solid fa-eye me-1"></i>Xem
                                    </a>
                                    <a href="{{ route('admin.customers.edit', $user) }}" class="btn btn-sm btn-primary px-2.5 py-1" title="Chỉnh sửa">
                                        <i class="fa-solid fa-pen-to-square me-1"></i>Sửa
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-users fs-2 mb-2 opacity-50"></i>
                                <div class="fw-semibold">Không tìm thấy khách hàng nào</div>
                                <small>Thử thay đổi từ khóa tìm kiếm hoặc bấm thêm khách hàng mới.</small>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($customers->hasPages())
            <div class="p-3 border-top bg-white d-flex justify-content-end">
                {{ $customers->links() }}
            </div>
        @endif
    </div>
</div>

{{-- MODAL THÊM KHÁCH HÀNG MỚI --}}
<div class="modal fade" id="createCustomerModal" tabindex="-1" aria-labelledby="createCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-dark text-white p-3 px-4 border-0">
                <h5 class="modal-title fw-bold" id="createCustomerModalLabel">
                    <i class="fa-solid fa-user-plus me-2" style="color: var(--accent, #c8a96e);"></i>Thêm khách hàng mới
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.customers.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Họ và tên <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Nhập họ và tên khách hàng" required value="{{ old('name') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Địa chỉ Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" placeholder="example@domain.com" required value="{{ old('email') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Số điện thoại</label>
                        <input type="text" name="phone" class="form-control" placeholder="Ví dụ: 0901234567" value="{{ old('phone') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Mật khẩu <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" placeholder="Tối thiểu 6 ký tự" required minlength="6">
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-semibold small">Trạng thái tài khoản</label>
                        <select name="status" class="form-select">
                            <option value="active" @selected(old('status') === 'active' || !old('status'))>Hoạt động (Active)</option>
                            <option value="locked" @selected(old('status') === 'locked')>Khóa tài khoản (Locked)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 px-4 bg-light">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold">
                        <i class="fa-solid fa-floppy-disk me-1"></i>Lưu khách hàng
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
