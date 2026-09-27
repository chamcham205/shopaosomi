<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký – Shirt Store</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; display: flex; font-family: 'Inter', sans-serif; background: #f8f7f4; min-height: 100vh; }
        .auth-hero {
            width: 42%; background: linear-gradient(160deg, #0f3460 0%, #16213e 50%, #1a1a2e 100%);
            display: flex; flex-direction: column; justify-content: center; align-items: center;
            padding: 3rem; color: #fff; position: relative; overflow: hidden;
        }
        .auth-hero::before {
            content: ''; position: absolute; inset: 0;
            background: url('https://images.unsplash.com/photo-1620012253295-c15cc3e65df4?w=800&q=80') center/cover;
            opacity: .15;
        }
        .auth-hero-content { position: relative; text-align: center; }
        .auth-hero h1 { font-family: 'Playfair Display', serif; font-size: 2.4rem; color: #c8a96e; letter-spacing: 2px; }
        .auth-hero .features { list-style: none; padding: 0; margin-top: 2rem; text-align: left; }
        .auth-hero .features li { padding: .4rem 0; font-size: .9rem; opacity: .85; }
        .auth-hero .features li i { color: #c8a96e; margin-right: .6rem; }
        .auth-form-wrap { flex: 1; display: flex; align-items: center; justify-content: center; padding: 2rem; overflow-y: auto; }
        .auth-form-box { width: 100%; max-width: 460px; }
        .auth-form-box h2 { font-family: 'Playfair Display', serif; font-size: 1.7rem; color: #1a1a2e; margin-bottom: .3rem; }
        .auth-form-box .subtitle { color: #888; font-size: .9rem; margin-bottom: 1.5rem; }
        .form-label { font-size: .85rem; font-weight: 600; color: #555; margin-bottom: .3rem; }
        .form-control { border: 1.5px solid #e0e0e0; border-radius: 8px; padding: .65rem 1rem; font-size: .92rem; transition: border-color .2s; }
        .form-control:focus { border-color: #c8a96e; box-shadow: 0 0 0 3px rgba(200,169,110,.15); }
        .input-group .form-control { border-right: none; }
        .input-group .btn-outline-secondary { border: 1.5px solid #e0e0e0; border-left: none; border-radius: 0 8px 8px 0; background: transparent; color: #888; }
        .btn-register { background: linear-gradient(135deg, #c8a96e, #a0823e); color: #fff; border: none; border-radius: 8px; padding: .75rem; font-size: .95rem; font-weight: 600; width: 100%; transition: opacity .2s, transform .1s; }
        .btn-register:hover { opacity: .9; transform: translateY(-1px); color: #fff; }
        .link-accent { color: #c8a96e; font-weight: 600; text-decoration: none; }
        .link-accent:hover { color: #a0823e; text-decoration: underline; }
        .password-strength { height: 4px; border-radius: 2px; transition: all .3s; background: #e0e0e0; }
        @media (max-width: 768px) { body { flex-direction: column; } .auth-hero { width: 100%; min-height: 180px; } }
    </style>
</head>
<body>

{{-- Cột trái --}}
<div class="auth-hero">
    <div class="auth-hero-content">
        <p style="letter-spacing:3px;font-size:.8rem;opacity:.6;text-transform:uppercase">Tạo tài khoản</p>
        <h1>SHIRT STORE</h1>
        <p style="opacity:.75;line-height:1.7;margin-top:1rem">Tham gia cộng đồng những người đàn ông<br>yêu thích phong cách thời trang cao cấp.</p>
        <ul class="features">
            <li><i class="fa-solid fa-check"></i>Hơn 200+ mẫu áo sơ mi premium</li>
            <li><i class="fa-solid fa-check"></i>Miễn phí vận chuyển đơn từ 1 triệu</li>
            <li><i class="fa-solid fa-check"></i>Đổi trả dễ dàng trong 30 ngày</li>
            <li><i class="fa-solid fa-check"></i>Ưu đãi độc quyền cho thành viên</li>
        </ul>
    </div>
</div>

{{-- Cột phải: form đăng ký --}}
<div class="auth-form-wrap">
    <div class="auth-form-box">
        <h2>Tạo tài khoản</h2>
        <p class="subtitle">Điền thông tin để bắt đầu mua sắm.</p>

        <form method="POST" action="{{ route('register.store') }}">
            @csrf

            {{-- Họ và tên --}}
            <div class="mb-3">
                <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                <input type="text" name="name"
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name') }}" placeholder="Nguyễn Văn A" autofocus>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Email --}}
            <div class="mb-3">
                <label class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" name="email"
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}" placeholder="ten@example.com">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Số điện thoại --}}
            <div class="mb-3">
                <label class="form-label">Số điện thoại</label>
                <input type="tel" name="phone"
                       class="form-control @error('phone') is-invalid @enderror"
                       value="{{ old('phone') }}" placeholder="0912345678">
                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Mật khẩu --}}
            <div class="mb-3">
                <label class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="password" name="password" id="password"
                           class="form-control @error('password') is-invalid @enderror"
                           placeholder="Tối thiểu 8 ký tự" oninput="checkStrength(this.value)">
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password','eye1')">
                        <i class="fa-solid fa-eye" id="eye1"></i>
                    </button>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="password-strength mt-1" id="strengthBar"></div>
                <small class="text-muted" id="strengthText"></small>
            </div>

            {{-- Xác nhận mật khẩu --}}
            <div class="mb-3">
                <label class="form-label">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="password" name="password_confirmation" id="password_confirmation"
                           class="form-control"
                           placeholder="Nhập lại mật khẩu">
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirmation','eye2')">
                        <i class="fa-solid fa-eye" id="eye2"></i>
                    </button>
                </div>
            </div>

            {{-- Checkbox điều khoản --}}
            <div class="mb-4">
                <div class="form-check">
                    <input type="checkbox" name="terms" class="form-check-input @error('terms') is-invalid @enderror"
                           id="terms" {{ old('terms') ? 'checked' : '' }}>
                    <label class="form-check-label small" for="terms">
                        Tôi đồng ý với <a href="#" class="link-accent">Điều khoản dịch vụ</a>
                        và <a href="#" class="link-accent">Chính sách bảo mật</a>
                    </label>
                    @error('terms')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <button type="submit" class="btn-register">
                <i class="fa-solid fa-user-plus me-2"></i>Tạo tài khoản
            </button>
        </form>

        <p class="text-center mt-3 small text-muted">
            Đã có tài khoản? <a href="{{ route('login') }}" class="link-accent">Đăng nhập</a>
        </p>
        <p class="text-center mt-2">
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
    input.type  = input.type === 'password' ? 'text' : 'password';
    icon.classList.toggle('fa-eye'); icon.classList.toggle('fa-eye-slash');
}

function checkStrength(val) {
    const bar  = document.getElementById('strengthBar');
    const text = document.getElementById('strengthText');
    let strength = 0;
    if (val.length >= 8) strength++;
    if (/[A-Z]/.test(val)) strength++;
    if (/[0-9]/.test(val)) strength++;
    if (/[^A-Za-z0-9]/.test(val)) strength++;
    const levels = ['', '#ef4444', '#f97316', '#eab308', '#22c55e'];
    const labels = ['', 'Rất yếu', 'Yếu', 'Trung bình', 'Mạnh'];
    bar.style.background = levels[strength] || '#e0e0e0';
    bar.style.width = (strength * 25) + '%';
    text.textContent = labels[strength] || '';
    text.style.color = levels[strength] || '#aaa';
}
</script>
</body>
</html>
