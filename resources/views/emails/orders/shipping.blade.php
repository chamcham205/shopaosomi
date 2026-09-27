@extends('emails.layout')
@section('content')
<h1 style="font-size:22px;margin-top:0">Đơn hàng đang được giao</h1>
<p>Xin chào {{ $order->recipient_name }}, đơn hàng của bạn đã được bàn giao để vận chuyển.</p>
@include('emails.orders._summary')
<p><a href="{{ route('account.orders.show', $order) }}">Theo dõi đơn hàng</a></p>
@endsection
