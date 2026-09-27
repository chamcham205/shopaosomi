<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

/**
 * ResetPasswordController
 *
 * Xử lý đặt lại mật khẩu qua link email.
 *
 * Luồng:
 *   GET  /reset-password/{token} → showForm()  → view auth.reset-password
 *   POST /reset-password         → update()    → validate → reset → login → redirect
 *
 * Rule AUTH-09: Token hết hạn sau 15 phút (cấu hình trong config/auth.php)
 * Rule AUTH-10: Sau khi dùng token → xóa khỏi password_reset_tokens ngay
 */
class ResetPasswordController extends Controller
{
    /**
     * Hiển thị form đặt lại mật khẩu.
     * Token được đính kèm trong URL từ email.
     */
    public function showForm(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    /**
     * Xử lý đặt lại mật khẩu.
     * Laravel Password Broker tự kiểm tra token hợp lệ và chưa hết hạn.
     */
    public function update(Request $request)
    {
        // Validate đầu vào
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::min(8)],
        ], [
            'email.required'     => 'Vui lòng nhập email.',
            'email.email'        => 'Email không đúng định dạng.',
            'password.required'  => 'Vui lòng nhập mật khẩu mới.',
            'password.min'       => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ]);

        // Thực hiện reset password qua Laravel Password Broker
        // Tự động: kiểm tra token + thời hạn → hash password → xóa token → lưu DB
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                // Cập nhật mật khẩu mới (hash tự động qua cast 'hashed')
                $user->forceFill([
                    'password'       => Hash::make($password),
                    'remember_token' => Str::random(60), // Vô hiệu hóa all remember tokens cũ
                ])->save();

                // Dispatch event để Laravel xử lý các task sau reset (log, notification...)
                event(new PasswordReset($user));
            }
        );

        // Kiểm tra kết quả reset
        if ($status === Password::PasswordReset) {
            // Thành công → redirect về login
            return redirect()->route('login')
                ->with('success', 'Mật khẩu đã được đặt lại thành công. Vui lòng đăng nhập.');
        }

        // Thất bại (token hết hạn, email không đúng, v.v.)
        return back()->withErrors([
            'email' => match ($status) {
                Password::INVALID_TOKEN   => 'Link đặt lại mật khẩu không hợp lệ hoặc đã hết hạn. Vui lòng yêu cầu lại.',
                Password::INVALID_USER    => 'Không tìm thấy tài khoản với email này.',
                Password::RESET_THROTTLED => 'Bạn đã thực hiện quá nhiều lần. Vui lòng thử lại sau.',
                default                   => 'Có lỗi xảy ra. Vui lòng thử lại.',
            },
        ]);
    }
}
