@extends('emails.layout')
@section('content')
<h1 style="font-size:22px;margin-top:0">Đặt hàng thành công</h1>
<p>Xin chào {{ $order->recipient_name }}, SHIRT STORE đã nhận đơn hàng của bạn.</p>
@include('emails.orders._summary')
<p><strong>Thanh toán:</strong> COD — thanh toán khi nhận hàng.</p>
<p><strong>Địa chỉ giao hàng:</strong><br>{{ $order->recipient_name }} — {{ $order->phone }}<br>{{ $order->address }}, {{ $order->ward }}, {{ $order->district }}, {{ $order->province }}</p>
<p><a href="{{ route('account.orders.show', $order) }}" style="display:inline-block;background:#18181b;color:#fff;text-decoration:none;padding:12px 18px;border-radius:5px">Xem đơn hàng</a></p>
@endsection
