<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập – Shirt Store</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0; height: 100vh; display: flex;
            font-family: 'Inter', sans-serif;
            background: #f8f7f4;
        }
        /* ── Cột trái: hình ảnh/branding ── */
        .auth-hero {
            width: 50%;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            display: flex; flex-direction: column;
            justify-content: center; align-items: center;
            padding: 3rem; color: #fff; position: relative; overflow: hidden;
        }
        .auth-hero::before {
            content: '';
            position: absolute; inset: 0;
            background: url('https://images.unsplash.com/photo-1594938298603-c8148c4dae35?w=800&q=80') center/cover;
            opacity: .18;
        }
        .auth-hero-content { position: relative; text-align: center; }
        .auth-hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.8rem; font-weight: 700;
            color: #c8a96e; margin-bottom: .5rem; letter-spacing: 2px;
        }
        .auth-hero p { font-size: 1rem; opacity: .8; line-height: 1.7; }
        .auth-hero .tagline {
            font-size: .85rem; letter-spacing: 3px;
            text-transform: uppercase; opacity: .6; margin-bottom: 1rem;
        }

        /* ── Cột phải: form ── */
        .auth-form-wrap {
            width: 50%; display: flex;
            align-items: center; justify-content: center;
            padding: 2rem;
        }
        .auth-form-box { width: 100%; max-width: 420px; }
        .auth-form-box h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem; font-weight: 700;
            color: #1a1a2e; margin-bottom: .3rem;
        }
        .auth-form-box .subtitle { color: #888; font-size: .9rem; margin-bottom: 2rem; }

        .form-label { font-size: .85rem; font-weight: 600; color: #555; margin-bottom: .3rem; }
        .form-control {
            border: 1.5px solid #e0e0e0; border-radius: 8px;
            padding: .7rem 1rem; font-size: .92rem;
            transition: border-color .2s;
        }
        .form-control:focus {
            border-color: #c8a96e; box-shadow: 0 0 0 3px rgba(200,169,110,.15);
        }
        .input-group .form-control { border-right: none; }
        .input-group .btn-outline-secondary {
            border: 1.5px solid #e0e0e0; border-left: none;
            border-radius: 0 8px 8px 0; background: transparent; color: #888;
        }
        .input-group .btn-outline-secondary:hover { background: #f5f5f5; }

        .btn-login {
            background: linear-gradient(135deg, #c8a96e, #a0823e);
            color: #fff; border: none; border-radius: 8px;
            padding: .75rem; font-size: .95rem; font-weight: 600;
            width: 100%; letter-spacing: .5px;
            transition: opacity .2s, transform .1s;
        }
        .btn-login:hover { opacity: .92; transform: translateY(-1px); color: #fff; }

        .divider { text-align: center; color: #aaa; font-size: .8rem; margin: 1.2rem 0; position: relative; }
        .divider::before, .divider::after {
            content: ''; position: absolute; top: 50%;
            width: 40%; height: 1px; background: #e8e8e8;
        }
        .divider::before { left: 0; } .divider::after { right: 0; }

        .link-accent { color: #c8a96e; font-weight: 600; text-decoration: none; }
        .link-accent:hover { color: #a0823e; text-decoration: underline; }

        /* Responsive */
        @media (max-width: 768px) {
            body { flex-direction: column; height: auto; min-height: 100vh; }
            .auth-hero { width: 100%; min-height: 220px; padding: 2rem; }
            .auth-hero h1 { font-size: 2rem; }
            .auth-form-wrap { width: 100%; padding: 2rem 1.5rem; }
        }
    </style>
</head>
<body>

{{-- ── CỘT TRÁI: Branding ── --}}
<div class="auth-hero">
    <div class="auth-hero-content">
        <p class="tagline">Thời trang nam cao cấp</p>
        <h1>SHIRT STORE</h1>
        <p>Bộ sưu tập áo sơ mi nam premium.<br>Chất liệu cao cấp – Thiết kế tinh tế.<br>Phong cách đẳng cấp mọi dịp.</p>
    </div>
</div>

{{-- ── CỘT PHẢI: Form đăng nhập ── --}}
<div class="auth-form-wrap">
    <div class="auth-form-box">

        <h2>Đăng nhập</h2>
        <p class="subtitle">Chào mừng trở lại! Vui lòng nhập thông tin.</p>

        {{-- Flash error --}}
        @if(session('error'))
            <div class="alert alert-danger rounded-3 py-2 px-3 mb-3 small">
                <i class="fa-solid fa-circle-xmark me-1"></i>{{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}">
            @csrf

            {{-- Email --}}
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email"
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}"
                       placeholder="ten@example.com" autocomplete="email" autofocus>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Password với show/hide --}}
            <div class="mb-3">
                <label class="form-label d-flex justify-content-between">
                    <span>Mật khẩu</span>
                    <a href="{{ route('password.request') }}" class="link-accent" style="font-size:.8rem">Quên mật khẩu?</a>
                </label>
                <div class="input-group">
                    <input type="password" name="password" id="password"
                           class="form-control @error('password') is-invalid @enderror"
                           placeholder="Nhập mật khẩu" autocomplete="current-password">
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password','eyeIcon')">
                        <i class="fa-solid fa-eye" id="eyeIcon"></i>
                    </button>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Remember me --}}
            <div class="mb-4 d-flex justify-content-between align-items-center">
                <div class="form-check">
                    <input type="checkbox" name="remember" class="form-check-input" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label small" for="remember">Ghi nhớ đăng nhập</label>
                </div>
            </div>

            <button type="submit" class="btn-login">
                <i class="fa-solid fa-right-to-bracket me-2"></i>Đăng nhập
            </button>
        </form>

        <div class="divider">hoặc</div>

        <p class="text-center small text-muted">
            Chưa có tài khoản?
            <a href="{{ route('register') }}" class="link-accent">Đăng ký ngay</a>
        </p>

        <p class="text-center mt-4">
            <a href="{{ route('home') }}" class="text-muted small">
                <i class="fa-solid fa-arrow-left me-1"></i>Về trang chủ
            </a>
        </p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePassword(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon  = document.getElementById(iconId);
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
</script>
</body>
</html>
