@extends('layouts.app')
@section('title', 'Đặt hàng & Thanh toán')

@push('styles')
<style>
    .payment-card-option {
        border: 2px solid #e5e7eb;
        border-radius: 1rem;
        padding: 1.25rem;
        cursor: pointer;
        transition: all 0.2s ease;
        background: #fff;
    }
    .payment-card-option:hover {
        border-color: #94a3b8;
        background: #f8fafc;
    }
    .payment-card-option.active {
        border-color: var(--ink, #17202a);
        background: #fafaf9;
        box-shadow: 0 6px 20px rgba(0,0,0,0.06);
    }
    .payment-icon-wrap {
        width: 50px;
        height: 50px;
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        flex-shrink: 0;
    }
    .checkout-item-thumb {
        width: 64px;
        height: 80px;
        object-fit: cover;
        border-radius: 0.6rem;
        background: #f3f4f6;
        flex-shrink: 0;
    }
    .color-dot {
        width: 13px;
        height: 13px;
        border-radius: 50%;
        display: inline-block;
        border: 1px solid #ccc;
        vertical-align: middle;
    }
    .shipping-method-card {
        border: 2px solid #e2e8f0;
        border-radius: 0.85rem;
        background: #f8fafc;
    }
    .sticky-checkout-card {
        position: sticky;
        top: 85px;
        z-index: 10;
        max-height: calc(100vh - 105px);
        overflow-y: auto;
    }
    .sticky-checkout-card::-webkit-scrollbar,
    .checkout-items::-webkit-scrollbar {
        width: 4px;
    }
    .sticky-checkout-card::-webkit-scrollbar-thumb,
    .checkout-items::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
</style>
@endpush

@section('content')
<div class="container shop-shell py-5">
    {{-- THANH TIẾN TRÌNH ĐẶT HÀNG (3 BƯỚC) --}}
    <div class="checkout-steps">
        <a href="{{ route('cart.index') }}" class="step completed">
            <span class="step-num"><i class="fa-solid fa-check"></i></span>
            <span class="step-title">Giỏ hàng</span>
        </a>
        <div class="step-divider completed"></div>
        <div class="step active">
            <span class="step-num">2</span>
            <span class="step-title">Đặt hàng & Thanh toán</span>
        </div>
        <div class="step-divider"></div>
        <div class="step">
            <span class="step-num">3</span>
            <span class="step-title">Hoàn tất</span>
        </div>
    </div>

    {{-- TIÊU ĐỀ TRANG --}}
    <div class="mb-4">
        <p class="eyebrow mb-1">Xác nhận thanh toán</p>
        <h1 class="section-title mb-1">Đặt hàng & Thanh toán</h1>
        <p class="text-muted">Giao hàng toàn quốc 63 tỉnh thành · Phí ship tự động tính theo GHN</p>
    </div>

    {{-- THÔNG BÁO FLASH --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show rounded-3" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('user.payment.process') }}" id="payment-form">
        @csrf

        {{-- Hidden fields lưu tên tỉnh/huyện/xã --}}
        <input type="hidden" name="province_name" id="province_name" value="{{ old('province_name', 'Hồ Chí Minh') }}">
        <input type="hidden" name="district_name" id="district_name" value="{{ old('district_name', 'Quận 1') }}">
        <input type="hidden" name="ward_name" id="ward_name" value="{{ old('ward_name', 'Phường Bến Nghé') }}">

        <div class="row g-4">
            {{-- CỘT TRÁI: THÔNG TIN NHẬN HÀNG VÀ THANH TOÁN --}}
            <div class="col-lg-7">
                {{-- KHỐI 1: THÔNG TIN NGƯỜI NHẬN --}}
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                        <span class="badge rounded-circle bg-dark text-white p-2" style="width:28px;height:28px;display:flex;align-items:center;justify-content:center;">1</span>
                        <h5 class="fw-bold mb-0">Thông tin người nhận</h5>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label fw-semibold">Họ tên người nhận <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-regular fa-user text-muted"></i></span>
                                <input type="text"
                                       class="form-control @error('name') is-invalid @enderror"
                                       id="name"
                                       name="name"
                                       value="{{ old('name', $user->name ?? '') }}"
                                       placeholder="Ví dụ: Nguyễn Văn A"
                                       required>
                            </div>
                            @error('name')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="phone" class="form-label fw-semibold">Số điện thoại liên hệ <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-phone text-muted"></i></span>
                                <input type="tel"
                                       class="form-control @error('phone') is-invalid @enderror"
                                       id="phone"
                                       name="phone"
                                       value="{{ old('phone', $user->phone ?? '') }}"
                                       placeholder="0912345678"
                                       pattern="0[0-9]{9}"
                                       required>
                            </div>
                            @error('phone')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <small class="text-muted" style="font-size:0.75rem">10 chữ số bắt đầu bằng 0 (để bưu tá liên hệ)</small>
                        </div>

                        <div class="col-12">
                            <label for="email" class="form-label fw-semibold">Email nhận thông báo đơn hàng</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-regular fa-envelope text-muted"></i></span>
                                <input type="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       id="email"
                                       name="email"
                                       value="{{ old('email', $user->email ?? '') }}"
                                       placeholder="email@example.com">
                            </div>
                            @error('email')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- KHỐI 2: ĐỊA CHỈ NHẬN HÀNG (TOÀN QUỐC 63 TỈNH THÀNH) --}}
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge rounded-circle bg-dark text-white p-2" style="width:28px;height:28px;display:flex;align-items:center;justify-content:center;">2</span>
                            <h5 class="fw-bold mb-0">Địa chỉ giao hàng</h5>
                        </div>
                        <span class="badge text-bg-light border text-muted">
                            <i class="fa-solid fa-map-location-dot me-1 text-primary"></i>Toàn quốc 63 tỉnh/thành
                        </span>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="province_select" class="form-label fw-semibold">Tỉnh / Thành phố <span class="text-danger">*</span></label>
                            <select class="form-select @error('province_name') is-invalid @enderror" id="province_select" required>
                                <option value="">-- Chọn Tỉnh / Thành phố --</option>
                                @foreach($provinces as $p)
                                    <option value="{{ $p['id'] }}" data-name="{{ $p['name'] }}"
                                        @selected(old('province_name') == $p['name'] || (!old('province_name') && ($p['id'] == 202 || $p['name'] == 'Hồ Chí Minh' || $p['name'] == 'TP. Hồ Chí Minh')))>
                                        {{ $p['name'] }}
                                    </option>
                                @endforeach
                            </select>
                            @error('province_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="to_district_id" class="form-label fw-semibold">Quận / Huyện (GHN) <span class="text-danger">*</span></label>
                            <select class="form-select @error('to_district_id') is-invalid @enderror" id="to_district_id" name="to_district_id" required>
                                <option value="">-- Chọn Quận / Huyện --</option>
                            </select>
                            @error('to_district_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="to_ward_code" class="form-label fw-semibold">Phường / Xã (GHN) <span class="text-danger">*</span></label>
                            <select class="form-select @error('to_ward_code') is-invalid @enderror" id="to_ward_code" name="to_ward_code" required>
                                <option value="">-- Chọn Phường / Xã --</option>
                            </select>
                            @error('to_ward_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="address" class="form-label fw-semibold">Số nhà, tên đường cụ thể <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-location-dot text-muted"></i></span>
                                <input type="text"
                                       class="form-control @error('address') is-invalid @enderror"
                                       id="address"
                                       name="address"
                                       value="{{ old('address') }}"
                                       placeholder="Ví dụ: Tầng 4, Tòa nhà ABC, 123 Đường Lê Lợi..."
                                       required>
                            </div>
                            @error('address')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="note" class="form-label fw-semibold">Ghi chú giao hàng (Tùy chọn)</label>
                            <input type="text"
                                   class="form-control"
                                   id="note"
                                   name="note"
                                   value="{{ old('note') }}"
                                   placeholder="Ví dụ: Giao giờ hành chính, gọi trước khi giao...">
                        </div>

                        {{-- THÔNG TIN ĐƠN VỊ VẬN CHUYỂN GHN --}}
                        <div class="col-12 mt-3">
                            <div class="shipping-method-card p-3 d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle p-2 bg-white shadow-sm text-warning" style="width:42px;height:42px;display:flex;align-items:center;justify-content:center;">
                                        <i class="fa-solid fa-truck-fast fs-5 text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                            <span>Giao Hàng Nhanh (GHN Express)</span>
                                            <span class="badge text-bg-success" style="font-size:0.7rem">Tiêu chuẩn</span>
                                        </div>
                                        <div class="small text-muted">Giao hàng toàn quốc · Thời gian dự kiến: 2 - 3 ngày làm việc</div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <strong class="text-primary fs-5" id="shipping-fee-display">{{ number_format($estimatedShipping, 0, ',', '.') }}₫</strong>
                                    <small class="text-muted d-block" style="font-size:0.72rem">Cước GHN chuẩn</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- KHỐI 3: PHƯƠNG THỨC THANH TOÁN --}}
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                        <span class="badge rounded-circle bg-dark text-white p-2" style="width:28px;height:28px;display:flex;align-items:center;justify-content:center;">3</span>
                        <h5 class="fw-bold mb-0">Phương thức thanh toán <span class="text-danger">*</span></h5>
                    </div>

                    <div class="payment-options d-flex flex-column gap-3">
                        {{-- PHƯƠNG THỨC 1: COD --}}
                        <label class="payment-card-option d-flex align-items-center justify-content-between @if(old('payment_method', 'cod') == 'cod') active @endif" for="pm_cod">
                            <div class="d-flex align-items-center gap-3">
                                <input type="radio"
                                       name="payment_method"
                                       id="pm_cod"
                                       value="cod"
                                       class="form-check-input"
                                       @checked(old('payment_method', 'cod') == 'cod')
                                       required>
                                <div class="payment-icon-wrap bg-success-subtle text-success">
                                    <i class="fa-solid fa-money-bill-wave"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                        <span>Thanh toán khi nhận hàng (COD)</span>
                                        <span class="badge text-bg-light border text-muted" style="font-size:0.7rem">Phổ biến</span>
                                    </div>
                                    <small class="text-muted d-block">Kiểm tra sản phẩm và thanh toán tiền mặt cho nhân viên giao hàng GHN khi nhận</small>
                                </div>
                            </div>
                            <div class="check-indicator ms-2">
                                <i class="fa-solid fa-circle-check text-success fs-5 @if(old('payment_method', 'cod') != 'cod') d-none @endif" id="icon-cod"></i>
                            </div>
                        </label>

                        {{-- PHƯƠNG THỨC 2: MOMO --}}
                        <label class="payment-card-option d-flex align-items-center justify-content-between @if(old('payment_method') == 'momo') active @endif" for="pm_momo">
                            <div class="d-flex align-items-center gap-3">
                                <input type="radio"
                                       name="payment_method"
                                       id="pm_momo"
                                       value="momo"
                                       class="form-check-input"
                                       @checked(old('payment_method') == 'momo')
                                       required>
                                <div class="payment-icon-wrap" style="background-color: #fdf0f6; color: #a50064;">
                                    <i class="fa-solid fa-wallet"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                        <span>Cổng thanh toán MoMo</span>
                                        <span class="badge text-white" style="background-color: #a50064; font-size:0.7rem">Khuyên dùng</span>
                                    </div>
                                    <small class="text-muted d-block">Thanh toán tức thì qua ứng dụng MoMo, quét mã QR, thẻ ATM nội địa hoặc thẻ Quốc tế</small>
                                </div>
                            </div>
                            <div class="check-indicator ms-2">
                                <i class="fa-solid fa-circle-check text-success fs-5 @if(old('payment_method') != 'momo') d-none @endif" id="icon-momo"></i>
                            </div>
                        </label>
                    </div>

                    @error('payment_method')
                        <div class="text-danger small mt-2">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- CỘT PHẢI: TÓM TẮT ĐƠN HÀNG (STICKY GỌN GÀNG, KHÔNG TRÀN MÀN HÌNH) --}}
            <aside class="col-lg-5">
                <div class="card border border-light-subtle shadow-sm rounded-4 p-3 p-lg-4 sticky-checkout-card bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <h5 class="fw-bold mb-0">
                            <i class="fa-solid fa-bag-shopping me-2 text-primary"></i>Đơn hàng của bạn
                        </h5>
                        <span class="badge text-bg-light border text-dark">{{ count($cart) }} món</span>
                    </div>

                    {{-- DANH SÁCH SẢN PHẨM (CUỘN GỌN GÀNG) --}}
                    <div class="checkout-items mb-3 border-bottom pb-2" style="max-height: 200px; overflow-y: auto;">
                        @foreach($cart as $item)
                            <div class="d-flex align-items-center gap-3 py-1.5">
                                <img src="{{ $item['image'] ?? asset('assets/images/product-fallback.svg') }}"
                                     onerror="this.onerror=null;this.src='{{ asset('assets/images/product-fallback.svg') }}'"
                                     alt="{{ $item['name'] }}"
                                     class="checkout-item-thumb shadow-sm">
                                <div class="flex-grow-1 min-w-0">
                                    <div class="fw-bold text-dark text-truncate mb-1" style="max-width: 210px;" title="{{ $item['name'] }}">
                                        {{ $item['name'] }}
                                    </div>
                                    <div class="small text-muted d-flex align-items-center gap-1 flex-wrap mb-1">
                                        @if(!empty($item['color']))
                                            <span>
                                                <span class="color-dot me-1" style="background-color:{{ $item['color_hex'] ?? '#ccc' }}"></span>
                                                {{ $item['color'] }}
                                            </span>
                                            <span>·</span>
                                        @endif
                                        @if(!empty($item['size']))
                                            <span class="badge text-bg-light border text-dark fw-normal" style="font-size:0.75rem">Size: {{ $item['size'] }}</span>
                                        @endif
                                        <span class="badge text-bg-secondary ms-auto fw-normal">×{{ $item['quantity'] }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">{{ number_format($item['price'], 0, ',', '.') }}₫</small>
                                        <strong class="text-dark">{{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}₫</strong>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- CHI TIẾT THANH TOÁN --}}
                    <div class="d-flex justify-content-between py-1 small">
                        <span class="text-muted">Tạm tính hàng hóa:</span>
                        <strong>{{ number_format($subtotal, 0, ',', '.') }}₫</strong>
                    </div>

                    @if(!empty($discount) && $discount > 0)
                        <div class="d-flex justify-content-between py-1 small text-success">
                            <span>
                                <i class="fa-solid fa-tag me-1"></i>Giảm giá ({{ $coupon?->code }}):
                            </span>
                            <strong>-{{ number_format($discount, 0, ',', '.') }}₫</strong>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between py-1 small">
                        <span class="text-muted">Phí giao hàng GHN:</span>
                        <strong class="text-primary" id="summary-shipping-fee">{{ number_format($estimatedShipping, 0, ',', '.') }}₫</strong>
                    </div>

                    <hr class="my-2.5">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <span class="h6 mb-0 fw-bold d-block">Tổng thanh toán:</span>
                            <small class="text-muted" style="font-size:0.75rem">(Đã gồm phí ship GHN)</small>
                        </div>
                        <span class="h4 mb-0 text-danger fw-bold" id="summary-total-price">
                            {{ number_format(max(0, $subtotal - ($discount ?? 0)) + $estimatedShipping, 0, ',', '.') }}₫
                        </span>
                    </div>

                    {{-- NÚT XÁC NHẬN ĐẶT HÀNG --}}
                    <button type="submit"
                            class="btn btn-dark btn-lg w-100 py-2.5 rounded-pill fw-bold text-uppercase shadow-sm"
                            id="btn-submit-order">
                        <i class="fa-solid fa-shield-halved me-2"></i>Xác nhận đặt hàng
                    </button>

                    <a href="{{ route('cart.index') }}" class="btn btn-link text-secondary w-100 mt-1 text-decoration-none small text-center">
                        <i class="fa-solid fa-arrow-left me-1"></i>Quay lại chỉnh sửa giỏ hàng
                    </a>

                    {{-- CAM KẾT & BẢO MẬT GỌN GÀNG --}}
                    <div class="bg-light rounded-3 p-2.5 mt-2.5 small text-muted">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="fa-solid fa-shield-halved text-success"></i>
                            <span>Bảo mật giao dịch 100% chuẩn quốc tế</span>
                        </div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="fa-solid fa-rotate-left text-primary"></i>
                            <span>Đổi trả sản phẩm linh hoạt trong 7 ngày</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-headset text-warning"></i>
                            <span>Hỗ trợ tư vấn đặt hàng 24/7 tận tình</span>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const provinceSelect = document.getElementById('province_select');
    const districtSelect = document.getElementById('to_district_id');
    const wardSelect = document.getElementById('to_ward_code');

    const provinceNameInput = document.getElementById('province_name');
    const districtNameInput = document.getElementById('district_name');
    const wardNameInput = document.getElementById('ward_name');

    const shippingFeeDisplay = document.getElementById('shipping-fee-display');
    const summaryShippingFee = document.getElementById('summary-shipping-fee');
    const summaryTotalPrice = document.getElementById('summary-total-price');

    const subtotal = {{ (float) $subtotal }};
    const discount = {{ (float) ($discount ?? 0) }};

    const savedDistrictId = "{{ old('to_district_id') }}";
    const savedWardCode = "{{ old('to_ward_code') }}";

    function formatVND(amount) {
        return new Intl.NumberFormat('vi-VN').format(Math.max(0, Math.round(amount))) + '₫';
    }

    function updateFee() {
        const distId = districtSelect.value;
        const wardCode = wardSelect.value;
        if (!distId || !wardCode) return;

        if (shippingFeeDisplay) {
            shippingFeeDisplay.innerHTML = '<span class="spinner-border spinner-border-sm text-primary" role="status" style="width:1rem;height:1rem;"></span>';
        }

        fetch(`{{ route('user.payment.ghn.fee') }}?to_district_id=${distId}&to_ward_code=${wardCode}`)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.shipping_fee !== undefined) {
                    const fee = data.shipping_fee;
                    const feeFormatted = data.formatted || formatVND(fee);
                    if (shippingFeeDisplay) shippingFeeDisplay.textContent = feeFormatted;
                    if (summaryShippingFee) summaryShippingFee.textContent = feeFormatted;

                    const total = Math.max(0, subtotal - discount) + fee;
                    if (summaryTotalPrice) summaryTotalPrice.textContent = formatVND(total);
                }
            })
            .catch(err => {
                console.warn('Fee fetch failed:', err);
                if (shippingFeeDisplay) shippingFeeDisplay.textContent = '30.000₫';
            });
    }

    function loadWards(districtId, targetWardCode = null) {
        if (!districtId) {
            wardSelect.innerHTML = '<option value="">-- Chọn Phường / Xã --</option>';
            return;
        }

        wardSelect.innerHTML = '<option value="">⏳ Đang tải Phường / Xã...</option>';

        fetch(`{{ route('user.payment.ghn.wards') }}?district_id=${districtId}`)
            .then(res => res.json())
            .then(data => {
                wardSelect.innerHTML = '';
                if (data.success && data.data && data.data.length > 0) {
                    data.data.forEach(w => {
                        const opt = document.createElement('option');
                        opt.value = w.code;
                        opt.textContent = `${w.name} (Mã: ${w.code})`;
                        opt.dataset.name = w.name;
                        if (targetWardCode && String(w.code) === String(targetWardCode)) {
                            opt.selected = true;
                        }
                        wardSelect.appendChild(opt);
                    });

                    if (!wardSelect.value && data.data.length > 0) {
                        wardSelect.value = data.data[0].code;
                    }

                    const selectedOpt = wardSelect.options[wardSelect.selectedIndex];
                    if (selectedOpt) {
                        wardNameInput.value = selectedOpt.dataset.name || selectedOpt.textContent.split(' (Mã:')[0];
                    }

                    updateFee();
                } else {
                    wardSelect.innerHTML = '<option value="20308">Phường / Xã (Mặc định)</option>';
                    wardNameInput.value = 'Phường Bến Nghé';
                    updateFee();
                }
            })
            .catch(err => {
                console.warn('Wards load failed:', err);
                wardSelect.innerHTML = '<option value="20308">Phường / Xã (Mặc định)</option>';
                wardNameInput.value = 'Phường Bến Nghé';
                updateFee();
            });
    }

    function loadDistricts(provinceId, targetDistrictId = null, targetWardCode = null) {
        if (!provinceId) {
            districtSelect.innerHTML = '<option value="">-- Chọn Quận / Huyện --</option>';
            wardSelect.innerHTML = '<option value="">-- Chọn Phường / Xã --</option>';
            return;
        }

        const selectedProvOpt = provinceSelect.options[provinceSelect.selectedIndex];
        if (selectedProvOpt) {
            provinceNameInput.value = selectedProvOpt.dataset.name || selectedProvOpt.textContent.trim();
        }

        districtSelect.innerHTML = '<option value="">⏳ Đang tải Quận / Huyện...</option>';
        wardSelect.innerHTML = '<option value="">-- Chọn Phường / Xã --</option>';

        fetch(`{{ route('user.payment.ghn.districts') }}?province_id=${provinceId}`)
            .then(res => res.json())
            .then(data => {
                districtSelect.innerHTML = '';
                if (data.success && data.data && data.data.length > 0) {
                    data.data.forEach(d => {
                        const opt = document.createElement('option');
                        opt.value = d.id;
                        opt.textContent = `${d.name} (Mã GHN: ${d.id})`;
                        opt.dataset.name = d.name;
                        if (targetDistrictId && String(d.id) === String(targetDistrictId)) {
                            opt.selected = true;
                        }
                        districtSelect.appendChild(opt);
                    });

                    if (!districtSelect.value && data.data.length > 0) {
                        districtSelect.value = data.data[0].id;
                    }

                    const selectedDistOpt = districtSelect.options[districtSelect.selectedIndex];
                    if (selectedDistOpt) {
                        districtNameInput.value = selectedDistOpt.dataset.name || selectedDistOpt.textContent.split(' (Mã GHN:')[0];
                    }

                    loadWards(districtSelect.value, targetWardCode);
                } else {
                    districtSelect.innerHTML = '<option value="1442">Quận / Huyện (Mặc định)</option>';
                    districtNameInput.value = 'Quận 1';
                    loadWards(1442, targetWardCode);
                }
            })
            .catch(err => {
                console.warn('Districts load failed:', err);
                districtSelect.innerHTML = '<option value="1442">Quận / Huyện (Mặc định)</option>';
                districtNameInput.value = 'Quận 1';
                loadWards(1442, targetWardCode);
            });
    }

    provinceSelect.addEventListener('change', function () {
        loadDistricts(this.value);
    });

    districtSelect.addEventListener('change', function () {
        const selectedDistOpt = this.options[this.selectedIndex];
        if (selectedDistOpt) {
            districtNameInput.value = selectedDistOpt.dataset.name || selectedDistOpt.textContent.split(' (Mã GHN:')[0];
        }
        loadWards(this.value);
    });

    wardSelect.addEventListener('change', function () {
        const selectedOpt = this.options[this.selectedIndex];
        if (selectedOpt) {
            wardNameInput.value = selectedOpt.dataset.name || selectedOpt.textContent.split(' (Mã:')[0];
        }
        updateFee();
    });

    // Khởi tạo ban đầu với tỉnh được chọn
    const initialProvId = provinceSelect.value || 202;
    loadDistricts(initialProvId, savedDistrictId || null, savedWardCode || null);

    // Xử lý chọn phương thức thanh toán
    const paymentRadios = document.querySelectorAll('input[name="payment_method"]');
    const paymentCards = document.querySelectorAll('.payment-card-option');
    const iconCod = document.getElementById('icon-cod');
    const iconMomo = document.getElementById('icon-momo');
    const btnSubmitOrder = document.getElementById('btn-submit-order');

    function syncPaymentUI(method) {
        paymentCards.forEach(card => {
            const radio = card.querySelector('input[type="radio"]');
            if (radio && radio.value === method) {
                card.classList.add('active');
            } else {
                card.classList.remove('active');
            }
        });

        if (iconCod) iconCod.classList.toggle('d-none', method !== 'cod');
        if (iconMomo) iconMomo.classList.toggle('d-none', method !== 'momo');

        if (btnSubmitOrder) {
            if (method === 'momo') {
                btnSubmitOrder.innerHTML = '<i class="fa-solid fa-wallet me-2"></i>Xác nhận thanh toán MoMo';
            } else {
                btnSubmitOrder.innerHTML = '<i class="fa-solid fa-shield-halved me-2"></i>Xác nhận đặt hàng';
            }
        }
    }

    paymentRadios.forEach(radio => {
        radio.addEventListener('change', function () {
            syncPaymentUI(this.value);
        });
    });

    // Xử lý gửi biểu mẫu thanh toán
    const paymentForm = document.getElementById('payment-form');
    if (paymentForm) {
        paymentForm.addEventListener('submit', function(e) {
            const btn = document.getElementById('btn-submit-order');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Đang xử lý đơn hàng...';
            }
        });
    }
});
</script>
@endpush
