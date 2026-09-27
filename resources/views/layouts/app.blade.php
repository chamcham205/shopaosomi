<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $siteSettings['site_name']) – Thời Trang Nam Cao Cấp</title>
    <script>(function(){const d='{{ $siteSettings['default_theme'] }}',v=localStorage.getItem('shirt-theme')||d,m=v==='system'?(matchMedia('(prefers-color-scheme:dark)').matches?'dark':'light'):v;document.documentElement.dataset.theme=m;document.documentElement.dataset.themeChoice=v})()</script>

    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/customer.css') }}">

    <style>
        :root {
            --primary:    #1a1a2e;
            --secondary:  #16213e;
            --accent:     #c8a96e;
            --accent-dark:#a0823e;
            --text-muted: #8a8a9a;
            --bg-light:   #f8f7f4;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            color: #2d2d2d;
        }

        /* Navbar */
        .navbar-brand {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--primary) !important;
            letter-spacing: 1px;
        }

        .navbar {
            background: #fff;
            border-bottom: 1px solid rgba(0,0,0,.08);
            padding: 1rem 0;
            z-index: 1040 !important;
        }

        .nav-link {
            font-size: .9rem;
            font-weight: 500;
            color: #444 !important;
            transition: color .2s;
        }

        .nav-link:hover { color: var(--accent) !important; }

        .btn-accent {
            background: var(--accent);
            color: #fff;
            border: none;
            font-weight: 600;
            letter-spacing: .5px;
            transition: background .2s, transform .1s;
        }

        .btn-accent:hover {
            background: var(--accent-dark);
            color: #fff;
            transform: translateY(-1px);
        }

        /* Toast / Flash alerts */
        .flash-toast {
            position: fixed;
            top: 1.2rem;
            right: 1.2rem;
            z-index: 9999;
            min-width: 300px;
        }
        [data-theme="dark"]{color-scheme:dark;--bg-light:#10141b;--primary:#090c12;--secondary:#171d27;--text-muted:#aeb5c0}
        [data-theme="dark"] body{background:#10141b;color:#e8eaed}
        [data-theme="dark"] .navbar,[data-theme="dark"] .dropdown-menu,[data-theme="dark"] .card,[data-theme="dark"] .filter-panel,[data-theme="dark"] .category-card,[data-theme="dark"] .product-card,[data-theme="dark"] .modal-content,[data-theme="dark"] .form-control,[data-theme="dark"] .form-select,[data-theme="dark"] .choice label{background-color:#171d27!important;color:#e8eaed!important;border-color:#343b46!important}
        [data-theme="dark"] .nav-link,[data-theme="dark"] .navbar-brand,[data-theme="dark"] .text-dark,[data-theme="dark"] .product-name,[data-theme="dark"] .section-title{color:#e8eaed!important}
        [data-theme="dark"] .text-muted{color:#aeb5c0!important}
        :focus-visible{outline:3px solid var(--accent);outline-offset:2px}
    </style>

    @stack('styles')
</head>
<body>
{{-- ── NAVBAR ──────────────────────────────────────────────── --}}
<nav class="navbar navbar-expand-lg sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">
            <i class="fa-solid fa-shirt me-2" style="color:var(--accent)"></i>{{ $siteSettings['site_name'] }}
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav mx-lg-auto align-items-lg-center gap-lg-2">
                <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">Trang chủ</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('products.index') }}">Cửa hàng</a></li>
            </ul>
            <form action="{{ route('products.index') }}" class="d-flex my-3 my-lg-0 me-lg-3" role="search">
                <input class="form-control form-control-sm rounded-start-pill" name="q" value="{{ request('q') }}" placeholder="Tìm sản phẩm..." aria-label="Tìm sản phẩm">
                <button class="btn btn-dark btn-sm rounded-end-pill px-3" aria-label="Tìm"><i class="fa-solid fa-magnifying-glass"></i></button>
            </form>
            <ul class="navbar-nav align-items-lg-center gap-2">
                <li class="nav-item dropdown"><button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-label="Chọn giao diện"><i class="fa-solid fa-circle-half-stroke"></i><span class="d-lg-none ms-2">Giao diện</span></button><ul class="dropdown-menu dropdown-menu-end"><li><button class="dropdown-item theme-choice" data-theme="light">Light</button></li><li><button class="dropdown-item theme-choice" data-theme="dark">Dark</button></li><li><button class="dropdown-item theme-choice" data-theme="system">System</button></li></ul></li>
                @auth
                @if(auth()->user()->isCustomer())
                <li class="nav-item"><a class="nav-link position-relative" href="{{ route('wishlist.index') }}" aria-label="Yêu thích"><i class="fa-regular fa-heart"></i><span class="badge rounded-pill bg-danger">{{ auth()->user()->wishlistItems()->count() }}</span></a></li>
                <li class="nav-item"><a class="nav-link position-relative" href="{{ route('cart.index') }}" aria-label="Giỏ hàng"><i class="fa-solid fa-bag-shopping"></i><span class="badge rounded-pill bg-danger">{{ auth()->user()->cart?->items()->sum('quantity') ?? 0 }}</span></a></li>
                @endif
                @endauth

                @auth
                    {{-- Đã đăng nhập --}}
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" data-bs-toggle="dropdown">
                            <span class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center"
                                  style="width:32px;height:32px;font-size:.8rem">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </span>
                            {{ auth()->user()->name }}
                            @if(auth()->user()->isAdmin())
                                <span class="badge ms-1" style="background:var(--accent);font-size:.65rem">
                                    {{ auth()->user()->role === 'super_admin' ? 'Super Admin' : 'Admin' }}
                                </span>
                            @endif
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                            @if(auth()->user()->isCustomer())
                                <li><a class="dropdown-item" href="{{ route('account.orders.index') }}"><i class="fa-solid fa-receipt me-2 text-muted"></i>Đơn hàng của tôi</a></li>
                                <li><a class="dropdown-item" href="{{ route('account.reviews.index') }}"><i class="fa-solid fa-star me-2 text-muted"></i>Đánh giá của tôi</a></li>
                                <li><a class="dropdown-item d-flex align-items-center justify-content-between" href="{{ route('account.chat.index') }}"><span><i class="fa-solid fa-comments me-2 text-muted"></i>Chat với Shop</span><span id="nav-chat-unread-badge" class="badge rounded-pill bg-danger d-none" style="font-size:0.65rem;">0</span></a></li>
                                <li><hr class="dropdown-divider"></li>
                            @endif
                            @if(auth()->user()->isAdmin())
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                                        <i class="fa-solid fa-gauge-high me-2 text-muted"></i>Quản trị
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                            @endif
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fa-solid fa-right-from-bracket me-2"></i>Đăng xuất
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    {{-- Chưa đăng nhập --}}
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">Đăng nhập</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-accent rounded-pill px-4" href="{{ route('register') }}">Đăng ký</a>
                    </li>
                @endauth

            </ul>
        </div>
    </div>
</nav>

{{-- ── FLASH MESSAGES ───────────────────────────────────────── --}}
<div class="flash-toast">
    @foreach(['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info'] as $type => $class)
        @if(session($type))
            <div class="alert alert-{{ $class }} alert-dismissible fade show shadow-sm" role="alert">
                <i class="fa-solid fa-{{ $type === 'success' ? 'circle-check' : ($type === 'error' ? 'circle-xmark' : ($type === 'warning' ? 'triangle-exclamation' : 'circle-info')) }} me-2"></i>
                {{ session($type) }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    @endforeach
</div>

{{-- ── MAIN CONTENT ─────────────────────────────────────────── --}}
<main>
    @yield('content')
</main>

{{-- ── FOOTER ──────────────────────────────────────────────── --}}
<footer class="py-5 mt-5" style="background:var(--primary);color:#aaa">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-5"><p class="mb-2" style="font-family:'Playfair Display',serif;color:var(--accent);font-size:1.35rem">{{ $siteSettings['site_name'] }}</p><p class="small mb-0">{{ $siteSettings['site_description'] }}</p></div>
            <div class="col-6 col-md-3"><h2 class="h6 text-white">Khám phá</h2><a class="footer-link d-block small mb-2" href="{{ route('products.index') }}">Tất cả sản phẩm</a><a class="footer-link d-block small" href="{{ route('products.index', ['sort'=>'discount']) }}">Đang giảm giá</a></div>
            <div class="col-6 col-md-4"><h2 class="h6 text-white">Liên hệ</h2>@if($siteSettings['contact_phone'])<p class="small mb-1">{{ $siteSettings['contact_phone'] }}</p>@endif @if($siteSettings['contact_email'])<p class="small mb-1">{{ $siteSettings['contact_email'] }}</p>@endif @if($siteSettings['address'])<p class="small mb-0">{{ $siteSettings['address'] }}</p>@endif</div>
        </div>
        <hr class="border-secondary my-4"><p class="small mb-0">© {{ date('Y') }} {{ $siteSettings['site_name'] }}.</p>
    </div>
</footer>

{{-- ── QUICK ADD TO CART MODAL ─────────────────────────────── --}}
<div class="modal fade" id="quickAddModal" tabindex="-1" aria-labelledby="quickAddModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="quickAddModalLabel">Thêm vào giỏ hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-4 col-sm-3">
                        <img id="qa-product-img" src="" alt="" class="quick-modal-thumb shadow-sm">
                    </div>
                    <div class="col-8 col-sm-9">
                        <h6 id="qa-product-name" class="fw-bold mb-1"></h6>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span id="qa-product-price" class="fw-bold price fs-5"></span>
                            <del id="qa-product-original-price" class="text-muted small"></del>
                            <span id="qa-product-discount" class="badge text-bg-danger" style="font-size:0.75rem"></span>
                        </div>
                        <a id="qa-product-link" href="#" class="small text-muted text-decoration-none">
                            <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Xem chi tiết sản phẩm
                        </a>
                    </div>
                </div>

                <hr class="my-3 text-muted opacity-25">

                {{-- Color Selector --}}
                <div class="mb-3">
                    <label class="form-label small fw-bold text-uppercase text-muted d-block mb-2">
                        Màu sắc: <span id="qa-selected-color-name" class="text-dark fw-semibold"></span>
                    </label>
                    <div id="qa-color-options" class="d-flex gap-2 flex-wrap align-items-center"></div>
                </div>

                {{-- Size Selector --}}
                <div class="mb-3">
                    <label class="form-label small fw-bold text-uppercase text-muted d-block mb-2">
                        Kích thước: <span id="qa-selected-size-name" class="text-dark fw-semibold"></span>
                    </label>
                    <div id="qa-size-options" class="d-flex gap-2 flex-wrap"></div>
                </div>

                {{-- Stock Status --}}
                <div id="qa-stock-alert" class="small mb-3 py-2 px-3 rounded-3 bg-light"></div>

                {{-- Quantity Selector --}}
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <span class="small fw-bold text-uppercase text-muted">Số lượng</span>
                    <div class="input-group input-group-sm" style="width: 120px;">
                        <button class="btn btn-outline-secondary" type="button" id="qa-qty-minus">-</button>
                        <input type="number" class="form-control text-center" id="qa-qty-input" value="1" min="1" max="99" readonly>
                        <button class="btn btn-outline-secondary" type="button" id="qa-qty-plus">+</button>
                    </div>
                </div>

                {{-- Action Buttons --}}
                @auth
                    @if(auth()->user()->isCustomer())
                    <div class="d-grid gap-2">
                        <button type="button" id="qa-btn-add-cart" class="btn btn-dark rounded-pill py-2 fw-semibold">
                            <i class="fa-solid fa-cart-plus me-2"></i>Thêm vào giỏ hàng
                        </button>
                        <button type="button" id="qa-btn-buy-now" class="btn btn-outline-dark rounded-pill py-2 fw-semibold">
                            <i class="fa-solid fa-bolt me-2"></i>Mua ngay
                        </button>
                    </div>
                    @else
                    <div class="alert alert-warning small mb-0">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i>Tài khoản Quản trị viên không thể mua hàng.
                    </div>
                    @endif
                @else
                <div class="d-grid gap-2">
                    <a href="{{ route('login') }}" class="btn btn-dark rounded-pill py-2 fw-semibold">
                        <i class="fa-solid fa-right-to-bracket me-2"></i>Đăng nhập để mua hàng
                    </a>
                </div>
                @endauth
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    const applyTheme=choice=>{localStorage.setItem('shirt-theme',choice);const resolved=choice==='system'?(matchMedia('(prefers-color-scheme:dark)').matches?'dark':'light'):choice;document.documentElement.dataset.theme=resolved;document.documentElement.dataset.themeChoice=choice};
    document.querySelectorAll('.theme-choice').forEach(button=>button.addEventListener('click',()=>applyTheme(button.dataset.theme)));
    matchMedia('(prefers-color-scheme:dark)').addEventListener('change',()=>{if((localStorage.getItem('shirt-theme')||'{{ $siteSettings['default_theme'] }}')==='system')applyTheme('system')});
    // Tự động ẩn flash message sau 4 giây
    document.querySelectorAll('.flash-toast .alert').forEach(el => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(el);
            bsAlert.close();
        }, 4000);
    });

    // ── QUICK ADD MODAL LOGIC ──────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        const quickModalEl = document.getElementById('quickAddModal');
        if (!quickModalEl) return;
        const quickModal = new bootstrap.Modal(quickModalEl);

        let currentProduct = null;
        let variants = [];
        let selectedColorId = null;
        let selectedSizeId = null;
        let activeVariant = null;

        function showToast(message, type = 'success') {
            let toastContainer = document.querySelector('.flash-toast');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.className = 'flash-toast';
                document.body.appendChild(toastContainer);
            }
            const icon = type === 'success' ? 'circle-check' : 'triangle-exclamation';
            const alertEl = document.createElement('div');
            alertEl.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show shadow-sm`;
            alertEl.role = 'alert';
            alertEl.innerHTML = `
                <i class="fa-solid fa-${icon} me-2"></i>
                <span>${message}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            toastContainer.appendChild(alertEl);
            setTimeout(() => {
                const bsAlert = bootstrap.Alert.getOrCreateInstance(alertEl);
                bsAlert.close();
            }, 4000);
        }

        function updateCartBadge(count) {
            const badge = document.querySelector('.navbar a[href*="cart"] .badge');
            if (badge) {
                badge.textContent = count;
                badge.classList.remove('d-none');
            }
        }

        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.btn-open-quick-add');
            if (!btn) return;
            e.preventDefault();
            e.stopPropagation();

            const dataset = btn.dataset;
            currentProduct = {
                id: dataset.productId,
                name: dataset.productName,
                price: dataset.productPrice,
                originalPrice: dataset.productOriginalPrice,
                discount: dataset.productDiscount,
                image: dataset.productImage,
                url: dataset.productUrl,
            };

            try {
                variants = JSON.parse(dataset.variants || '[]');
            } catch (err) {
                variants = [];
            }

            document.getElementById('qa-product-img').src = currentProduct.image;
            document.getElementById('qa-product-name').textContent = currentProduct.name;
            document.getElementById('qa-product-price').textContent = currentProduct.price;

            const delEl = document.getElementById('qa-product-original-price');
            if (currentProduct.originalPrice) {
                delEl.textContent = currentProduct.originalPrice;
                delEl.style.display = 'inline';
            } else {
                delEl.style.display = 'none';
            }

            const discEl = document.getElementById('qa-product-discount');
            if (currentProduct.discount) {
                discEl.textContent = currentProduct.discount;
                discEl.style.display = 'inline-block';
            } else {
                discEl.style.display = 'none';
            }

            document.getElementById('qa-product-link').href = currentProduct.url;

            const qtyInput = document.getElementById('qa-qty-input');
            if (qtyInput) qtyInput.value = 1;

            initVariants();
            quickModal.show();
        });

        function initVariants() {
            if (!variants.length) return;

            const colorMap = new Map();
            variants.forEach(v => {
                if (!colorMap.has(v.color_id)) {
                    colorMap.set(v.color_id, { id: v.color_id, name: v.color_name, hex: v.color_hex });
                }
            });
            const colors = Array.from(colorMap.values());

            const defaultVar = variants.find(v => v.stock > 0) || variants[0];
            selectedColorId = defaultVar ? defaultVar.color_id : (colors[0]?.id || null);

            renderColors(colors);
            updateSizesForSelectedColor(defaultVar ? defaultVar.size_id : null);
        }

        function renderColors(colors) {
            const container = document.getElementById('qa-color-options');
            container.innerHTML = '';
            colors.forEach(c => {
                const swatch = document.createElement('span');
                swatch.className = 'quick-modal-swatch' + (c.id === selectedColorId ? ' active' : '');
                swatch.style.backgroundColor = c.hex;
                swatch.title = c.name;
                swatch.dataset.colorId = c.id;
                swatch.dataset.colorName = c.name;
                swatch.addEventListener('click', () => {
                    selectedColorId = c.id;
                    container.querySelectorAll('.quick-modal-swatch').forEach(s => s.classList.remove('active'));
                    swatch.classList.add('active');
                    document.getElementById('qa-selected-color-name').textContent = c.name;
                    updateSizesForSelectedColor();
                });
                container.appendChild(swatch);
            });
            const activeColor = colors.find(c => c.id === selectedColorId);
            document.getElementById('qa-selected-color-name').textContent = activeColor ? activeColor.name : '';
        }

        function updateSizesForSelectedColor(preferredSizeId = null) {
            const container = document.getElementById('qa-size-options');
            container.innerHTML = '';

            const sizeMap = new Map();
            variants.forEach(v => {
                if (!sizeMap.has(v.size_id)) {
                    sizeMap.set(v.size_id, { id: v.size_id, name: v.size_name });
                }
            });
            const allSizes = Array.from(sizeMap.values());

            let firstInStockSizeId = null;
            allSizes.forEach(s => {
                const matchingVar = variants.find(v => v.color_id === selectedColorId && v.size_id === s.id);
                const inStock = matchingVar && matchingVar.stock > 0;
                if (inStock && !firstInStockSizeId) {
                    firstInStockSizeId = s.id;
                }
            });

            selectedSizeId = preferredSizeId || firstInStockSizeId || (allSizes[0]?.id || null);

            allSizes.forEach(s => {
                const matchingVar = variants.find(v => v.color_id === selectedColorId && v.size_id === s.id);
                const inStock = matchingVar && matchingVar.stock > 0;

                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'quick-modal-size-btn' + (s.id === selectedSizeId ? ' active' : '') + (!inStock ? ' disabled' : '');
                btn.textContent = s.name;
                btn.dataset.sizeId = s.id;
                btn.dataset.sizeName = s.name;
                if (!inStock) btn.disabled = true;

                btn.addEventListener('click', () => {
                    if (!inStock) return;
                    selectedSizeId = s.id;
                    container.querySelectorAll('.quick-modal-size-btn').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    document.getElementById('qa-selected-size-name').textContent = s.name;
                    updateStockAndControls();
                });
                container.appendChild(btn);
            });

            const activeSize = allSizes.find(s => s.id === selectedSizeId);
            document.getElementById('qa-selected-size-name').textContent = activeSize ? activeSize.name : '';
            updateStockAndControls();
        }

        function updateStockAndControls() {
            activeVariant = variants.find(v => v.color_id === selectedColorId && v.size_id === selectedSizeId);
            const alertEl = document.getElementById('qa-stock-alert');
            const addBtn = document.getElementById('qa-btn-add-cart');
            const buyBtn = document.getElementById('qa-btn-buy-now');
            const qtyInput = document.getElementById('qa-qty-input');

            if (!activeVariant || activeVariant.stock <= 0) {
                alertEl.className = 'small mb-3 py-2 px-3 rounded-3 text-danger bg-danger-subtle';
                alertEl.innerHTML = '<i class="fa-solid fa-circle-xmark me-1"></i> Biến thể này hiện đã hết hàng.';
                if (addBtn) addBtn.disabled = true;
                if (buyBtn) buyBtn.disabled = true;
            } else {
                alertEl.className = 'small mb-3 py-2 px-3 rounded-3 text-success bg-success-subtle';
                alertEl.innerHTML = `<i class="fa-solid fa-circle-check me-1"></i> Còn <strong>${activeVariant.stock}</strong> sản phẩm trong kho`;
                if (addBtn) addBtn.disabled = false;
                if (buyBtn) buyBtn.disabled = false;
                if (qtyInput && parseInt(qtyInput.value) > activeVariant.stock) {
                    qtyInput.value = Math.max(1, activeVariant.stock);
                }
            }
        }

        const minusBtn = document.getElementById('qa-qty-minus');
        const plusBtn = document.getElementById('qa-qty-plus');
        const qtyInput = document.getElementById('qa-qty-input');

        if (minusBtn && plusBtn && qtyInput) {
            minusBtn.addEventListener('click', () => {
                let val = parseInt(qtyInput.value) || 1;
                if (val > 1) qtyInput.value = val - 1;
            });
            plusBtn.addEventListener('click', () => {
                let val = parseInt(qtyInput.value) || 1;
                const max = activeVariant ? activeVariant.stock : 99;
                if (val < max) qtyInput.value = val + 1;
            });
        }

        async function submitCart(buyNow = false) {
            if (!activeVariant || activeVariant.stock <= 0) return;
            const qty = parseInt(document.getElementById('qa-qty-input').value) || 1;
            const addBtn = document.getElementById('qa-btn-add-cart');
            const buyBtn = document.getElementById('qa-btn-buy-now');

            if (addBtn) addBtn.disabled = true;
            if (buyBtn) buyBtn.disabled = true;

            const originalAddHtml = addBtn ? addBtn.innerHTML : '';
            if (buyNow && buyBtn) {
                buyBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Đang xử lý...';
            } else if (addBtn) {
                addBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Đang thêm...';
            }

            try {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
                const response = await fetch('{{ route('cart.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        variant_id: activeVariant.id,
                        quantity: qty,
                        buy_now: buyNow ? 1 : 0
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    if (data.redirect_url) {
                        window.location.href = data.redirect_url;
                        return;
                    }
                    updateCartBadge(data.cart_count);
                    quickModal.hide();
                    showToast(data.message || 'Đã thêm vào giỏ hàng thành công!', 'success');
                } else {
                    showToast(data.message || 'Có lỗi xảy ra, vui lòng thử lại.', 'error');
                }
            } catch (err) {
                showToast('Không thể kết nối đến máy chủ.', 'error');
            } finally {
                if (addBtn) {
                    addBtn.disabled = false;
                    addBtn.innerHTML = originalAddHtml;
                }
                if (buyBtn) {
                    buyBtn.disabled = false;
                    buyBtn.innerHTML = '<i class="fa-solid fa-bolt me-2"></i>Mua ngay';
                }
            }
        }

        const btnAdd = document.getElementById('qa-btn-add-cart');
        const btnBuy = document.getElementById('qa-btn-buy-now');
        if (btnAdd) btnAdd.addEventListener('click', () => submitCart(false));
        if (btnBuy) btnBuy.addEventListener('click', () => submitCart(true));
    });
