@extends('layouts.app')
@section('title',$order->order_code)
@section('content')<div class="container shop-shell py-5"><div class="d-flex justify-content-between flex-wrap align-items-center mb-4 gap-3">
    <div>
        <p class="eyebrow mb-1">Chi tiết đơn hàng</p>
        <h1 class="section-title mb-0">{{ $order->order_code }}</h1>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-secondary py-2 px-3">{{ $order->status_label }}</span>
        <a href="{{ route('home') }}" class="btn btn-outline-dark">
            <i class="fa-solid fa-house me-1"></i>Trang chủ
        </a>
        <a href="{{ route('user.orders.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-list me-1"></i>Lịch sử đơn
        </a>
    </div>
</div><div class="row g-4"><div class="col-lg-8"><div class="filter-panel p-4"><h2 class="h5">Sản phẩm</h2>@foreach($order->items as $item)<div class="d-flex justify-content-between border-bottom py-3"><span>{{ $item->product_name }}<small class="d-block text-muted">SKU {{ $item->product_sku }} · {{ $item->color_name }}/{{ $item->size_name }} × {{ $item->quantity }}</small></span><strong>{{ number_format($item->subtotal,0,',','.') }}₫</strong></div>@endforeach</div><div class="filter-panel p-4 mt-3"><h2 class="h5">Tiến trình</h2>@foreach($order->statusLogs as $log)<div class="border-start ps-3 mb-3"><strong>{{ ucfirst($log->to_status) }}</strong><small class="d-block text-muted">{{ $log->created_at->format('d/m/Y H:i') }} · {{ $log->note }}</small></div>@endforeach</div></div><aside class="col-lg-4"><div class="filter-panel p-4"><h2 class="h5">Người nhận</h2><p>{{ $order->recipient_name }}<br>{{ $order->phone }}<br>{{ $order->email }}</p><p>{{ $order->address }}, {{ $order->ward }}, {{ $order->district }}, {{ $order->province }}</p><hr><div class="d-flex justify-content-between"><span>Tạm tính</span><strong>{{ number_format($order->subtotal,0,',','.') }}₫</strong></div><div class="d-flex justify-content-between"><span>Giảm giá</span><strong>-{{ number_format($order->discount,0,',','.') }}₫</strong></div><div class="d-flex justify-content-between"><span>Phí ship</span><strong>{{ number_format($order->shipping_fee,0,',','.') }}₫</strong></div><hr><div class="d-flex justify-content-between h5"><span>Tổng</span><strong>{{ number_format($order->total,0,',','.') }}₫</strong></div>@if($order->payment_method === 'momo' && $order->status !== 'paid' && $order->status !== 'cancelled')<a href="{{ route('user.orders.momo.pay', $order) }}" class="btn btn-danger w-100 mt-3 fw-bold" style="background-color: #a50064; border-color: #a50064;"><i class="fa-solid fa-rotate-right me-1"></i>Thanh toán lại qua MoMo</a>@endif @if($order->status==='pending')<form method="POST" action="{{ route('account.orders.cancel',$order) }}" class="mt-4">@csrf<label class="form-label" for="reason">Lý do hủy</label><textarea class="form-control" id="reason" name="reason" required></textarea><button class="btn btn-outline-danger w-100 mt-2">Hủy đơn hàng</button></form>@endif</div></aside></div></div>@endsection

