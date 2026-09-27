@extends('layouts.admin')

@section('title', 'Giao dịch thanh toán')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="mb-4">
        <h2 class="h3 mb-1 text-dark fw-bold">Giao dịch thanh toán</h2>
        <p class="text-muted mb-0">Tra cứu thanh toán theo đơn hàng và cập nhật trạng thái COD.</p>
    </div>

    {{-- Sub-tabs --}}
    <ul class="nav nav-pills mb-4 gap-2">
        <li class="nav-item">
            <a class="nav-link fw-semibold text-secondary" href="{{ route('admin.finance.index') }}">
                <i class="fa-solid fa-chart-pie me-1"></i>Thống kê chỉ số
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link active fw-semibold" href="{{ route('admin.finance.transactions') }}">
                <i class="fa-solid fa-list-check me-1"></i>Giao dịch thanh toán
            </a>
        </li>
    </ul>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(isset($errors) && $errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Bộ lọc --}}
    <div class="card shadow-sm border-0 rounded-3 p-3 mb-4 bg-white">
        <form method="GET" action="{{ route('admin.finance.transactions') }}" class="row g-3">
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">Tìm đơn hàng</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Mã đơn, tên hoặc số điện thoại" value="{{ $filters['search'] ?? '' }}">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">Từ ngày tạo đơn</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $filters['date_from'] ?? '' }}">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">Đến ngày tạo đơn</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $filters['date_to'] ?? '' }}">
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted">Số tiền từ (đ)</label>
                <input type="number" step="any" min="0" name="min_amount" class="form-control form-control-sm" placeholder="Không giới hạn" value="{{ $filters['min_amount'] ?? '' }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted">Số tiền đến (đ)</label>
                <input type="number" step="any" min="0" name="max_amount" class="form-control form-control-sm" placeholder="Không giới hạn" value="{{ $filters['max_amount'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted">Phương thức</label>
                <select name="gateway" class="form-select form-select-sm">
                    <option value="">Tất cả</option>
                    @foreach($methods as $code => $name)
                        <option value="{{ $code }}" @selected(($filters['gateway'] ?? '') === $code)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted">Trạng thái thanh toán</label>
                <select name="payment_status" class="form-select form-select-sm">
                    <option value="">Tất cả</option>
                    @foreach($statuses as $code => $name)
                        <option value="{{ $code }}" @selected(($filters['payment_status'] ?? '') === $code)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted">Sắp xếp</label>
                <select name="sort" class="form-select form-select-sm">
                    <option value="newest" @selected(($filters['sort'] ?? '') === 'newest')>Mới nhất</option>
                    <option value="oldest" @selected(($filters['sort'] ?? '') === 'oldest')>Cũ nhất</option>
                    <option value="amount_asc" @selected(($filters['sort'] ?? '') === 'amount_asc')>Số tiền tăng dần</option>
                    <option value="amount_desc" @selected(($filters['sort'] ?? '') === 'amount_desc')>Số tiền giảm dần</option>
                </select>
            </div>

            <div class="col-12 d-flex justify-content-end gap-2 pt-2 border-top">
                <a href="{{ route('admin.finance.transactions') }}" class="btn btn-sm btn-light border">
                    <i class="fa-solid fa-rotate-left me-1"></i>Đặt lại
                </a>
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fa-solid fa-filter me-1"></i>Lọc dữ liệu
                </button>
            </div>
        </form>
    </div>

    {{-- Bảng danh sách giao dịch --}}
    <div class="card shadow-sm border-0 rounded-3 overflow-hidden bg-white mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <h5 class="card-title mb-0 fw-bold text-dark">Danh sách giao dịch</h5>
                <span class="badge bg-primary rounded-pill">{{ $orders->total() }}</span>
            </div>
            <span class="small text-muted">Trang {{ $orders->currentPage() }} / {{ $orders->lastPage() }}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Đơn hàng</th>
                        <th>Khách hàng</th>
                        <th>Phương thức</th>
                        <th class="text-end">Số tiền</th>
                        <th>Trạng thái thanh toán</th>
                        <th style="min-width: 220px;">Cập nhật COD</th>
                        <th class="text-center pe-4">Chi tiết</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            {{-- Đơn hàng --}}
                            <td class="ps-4">
                                <div class="fw-bold text-dark">#{{ $order->id }}</div>
                                <div class="text-primary small fw-semibold">{{ $order->order_code ?? '---' }}</div>
                                <div class="text-muted small">
                                    <i class="fa-regular fa-clock me-1"></i>{{ $order->created_at ? \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') : '---' }}
                                </div>
                            </td>

                            {{-- Khách hàng --}}
                            <td>
                                <div class="fw-semibold text-dark">{{ $order->name }}</div>
                                <div class="text-muted small">
                                    <i class="fa-solid fa-phone me-1"></i>{{ $order->phone ?? '---' }}
                                </div>
                            </td>

                            {{-- Phương thức --}}
                            <td>
                                @if($order->gateway === 'cod')
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary px-2 py-1">
                                        <i class="fa-solid fa-truck-fast me-1"></i>COD
                                    </span>
                                @elseif($order->gateway === 'momo')
                                    <span class="badge bg-danger-subtle text-danger border border-danger px-2 py-1">
                                        <i class="fa-solid fa-mobile-screen me-1"></i>MoMo
                                    </span>
                                @else
                                    <span class="badge bg-light text-muted border px-2 py-1">
                                        <i class="fa-solid fa-circle-question me-1"></i>{{ strtoupper($order->gateway ?? 'N/A') }}
                                    </span>
                                @endif

                                @if($order->payment_id)
                                    <div class="small text-muted mt-1 font-monospace">TX #{{ $order->payment_id }}</div>
                                @endif
                            </td>

                            {{-- Số tiền --}}
                            <td class="text-end">
                                <span class="fw-bold text-dark">{{ number_format($order->total_price) }} đ</span>
                            </td>

                            {{-- Trạng thái thanh toán --}}
                            <td>
                                @php
                                    $pStatus = $order->payment_status;
                                    $badgeClass = match($pStatus) {
                                        'paid' => 'bg-success text-white',
                                        'pending' => 'bg-warning text-dark',
                                        'failed' => 'bg-danger text-white',
                                        'refunded' => 'bg-secondary text-white',
                                        'refund_pending' => 'bg-info text-dark',
                                        default => 'bg-light text-dark border',
                                    };
                                    $statusName = $statuses[$pStatus] ?? $pStatus;
                                @endphp
                                <span class="badge {{ $badgeClass }} px-2 py-1">
                                    {{ $statusName }}
                                </span>
                                @if($order->paid_at)
                                    <div class="small text-muted mt-1">
                                        {{ \Carbon\Carbon::parse($order->paid_at)->format('d/m/Y H:i') }}
                                    </div>
                                @endif
                            </td>

                            {{-- Cập nhật COD --}}
                            <td>
                                @if($order->gateway === 'cod')
                                    @if($order->payment_status === 'paid')
                                        <span class="badge bg-success-subtle text-success border border-success px-2 py-1">
                                            <i class="fa-solid fa-check-double me-1"></i>Đã thu tiền COD
                                        </span>
                                    @elseif($order->payment_status === 'refunded')
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary px-2 py-1">
                                            <i class="fa-solid fa-arrow-rotate-left me-1"></i>Đã hoàn tiền
                                        </span>
                                    @else
                                        @php
                                            $transitions = $codTransitions[$order->payment_status] ?? ['pending', 'paid', 'failed'];
                                        @endphp
                                        <form method="POST" action="{{ route('admin.finance.update-status', $order->id) }}" class="d-flex align-items-center gap-1">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="current_payment_status" value="{{ $order->payment_status }}">
                                            <input type="hidden" name="current_order_status" value="{{ $order->status }}">
                                            <input type="hidden" name="current_payment_id" value="{{ $order->payment_id ?? 0 }}">

                                            <select name="payment_status" class="form-select form-select-sm" style="min-width: 140px;">
                                                @foreach($transitions as $transStatus)
                                                    <option value="{{ $transStatus }}" @selected($transStatus === $order->payment_status)>
                                                        {{ $statuses[$transStatus] ?? $transStatus }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-outline-primary" title="Lưu trạng thái COD">
                                                <i class="fa-solid fa-floppy-disk"></i>
                                            </button>
                                        </form>
                                    @endif
                                @else
                                    <span class="badge bg-light text-muted border px-2 py-1">
                                        <i class="fa-solid fa-bolt me-1"></i>Cổng tự động
                                    </span>
                                @endif
                            </td>

                            {{-- Chi tiết --}}
                            <td class="text-center pe-4">
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-outline-secondary" title="Xem chi tiết đơn hàng">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-inbox fa-3x mb-3 text-secondary d-block"></i>
                                Không tìm thấy giao dịch nào phù hợp với bộ lọc.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
            <div class="card-footer bg-white py-3 border-top d-flex justify-content-center">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