</script>

@auth
    @if(auth()->user()->isCustomer() && !request()->routeIs('account.chat.*'))
        {{-- NÚT CHAT NỔI VỚI SHOP DÀNH CHO KHÁCH HÀNG --}}
        <a href="{{ route('account.chat.index') }}"
           id="customer-floating-chat-btn"
           class="position-fixed bottom-0 end-0 m-4 btn btn-dark rounded-pill shadow-lg d-flex align-items-center gap-2 px-3 py-2.5"
           style="z-index: 1040; transition: transform 0.2s, box-shadow 0.2s; border: 1px solid rgba(255,255,255,0.15);"
           title="Chat hỗ trợ trực tuyến với Shop">
            <div class="position-relative d-flex align-items-center justify-content-center">
                <i class="fa-solid fa-comments fs-5" style="color: var(--accent);"></i>
                <span id="floating-chat-unread-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none" style="font-size: 0.65rem;">
                    0
                </span>
            </div>
            <span class="d-none d-sm-inline fw-semibold small">Chat với Shop</span>
        </a>
    @endif

    @if(auth()->user()->isCustomer())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                async function checkCustomerUnreadChat() {
                    try {
                        const res = await fetch('{{ route('account.chat.unread') }}', {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        const data = await res.json();
                        if (data.success) {
                            const navBadge = document.getElementById('nav-chat-unread-badge');
                            const floatBadge = document.getElementById('floating-chat-unread-badge');
                            if (data.unread > 0) {
                                const text = data.unread > 99 ? '99+' : data.unread;
                                if (navBadge) {
                                    navBadge.textContent = text;
                                    navBadge.classList.remove('d-none');
                                }
                                if (floatBadge) {
                                    floatBadge.textContent = text;
                                    floatBadge.classList.remove('d-none');
                                }
                            } else {
                                if (navBadge) navBadge.classList.add('d-none');
                                if (floatBadge) floatBadge.classList.add('d-none');
                            }
                        }
                    } catch (e) {}
                }
                checkCustomerUnreadChat();
                setInterval(checkCustomerUnreadChat, 15000);
            });
        </script>
    @endif
@endauth

@stack('scripts')
</body>
</html>
