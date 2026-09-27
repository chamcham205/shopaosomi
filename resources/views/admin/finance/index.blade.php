@extends('layouts.admin')

@section('title', 'Thống kê tài chính')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="mb-4">
        <h2 class="h3 mb-1 text-dark fw-bold">Thống kê tài chính</h2>
        <p class="text-muted mb-0">Tổng hợp giá trị thanh toán theo trạng thái và phương thức</p>
    </div>

    {{-- Sub-tabs --}}
    <ul class="nav nav-pills mb-4 gap-2">
        <li class="nav-item">
            <a class="nav-link active fw-semibold" href="{{ route('admin.finance.index') }}">
                <i class="fa-solid fa-chart-pie me-1"></i>Thống kê chỉ số
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link fw-semibold text-secondary" href="{{ route('admin.finance.transactions') }}">
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
        <form method="GET" action="{{ route('admin.finance.index') }}" class="row g-3">
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
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted">Phương thức thanh toán</label>
                <select name="gateway" class="form-select form-select-sm">
                    <option value="">Tất cả phương thức</option>
                    @foreach($methods as $code => $name)
                        <option value="{{ $code }}" @selected(($filters['gateway'] ?? '') === $code)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted">Trạng thái thanh toán</label>
                <select name="payment_status" class="form-select form-select-sm">
                    <option value="">Tất cả trạng thái</option>
                    @foreach($statuses as $code => $name)
                        <option value="{{ $code }}" @selected(($filters['payment_status'] ?? '') === $code)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 d-flex justify-content-end gap-2 pt-2 border-top">
                <a href="{{ route('admin.finance.index') }}" class="btn btn-sm btn-light border">
                    <i class="fa-solid fa-rotate-left me-1"></i>Đặt lại
                </a>
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fa-solid fa-filter me-1"></i>Lọc dữ liệu
                </button>
            </div>
        </form>
    </div>

    {{-- KPI Cards --}}
    @php
        $totalOrders = (int) ($summary->order_count ?? 0);
        $totalAmount = (float) ($summary->total_amount ?? 0);

        $paidCount = (int) ($statusTotals['paid']->order_count ?? 0);
        $paidAmount = (float) ($statusTotals['paid']->total_amount ?? 0);

        $pendingCount = (int) ($statusTotals['pending']->order_count ?? 0);
        $pendingAmount = (float) ($statusTotals['pending']->total_amount ?? 0);

        $failedCount = (int) ($statusTotals['failed']->order_count ?? 0);
        $failedAmount = (float) ($statusTotals['failed']->total_amount ?? 0);

        $refundedCount = (int) ($statusTotals['refunded']->order_count ?? 0);
        $refundedAmount = (float) ($statusTotals['refunded']->total_amount ?? 0);

        $codCount = (int) ($methodTotals['cod']->order_count ?? 0);
        $codAmount = (float) ($methodTotals['cod']->total_amount ?? 0);

        $paidRate = $totalAmount > 0 ? round(($paidAmount / $totalAmount) * 100, 1) : 0;
    @endphp

    <div class="row g-3 mb-4">
        {{-- Tổng đơn hàng --}}
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 p-3 bg-white border-start border-4 border-primary">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">Tổng số đơn hàng</div>
                        <h4 class="fw-bold mb-0 text-dark mt-1">{{ number_format($totalOrders) }}</h4>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                        <i class="fa-solid fa-receipt fa-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tổng giá trị đơn hàng --}}
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 p-3 bg-white border-start border-4 border-info">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">Tổng giá trị đơn hàng</div>
                        <h4 class="fw-bold mb-0 text-info mt-1">{{ number_format($totalAmount) }} đ</h4>
                    </div>
                    <div class="bg-info bg-opacity-10 text-info p-3 rounded-circle">
                        <i class="fa-solid fa-wallet fa-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Đã thanh toán --}}
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 p-3 bg-white border-start border-4 border-success">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">Đã thanh toán ({{ number_format($paidCount) }} đơn)</div>
                        <h4 class="fw-bold mb-0 text-success mt-1">{{ number_format($paidAmount) }} đ</h4>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle">
                        <i class="fa-solid fa-circle-check fa-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Chờ thanh toán --}}
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 p-3 bg-white border-start border-4 border-warning">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">Chờ thanh toán ({{ number_format($pendingCount) }} đơn)</div>
                        <h4 class="fw-bold mb-0 text-warning mt-1">{{ number_format($pendingAmount) }} đ</h4>
                    </div>
                    <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-circle">
                        <i class="fa-solid fa-clock fa-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Thất bại --}}
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 p-3 bg-white border-start border-4 border-danger">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">Thất bại ({{ number_format($failedCount) }} đơn)</div>
                        <h4 class="fw-bold mb-0 text-danger mt-1">{{ number_format($failedAmount) }} đ</h4>
                    </div>
                    <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-circle">
                        <i class="fa-solid fa-circle-xmark fa-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Hoàn tiền --}}
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 p-3 bg-white border-start border-4 border-secondary">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">Đã hoàn tiền ({{ number_format($refundedCount) }} đơn)</div>
                        <h4 class="fw-bold mb-0 text-secondary mt-1">{{ number_format($refundedAmount) }} đ</h4>
                    </div>
                    <div class="bg-secondary bg-opacity-10 text-secondary p-3 rounded-circle">
                        <i class="fa-solid fa-arrow-rotate-left fa-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tiền COD --}}
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 p-3 bg-white border-start border-4 border-dark">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">Tổng đơn COD ({{ number_format($codCount) }} đơn)</div>
                        <h4 class="fw-bold mb-0 text-dark mt-1">{{ number_format($codAmount) }} đ</h4>
                    </div>
                    <div class="bg-dark bg-opacity-10 text-dark p-3 rounded-circle">
                        <i class="fa-solid fa-truck-ramp-box fa-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tỷ lệ đã thanh toán --}}
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 p-3 bg-white border-start border-4 border-primary">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">Tỷ lệ đã thanh toán</div>
                        <h4 class="fw-bold mb-0 text-primary mt-1">{{ $paidRate }}%</h4>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                        <i class="fa-solid fa-percent fa-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Thống kê theo phương thức --}}
    <div class="card shadow-sm border-0 rounded-3 overflow-hidden bg-white mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0 fw-bold text-dark">Thống kê theo phương thức</h5>
            <span class="badge bg-light text-muted border">Báo cáo tài chính</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Phương thức</th>
                        <th class="text-center">Số đơn</th>
                        <th class="text-end">Tổng tiền</th>
                        <th class="text-end">Đã thanh toán</th>
                        <th class="text-end">Chờ xử lý</th>
                        <th class="text-center pe-4">Tỷ lệ thanh toán</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($methods as $code => $name)
                        @php
                            $mTotal = (float) ($methodTotals[$code]->total_amount ?? 0);
                            $mPaid = (float) ($methodTotals[$code]->paid_amount ?? 0);
                            $mPending = max(0, $mTotal - $mPaid);
                            $mCount = (int) ($methodTotals[$code]->order_count ?? 0);
                            $mRate = $mTotal > 0 ? round(($mPaid / $mTotal) * 100, 1) : 0;
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    @if($code === 'cod')
                                        <span class="badge bg-secondary p-2"><i class="fa-solid fa-truck-fast"></i></span>
                                    @elseif($code === 'momo')
                                        <span class="badge bg-danger p-2"><i class="fa-solid fa-mobile-screen"></i></span>
                                    @else
                                        <span class="badge bg-light text-dark border p-2"><i class="fa-solid fa-circle-question"></i></span>
                                    @endif
                                    <div>
                                        <span class="fw-bold text-dark">{{ $name }}</span>
                                        <span class="badge bg-light text-muted border ms-1">{{ strtoupper($code) }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center fw-semibold">{{ number_format($mCount) }}</td>
                            <td class="text-end fw-bold text-dark">{{ number_format($mTotal) }} đ</td>
                            <td class="text-end text-success fw-semibold">{{ number_format($mPaid) }} đ</td>
                            <td class="text-end text-warning fw-semibold">{{ number_format($mPending) }} đ</td>
                            <td class="text-center pe-4">
                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 6px; max-width: 100px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $mRate }}%"></div>
                                    </div>
                                    <span class="small fw-bold text-muted">{{ $mRate }}%</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Không có dữ liệu phương thức thanh toán.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection