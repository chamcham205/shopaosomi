<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CheckUserStatus Middleware
 *
 * Kiểm tra tài khoản có bị khóa không sau khi đã đăng nhập.
 * Nếu admin khóa tài khoản đang đăng nhập → tự động logout ngay lần request tiếp theo.
 *
 * Rule PQ-06 (BUSINESS_RULES.md):
 * Tài khoản bị khóa (status=locked) không được đăng nhập
 */
class CheckUserStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        // Chỉ kiểm tra khi đã đăng nhập
        if (auth()->check()) {
            $user = auth()->user();

            // Nếu tài khoản bị khóa → đăng xuất ngay lập tức
            if ($user->isLocked()) {
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->with('error', 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ hỗ trợ.');
            }
        }

        return $next($request);
    }
}
