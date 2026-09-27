@extends('layouts.app')
@section('title','Đặt hàng thành công')
@section('content')
<div class="container shop-shell py-5 text-center">
    <div class="filter-panel p-5 mx-auto rounded-4 shadow-sm" style="max-width:650px">
        <div class="rounded-circle bg-success-subtle text-success mx-auto d-flex align-items-center justify-content-center mb-3" style="width:76px;height:76px;">
            <i class="fa-solid fa-circle-check fa-3x"></i>
        </div>
        <h1 class="section-title mb-2">ĐẶT HÀNG THÀNH CÔNG!</h1>
        <p class="lead text-muted mb-2">Mã đơn hàng: <strong class="text-dark">{{ $order->order_code }}</strong></p>
        <p class="mb-4">Trạng thái: <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">{{ $order->status_label }}</span></p>
        <div class="d-flex gap-2 justify-content-center flex-wrap">
            <a class="btn btn-dark px-4 py-2 rounded-pill fw-semibold shadow-sm" href="{{ route('home') }}">
                <i class="fa-solid fa-house me-2"></i>Quay về trang chủ
            </a>
            <a class="btn btn-outline-dark px-3 py-2 rounded-pill" href="{{ route('account.orders.show',$order) }}">
                <i class="fa-regular fa-file-lines me-1"></i>Xem đơn hàng
            </a>
            <a class="btn btn-outline-secondary px-3 py-2 rounded-pill" href="{{ route('products.index') }}">
                <i class="fa-solid fa-bag-shopping me-1"></i>Tiếp tục mua sắm
            </a>
        </div>
    </div>
</div>
@endsection
