<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * AdminMiddleware
 *
 * Middleware này bảo vệ tất cả route /admin/*.
 * Chỉ cho phép người dùng có role = 'admin' hoặc 'super_admin' truy cập.
 *
 * Áp dụng cho: Route::prefix('admin')->middleware(['auth', 'admin'])
 */
class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Kiểm tra người dùng đã đăng nhập chưa
        if (! auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Vui lòng đăng nhập để tiếp tục.');
        }

        $user = auth()->user();

        // Kiểm tra role có phải admin hoặc super_admin không
        if (! $user->isAdmin()) {
            // Customer cố tình truy cập /admin → trả về 403
            abort(403, 'Bạn không có quyền truy cập khu vực quản trị.');
        }

        // Kiểm tra tài khoản admin có bị khóa không
        if ($user->isLocked()) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')
                ->with('error', 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ hỗ trợ.');
        }

        return $next($request);
    }
}
