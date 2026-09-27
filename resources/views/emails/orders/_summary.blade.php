<p><strong>Mã đơn:</strong> {{ $order->order_code }}<br>
<strong>Ngày đặt:</strong> {{ $order->created_at->format('d/m/Y H:i') }}<br>
<strong>Trạng thái:</strong> {{ $order->status_label }}</p>
<table role="presentation" width="100%" cellspacing="0" cellpadding="8" style="border-collapse:collapse;font-size:14px">
@foreach($order->items as $item)
<tr style="border-bottom:1px solid #e4e4e7"><td>{{ $item->product_name }}<br><small>SKU: {{ $item->product_sku }} · Màu: {{ $item->color_name }} · Size: {{ $item->size_name }}</small></td><td align="right">{{ $item->quantity }} × {{ number_format((float) $item->price, 0, ',', '.') }}₫</td></tr>
@endforeach
</table>
<table role="presentation" width="100%" cellspacing="0" cellpadding="5" style="margin-top:16px;font-size:14px">
<tr><td>Tạm tính</td><td align="right">{{ number_format((float) $order->subtotal, 0, ',', '.') }}₫</td></tr>
<tr><td>Giảm giá</td><td align="right">-{{ number_format((float) $order->discount, 0, ',', '.') }}₫</td></tr>
<tr><td>Phí giao hàng</td><td align="right">{{ number_format((float) $order->shipping_fee, 0, ',', '.') }}₫</td></tr>
<tr style="font-weight:bold;font-size:16px"><td>Tổng cộng</td><td align="right">{{ number_format((float) $order->total, 0, ',', '.') }}₫</td></tr>
</table>
