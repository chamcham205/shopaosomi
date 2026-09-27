<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ColorController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SizeController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Customer\HomeController;
use App\Http\Controllers\Customer\ProductController as CustomerProductController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\WishlistController;
use App\Http\Controllers\Customer\CheckoutController;
use App\Http\Controllers\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\ShippingController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Customer\ReviewController as CustomerReviewController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\BannedWordController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\FinanceController;

use App\Http\Controllers\GHNWebhookController;
use App\Http\Controllers\User\MomoController;
use App\Http\Controllers\User\OrderController as UserOrderController;
use App\Http\Controllers\Customer\ChatController as CustomerChatController;
use App\Http\Controllers\Admin\ChatController as AdminChatController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| SHIRT STORE – Web Routes
|--------------------------------------------------------------------------
*/

// ── PUBLIC ──────────────────────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/products', [CustomerProductController::class, 'index'])->name('products.index');
Route::get('/products/{slug}', [CustomerProductController::class, 'show'])->name('products.show');

// ── GUEST ONLY ───────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'showForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store')->middleware('throttle:10,5');
    Route::get('/login', [LoginController::class, 'showForm'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendLink'])->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'update'])->name('password.update');
});

// ── LOGOUT ───────────────────────────────────────────────────────────────
Route::match(['get', 'post'], '/logout', [LoginController::class, 'destroy'])->name('logout');

Route::middleware(['auth','check.user.status','prevent.admin.shopping'])->group(function () {
    Route::get('/cart',[CartController::class,'index'])->name('cart.index');
    Route::post('/cart',[CartController::class,'store'])->name('cart.store');
    Route::delete('/cart',[CartController::class,'clear'])->name('cart.clear');
    Route::post('/cart/coupon',[CartController::class,'coupon'])->name('cart.coupon');
    Route::delete('/cart/coupon',[CartController::class,'removeCoupon'])->name('cart.coupon.destroy');
    Route::post('/cart/shipping',[CartController::class,'shipping'])->name('cart.shipping');
    Route::patch('/cart/{item}',[CartController::class,'update'])->name('cart.update');
    Route::delete('/cart/{item}',[CartController::class,'destroy'])->name('cart.destroy');
    Route::get('/wishlist',[WishlistController::class,'index'])->name('wishlist.index');
    Route::post('/wishlist/{product}',[WishlistController::class,'store'])->name('wishlist.store');
    Route::delete('/wishlist/{product}',[WishlistController::class,'destroy'])->name('wishlist.destroy');
    Route::get('/checkout',[CheckoutController::class,'index'])->name('checkout.index');
    Route::post('/checkout',[CheckoutController::class,'store'])->name('checkout.store');
    Route::get('/checkout/success/{order}',[CheckoutController::class,'success'])->name('checkout.success');
    Route::get('/account/orders',[CustomerOrderController::class,'index'])->name('account.orders.index');
    Route::get('/account/orders/{order}',[CustomerOrderController::class,'show'])->name('account.orders.show');
    Route::post('/account/orders/{order}/cancel',[CustomerOrderController::class,'cancel'])->name('account.orders.cancel');
    Route::post('/products/{product}/reviews',[CustomerReviewController::class,'store'])->middleware('throttle:5,1')->name('reviews.store');
    Route::get('/account/reviews',[CustomerReviewController::class,'index'])->name('account.reviews.index');
    Route::get('/account/chat', [CustomerChatController::class, 'index'])->name('account.chat.index');
    Route::get('/account/chat/messages', [CustomerChatController::class, 'getMessages'])->name('account.chat.messages');
    Route::post('/account/chat/messages', [CustomerChatController::class, 'sendMessage'])->name('account.chat.send');
    Route::get('/account/chat/unread-count', [CustomerChatController::class, 'unreadCount'])->name('account.chat.unread');
});

// ── 🚚 THIRD-PARTY WEBHOOKS & CALLBACKS (GHN, MOMO IPN) ───────────────────
// NOTE: Không dùng middleware auth vì bên thứ 3 gọi sang. Bypassed CSRF trong bootstrap/app.php
Route::post('/ghn/webhook', [GHNWebhookController::class, 'handle'])->name('ghn.webhook');
Route::post('/payment/momo/ipn', [MomoController::class, 'ipn'])->name('payment.momo.ipn');
Route::get('/payment/momo/callback', [MomoController::class, 'callback'])->name('user.payment.momo.callback');

// ── 🛒 LAB 06: USER PAYMENT & ORDERS ──────────────────────────────────────
Route::middleware(['auth', 'check.user.status', 'prevent.admin.shopping'])->prefix('user')->name('user.')->group(function () {
    Route::get('/payment', [UserOrderController::class, 'index'])->name('payment.index');
    Route::post('/payment/process', [UserOrderController::class, 'processPayment'])->name('payment.process');
    Route::get('/payment/ghn-districts', [UserOrderController::class, 'getDistricts'])->name('payment.ghn.districts');
    Route::get('/payment/ghn-wards', [UserOrderController::class, 'getWards'])->name('payment.ghn.wards');
    Route::get('/payment/ghn-fee', [UserOrderController::class, 'calculateFee'])->name('payment.ghn.fee');
    Route::get('/orders/{order}/pay/momo', [MomoController::class, 'payAgain'])->name('orders.momo.pay');
    Route::get('/orders/{order}/start-momo', [MomoController::class, 'start'])->name('orders.momo.start');
    Route::get('/orders', [UserOrderController::class, 'orderIndex'])->name('orders.index');
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
});

