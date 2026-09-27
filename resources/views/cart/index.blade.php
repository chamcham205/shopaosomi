@extends('layouts.app')
@section('title', 'Giỏ hàng')

@push('styles')
<style>
    .cart-item-card {
        border-radius: 1rem;
        border: 1px solid var(--line, #e7e1d7);
        transition: box-shadow 0.2s;
    }
    .cart-item-card:hover {
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    }
    .cart-checkbox {
        width: 1.3rem;
        height: 1.3rem;
        cursor: pointer;
    }
    .cart-img-thumb {
        width: 88px;
        height: 110px;
        object-fit: cover;
        border-radius: 0.65rem;
        background: #f3f4f6;
    }
    .color-dot {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        display: inline-block;
        border: 1px solid #ccc;
        vertical-align: middle;
    }
</style>
@endpush

@section('content')
<div class="container shop-shell py-5">
    {{-- THANH TIẾN TRÌNH ĐẶT HÀNG --}}
    <div class="checkout-steps">
        <div class="step active">
            <span class="step-num">1</span>
            <span class="step-title">Giỏ hàng</span>
        </div>
        <div class="step-divider"></div>
        <div class="step">
            <span class="step-num">2</span>
            <span class="step-title">Đặt hàng & Thanh toán</span>
        </div>
        <div class="step-divider"></div>
        <div class="step">
            <span class="step-num">3</span>
            <span class="step-title">Hoàn tất</span>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="eyebrow mb-1">Đơn hàng của bạn</p>
            <h1 class="section-title mb-0">Giỏ hàng</h1>
        </div>
        <a href="{{ route('products.index') }}" class="btn btn-outline-dark btn-sm rounded-pill px-3">
            <i class="fa-solid fa-arrow-left me-1"></i>Tiếp tục mua sắm
        </a>
    </div>

    @if($summary['items']->isNotEmpty())
    <div class="row g-4">
        {{-- DANH SÁCH SẢN PHẨM TRONG GIỎ HÀNG --}}
        <div class="col-lg-8">

            {{-- THANH CÔNG CỤ CHỌN TẤT CẢ --}}
            <div class="card border-0 shadow-sm mb-3 rounded-4 bg-white p-3">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="form-check d-flex align-items-center gap-2 mb-0">
                        <input class="form-check-input cart-checkbox" type="checkbox" id="selectAllCart" checked>
                        <label class="form-check-label fw-bold small cursor-pointer" for="selectAllCart" style="cursor:pointer">
                            Chọn tất cả (<span id="totalItemsCount">{{ $summary['items']->count() }}</span> sản phẩm)
                        </label>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <form method="POST" action="{{ route('cart.clear') }}" onsubmit="return confirm('Bạn có chắc muốn xóa toàn bộ giỏ hàng?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-link text-danger text-decoration-none p-0 small">
                                <i class="fa-solid fa-trash me-1"></i>Xóa tất cả
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- FORM CHECKOUT ĐÍNH KÈM CHECKBOX SẢN PHẨM --}}
            <form id="checkout-form" method="GET" action="{{ route('user.payment.index') }}">
                @foreach($summary['items'] as $item)
                @php
                    $prod = $item->variant?->product;
                    $itemImage = $prod?->images->firstWhere('is_main', true)?->url ?? $prod?->images->first()?->url ?? asset('assets/images/product-fallback.svg');
                    $unitPrice = (float) ($prod?->current_price ?? 0);
                    $lineTotal = $unitPrice * $item->quantity;
                    $inStock = $item->variant?->stock >= $item->quantity;
                @endphp
                <div class="card cart-item-card border-0 shadow-sm mb-3 bg-white p-3">
                    <div class="d-flex gap-3 align-items-center">
                        {{-- CHECKBOX CHỌN SẢN PHẨM --}}
                        <div class="form-check ps-0 mb-0 d-flex align-items-center">
                            <input class="form-check-input cart-checkbox item-checkbox"
                                   type="checkbox"
                                   name="selected_items[]"
                                   value="{{ $item->id }}"
                                   id="item-check-{{ $item->id }}"
                                   data-item-id="{{ $item->id }}"
                                   data-price="{{ $unitPrice }}"
                                   data-quantity="{{ $item->quantity }}"
                                   data-line-total="{{ $lineTotal }}"
                                   checked>
                        </div>

                        {{-- ẢNH SẢN PHẨM --}}
                        <a href="{{ $prod ? route('products.show', $prod->slug) : '#' }}" class="d-block flex-shrink-0">
                            <img src="{{ $itemImage }}"
                                 onerror="this.onerror=null;this.src='{{ asset('assets/images/product-fallback.svg') }}'"
                                 alt="{{ $prod?->name }}"
                                 class="cart-img-thumb shadow-sm">
                        </a>

                        {{-- CHI TIẾT SẢN PHẨM --}}
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div>
                                    <h2 class="h6 mb-1 text-truncate" style="max-width:320px">
                                        <a href="{{ $prod ? route('products.show', $prod->slug) : '#' }}" class="text-decoration-none text-dark fw-bold">
                                            {{ $prod?->name }}
                                        </a>
                                    </h2>
                                    <div class="small text-muted d-flex align-items-center gap-2 flex-wrap mb-1">
                                        <span>
                                            <span class="color-dot me-1" style="background-color:{{ $item->variant?->color?->hex_code ?? '#ddd' }}"></span>
                                            {{ $item->variant?->color?->name }}
                                        </span>
                                        <span>·</span>
                                        <span class="badge text-bg-light border text-dark fw-normal">Size: {{ $item->variant?->size?->name }}</span>
                                        <span>·</span>
                                        <code class="text-muted" style="font-size:0.75rem">SKU: {{ $item->variant?->sku }}</code>
                                    </div>
                                    @if(!$inStock)
                                        <span class="badge text-bg-danger mb-2">Chỉ còn {{ $item->variant?->stock }} sản phẩm</span>
                                    @endif
                                </div>

                                {{-- NÚT XÓA 1 SẢN PHẨM --}}
                                <button type="submit" form="del-form-{{ $item->id }}" class="btn btn-link text-muted p-0 text-decoration-none" title="Xóa sản phẩm" onclick="return confirm('Xóa sản phẩm này khỏi giỏ hàng?')">
                                    <i class="fa-solid fa-xmark fs-5"></i>
                                </button>
                            </div>

                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-2">
                                <div class="d-flex align-items-center gap-2">
                                    <strong class="price fs-6">{{ number_format($unitPrice, 0, ',', '.') }}₫</strong>
                                    @if($prod && $prod->is_on_sale)
                                        <del class="text-muted small">{{ number_format($prod->price, 0, ',', '.') }}₫</del>
                                    @endif
                                </div>

                                {{-- CẬP NHẬT SỐ LƯỢNG --}}
                                <div class="d-flex align-items-center gap-2">
                                    <div class="input-group input-group-sm" style="width:130px">
                                        <button class="btn btn-outline-secondary btn-qty-change"
                                                type="button"
                                                data-step="-1"
                                                data-form="qty-form-{{ $item->id }}">-</button>
                                        <input class="form-control text-center qty-input"
                                               type="number"
                                               name="quantity"
                                               form="qty-form-{{ $item->id }}"
                                               id="qty-input-{{ $item->id }}"
                                               min="1"
                                               max="{{ $item->variant?->stock ?? 99 }}"
                                               value="{{ $item->quantity }}"
                                               aria-label="Số lượng">
                                        <button class="btn btn-outline-secondary btn-qty-change"
                                                type="button"
                                                data-step="1"
                                                data-form="qty-form-{{ $item->id }}">+</button>
                                    </div>
                                    <button type="submit" form="qty-form-{{ $item->id }}" class="btn btn-sm btn-outline-dark" title="Cập nhật số lượng">
                                        <i class="fa-solid fa-check"></i>
                                    </button>
                                </div>

                                {{-- THÀNH TIỀN CỦA DÒNG --}}
                                <div class="text-end">
                                    <div class="small text-muted">Thành tiền</div>
                                    <strong class="text-dark fs-6">{{ number_format($lineTotal, 0, ',', '.') }}₫</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </form>

            {{-- CÁC FORM PHỤ CHO UPDATE VÀ DELETE (Tránh lồng form) --}}
            @foreach($summary['items'] as $item)
                <form id="qty-form-{{ $item->id }}" method="POST" action="{{ route('cart.update', $item) }}" class="d-none">
                    @csrf @method('PATCH')
                </form>
                <form id="del-form-{{ $item->id }}" method="POST" action="{{ route('cart.destroy', $item) }}" class="d-none">
                    @csrf @method('DELETE')
                </form>
            @endforeach

        </div>

        {{-- BẢNG TÓM TẮT ĐƠN HÀNG (BÊN PHẢI) --}}
        <aside class="col-lg-4">
            <div class="filter-panel p-4 sticky-top" style="top: 85px; z-index: 10; border-radius: 1.25rem;">
                <h2 class="h5 fw-bold mb-3">Tóm tắt đơn hàng</h2>

                {{-- MÃ GIẢM GIÁ --}}
                <form method="POST" action="{{ route('cart.coupon') }}" class="mb-3">
                    @csrf
                    <label class="form-label small fw-bold text-muted text-uppercase mb-1">Mã khuyến mãi</label>
                    <div class="input-group">
                        <input class="form-control" name="coupon" placeholder="Nhập mã ưu đãi..." value="{{ $coupon?->code }}">
                        <button class="btn btn-outline-dark">Áp dụng</button>
                    </div>
                    @if($coupon)
                        <div class="d-flex justify-content-between align-items-center mt-2 small text-success">
                            <span><i class="fa-solid fa-tag me-1"></i>Đã áp dụng mã: <strong>{{ $coupon->code }}</strong></span>
                        </div>
                    @endif
                </form>
                @error('coupon')
                    <p class="text-danger small mb-3">{{ $message }}</p>
                @enderror

                {{-- CHI TIẾT TÍNH TIỀN THEO CÁC SẢN PHẨM ĐÃ CHỌN --}}
                <div class="border-top pt-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Tạm tính (<span id="calcCount">0</span> sản phẩm)</span>
                        <strong id="calcSubtotal">0₫</strong>
                    </div>
                    <div class="d-flex justify-content-between text-success mb-2">
                        <span>Giảm giá</span>
                        <strong id="calcDiscount">-0₫</strong>
                    </div>

                    {{-- THÔNG BÁO PHÍ VẬN CHUYỂN ĐƯỢC TÍNH Ở BƯỚC ĐẶT HÀNG --}}
                    <div class="alert alert-light border small text-muted p-2 rounded-3 my-3 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-truck-fast text-primary fs-5"></i>
                        <span>Phí vận chuyển sẽ được tính chi tiết theo địa chỉ nhận hàng ở bước thanh toán.</span>
                    </div>

                    <hr class="my-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <span class="h6 mb-0 fw-bold d-block">Tổng thanh toán</span>
                            <small class="text-muted" style="font-size:0.75rem">(Chưa gồm phí vận chuyển)</small>
                        </div>
                        <strong class="text-danger fs-4 fw-bold" id="calcTotal">0₫</strong>
                    </div>
                </div>

                {{-- NÚT THANH TOÁN KÍCH HOẠT FORM CHECKOUT --}}
                <button type="submit"
                        form="checkout-form"
                        id="btnCheckout"
                        class="btn btn-dark btn-lg w-100 rounded-pill py-3 fw-bold shadow-sm">
                    <i class="fa-solid fa-credit-card me-2"></i>Tiến hành thanh toán (<span id="btnCheckoutCount">0</span>)
                </button>

                <div class="text-center mt-3 small text-muted">
                    <i class="fa-solid fa-shield-halved me-1 text-success"></i> Thanh toán an toàn 100% · Hỗ trợ COD & Ví MoMo
                </div>
            </div>
        </aside>
    </div>
    @else
    {{-- GIỎ HÀNG TRỐNG --}}
    <div class="card border-0 shadow-sm p-5 text-center rounded-4 bg-white">
        <div class="my-4">
            <i class="fa-solid fa-bag-shopping fa-3x text-muted mb-3 opacity-50"></i>
            <h2 class="h4 fw-bold">Giỏ hàng của bạn đang trống</h2>
            <p class="text-muted">Hãy khám phá các mẫu áo sơ mi phong cách và thêm vào giỏ hàng ngay nhé!</p>
            <a href="{{ route('products.index') }}" class="btn btn-dark rounded-pill px-5 py-3 mt-2">
                Khám phá cửa hàng
            </a>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectAllBox = document.getElementById('selectAllCart');
    const itemBoxes = Array.from(document.querySelectorAll('.item-checkbox'));
    const btnCheckout = document.getElementById('btnCheckout');
    const btnCheckoutCount = document.getElementById('btnCheckoutCount');
    const calcCount = document.getElementById('calcCount');
    const calcSubtotal = document.getElementById('calcSubtotal');
    const calcDiscount = document.getElementById('calcDiscount');
    const calcTotal = document.getElementById('calcTotal');

    const baseDiscount = {{ (float) ($summary['discount'] ?? 0) }};

    function formatVND(amount) {
        return new Intl.NumberFormat('vi-VN').format(Math.max(0, Math.round(amount))) + '₫';
    }

    function calculateSummary() {
        const checkedBoxes = itemBoxes.filter(cb => cb.checked);
        const count = checkedBoxes.length;

        let subtotal = 0;
        checkedBoxes.forEach(cb => {
            const lineTotal = parseFloat(cb.dataset.lineTotal) || (parseFloat(cb.dataset.price) * parseInt(cb.dataset.quantity));
            subtotal += lineTotal;
        });

        const discount = subtotal > 0 ? Math.min(baseDiscount, subtotal) : 0;
        const total = Math.max(0, subtotal - discount);

        if (calcCount) calcCount.textContent = count;
        if (calcSubtotal) calcSubtotal.textContent = formatVND(subtotal);
        if (calcDiscount) calcDiscount.textContent = '-' + formatVND(discount);
        if (calcTotal) calcTotal.textContent = formatVND(total);

        if (btnCheckoutCount) btnCheckoutCount.textContent = count;

        if (btnCheckout) {
            if (count === 0) {
                btnCheckout.disabled = true;
                btnCheckout.innerHTML = '<i class="fa-solid fa-ban me-2"></i>Vui lòng chọn sản phẩm';
                btnCheckout.classList.remove('btn-dark');
                btnCheckout.classList.add('btn-secondary');
            } else {
                btnCheckout.disabled = false;
                btnCheckout.innerHTML = `<i class="fa-solid fa-credit-card me-2"></i>Tiến hành thanh toán (${count})`;
                btnCheckout.classList.remove('btn-secondary');
                btnCheckout.classList.add('btn-dark');
            }
        }

        if (selectAllBox) {
            selectAllBox.checked = count === itemBoxes.length && itemBoxes.length > 0;
            selectAllBox.indeterminate = count > 0 && count < itemBoxes.length;
        }
    }

    if (selectAllBox) {
        selectAllBox.addEventListener('change', function () {
            itemBoxes.forEach(cb => {
                cb.checked = selectAllBox.checked;
            });
            calculateSummary();
        });
    }

    itemBoxes.forEach(cb => {
        cb.addEventListener('change', function () {
            calculateSummary();
        });
    });

    document.querySelectorAll('.btn-qty-change').forEach(btn => {
        btn.addEventListener('click', function () {
            const step = parseInt(this.dataset.step) || 0;
            const formId = this.dataset.form;
            const input = document.querySelector(`input[form="${formId}"]`);
            if (!input) return;

            const current = parseInt(input.value) || 1;
            const min = parseInt(input.min) || 1;
            const max = parseInt(input.max) || 99;
            const newVal = Math.min(max, Math.max(min, current + step));

            if (newVal !== current) {
                input.value = newVal;
                const form = document.getElementById(formId);
                if (form) form.submit();
            }
        });
    });

    calculateSummary();
});
</script>
@endpush
