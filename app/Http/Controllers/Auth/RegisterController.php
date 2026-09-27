<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * RegisterController
 *
 * Controller xử lý đăng ký tài khoản mới.
 *
 * Luồng dữ liệu:
 *   GET /register  → showForm()  → view auth.register
 *   POST /register → store()     → validate → tạo User → login → redirect
 *
 * Rule AUTH-04: Role luôn = 'customer' khi đăng ký, KHÔNG lấy từ request.
 */
class RegisterController extends Controller
{
    /**
     * Hiển thị form đăng ký.
     */
    public function showForm()
    {
        // Nếu đã đăng nhập rồi thì không cần vào trang đăng ký nữa
        if (Auth::check()) {
            return Auth::user()->isAdmin()
                ? redirect()->route('admin.dashboard')
                : redirect()->route('home');
        }

        return view('auth.register');
    }

    /**
     * Xử lý đăng ký tài khoản mới.
     * Rate limit: 10 lần / 5 phút / IP (áp qua route middleware)
     */
    public function store(Request $request)
    {
        // Validate dữ liệu đầu vào, thông báo lỗi bằng tiếng Việt
        $request->validate([
            'name'     => ['required', 'string', 'max:191', 'min:2'],
            'email'    => ['required', 'string', 'email', 'max:191', 'unique:users,email'],
            'phone'    => ['nullable', 'regex:/^0[3-9][0-9]{8}$/'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'terms'    => ['accepted'],
        ], [
            'name.required'     => 'Vui lòng nhập họ và tên.',
            'name.min'          => 'Họ và tên phải có ít nhất 2 ký tự.',
            'email.required'    => 'Vui lòng nhập email.',
            'email.email'       => 'Email không đúng định dạng.',
            'email.unique'      => 'Email này đã được đăng ký. Vui lòng dùng email khác.',
            'phone.regex'       => 'Số điện thoại không hợp lệ (VD: 0912345678).',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min'      => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password.confirmed'=> 'Xác nhận mật khẩu không khớp.',
            'terms.accepted'    => 'Vui lòng đồng ý với điều khoản sử dụng.',
        ]);

        // Normalize email: lowercase + trim để tránh trùng do chữ hoa/thường
        $email = strtolower(trim($request->input('email')));

        // Tạo tài khoản mới
        // Rule AUTH-04: KHÔNG lấy role từ request, luôn hardcode = 'customer'
        // Rule AUTH-03: password tự động hash nhờ cast 'hashed' trong Model
        $user = User::create([
            'name'     => trim($request->input('name')),
            'email'    => $email,
            'phone'    => $request->input('phone'),
            'password' => Hash::make($request->input('password')),
            'role'     => 'customer', // Hardcode – không bao giờ lấy từ $request
            'status'   => 'active',
            'theme'    => 'light',
        ]);

        // Đăng nhập ngay sau khi đăng ký thành công
        Auth::login($user);

        return redirect()->route('home')
            ->with('success', 'Đăng ký thành công! Chào mừng bạn đến với Shirt Store.');
    }
}
