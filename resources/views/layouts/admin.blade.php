<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') – {{ $siteSettings['site_name'] }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --sidebar-w:240px; --accent:#c8a96e; --dark:#1a1a2e; }
        body { font-family:'Inter',sans-serif; background:#f4f5f7; margin:0; }
        /* Sidebar */
        .admin-sidebar { position:fixed; top:0; left:0; width:var(--sidebar-w); height:100vh; background:var(--dark); z-index:1000; overflow-y:auto; overflow-x:hidden; display:flex; flex-direction:column; scroll-behavior:smooth; }
        .admin-sidebar::-webkit-scrollbar { width:4px; }
        .admin-sidebar::-webkit-scrollbar-track { background:transparent; }
        .admin-sidebar::-webkit-scrollbar-thumb { background:rgba(255,255,255,.15); border-radius:4px; }
        .admin-sidebar::-webkit-scrollbar-thumb:hover { background:var(--accent); }
        .sidebar-brand { padding:1.25rem 1.5rem; border-bottom:1px solid rgba(255,255,255,.08); }
        .sidebar-brand a { color:var(--accent); font-weight:700; font-size:1.1rem; text-decoration:none; letter-spacing:1px; }
        .sidebar-brand small { display:block; color:rgba(255,255,255,.4); font-size:.7rem; margin-top:2px; }
        .sidebar-nav { flex:1; padding:.75rem 0; }
        .nav-section { font-size:.65rem; font-weight:700; color:rgba(255,255,255,.3); letter-spacing:1.5px; text-transform:uppercase; padding:.75rem 1.5rem .25rem; }
        .sidebar-link { display:flex; align-items:center; gap:.75rem; padding:.65rem 1.5rem; color:rgba(255,255,255,.65); text-decoration:none; font-size:.875rem; transition:all .15s; }
        .sidebar-link:hover, .sidebar-link.active { background:rgba(200,169,110,.12); color:var(--accent); }
        .sidebar-link i { width:18px; text-align:center; font-size:.9rem; }
        .sidebar-footer { padding:1rem 1.5rem; border-top:1px solid rgba(255,255,255,.08); }
        /* Main */
        .admin-main { margin-left:var(--sidebar-w); min-height:100vh; display:flex; flex-direction:column; }
        .admin-topbar { background:#fff; border-bottom:1px solid #e5e7eb; padding:.75rem 1.5rem; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:100; }
        .admin-content { padding:1.5rem; flex:1; }
        /* Cards */
        .card { border:none; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,.06); }
        .card-header { background:#fff; border-bottom:1px solid #f0f0f0; border-radius:12px 12px 0 0 !important; padding:.875rem 1.25rem; font-weight:600; }
        /* Badges */
        .badge-active { background:#d1fae5; color:#065f46; }
        .badge-inactive { background:#f3f4f6; color:#6b7280; }
        .badge-draft { background:#fef3c7; color:#92400e; }
        .badge-out { background:#fee2e2; color:#991b1b; }
        .badge-low { background:#fef3c7; color:#92400e; }
        .badge-ok { background:#d1fae5; color:#065f46; }
        /* Alert */
        .alert { border-radius:8px; border:none; }
        /* Table */
        .table th { font-size:.75rem; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.5px; }
        .table td { vertical-align:middle; }
        /* Buttons */
        .btn-accent { background:var(--accent); color:#fff; border:none; }
        .btn-accent:hover { background:#a0823e; color:#fff; }
        /* Form */
        .form-label { font-size:.85rem; font-weight:600; color:#374151; }
        .form-control, .form-select { border:1.5px solid #e5e7eb; border-radius:8px; }
        .form-control:focus, .form-select:focus { border-color:var(--accent); box-shadow:0 0 0 3px rgba(200,169,110,.15); }
        :focus-visible{outline:3px solid var(--accent);outline-offset:2px}
        @media (max-width:768px) { .admin-sidebar { transform:translateX(-100%);transition:transform .2s; } .admin-sidebar.open{transform:translateX(0)} .admin-main { margin-left:0; } }
    </style>
    @stack('styles')
</head>
<body>

{{-- ─── SIDEBAR ─── --}}
<div class="admin-sidebar">
    <div class="sidebar-brand">
        <a href="{{ route('admin.dashboard') }}">{{ $siteSettings['site_name'] }}</a>
        <small>Quản trị hệ thống</small>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section">Tổng quan</div>
        <a class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
           href="{{ route('admin.dashboard') }}">
            <i class="fa-solid fa-gauge-high"></i> Bảng điều khiển
        </a>

        <div class="nav-section">Sản phẩm</div>
        <a class="sidebar-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}"
           href="{{ route('admin.products.index') }}">
            <i class="fa-solid fa-shirt"></i> Sản phẩm
        </a>
        <a class="sidebar-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}"
           href="{{ route('admin.categories.index') }}">
            <i class="fa-solid fa-layer-group"></i> Danh mục
        </a>
        <a class="sidebar-link {{ request()->routeIs('admin.colors.*') ? 'active' : '' }}"
           href="{{ route('admin.colors.index') }}">
            <i class="fa-solid fa-palette"></i> Màu sắc
        </a>
        <a class="sidebar-link {{ request()->routeIs('admin.sizes.*') ? 'active' : '' }}"
           href="{{ route('admin.sizes.index') }}">
            <i class="fa-solid fa-ruler"></i> Kích cỡ
        </a>

        <div class="nav-section">Kho hàng</div>
        <a class="sidebar-link {{ request()->routeIs('admin.inventory.*') ? 'active' : '' }}"
           href="{{ route('admin.inventory.index') }}">
            <i class="fa-solid fa-boxes-stacked"></i> Tồn kho
        </a>

        <div class="nav-section">Vận hành</div>
        <a class="sidebar-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}"><i class="fa-solid fa-receipt"></i> Đơn hàng</a>
        <a class="sidebar-link {{ request()->routeIs('admin.finance.index') ? 'active' : '' }}" href="{{ route('admin.finance.index') }}"><i class="fa-solid fa-chart-pie"></i> Thống kê tài chính</a>
        <a class="sidebar-link {{ request()->routeIs('admin.finance.transactions') ? 'active' : '' }}" href="{{ route('admin.finance.transactions') }}"><i class="fa-solid fa-money-bill-transfer"></i> Giao dịch thanh toán</a>
        <a class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}"><i class="fa-solid fa-user-gear"></i> Người dùng</a>
        <a class="sidebar-link {{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}" href="{{ route('admin.coupons.index') }}"><i class="fa-solid fa-ticket"></i> Mã giảm giá</a>
        <a class="sidebar-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" href="{{ route('admin.reports.index') }}"><i class="fa-solid fa-chart-column"></i> Báo cáo</a>
        <a class="sidebar-link {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}" href="{{ route('admin.reviews.index') }}"><i class="fa-solid fa-star"></i> Đánh giá</a>
        <a class="sidebar-link {{ request()->routeIs('admin.banned-words.*') ? 'active' : '' }}" href="{{ route('admin.banned-words.index') }}"><i class="fa-solid fa-ban"></i> Từ cấm</a>
        <a class="sidebar-link {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}" href="{{ route('admin.banners.index') }}"><i class="fa-solid fa-images"></i> Banner quảng cáo</a>
        <a class="sidebar-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.index') }}"><i class="fa-solid fa-gear"></i> Cài đặt</a>
    </nav>

    <div class="sidebar-footer">
        <div class="d-flex align-items-center gap-2 mb-2">
            <div class="rounded-circle bg-warning d-flex align-items-center justify-content-center"
                 style="width:32px;height:32px;font-size:.75rem;font-weight:700;color:#1a1a2e">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div>
                <div style="color:#fff;font-size:.8rem;font-weight:600">{{ auth()->user()->name }}</div>
                <div style="color:rgba(255,255,255,.4);font-size:.7rem">{{ auth()->user()->role === 'admin' ? 'Quản trị viên' : auth()->user()->role }}</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="sidebar-link w-100 border-0" style="background:transparent;cursor:pointer">
                <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
            </button>
        </form>
    </div>
</div>
<script>
    (function() {
        try {
            const sb = document.querySelector('.admin-sidebar');
            const pos = sessionStorage.getItem('admin_sidebar_scroll');
            if (sb && pos !== null) {
                sb.scrollTop = parseInt(pos, 10);
            }
        } catch(e) {}
    })();
</script>

{{-- ─── MAIN ─── --}}
<div class="admin-main">
    <div class="admin-topbar">
        <div class="d-flex align-items-center gap-2"><button id="adminMenu" class="btn btn-sm btn-outline-secondary d-md-none" type="button" aria-label="Mở menu quản trị"><i class="fa-solid fa-bars"></i></button><h6 class="mb-0 fw-600">@yield('page-title', 'Bảng điều khiển')</h6></div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('home') }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Xem Shop
            </a>
        </div>
    </div>

    <div class="admin-content">
        {{-- Flash messages --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible mb-3" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible mb-3" role="alert">
                <i class="fa-solid fa-circle-xmark me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if($errors->any())<div class="alert alert-danger" role="alert"><strong>Vui lòng kiểm tra dữ liệu:</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        @yield('content')
    </div>
</div>

{{-- ── NÚT VÀ KHUNG CHAT NỔI CHO ADMIN (GÓC DƯỚI BÊN PHẢI) ── --}}
<div id="admin-chat-container">
    {{-- Nút tròn nổi mở Chat --}}
    <button type="button"
            id="admin-chat-toggle-btn"
            class="position-fixed d-flex align-items-center justify-content-center shadow-lg border-0"
            style="bottom: 24px; right: 24px; width: 56px; height: 56px; border-radius: 50%; background: #1a1a2e; color: #c8a96e; z-index: 1060; cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;"
            onmouseover="this.style.transform='scale(1.08)'"
            onmouseout="this.style.transform='scale(1)'"
            title="Tin nhắn & Hỗ trợ khách hàng">
        <i class="fa-solid fa-comments fs-4"></i>
        <span id="admin-chat-unread-badge"
              class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-2 border-white d-none"
              style="font-size: 0.72rem; min-width: 22px;">
            0
        </span>
    </button>

    {{-- Khung Chat Popup --}}
    <div id="admin-chat-widget"
         class="position-fixed shadow-lg rounded-4 overflow-hidden d-none flex-column border"
         style="bottom: 92px; right: 24px; width: 400px; height: 560px; max-height: calc(100vh - 120px); background: #ffffff; z-index: 1060;">

        {{-- VIEW 1: DANH SÁCH HỘI THOẠI KHÁCH HÀNG --}}
        <div id="admin-chat-list-view" class="d-flex flex-column h-100">
            {{-- Header Danh sách --}}
            <div class="p-3 text-white d-flex align-items-center justify-content-between" style="background: #1a1a2e;">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-headset fs-5" style="color: #c8a96e;"></i>
                    <div>
                        <h6 class="mb-0 fw-bold text-white">Tin nhắn khách hàng</h6>
                        <small class="text-white-50" style="font-size: 0.72rem;">Trực tiếp từ cửa hàng</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-1">
                    <button type="button" class="btn btn-sm btn-link text-white-50 p-1 text-decoration-none" id="btn-admin-refresh-convs" title="Làm mới danh sách">
                        <i class="fa-solid fa-rotate-right"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-link text-white-50 p-1 text-decoration-none" id="btn-admin-close-chat" title="Thu nhỏ chat">
                        <i class="fa-solid fa-xmark fs-6"></i>
                    </button>
                </div>
            </div>

            {{-- Ô tìm kiếm khách hàng --}}
            <div class="p-2 border-bottom bg-light">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input type="text" id="admin-chat-search" class="form-control border-start-0 ps-0" placeholder="Tìm theo tên, email, SĐT...">
                </div>
            </div>

            {{-- Danh sách cuộc trò chuyện --}}
            <div class="flex-grow-1 overflow-y-auto" id="admin-chat-convs-list" style="background: #ffffff;">
                <div class="text-center py-5 text-muted" id="admin-convs-loading">
                    <div class="spinner-border spinner-border-sm text-secondary mb-2" role="status"></div>
                    <div class="small">Đang tải danh sách tin nhắn...</div>
                </div>
            </div>
        </div>

        {{-- VIEW 2: CHI TIẾT TRÒ CHUYỆN VỚI 1 KHÁCH HÀNG --}}
        <div id="admin-chat-detail-view" class="d-none flex-column h-100">
            {{-- Header Chi tiết --}}
            <div class="p-2.5 px-3 text-white d-flex align-items-center justify-content-between" style="background: #1a1a2e;">
                <div class="d-flex align-items-center gap-2 overflow-hidden">
                    <button type="button" class="btn btn-sm btn-link text-white p-0 me-1 text-decoration-none" id="btn-admin-back-to-list" title="Quay lại danh sách">
                        <i class="fa-solid fa-arrow-left fs-6"></i>
                    </button>
                    <div class="rounded-circle bg-warning text-dark fw-bold d-flex align-items-center justify-content-center flex-shrink-0"
                         id="admin-chat-active-avatar"
                         style="width: 34px; height: 34px; font-size: 0.85rem;">
                        K
                    </div>
                    <div class="text-truncate">
                        <div class="fw-bold small mb-0 text-white text-truncate" id="admin-chat-active-name">Khách hàng</div>
                        <small class="text-white-50 d-block text-truncate" id="admin-chat-active-phone" style="font-size: 0.7rem;">0987654321</small>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-link text-white-50 p-1 text-decoration-none flex-shrink-0" id="btn-admin-close-chat-2" title="Thu nhỏ">
                    <i class="fa-solid fa-xmark fs-6"></i>
                </button>
            </div>

            {{-- Khung tin nhắn cuộn --}}
            <div class="flex-grow-1 overflow-y-auto p-3 d-flex flex-column gap-2" id="admin-chat-messages-box" style="background: #f8fafc;">
                <!-- Tin nhắn hiển thị ở đây -->
            </div>

            {{-- Ô nhập trả lời --}}
            <div class="p-2.5 bg-white border-top">
                <form id="admin-chat-reply-form" class="d-flex align-items-center gap-2">
                    <input type="text"
                           id="admin-chat-reply-input"
                           class="form-control form-control-sm rounded-pill px-3"
                           placeholder="Nhập câu trả lời cho khách..."
                           autocomplete="off"
                           maxlength="2000"
                           required>
                    <button type="submit" class="btn btn-sm rounded-circle text-white d-flex align-items-center justify-content-center flex-shrink-0"
                            id="btn-admin-send-reply"
                            style="width: 34px; height: 34px; background: #1a1a2e;"
                            title="Gửi tin nhắn">
                        <i class="fa-solid fa-paper-plane" style="font-size: 0.8rem; color: #c8a96e;"></i>
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>

<style>
    .admin-conv-item {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #f1f5f9;
        cursor: pointer;
        transition: background 0.15s ease;
    }
    .admin-conv-item:hover {
        background: #f8fafc;
    }
    .admin-conv-item.unread-active {
        background: #fffbeb;
    }
    .admin-chat-bubble {
        max-width: 80%;
        padding: 0.65rem 0.9rem;
        border-radius: 12px;
        font-size: 0.875rem;
        line-height: 1.4;
        word-break: break-word;
    }
    .admin-chat-bubble.mine {
        align-self: flex-end;
        background: #1a1a2e;
        color: #ffffff;
        border-bottom-right-radius: 2px;
    }
    .admin-chat-bubble.customer {
        align-self: flex-start;
        background: #ffffff;
        color: #1e293b;
        border: 1px solid #e2e8f0;
        border-bottom-left-radius: 2px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    }
    .admin-chat-bubble-meta {
        font-size: 0.68rem;
        margin-top: 3px;
        opacity: 0.75;
    }
    .admin-chat-bubble.mine .admin-chat-bubble-meta {
        color: rgba(255, 255, 255, 0.7);
        text-align: right;
    }
    .admin-chat-bubble.customer .admin-chat-bubble-meta {
        color: #64748b;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggleBtn = document.getElementById('admin-chat-toggle-btn');
    const chatWidget = document.getElementById('admin-chat-widget');
    const unreadBadge = document.getElementById('admin-chat-unread-badge');
    const listView = document.getElementById('admin-chat-list-view');
    const detailView = document.getElementById('admin-chat-detail-view');
    const convsList = document.getElementById('admin-chat-convs-list');
    const searchInput = document.getElementById('admin-chat-search');
    const btnRefreshConvs = document.getElementById('btn-admin-refresh-convs');
    const btnClose1 = document.getElementById('btn-admin-close-chat');
    const btnClose2 = document.getElementById('btn-admin-close-chat-2');
    const btnBackToList = document.getElementById('btn-admin-back-to-list');

    const activeName = document.getElementById('admin-chat-active-name');
    const activePhone = document.getElementById('admin-chat-active-phone');
    const activeAvatar = document.getElementById('admin-chat-active-avatar');
    const messagesBox = document.getElementById('admin-chat-messages-box');
    const replyForm = document.getElementById('admin-chat-reply-form');
    const replyInput = document.getElementById('admin-chat-reply-input');
    const btnSendReply = document.getElementById('btn-admin-send-reply');

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    let isWidgetOpen = false;
    let currentConversationId = null;
    let detailPollingInterval = null;
    let renderedMessageIds = new Set();
    let currentSearchTerm = '';

    // Mở / đóng widget chat
    function toggleChatWidget(open = null) {
        if (open === null) {
            isWidgetOpen = !isWidgetOpen;
        } else {
            isWidgetOpen = open;
        }

        if (isWidgetOpen) {
            chatWidget.classList.remove('d-none');
            chatWidget.classList.add('d-flex');
            if (currentConversationId) {
                showDetailView(currentConversationId);
            } else {
                showListView();
            }
        } else {
            chatWidget.classList.remove('d-flex');
            chatWidget.classList.add('d-none');
            clearInterval(detailPollingInterval);
        }
    }

    if (toggleBtn) toggleBtn.addEventListener('click', () => toggleChatWidget());
    if (btnClose1) btnClose1.addEventListener('click', () => toggleChatWidget(false));
    if (btnClose2) btnClose2.addEventListener('click', () => toggleChatWidget(false));

    function showListView() {
        clearInterval(detailPollingInterval);
        currentConversationId = null;
        listView.classList.remove('d-none');
        listView.classList.add('d-flex');
        detailView.classList.remove('d-flex');
        detailView.classList.add('d-none');
        loadConversations();
    }

    if (btnBackToList) {
        btnBackToList.addEventListener('click', () => {
            showListView();
            checkGlobalUnread();
        });
    }

    // Tải danh sách hội thoại
    async function loadConversations() {
        try {
            const url = `{{ route('admin.chat.conversations') }}?q=${encodeURIComponent(currentSearchTerm)}`;
            const res = await fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();

            if (data.success && Array.isArray(data.conversations)) {
                if (data.conversations.length === 0) {
                    convsList.innerHTML = `
                        <div class="text-center py-5 text-muted">
                            <i class="fa-regular fa-message fs-2 mb-2 opacity-50"></i>
                            <div class="small fw-semibold">Chưa có cuộc trò chuyện nào</div>
                            <small class="text-muted">Khi khách hàng gửi tin nhắn sẽ xuất hiện tại đây.</small>
                        </div>
                    `;
                    return;
                }

                convsList.innerHTML = '';
                data.conversations.forEach(conv => {
                    const item = document.createElement('div');
                    item.className = `admin-conv-item d-flex align-items-center gap-2.5 ${conv.unread_count > 0 ? 'unread-active' : ''}`;
                    item.dataset.id = conv.id;

                    const safeName = document.createElement('div');
                    safeName.textContent = conv.customer_name;
                    const safeMsg = document.createElement('div');
                    safeMsg.textContent = conv.last_message;

                    item.innerHTML = `
                        <div class="rounded-circle bg-dark text-warning fw-bold d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width: 40px; height: 40px; font-size: 0.95rem;">
                            ${conv.customer_initial}
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="fw-bold small text-dark text-truncate">${safeName.innerHTML}</span>
                                <small class="text-muted" style="font-size: 0.68rem;">${conv.last_time || ''}</small>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="small text-truncate ${conv.unread_count > 0 ? 'fw-bold text-dark' : 'text-muted'}" style="max-width: 220px; font-size: 0.8rem;">
                                    ${conv.last_message_is_mine ? '<span class="text-secondary me-1">Bạn:</span>' : ''}${safeMsg.innerHTML}
                                </span>
                                ${conv.unread_count > 0 ? `<span class="badge rounded-pill bg-danger" style="font-size: 0.65rem;">${conv.unread_count}</span>` : ''}
                            </div>
                        </div>
                    `;

                    item.addEventListener('click', () => openConversation(conv));
                    convsList.appendChild(item);
                });
            }
        } catch (e) {
            console.warn('Lỗi khi tải danh sách hội thoại admin:', e);
        }
    }

    if (btnRefreshConvs) {
        btnRefreshConvs.addEventListener('click', function () {
            this.classList.add('fa-spin');
            loadConversations().finally(() => {
                setTimeout(() => this.classList.remove('fa-spin'), 500);
            });
        });
    }

    if (searchInput) {
        let debounceTimeout = null;
        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimeout);
            currentSearchTerm = this.value.trim();
            debounceTimeout = setTimeout(loadConversations, 350);
        });
    }

    // Mở một hội thoại cụ thể
    function openConversation(conv) {
        currentConversationId = conv.id;
        renderedMessageIds.clear();
        messagesBox.innerHTML = `
            <div class="text-center py-5 text-muted">
                <div class="spinner-border spinner-border-sm text-secondary mb-2" role="status"></div>
                <div class="small">Đang tải tin nhắn...</div>
            </div>
        `;

        activeName.textContent = conv.customer_name;
        activePhone.textContent = conv.customer_phone || conv.customer_email || 'Khách hàng';
        activeAvatar.textContent = conv.customer_initial;

        listView.classList.remove('d-flex');
        listView.classList.add('d-none');
        detailView.classList.remove('d-none');
        detailView.classList.add('d-flex');

        loadMessagesDetail(true);
        clearInterval(detailPollingInterval);
        detailPollingInterval = setInterval(() => {
            if (isWidgetOpen && currentConversationId) {
                loadMessagesDetail(false);
            }
        }, 3000);
    }

    function scrollDetailToBottom(smooth = true) {
        if (!messagesBox) return;
        messagesBox.scrollTo({
            top: messagesBox.scrollHeight,
            behavior: smooth ? 'smooth' : 'auto'
        });
    }

    function renderAdminBubble(msg) {
        const bubble = document.createElement('div');
        bubble.className = `admin-chat-bubble ${msg.is_mine ? 'mine' : 'customer'}`;
        bubble.dataset.id = msg.id;

        const senderLabel = msg.is_mine ? '' : `<div class="fw-bold small mb-1 text-primary" style="font-size:0.75rem;">${msg.sender_name}</div>`;
        const textContent = document.createElement('div');
        textContent.textContent = msg.message;

        bubble.innerHTML = `
            ${senderLabel}
            <div>${textContent.innerHTML}</div>
            <div class="admin-chat-bubble-meta">
                <span>${msg.created_time}</span>
                ${msg.is_mine ? '<i class="fa-solid fa-check ms-1" style="font-size:0.6rem;"></i>' : ''}
            </div>
        `;
        return bubble;
    }

    // Tải tin nhắn của cuộc hội thoại đang mở
    async function loadMessagesDetail(initial = false) {
        if (!currentConversationId) return;

        try {
            const url = `/admin/chat/conversations/${currentConversationId}/messages`;
            const res = await fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();

            if (data.success && Array.isArray(data.messages)) {
                if (initial) messagesBox.innerHTML = '';

                let hasNew = false;
                data.messages.forEach(msg => {
                    if (!renderedMessageIds.has(msg.id)) {
                        renderedMessageIds.add(msg.id);
                        messagesBox.appendChild(renderAdminBubble(msg));
                        hasNew = true;
                    }
                });

                if (hasNew || initial) {
                    scrollDetailToBottom(!initial);
                }
            }
        } catch (e) {
            console.warn('Lỗi khi tải tin nhắn hội thoại:', e);
        }
    }

    // Gửi phản hồi từ admin
    if (replyForm) {
        replyForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const text = replyInput.value.trim();
            if (!text || !currentConversationId) return;

            replyInput.value = '';
            if (btnSendReply) {
                btnSendReply.disabled = true;
                btnSendReply.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" style="width:12px;height:12px;"></span>';
            }

            try {
                const url = `/admin/chat/conversations/${currentConversationId}/reply`;
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ message: text })
                });

                const data = await res.json();
                if (data.success && data.message) {
                    if (!renderedMessageIds.has(data.message.id)) {
                        renderedMessageIds.add(data.message.id);
                        messagesBox.appendChild(renderAdminBubble(data.message));
                        scrollDetailToBottom(true);
                    }
                }
            } catch (err) {
                alert('Không thể gửi tin nhắn. Vui lòng thử lại!');
            } finally {
                if (btnSendReply) {
                    btnSendReply.disabled = false;
                    btnSendReply.innerHTML = '<i class="fa-solid fa-paper-plane" style="font-size: 0.8rem; color: #c8a96e;"></i>';
                }
                replyInput.focus();
            }
        });
    }

    // Kiểm tra số tin chưa đọc toàn cục cho Admin (Badge đỏ)
    async function checkGlobalUnread() {
        try {
            const res = await fetch('{{ route('admin.chat.unread') }}', {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (data.success) {
                if (data.unread > 0) {
                    unreadBadge.textContent = data.unread > 99 ? '99+' : data.unread;
                    unreadBadge.classList.remove('d-none');
                } else {
                    unreadBadge.classList.add('d-none');
                }
            }
        } catch (e) {}
    }

    checkGlobalUnread();
    setInterval(checkGlobalUnread, 8000);

    // Khi widget đang mở ở danh sách, refresh danh sách mỗi 5s
    setInterval(() => {
        if (isWidgetOpen && !currentConversationId) {
            loadConversations();
        }
    }, 5000);
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('adminMenu')?.addEventListener('click',()=>document.querySelector('.admin-sidebar').classList.toggle('open'));

// Quản lý vị trí cuộn thanh menu bên (Sidebar) mượt mà, không bị giật hay nhảy về đầu trang
(function() {
    const sb = document.querySelector('.admin-sidebar');
    if (!sb) return;

    function adjustSidebarScroll() {
        try {
            const savedPos = sessionStorage.getItem('admin_sidebar_scroll');
            if (savedPos !== null) {
                sb.scrollTop = parseInt(savedPos, 10);
            }

            const activeItem = sb.querySelector('.sidebar-link.active');
            if (activeItem) {
                const sbRect = sb.getBoundingClientRect();
                const itemRect = activeItem.getBoundingClientRect();
                // Nếu mục đang chọn nằm ngoài hoặc sát mép vùng nhìn thấy thì cuộn nhẹ nhàng đến nó
                if (itemRect.top < sbRect.top + 40 || itemRect.bottom > sbRect.bottom - 40) {
                    activeItem.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                }
            }
        } catch(e) {}
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', adjustSidebarScroll);
    } else {
        adjustSidebarScroll();
    }

    // Lưu vị trí cuộn khi cuộn thanh bên
    let scrollTimer;
    sb.addEventListener('scroll', function() {
        clearTimeout(scrollTimer);
        scrollTimer = setTimeout(function() {
            try { sessionStorage.setItem('admin_sidebar_scroll', sb.scrollTop); } catch(e) {}
        }, 80);
    }, { passive: true });

    // Lưu ngay vị trí khi click chọn mục
    sb.querySelectorAll('.sidebar-link').forEach(function(link) {
        link.addEventListener('click', function() {
            try { sessionStorage.setItem('admin_sidebar_scroll', sb.scrollTop); } catch(e) {}
        });
    });
})();
</script>
@stack('scripts')
</body>
</html>
