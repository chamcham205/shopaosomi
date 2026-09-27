<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\CheckUserStatus;
use App\Http\Middleware\PreventAdminShoppingMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Đăng ký middleware alias để dùng trong routes
        $middleware->alias([
            'admin'                   => AdminMiddleware::class,
            'check.user.status'       => CheckUserStatus::class,
            'prevent.admin.shopping'  => PreventAdminShoppingMiddleware::class,
        ]);

        // Bypass CSRF cho webhooks từ bên thứ ba (GHN, MoMo) và logout để tránh lỗi 419 khi session hết hạn
        $middleware->validateCsrfTokens(except: [
            'ghn/webhook',
            'payment/momo/ipn',
            'logout',
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            if ($request->is('logout') || $request->routeIs('logout')) {
                \Illuminate\Support\Facades\Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('login')->with('success', 'Bạn đã đăng xuất thành công.');
            }
            return redirect()->back()->with('warning', 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang.');
        });
    })->create();

