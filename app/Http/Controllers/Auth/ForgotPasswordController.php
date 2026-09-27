<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;

/**
 * ForgotPasswordController
 *
 * Xử lý yêu cầu đặt lại mật khẩu.
 *
 * Luồng:
 *   GET  /forgot-password → showForm()
 *   POST /forgot-password → sendLink() → tạo token → gửi email → thông báo chung
 *
 * BUSINESS_RULES §10.2:
 * - Rate limit email: 3 request / 15 phút
 * - Rate limit IP   : 5 request / 15 phút
 * - Không tiết lộ email có tồn tại hay không (anti user-enumeration)
 */
class ForgotPasswordController extends Controller
{
    /**
     * Hiển thị form nhập email quên mật khẩu.
     */
    public function showForm()
    {
        if (auth()->check()) {
            return redirect()->route('home');
        }
        return view('auth.forgot-password');
    }

    /**
     * Gửi link đặt lại mật khẩu.
     *
     * Quan trọng: dù email có tồn tại hay không,
     * luôn trả về cùng một thông báo để tránh user enumeration attack.
     */
    public function sendLink(Request $request)
    {
        // Validate email cơ bản
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email'    => 'Email không đúng định dạng.',
        ]);

        // Normalize email trước khi xử lý
        $email = strtolower(trim($request->input('email')));

        // Kiểm tra rate limit theo EMAIL (3 lần / 15 phút)
        $emailKey = 'forgot-password:email:' . $email;
        if (RateLimiter::tooManyAttempts($emailKey, 3)) {
            $seconds = RateLimiter::availableIn($emailKey);
            return back()->withErrors([
                'email' => "Bạn đã gửi quá nhiều yêu cầu. Vui lòng thử lại sau {$seconds} giây.",
            ]);
        }

        // Kiểm tra rate limit theo IP (5 lần / 15 phút)
        $ipKey = 'forgot-password:ip:' . $request->ip();
        if (RateLimiter::tooManyAttempts($ipKey, 5)) {
            $seconds = RateLimiter::availableIn($ipKey);
            return back()->withErrors([
                'email' => "Quá nhiều yêu cầu từ địa chỉ này. Vui lòng thử lại sau {$seconds} giây.",
            ]);
        }

        // Tăng bộ đếm rate limit (decay 900 giây = 15 phút)
        RateLimiter::hit($emailKey, 900);
        RateLimiter::hit($ipKey, 900);

        // Gửi link reset password qua Laravel Password Broker
        // Laravel tự xử lý: tạo token → lưu vào password_reset_tokens → gửi email
        // Nếu email không tồn tại → Laravel vẫn trả về status mà không gửi email
        Password::sendResetLink(['email' => $email]);

        // Rule RL-FP-03: KHÔNG tiết lộ email có tồn tại hay không
        // Luôn trả cùng thông báo dù email có tồn tại hay không
        return back()->with(
            'status',
            'Nếu email tồn tại trong hệ thống, chúng tôi sẽ gửi hướng dẫn đặt lại mật khẩu. Vui lòng kiểm tra hộp thư (kể cả Spam).'
        );
    }
}
