@extends('layouts.admin')

@section('title', $user->name . ' – Chi tiết khách hàng')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <div class="small text-muted mb-1">
                <a href="{{ route('admin.customers.index') }}" class="text-decoration-none text-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i>Quay lại danh sách khách hàng
                </a>
            </div>
            <h1 class="h3 mb-0 fw-bold">{{ $user->name }}</h1>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.customers.edit', $user) }}" class="btn btn-primary btn-sm rounded-pill px-3">
                <i class="fa-solid fa-pen-to-square me-1"></i>Chỉnh sửa thông tin
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-3" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        {{-- THÔNG TIN KHÁCH HÀNG & CHỈ SỐ --}}
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 rounded-4 p-4 mb-4">
                <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom">
                    <div class="rounded-circle bg-dark text-warning fw-bold d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; font-size: 1.3rem;">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold">{{ $user->name }}</h5>
                        <div class="small text-muted">{{ $user->email }}</div>
                    </div>
                </div>

                <div class="d-flex flex-column gap-2 small mb-4">
                    <div><strong>Điện thoại:</strong> <span class="text-dark">{{ $user->phone ?? 'Chưa cập nhật' }}</span></div>
                    <div><strong>Trạng thái:</strong>
                        @if($user->status === 'active')
                            <span class="badge bg-success-subtle text-success border border-success">active</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger border border-danger">locked</span>
                        @endif
                    </div>
                    <div><strong>Ngày đăng ký:</strong> <span class="text-muted">{{ $user->created_at ? $user->created_at->format('d/m/Y H:i') : '---' }}</span></div>
                </div>

                <div class="row text-center g-2 py-3 bg-light rounded-3 mb-3">
                    <div class="col-4">
                        <strong class="fs-5 text-dark">{{ $stats['total'] }}</strong>
                        <small class="d-block text-muted">Tổng đơn</small>
                    </div>
                    <div class="col-4">
                        <strong class="fs-5 text-success">{{ $stats['completed'] }}</strong>
                        <small class="d-block text-muted">Hoàn thành</small>
                    </div>
                    <div class="col-4">
                        <strong class="fs-5 text-danger">{{ $stats['cancelled'] }}</strong>
                        <small class="d-block text-muted">Đã hủy</small>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Tổng chi tiêu:</span>
                    <strong class="fs-5 text-primary">{{ number_format($stats['spent'], 0, ',', '.') }}₫</strong>
                </div>

                <form method="POST" action="{{ route('admin.customers.status', $user) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="{{ $user->status === 'active' ? 'locked' : 'active' }}">
                    <button class="btn {{ $user->status === 'active' ? 'btn-outline-danger' : 'btn-outline-success' }} w-100 rounded-pill">
                        <i class="fa-solid {{ $user->status === 'active' ? 'fa-lock' : 'fa-unlock' }} me-1"></i>
                        {{ $user->status === 'active' ? 'Khóa tài khoản khách hàng' : 'Mở khóa tài khoản' }}
                    </button>
                </form>
            </div>
        </div>

        {{-- DANH SÁCH ĐƠN HÀNG CỦA KHÁCH HÀNG --}}
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold">Lịch sử đơn hàng ({{ $user->orders->count() }})</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.875rem;">
                        <thead class="table-light text-muted small">
                            <tr>
                                <th>Mã đơn</th>
                                <th>Ngày đặt</th>
                                <th class="text-end">Tổng tiền</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($user->orders as $order)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.orders.show', $order) }}" class="fw-bold text-decoration-none">
                                            {{ $order->order_code }}
                                        </a>
                                    </td>
                                    <td class="text-muted small">
                                        {{ $order->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="text-end fw-semibold">
                                        {{ number_format($order->total, 0, ',', '.') }}₫
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $order->status_label }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        Khách hàng này chưa có đơn hàng nào.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
