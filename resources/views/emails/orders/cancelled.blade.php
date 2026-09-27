@extends('emails.layout')
@section('content')
<h1 style="font-size:22px;margin-top:0">Đơn hàng đã bị hủy</h1>
<p>Xin chào {{ $order->recipient_name }}, đơn hàng <strong>{{ $order->order_code }}</strong> đã được hủy.</p>
@if($order->cancelled_reason)<p><strong>Lý do:</strong> {{ $order->cancelled_reason }}</p>@endif
<p><strong>Tổng tiền:</strong> {{ number_format((float) $order->total, 0, ',', '.') }}₫<br><strong>Trạng thái:</strong> Đã hủy</p>
<p><a href="{{ route('account.orders.show', $order) }}">Xem đơn hàng</a></p>
@endsection
