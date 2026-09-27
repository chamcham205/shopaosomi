<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * PreventAdminShoppingMiddleware
 *
 * Middleware này ngăn Admin/Super Admin sử dụng chức năng mua hàng.
 * Áp dụng cho: /cart, /wishlist, /buy-now, /checkout, /place-order
 *
 * Rule PQ-01 (BUSINESS_RULES.md):
 * Admin/Super_admin KHÔNG ĐƯỢC truy cập /cart, /checkout, /buy-now
 */
class PreventAdminShoppingMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Kiểm tra người dùng đã đăng nhập và có phải Admin không
        if (auth()->check() && auth()->user()->isAdmin()) {
            // Admin cố truy cập chức năng mua hàng → thông báo và redirect về dashboard
            return redirect()->route('admin.dashboard')
                ->with('warning', 'Tài khoản quản trị không được sử dụng chức năng mua hàng.');
        }

        return $next($request);
    }
}
