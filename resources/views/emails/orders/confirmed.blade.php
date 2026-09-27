@extends('emails.layout')
@section('content')
<h1 style="font-size:22px;margin-top:0">Đơn hàng đã được xác nhận</h1>
<p>Xin chào {{ $order->recipient_name }}, đơn hàng của bạn đã được SHIRT STORE xác nhận và sẽ sớm được chuẩn bị.</p>
@include('emails.orders._summary')
<p><a href="{{ route('account.orders.show', $order) }}">Xem chi tiết đơn hàng</a></p>
@endsection
