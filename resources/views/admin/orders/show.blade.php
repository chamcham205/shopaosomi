@extends('layouts.admin')
@section('title', 'Đơn hàng ' . $order->order_code)
@section('content')
{{-- THANH ĐIỀU HƯỚNG VÀ NÚT QUAY LẠI --}}
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-2 px-3 py-2 rounded-3 shadow-sm bg-white">
        <i class="fa-solid fa-arrow-left"></i>
        <span class="fw-semibold">Quay lại danh sách đơn hàng</span>
    </a>
    <div class="small text-muted">
        Quản trị / <a href="{{ route('admin.orders.index') }}" class="text-decoration-none text-muted">Đơn hàng</a> / <span class="text-dark fw-semibold">{{ $order->order_code }}</span>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-1 fw-bold text-dark">{{ $order->order_code }}</h1>
        <div class="small text-muted">
            <i class="fa-regular fa-clock me-1"></i>Thời gian đặt: <span class="fw-semibold text-dark">{{ $order->created_at->format('d/m/Y H:i:s') }}</span>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2">
        @if($order->status === 'paid')
            <span class="badge bg-success-subtle text-success border border-success px-3 py-2 fs-6">
                <i class="fa-solid fa-circle-check me-1"></i>Đã thanh toán (MoMo)
            </span>
        @elseif($order->status === 'cod_ordered')
            <span class="badge bg-info-subtle text-info-emphasis border border-info px-3 py-2 fs-6">
                <i class="fa-solid fa-truck-ramp-box me-1"></i>Chờ giao hàng (COD)
            </span>
        @elseif($order->status === 'pending')
            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning px-3 py-2 fs-6">
                <i class="fa-solid fa-clock me-1"></i>Chờ xác nhận
            </span>
        @elseif($order->status === 'confirmed')
            <span class="badge bg-primary-subtle text-primary border border-primary px-3 py-2 fs-6">
                <i class="fa-solid fa-thumbs-up me-1"></i>Đã xác nhận
            </span>
        @elseif($order->status === 'packing')
            <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary px-3 py-2 fs-6">
                <i class="fa-solid fa-box-open me-1"></i>Đang đóng gói
            </span>
        @elseif($order->status === 'shipping')
            <span class="badge bg-primary text-white px-3 py-2 fs-6">
                <i class="fa-solid fa-truck-fast me-1"></i>Đang giao hàng
            </span>
        @elseif($order->status === 'completed')
            <span class="badge bg-success text-white px-3 py-2 fs-6">
                <i class="fa-solid fa-circle-check me-1"></i>Hoàn thành
            </span>
        @elseif($order->status === 'cancelled')
            <span class="badge bg-danger-subtle text-danger border border-danger px-3 py-2 fs-6">
                <i class="fa-solid fa-ban me-1"></i>Đã hủy
            </span>
        @else
            <span class="badge bg-secondary px-3 py-2 fs-6">{{ $order->status_label }}</span>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
        <i class="fa-solid fa-circle-xmark me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-8">
        {{-- DANH SÁCH SẢN PHẨM --}}
        <div class="card shadow-sm border-0 rounded-3 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0 fw-bold"><i class="fa-solid fa-basket-shopping me-2 text-primary"></i>Sản phẩm trong đơn</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3 py-2">Sản phẩm</th>
                                <th class="py-2 text-center">Số lượng</th>
                                <th class="py-2 text-end">Đơn giá</th>
                                <th class="py-2 text-end pe-3">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                <tr>
                                    <td class="ps-3 py-3">
                                        <div class="fw-semibold text-dark">{{ $item->product_name }}</div>
                                        <small class="text-muted">
                                            SKU: {{ $item->product_sku }} · Màu: {{ $item->color_name }} · Size: {{ $item->size_name }}
                                        </small>
                                    </td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-end">{{ number_format($item->price, 0, ',', '.') }}₫</td>
                                    <td class="text-end pe-3 fw-bold text-dark">{{ number_format($item->subtotal, 0, ',', '.') }}₫</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- LỊCH SỬ TIẾN TRÌNH --}}
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0 fw-bold"><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>Lịch sử trạng thái</h5>
            </div>
            <div class="card-body p-4">
                @forelse($order->statusLogs as $log)
                    <div class="d-flex align-items-start gap-3 mb-3 pb-3 border-bottom">
                        <div class="rounded-circle bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                            <i class="fa-solid fa-arrow-right-arrow-left small"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold text-dark">
                                {{ $log->from_status ? ucfirst($log->from_status) : 'Khởi tạo đơn' }}
                                <i class="fa-solid fa-arrow-right text-muted mx-1 small"></i>
                                <span class="text-primary">{{ ucfirst($log->to_status) }}</span>
                            </div>
                            <small class="text-muted d-block mt-1">
                                <i class="fa-regular fa-clock me-1"></i>{{ $log->created_at->format('d/m/Y H:i:s') }}
                                @if($log->note) · <span class="text-dark fw-medium">{{ $log->note }}</span> @endif
                            </small>
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">Chưa có lịch sử trạng thái.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- CỘT PHẢI: THÔNG TIN KHÁCH HÀNG & CẬP NHẬT TRẠNG THÁI --}}
    <aside class="col-lg-4">
        {{-- THÔNG TIN GIAO HÀNG & GHN --}}
        <div class="card shadow-sm border-0 rounded-3 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0 fw-bold"><i class="fa-solid fa-location-dot me-2 text-danger"></i>Giao hàng & Người nhận</h5>
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>{{ $order->recipient_name }}</strong></p>
                <p class="mb-1 text-secondary"><i class="fa-solid fa-phone me-1 text-muted"></i>{{ $order->phone }}</p>
                @if($order->email)
                    <p class="mb-2 text-secondary"><i class="fa-regular fa-envelope me-1 text-muted"></i>{{ $order->email }}</p>
                @endif
                <div class="p-2 bg-light rounded-2 small text-secondary mb-3">
                    <i class="fa-solid fa-map-pin me-1 text-primary"></i>
                    {{ $order->address }}, {{ $order->ward }}, {{ $order->district }}, {{ $order->province }}
                </div>

                @if($order->ghn_order_code)
                    <div class="p-3 bg-primary-subtle rounded-3 border border-primary-subtle mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted d-block">Mã vận đơn GHN:</small>
                                <strong class="fs-6 font-monospace text-primary">{{ $order->ghn_order_code }}</strong>
                            </div>
                            <a href="https://5sao.ghn.dev/tracking" target="_blank" class="btn btn-sm btn-primary">
                                <i class="fa-solid fa-magnifying-glass me-1"></i>Tra cứu
                            </a>
                        </div>
                    </div>
                @endif

                <hr>

                {{-- TỔNG KẾT TIỀN --}}
                <div class="d-flex justify-content-between py-1">
                    <span class="text-muted">Tạm tính</span>
                    <strong>{{ number_format($order->subtotal, 0, ',', '.') }}₫</strong>
                </div>
                @if($order->discount > 0)
                    <div class="d-flex justify-content-between py-1 text-success">
                        <span>Giảm giá (Coupon {{ $order->coupon_code }})</span>
                        <strong>-{{ number_format($order->discount, 0, ',', '.') }}₫</strong>
                    </div>
                @endif
                <div class="d-flex justify-content-between py-1">
                    <span class="text-muted">Phí ship</span>
                    <strong>{{ number_format($order->shipping_fee, 0, ',', '.') }}₫</strong>
                </div>
                <hr>
                <div class="d-flex justify-content-between h5 mb-0">
                    <span>Tổng tiền</span>
                    <strong class="text-danger">{{ number_format($order->total, 0, ',', '.') }}₫</strong>
                </div>
            </div>
        </div>

        {{-- FORM CẬP NHẬT TRẠNG THÁI --}}
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0 fw-bold"><i class="fa-solid fa-sliders me-2 text-success"></i>Cập nhật trạng thái</h5>
            </div>
            <div class="card-body">
                @if($order->cancelled_reason)
                    <div class="alert alert-danger py-2 small mb-3">
                        <strong>Lý do hủy:</strong> {{ $order->cancelled_reason }}
                    </div>
                @endif

                @php
                    $nextTransitions = [
                        'pending'     => ['confirmed', 'cancelled'],
                        'cod_ordered' => ['confirmed', 'packing', 'shipping', 'completed', 'cancelled'],
                        'paid'        => ['confirmed', 'packing', 'shipping', 'completed', 'cancelled'],
                        'confirmed'   => ['packing', 'shipping', 'completed', 'cancelled'],
                        'packing'     => ['shipping', 'completed', 'cancelled'],
                        'shipping'    => ['completed', 'cancelled'],
                    ];
                    $statusLabels = [
                        'confirmed' => 'Đã xác nhận đơn hàng',
                        'packing'   => 'Đang đóng gói sản phẩm',
                        'shipping'  => 'Đang giao hàng cho shipper',
                        'completed' => 'Đã hoàn tất (Giao thành công & Thu tiền)',
                        'cancelled' => 'Hủy đơn hàng',
                    ];
                    $next = $nextTransitions[$order->status] ?? [];
                @endphp

                @if(!empty($next))
                    <form method="POST" action="{{ route('admin.orders.status', $order) }}">
                        @csrf
                        @method('PATCH')
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted">CHỌN TRẠNG THÁI TIẾP THEO</label>
                            <select class="form-select" name="status">
                                @foreach($next as $s)
                                    <option value="{{ $s }}">{{ $statusLabels[$s] ?? $s }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted">GHI CHÚ / LÝ DO HỦY</label>
                            <textarea class="form-control" name="note" rows="2" placeholder="Ví dụ: Đã bàn giao shipper GHN..."></textarea>
                        </div>
                        <button class="btn btn-primary w-100 py-2 fw-semibold">
                            <i class="fa-solid fa-floppy-disk me-1"></i>Cập nhật trạng thái
                        </button>
                    </form>
                @elseif($order->status === 'completed')
                    <div class="alert alert-success py-2 text-center mb-0">
                        <i class="fa-solid fa-circle-check me-1"></i>Đơn hàng đã hoàn thành thành công.
                    </div>
                @elseif($order->status === 'cancelled')
                    <div class="alert alert-danger py-2 text-center mb-0">
                        <i class="fa-solid fa-ban me-1"></i>Đơn hàng đã bị hủy.
                    </div>
                @endif
            </div>
        </div>
    </aside>
</div>
@endsection
