<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu – Shirt Store</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body { margin: 0; min-height: 100vh; display: flex; font-family: 'Inter', sans-serif; background: #f8f7f4; }
        .auth-hero { width: 45%; background: linear-gradient(135deg, #1a1a2e, #16213e); display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 3rem; color: #fff; position: relative; overflow: hidden; }
        .auth-hero::before { content: ''; position: absolute; inset: 0; background: url('https://images.unsplash.com/photo-1603252109303-2751441dd157?w=800&q=80') center/cover; opacity: .12; }
        .auth-hero-content { position: relative; text-align: center; }
        .auth-hero h1 { font-family: 'Playfair Display', serif; font-size: 2.4rem; color: #c8a96e; letter-spacing: 2px; }
        .auth-form-wrap { flex: 1; display: flex; align-items: center; justify-content: center; padding: 2rem; }
        .auth-form-box { width: 100%; max-width: 420px; }
        .auth-form-box h2 { font-family: 'Playfair Display', serif; font-size: 1.7rem; color: #1a1a2e; margin-bottom: .3rem; }
        .form-label { font-size: .85rem; font-weight: 600; color: #555; margin-bottom: .3rem; }
        .form-control { border: 1.5px solid #e0e0e0; border-radius: 8px; padding: .7rem 1rem; font-size: .92rem; transition: border-color .2s; }
        .form-control:focus { border-color: #c8a96e; box-shadow: 0 0 0 3px rgba(200,169,110,.15); }
        .input-group .form-control { border-right: none; }
        .input-group .btn-outline-secondary { border: 1.5px solid #e0e0e0; border-left: none; border-radius: 0 8px 8px 0; background: transparent; color: #888; }
        .btn-submit { background: linear-gradient(135deg, #c8a96e, #a0823e); color: #fff; border: none; border-radius: 8px; padding: .75rem; font-size: .95rem; font-weight: 600; width: 100%; transition: opacity .2s; }
        .btn-submit:hover { opacity: .9; color: #fff; }
        @media (max-width: 768px) { body { flex-direction: column; } .auth-hero { width: 100%; min-height: 160px; } }
    </style>
</head>
<body>

<div class="auth-hero">
    <div class="auth-hero-content">
        <p style="letter-spacing:3px;font-size:.8rem;opacity:.55;text-transform:uppercase">Bảo mật tài khoản</p>
        <h1>SHIRT STORE</h1>
        <p style="opacity:.75;margin-top:1rem;line-height:1.7">Tạo mật khẩu mới mạnh mẽ<br>để bảo vệ tài khoản của bạn.</p>
        <div style="margin-top:2rem;opacity:.6">
            <i class="fa-solid fa-shield-halved fa-3x"></i>
        </div>
    </div>
</div>

<div class="auth-form-wrap">
    <div class="auth-form-box">
        <h2>Đặt lại mật khẩu</h2>
        <p style="color:#888;font-size:.9rem;margin-bottom:1.8rem">
            Nhập mật khẩu mới cho tài khoản của bạn.
        </p>

        @if($errors->any())
            <div class="alert alert-danger rounded-3 small">
                <i class="fa-solid fa-circle-xmark me-2"></i>{{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf

            {{-- Token ẩn (từ URL) --}}
            <input type="hidden" name="token" value="{{ $token }}">

            {{-- Email --}}
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email"
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email', $email) }}" readonly
                       style="background:#f5f5f5">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Mật khẩu mới --}}
            <div class="mb-3">
                <label class="form-label">Mật khẩu mới <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="password" name="password" id="newPassword"
                           class="form-control @error('password') is-invalid @enderror"
                           placeholder="Tối thiểu 8 ký tự">
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('newPassword','eye1')">
                        <i class="fa-solid fa-eye" id="eye1"></i>
                    </button>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- Xác nhận mật khẩu --}}
            <div class="mb-4">
                <label class="form-label">Xác nhận mật khẩu mới <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="password" name="password_confirmation" id="confirmPassword"
                           class="form-control"
                           placeholder="Nhập lại mật khẩu mới">
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirmPassword','eye2')">
                        <i class="fa-solid fa-eye" id="eye2"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fa-solid fa-lock me-2"></i>Cập nhật mật khẩu
            </button>
        </form>

        <p class="text-center mt-3 small text-muted">
            <a href="{{ route('login') }}" class="text-muted">
                <i class="fa-solid fa-arrow-left me-1"></i>Quay lại đăng nhập
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
</script>
</body>
</html>
