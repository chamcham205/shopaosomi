@extends('emails.layout')
@section('content')
<h1 style="font-size:22px;margin-top:0">Đặt lại mật khẩu</h1>
<p>Xin chào {{ $name }}, chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản SHIRT STORE của bạn.</p>
<p><a href="{{ $url }}" style="display:inline-block;background:#18181b;color:#fff;text-decoration:none;padding:12px 18px;border-radius:5px">Đặt lại mật khẩu</a></p>
<p>Liên kết có hiệu lực trong {{ $expires }} phút. Nếu bạn không yêu cầu, hãy bỏ qua email này.</p>
@endsection