// ── ADMIN ────────────────────────────────────────────────────────────────

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin', 'check.user.status'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // ── Danh mục ──────────────────────────────────────────────────────
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::patch('/categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])
        ->name('categories.toggle-status');

    // ── Sản phẩm ──────────────────────────────────────────────────────
    Route::resource('products', ProductController::class);
    Route::patch('/products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])
        ->name('products.toggle-status');
    Route::patch('/products/{id}/restore', [ProductController::class, 'restore'])
        ->name('products.restore');
    Route::patch('/products/{product}/images/{image}/set-main', [ProductController::class, 'setMainImage'])
        ->name('products.images.set-main');
    Route::delete('/products/{product}/images/{image}', [ProductController::class, 'destroyImage'])
        ->name('products.images.destroy');

    // ── Màu sắc ───────────────────────────────────────────────────────
    Route::get('/colors', [ColorController::class, 'index'])->name('colors.index');
    Route::post('/colors', [ColorController::class, 'store'])->name('colors.store');
    Route::put('/colors/{color}', [ColorController::class, 'update'])->name('colors.update');
    Route::delete('/colors/{color}', [ColorController::class, 'destroy'])->name('colors.destroy');
    Route::patch('/colors/{color}/toggle-status', [ColorController::class, 'toggleStatus'])
        ->name('colors.toggle-status');

    // ── Kích cỡ ───────────────────────────────────────────────────────
    Route::get('/sizes', [SizeController::class, 'index'])->name('sizes.index');
    Route::post('/sizes', [SizeController::class, 'store'])->name('sizes.store');
    Route::put('/sizes/{size}', [SizeController::class, 'update'])->name('sizes.update');
    Route::delete('/sizes/{size}', [SizeController::class, 'destroy'])->name('sizes.destroy');

    // ── Tồn kho ───────────────────────────────────────────────────────
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/{product}', [InventoryController::class, 'show'])->name('inventory.show');
    Route::post('/inventory/{product}', [InventoryController::class, 'update'])->name('inventory.update');
    Route::resource('coupons',CouponController::class)->except(['show']);
    Route::patch('/coupons/{coupon}/toggle',[CouponController::class,'toggle'])->name('coupons.toggle');
    Route::get('/shipping',[ShippingController::class,'index'])->name('shipping.index');
    Route::post('/shipping',[ShippingController::class,'store'])->name('shipping.store');
    Route::put('/shipping/{zone}',[ShippingController::class,'update'])->name('shipping.update');
    Route::delete('/shipping/{zone}',[ShippingController::class,'destroy'])->name('shipping.destroy');
    Route::get('/orders',[AdminOrderController::class,'index'])->name('orders.index');
    Route::get('/orders/{order}',[AdminOrderController::class,'show'])->name('orders.show');
    Route::patch('/orders/{order}/status',[AdminOrderController::class,'updateStatus'])->name('orders.status');
    Route::get('/customers',[CustomerController::class,'index'])->name('customers.index');
    Route::get('/customers/create',[CustomerController::class,'create'])->name('customers.create');
    Route::post('/customers',[CustomerController::class,'store'])->name('customers.store');
    Route::get('/customers/{user}',[CustomerController::class,'show'])->name('customers.show');
    Route::get('/customers/{user}/edit',[CustomerController::class,'edit'])->name('customers.edit');
    Route::put('/customers/{user}',[CustomerController::class,'update'])->name('customers.update');
    Route::patch('/customers/{user}/status',[CustomerController::class,'status'])->name('customers.status');
    Route::resource('users', AdminUserController::class);
    Route::get('/reports',[ReportController::class,'index'])->name('reports.index');
    Route::get('/reports/charts', [ReportController::class, 'charts'])->name('reports.charts');
    Route::get('/finance', [FinanceController::class, 'index'])->name('finance.index');
    Route::get('/finance/transactions', [FinanceController::class, 'transactions'])->name('finance.transactions');
    Route::patch('/finance/{order}/status', [FinanceController::class, 'updateStatus'])->name('finance.update-status');
    Route::get('/reviews',[AdminReviewController::class,'index'])->name('reviews.index');
    Route::get('/reviews/{review}',[AdminReviewController::class,'show'])->name('reviews.show');
    Route::patch('/reviews/{review}/moderate',[AdminReviewController::class,'moderate'])->name('reviews.moderate');
    Route::get('/banned-words',[BannedWordController::class,'index'])->name('banned-words.index');
    Route::post('/banned-words',[BannedWordController::class,'store'])->name('banned-words.store');
    Route::put('/banned-words/{word}',[BannedWordController::class,'update'])->name('banned-words.update');
    Route::patch('/banned-words/{word}/toggle',[BannedWordController::class,'toggle'])->name('banned-words.toggle');
    Route::delete('/banned-words/{word}',[BannedWordController::class,'destroy'])->name('banned-words.destroy');
    Route::get('/settings',[SettingController::class,'index'])->name('settings.index');
    Route::put('/settings',[SettingController::class,'update'])->name('settings.update');
    Route::get('/banners',[BannerController::class,'index'])->name('banners.index');
    Route::post('/banners',[BannerController::class,'store'])->name('banners.store');
    Route::put('/banners/{banner}',[BannerController::class,'update'])->name('banners.update');
    Route::patch('/banners/{banner}/toggle',[BannerController::class,'toggle'])->name('banners.toggle');
    Route::delete('/banners/{banner}',[BannerController::class,'destroy'])->name('banners.destroy');

    // ── Live Chat Khách hàng ──────────────────────────────────────────
    Route::get('/chat/conversations', [AdminChatController::class, 'getConversations'])->name('chat.conversations');
    Route::get('/chat/conversations/{conversation}/messages', [AdminChatController::class, 'getMessages'])->name('chat.messages');
    Route::post('/chat/conversations/{conversation}/reply', [AdminChatController::class, 'reply'])->name('chat.reply');
    Route::get('/chat/unread-count', [AdminChatController::class, 'unreadCount'])->name('chat.unread');
});
