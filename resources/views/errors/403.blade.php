<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 – Không có quyền truy cập</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f8f7f4; display: flex; align-items: center; justify-content: center; min-height: 100vh; text-align: center; }
    </style>
</head>
<body>
<div>
    <div style="font-size:6rem;color:#e0cfa0">403</div>
    <h2 style="font-family:'Playfair Display',serif;color:#1a1a2e">Không có quyền truy cập</h2>
    <p class="text-muted">{{ $exception->getMessage() ?: 'Bạn không có quyền truy cập trang này.' }}</p>
    <a href="{{ url('/') }}" class="btn btn-dark mt-3 rounded-pill px-4">
        ← Về trang chủ
    </a>
</div>
</body>
</html>
