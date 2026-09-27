@extends('layouts.admin')

@section('title', 'Quản lý đơn hàng')

@push('styles')
<style>
    .order-status-tab {
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        padding: 0.5rem 0.85rem;
        border-radius: 8px;
        color: #64748b;
        background: #f1f5f9;
        text-decoration: none;
        transition: all 0.15s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        white-space: nowrap;
    }
    .order-status-tab:hover {
        background: #e2e8f0;
        color: #1e293b;
    }
    .order-status-tab.active {
        background: #1a1a2e;
        color: #ffffff;
    }
    .order-status-tab.active .tab-badge {
        background: #c8a96e;
        color: #1a1a2e;
    }
    .tab-badge {
        font-size: 0.7rem;
        padding: 0.15rem 0.45rem;
        border-radius: 12px;
        background: #cbd5e1;
        color: #334155;
        font-weight: 700;
    }
    .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    {{-- THANH ĐIỀU HƯỚNG BREADCRUMB & TIÊU ĐỀ --}}
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <div class="small text-muted mb-1">Quản trị / <strong>Đơn hàng</strong></div>
            <div class="d-flex align-items-baseline gap-2">
                <h1 class="h3 mb-0 fw-bold">Đơn hàng</h1>
                <span class="text-muted small">({{ $orders->firstItem() ?? 0 }}-{{ $orders->lastItem() ?? 0 }}/{{ $orders->total() }})</span>
            </div>
        </div>

        {{-- FORM TÌM KIẾM NHANH TRÊN HEADER --}}
        <form method="GET" action="{{ route('admin.orders.index') }}" class="d-flex align-items-center gap-2 flex-wrap" id="order-quick-filter-form">
            <input type="hidden" name="tab" value="{{ $activeTab ?? 'all' }}">

            {{-- Per Page --}}
            <select name="per_page" class="form-select form-select-sm" style="width: 120px;" onchange="this.form.submit()">
                <option value="25" @selected(($filters['per_page'] ?? 25) == 25)>Hiển thị 25</option>
                <option value="50" @selected(($filters['per_page'] ?? 25) == 50)>Hiển thị 50</option>
                <option value="100" @selected(($filters['per_page'] ?? 25) == 100)>Hiển thị 100</option>
            </select>

            {{-- Payment Status --}}
            <select name="payment_status" class="form-select form-select-sm" style="width: 160px;" onchange="this.form.submit()">
                <option value="">Tất cả thanh toán</option>
                @foreach($paymentLabels as $k => $lbl)
                    <option value="{{ $k }}" @selected(($filters['payment_status'] ?? '') === $k)>{{ $lbl }}</option>
                @endforeach
            </select>

            {{-- Search Input --}}
            <div class="input-group input-group-sm" style="width: 280px;">
                <input type="text" name="search" class="form-control" placeholder="Mã đơn, khách hàng, SĐT, sản phẩm..." value="{{ $filters['search'] ?? request('search') ?? request('q') }}">
                <button class="btn btn-outline-secondary" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
            </div>

            {{-- Xuất trang này / In --}}
            <button type="button" class="btn btn-success btn-sm d-flex align-items-center gap-1" onclick="window.print()">
                <i class="fa-solid fa-print"></i>
                <span>Xuất trang này</span>
            </button>
        </form>
    </div>

    {{-- BỘ LỌC NÂNG CAO (COLLAPSIBLE) --}}
    <div class="mb-3">
        <a class="text-decoration-none small text-secondary fw-semibold" data-bs-toggle="collapse" href="#advancedFilters" role="button" aria-expanded="false">
            <i class="fa-solid fa-sliders me-1"></i>Bộ lọc nâng cao
        </a>
        <div class="collapse mt-2 @if(!empty($filters['date_from']) || !empty($filters['date_to']) || !empty($filters['gateway']) || !empty($filters['sort'])) show @endif" id="advancedFilters">
            <div class="card card-body border-0 shadow-sm rounded-3 p-3 bg-light">
                <form method="GET" action="{{ route('admin.orders.index') }}" class="row g-2 align-items-end">
                    <input type="hidden" name="tab" value="{{ $activeTab ?? 'all' }}">
                    <input type="hidden" name="search" value="{{ $filters['search'] ?? '' }}">
                    <input type="hidden" name="per_page" value="{{ $filters['per_page'] ?? 25 }}">

                    <div class="col-md-2 col-6">
                        <label class="form-label small mb-1">Từ ngày</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $filters['date_from'] ?? '' }}">
                    </div>
                    <div class="col-md-2 col-6">
                        <label class="form-label small mb-1">Đến ngày</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $filters['date_to'] ?? '' }}">
                    </div>
                    <div class="col-md-2 col-6">
                        <label class="form-label small mb-1">Cổng thanh toán</label>
                        <select name="gateway" class="form-select form-select-sm">
                            <option value="">-- Tất cả cổng --</option>
                            <option value="cod" @selected(($filters['gateway'] ?? '') === 'cod')>COD (Tiền mặt)</option>
                            <option value="momo" @selected(($filters['gateway'] ?? '') === 'momo')>Ví MoMo</option>
                        </select>
                    </div>
                    <div class="col-md-2 col-6">
                        <label class="form-label small mb-1">Sắp xếp</label>
                        <select name="sort" class="form-select form-select-sm">
                            <option value="newest" @selected(($filters['sort'] ?? '') === 'newest')>Mới nhất</option>
                            <option value="oldest" @selected(($filters['sort'] ?? '') === 'oldest')>Cũ nhất</option>
                            <option value="amount_desc" @selected(($filters['sort'] ?? '') === 'amount_desc')>Giá trị cao nhất</option>
                            <option value="amount_asc" @selected(($filters['sort'] ?? '') === 'amount_asc')>Giá trị thấp nhất</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100">Áp dụng</button>
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm" title="Đặt lại">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- THANH TABS TRẠNG THÁI GIAO HÀNG (LAB 08) --}}
    <div class="d-flex gap-2 overflow-x-auto pb-2 mb-3" style="scrollbar-width: thin;">
        @foreach($tabs as $tabKey => $tabItem)
            <a href="{{ route('admin.orders.index', array_merge(request()->except(['page']), ['tab' => $tabKey])) }}"
               class="order-status-tab {{ ($activeTab ?? 'all') === $tabKey ? 'active' : '' }}">
                <span>{{ $tabItem['label'] }}</span>
                <span class="tab-badge">{{ $tabItem['count'] }}</span>
            </a>
        @endforeach
    </div>

    <div class="d-flex justify-content-between align-items-center mb-2">
        <small class="text-muted">{{ $orders->total() }} đơn hàng trong danh sách</small>
        <small class="text-muted" style="font-size: 0.75rem;">Trạng thái vận chuyển được cập nhật từ GHN</small>
    </div>

    {{-- BẢNG DANH SÁCH ĐƠN HÀNG --}}
    <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.875rem;">
                <thead class="table-light text-muted small">
                    <tr>
                        <th style="width: 30px;"><input type="checkbox" class="form-check-input" id="check-all-orders"></th>
                        <th style="width: 130px;">Mã đơn hàng</th>
                        <th style="width: 110px;">Ngày tạo đơn</th>
                        <th>Sản phẩm</th>
                        <th class="text-end" style="width: 110px;">Tổng tiền</th>
                        <th class="text-end" style="width: 100px;">COD cần thu</th>
                        <th>Tên khách hàng</th>
                        <th>Mã vận đơn</th>
                        <th>Trạng thái giao hàng</th>
                        <th class="text-center" style="width: 90px;">Đơn vị VC</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td><input type="checkbox" class="form-check-input"></td>
                            <td>
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="fw-bold text-decoration-none text-primary d-block">
                                    {{ $order->order_code ?? ('DH' . str_pad($order->id, 6, '0', STR_PAD_LEFT)) }}
                                </a>
                                {{-- Badge trạng thái thanh toán --}}
                                @if(in_array($order->payment_status ?? $order->status, ['paid', 'paid_momo', 'cod_paid', 'completed']))
                                    <span class="badge bg-success-subtle text-success border border-success" style="font-size: 0.65rem;">ĐÃ THANH TOÁN</span>
                                @elseif(($order->payment_status ?? $order->status) === 'cancelled')
                                    <span class="badge bg-danger-subtle text-danger border border-danger" style="font-size: 0.65rem;">ĐÃ HỦY</span>
                                @elseif(($order->payment_status ?? $order->status) === 'refund_pending')
                                    <span class="badge bg-warning-subtle text-warning border border-warning" style="font-size: 0.65rem;">CHỜ HOÀN TIỀN</span>
                                @elseif(($order->payment_status ?? $order->status) === 'initiated')
                                    <span class="badge bg-danger-subtle text-danger border border-danger" style="font-size: 0.65rem;">ĐANG CHỜ MOMO</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary" style="font-size: 0.65rem;">CHỜ THANH TOÁN</span>
                                @endif
                            </td>
                            <td class="small text-muted">
                                <div>{{ $order->created_at->format('d/m/Y') }}</div>
                                <div style="font-size: 0.72rem;">{{ $order->created_at->format('H:i') }}</div>
                            </td>
                            <td>
                                @if($order->items && $order->items->count() > 0)
                                    @php $firstItem = $order->items->first(); @endphp
                                    <div class="fw-medium text-dark text-truncate" style="max-width: 220px;" title="{{ $firstItem->product_name ?? $firstItem->product?->name }}">
                                        {{ $firstItem->product_name ?? $firstItem->product?->name ?? 'Sản phẩm' }} × {{ $firstItem->quantity }}
                                    </div>
                                    @if($order->items->count() > 1)
                                        <small class="text-muted" style="font-size: 0.72rem;">+{{ $order->items->count() - 1 }} sản phẩm khác</small>
                                    @endif
                                @else
                                    <span class="text-muted small">---</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold text-dark">
                                {{ number_format($order->total, 0, ',', '.') }}₫
                            </td>
                            <td class="text-end text-muted small">
                                @if(($order->gateway ?? '') === 'cod' && !in_array($order->payment_status ?? $order->status, ['paid', 'cod_paid', 'completed']))
                                    <span class="text-danger fw-semibold">{{ number_format($order->total, 0, ',', '.') }}₫</span>
                                @else
                                    <span>0₫</span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-medium text-dark">{{ $order->recipient_name ?? $order->user?->name ?? 'Khách lẻ' }}</div>
                                <small class="text-muted d-block" style="font-size: 0.75rem;">{{ $order->phone ?? $order->user?->phone ?? '---' }}</small>
                            </td>
                            <td>
                                @if(!empty($order->ghn_order_code))
                                    <span class="badge bg-light text-dark border font-monospace">{{ $order->ghn_order_code }}</span>
                                @else
                                    <span class="text-muted small">Chưa có vận đơn</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $wf = $order->workflow_info;
                                @endphp
                                <div class="d-flex align-items-center gap-1.5">
                                    <span class="status-dot" style="background-color: {{ $wf['color'] }};"></span>
                                    <span class="fw-semibold">{{ $wf['label'] }}</span>
                                </div>
                            </td>
                            <td class="text-center">
                                @if(!empty($order->ghn_order_code))
                                    <span class="badge bg-warning-subtle text-dark border border-warning fw-bold" style="font-size: 0.7rem;">GHN</span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-box-open fs-2 mb-2 opacity-50"></i>
                                <div class="fw-semibold">Không tìm thấy đơn hàng nào phù hợp</div>
                                <small>Thử chọn tab trạng thái khác hoặc làm mới bộ lọc tìm kiếm.</small>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PHÂN TRANG FOOTER --}}
        @if($orders->hasPages())
            <div class="p-3 border-top bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="small text-muted">
                    Hiển thị {{ $orders->firstItem() }}-{{ $orders->lastItem() }} trong tổng số {{ $orders->total() }} đơn hàng
                </div>
                <div>
                    {{ $orders->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
