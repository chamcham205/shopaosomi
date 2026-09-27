<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/**
 * LoginController
 *
 * Xử lý đăng nhập, đăng xuất.
 *
 * Luồng dữ liệu:
 *   GET /login   → showForm()  → view auth.login
 *   POST /login  → store()     → validate → rate limit → auth → redirect theo role
 *   POST /logout → destroy()   → logout → redirect
 *
 * BUSINESS_RULES §10.1: Rate limit key = login:{email_normalized}|{ip}
 */
class LoginController extends Controller
{
    /**
     * Hiển thị form đăng nhập.
     */
    public function showForm()
    {
        // Nếu đã đăng nhập rồi → redirect về trang phù hợp
        if (Auth::check()) {
            return Auth::user()->isAdmin()
                ? redirect()->route('admin.dashboard')
                : redirect()->route('home');
        }

        return view('auth.login');
    }

    /**
     * Xử lý đăng nhập.
     *
     * Rate Limiting theo BUSINESS_RULES §10.1:
     * - Key: login:{email_normalized}|{ip}
     * - Giới hạn: 5 lần thất bại / 60 giây
     * - Clear sau khi đăng nhập thành công
     */
    public function store(Request $request)
    {
        // Bước 1: Validate form cơ bản
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required'    => 'Vui lòng nhập email.',
            'email.email'       => 'Email không đúng định dạng.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        // Bước 2: Normalize email (lowercase + trim) – quan trọng để RateLimiter key nhất quán
        $email = strtolower(trim($request->input('email')));

        // Bước 3: Tạo RateLimiter key kết hợp email + IP
        // Theo BUSINESS_RULES §10.1: không chỉ dùng IP để tránh bị bypass bằng nhiều email
        $rateLimitKey = 'login:' . $email . '|' . $request->ip();

        // Bước 4: Kiểm tra đã vượt giới hạn đăng nhập thất bại chưa (5 lần / 60 giây)
        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return back()->withErrors([
                'email' => "Quá nhiều lần đăng nhập thất bại. Vui lòng thử lại sau {$seconds} giây.",
            ])->onlyInput('email');
        }

        // Bước 5: Thử đăng nhập
        $credentials = [
            'email'    => $email,
            'password' => $request->input('password'),
        ];
        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            // Đăng nhập thất bại → tăng bộ đếm rate limit (decay 60 giây)
            RateLimiter::hit($rateLimitKey, 60);

            return back()->withErrors([
                'email' => 'Email hoặc mật khẩu không đúng.',
            ])->onlyInput('email');
        }

        // Bước 6: Kiểm tra tài khoản có bị khóa không (Rule PQ-06)
        $user = Auth::user();
        if ($user->isLocked()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return back()->withErrors([
                'email' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ hỗ trợ.',
            ])->onlyInput('email');
        }

        // Bước 7: Đăng nhập thành công → clear RateLimiter, regenerate session
        RateLimiter::clear($rateLimitKey);
        $request->session()->regenerate();

        // Bước 8: Redirect theo role (Rule AUTH-06)
        // Admin/Super Admin → /admin/dashboard
        // Customer → /
        if ($user->isAdmin()) {
            return redirect()->intended(route('admin.dashboard'))
                ->with('success', 'Đăng nhập thành công! Xin chào ' . $user->name . '.');
        }

        return redirect()->intended(route('home'))
            ->with('success', 'Đăng nhập thành công! Xin chào ' . $user->name . '.');
    }

    /**
     * Đăng xuất.
     *
     * Phải: logout + invalidate session + regenerate CSRF token
     */
    public function destroy(Request $request)
    {
        Auth::logout();

        // Hủy session hiện tại để tránh session fixation attack
        $request->session()->invalidate();

        // Tạo CSRF token mới
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Bạn đã đăng xuất thành công.');
    }
}
