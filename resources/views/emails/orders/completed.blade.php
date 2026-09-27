@extends('emails.layout')
@section('content')
<h1 style="font-size:22px;margin-top:0">Đơn hàng đã hoàn tất</h1>
<p>Xin chào {{ $order->recipient_name }}, đơn hàng đã hoàn tất. Cảm ơn bạn đã mua sắm tại SHIRT STORE.</p>
@include('emails.orders._summary')
<p><a href="{{ route('account.orders.show', $order) }}">Xem đơn hàng</a></p>
@endsection
