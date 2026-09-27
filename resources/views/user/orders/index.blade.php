@extends('layouts.app')
@section('title', 'Lịch sử đơn hàng')

@section('content')
<div class="container shop-shell py-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h1 class="section-title mb-1">Lịch sử đơn hàng</h1>
            <p class="text-muted mb-0">Theo dõi trạng thái đơn hàng và tiến trình thanh toán / vận chuyển GHN</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('home') }}" class="btn btn-outline-dark">
                <i class="fa-solid fa-house me-1"></i>Trang chủ
            </a>
            <a href="{{ route('products.index') }}" class="btn btn-dark">
                <i class="fa-solid fa-store me-1"></i>Tiếp tục mua sắm
            </a>
        </div>
    </div>

    <!-- Thông báo kết quả từ Callback hoặc IPN -->
    @if(session('success'))
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-success-subtle border-start border-success border-4 p-4">
            <div class="d-flex align-items-start gap-3 flex-wrap justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width:48px;height:48px;">
                        <i class="fa-solid fa-check fs-4"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold text-success-emphasis mb-1">Thanh toán & Đặt hàng thành công!</h5>
                        <p class="text-secondary mb-0">{{ session('success') }}</p>
                    </div>
                </div>
                <div class="d-flex gap-2 align-items-center mt-2 mt-sm-0">
                    <a href="{{ route('home') }}" class="btn btn-success px-4 py-2 rounded-pill fw-semibold shadow-sm d-inline-flex align-items-center gap-2">
                        <i class="fa-solid fa-house"></i> Quay về trang chủ
                    </a>
                    <a href="{{ route('products.index') }}" class="btn btn-outline-success px-3 py-2 rounded-pill fw-medium d-inline-flex align-items-center gap-2">
                        <i class="fa-solid fa-bag-shopping"></i> Tiếp tục mua sắm
                    </a>
                </div>
            </div>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-xmark me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Bảng danh sách đơn hàng -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-5">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="py-3 ps-4">Mã đơn</th>
                        <th class="py-3">Ngày đặt</th>
                        <th class="py-3">Tổng tiền</th>
                        <th class="py-3">Cổng TT</th>
                        <th class="py-3">Trạng thái TT</th>
                        <th class="py-3">Vận đơn GHN</th>
                        <th class="py-3 text-end pe-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td class="ps-4">
                                <strong>{{ $order->order_code ?? ('ORD#' . $order->id) }}</strong>
                            </td>
                            <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <strong class="text-danger">{{ number_format($order->total_price ?? $order->total, 0, ',', '.') }}₫</strong>
                            </td>
                            <td>
                                @if($order->payment_method === 'momo')
                                    <span class="badge bg-danger" style="background-color: #a50064 !important;">
                                        <i class="fa-solid fa-wallet me-1"></i>MoMo
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="fa-solid fa-money-bill me-1"></i>COD
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($order->status === 'paid')
                                    <span class="badge bg-success">
                                        <i class="fa-solid fa-check-circle me-1"></i>Đã thanh toán
                                    </span>
                                @elseif($order->status === 'cod_ordered')
                                    <span class="badge bg-info text-dark">
                                        <i class="fa-solid fa-clock me-1"></i>Chờ thanh toán (COD)
                                    </span>
                                @elseif($order->status === 'cancelled')
                                    <span class="badge bg-dark">
                                        <i class="fa-solid fa-ban me-1"></i>Đã hủy
                                    </span>
                                @else
                                    <span class="badge bg-warning text-dark">
                                        <i class="fa-solid fa-hourglass-half me-1"></i>{{ $order->status_label }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($order->ghn_order_code)
                                    <span class="badge bg-light text-dark border font-monospace">
                                        <i class="fa-solid fa-truck-fast text-primary me-1"></i>{{ $order->ghn_order_code }}
                                    </span>
                                @else
                                    <span class="text-muted small">Chưa tạo</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-inline-flex gap-2">
                                    <!-- Nút Thanh toán lại cho đơn MoMo chưa thanh toán (Lab 06) -->
                                    @if($order->payment_method === 'momo' && $order->status !== 'paid' && $order->status !== 'cancelled')
                                        <a href="{{ route('user.orders.momo.pay', $order) }}" class="btn btn-sm btn-danger fw-bold" style="background-color: #a50064; border-color: #a50064;">
                                            <i class="fa-solid fa-rotate-right me-1"></i>Thanh toán lại
                                        </a>
                                    @endif

                                    <a href="{{ route('account.orders.show', $order) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="fa-regular fa-eye me-1"></i>Chi tiết
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-receipt fa-3x mb-3 d-block text-secondary opacity-50"></i>
                                Bạn chưa có đơn hàng nào.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="p-3 border-top">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

    <!-- Hướng dẫn tài khoản thẻ ATM Test MoMo Sandbox (Lab 06) -->
    <div class="card border-0 shadow-sm rounded-3 p-4 bg-light">
        <h5 class="fw-bold mb-2">
            <i class="fa-solid fa-circle-info text-primary me-2"></i>Thông tin thẻ ATM Test Cổng MoMo Sandbox
        </h5>
        <p class="text-muted small mb-3">
            Sử dụng các thông tin thẻ test dưới đây khi được chuyển hướng tới cổng thanh toán MoMo Sandbox để kiểm tra các trường hợp:
        </p>

        <div class="table-responsive bg-white rounded-2 border">
            <table class="table table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">No</th>
                        <th>Tên chủ thẻ</th>
                        <th>Số thẻ</th>
                        <th>Hạn ghi trên thẻ</th>
                        <th>Mã OTP</th>
                        <th>Trường hợp kiểm thử</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="ps-3">1</td>
                        <td><strong>NGUYEN VAN A</strong></td>
                        <td><code>9704 0000 0000 0018</code></td>
                        <td>12/30</td>
                        <td>OTP bất kỳ</td>
                        <td><span class="badge bg-success">Thành công</span></td>
                    </tr>
                    <tr>
                        <td class="ps-3">2</td>
                        <td><strong>NGUYEN VAN A</strong></td>
                        <td><code>9704 0000 0000 0026</code></td>
                        <td>12/30</td>
                        <td>OTP bất kỳ</td>
                        <td><span class="badge bg-danger">Thẻ khóa</span></td>
                    </tr>
                    <tr>
                        <td class="ps-3">3</td>
                        <td><strong>NGUYEN VAN A</strong></td>
                        <td><code>9704 0000 0000 0034</code></td>
                        <td>12/30</td>
                        <td>OTP bất kỳ</td>
                        <td><span class="badge bg-warning text-dark">Không đủ tiền</span></td>
                    </tr>
                    <tr>
                        <td class="ps-3">4</td>
                        <td><strong>NGUYEN VAN A</strong></td>
                        <td><code>9704 0000 0000 0042</code></td>
                        <td>12/30</td>
                        <td>OTP bất kỳ</td>
                        <td><span class="badge bg-secondary">Hạn mức thẻ</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
