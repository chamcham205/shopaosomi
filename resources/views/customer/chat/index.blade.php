@extends('layouts.app')

@section('title', 'Chat với Shop – Hỗ trợ khách hàng')

@push('styles')
<style>
    .chat-container {
        border-radius: 16px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
    }
    .chat-messages-box {
        height: 480px;
        overflow-y: auto;
        padding: 1.25rem;
        background: #f8fafc;
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
    }
    .chat-messages-box::-webkit-scrollbar {
        width: 6px;
    }
    .chat-messages-box::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    .chat-bubble {
        max-width: 75%;
        padding: 0.75rem 1rem;
        border-radius: 14px;
        font-size: 0.925rem;
        line-height: 1.45;
        position: relative;
        word-break: break-word;
    }
    .chat-bubble.mine {
        align-self: flex-end;
        background: #1e293b;
        color: #ffffff;
        border-bottom-right-radius: 3px;
    }
    .chat-bubble.theirs {
        align-self: flex-start;
        background: #ffffff;
        color: #1e293b;
        border: 1px solid #e2e8f0;
        border-bottom-left-radius: 3px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }
    .chat-meta {
        font-size: 0.72rem;
        margin-top: 4px;
        opacity: 0.75;
    }
    .chat-bubble.mine .chat-meta {
        color: rgba(255, 255, 255, 0.7);
        text-align: right;
    }
    .chat-bubble.theirs .chat-meta {
        color: #64748b;
    }
    .quick-chip {
        font-size: 0.8rem;
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        background: #eef2f6;
        color: #334155;
        border: 1px solid #e2e8f0;
        cursor: pointer;
        transition: all 0.15s ease;
        white-space: nowrap;
    }
    .quick-chip:hover {
        background: #1e293b;
        color: #ffffff;
        border-color: #1e293b;
    }
    .status-dot-pulse {
        width: 10px;
        height: 10px;
        background: #22c55e;
        border-radius: 50%;
        display: inline-block;
        box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
        animation: pulseGreen 2s infinite;
    }
    @keyframes pulseGreen {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(34, 197, 94, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
    }
    [data-theme="dark"] .chat-container {
        background: #171d27 !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    }
    [data-theme="dark"] .chat-messages-box {
        background: #0f131a !important;
    }
    [data-theme="dark"] .chat-bubble.theirs {
        background: #1f2937 !important;
        color: #f1f5f9 !important;
        border-color: #374151 !important;
    }
    [data-theme="dark"] .chat-bubble.mine {
        background: #c8a96e !important;
        color: #0f131a !important;
    }
    [data-theme="dark"] .quick-chip {
        background: #1f2937;
        color: #e2e8f0;
        border-color: #374151;
    }
    [data-theme="dark"] .quick-chip:hover {
        background: #c8a96e;
        color: #0f131a;
        border-color: #c8a96e;
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">Trang chủ</a></li>
            <li class="breadcrumb-item text-muted">Tài khoản</li>
            <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">Chat với Shop</li>
        </ol>
    </nav>

    <div class="row g-4">
        {{-- CỘT TRÁI: THÔNG TIN HỖ TRỢ SHOP --}}
        <div class="col-lg-4 col-md-5">
            <div class="card border-0 rounded-4 shadow-sm p-4 mb-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-dark text-white fw-bold shadow-sm" style="width: 52px; height: 52px; font-size: 1.25rem;">
                        <i class="fa-solid fa-shirt" style="color: var(--accent);"></i>
                    </div>
                    <div>
                        <h5 class="mb-1 fw-bold">{{ $siteSettings['site_name'] ?? 'Shirt Store' }}</h5>
                        <div class="d-flex align-items-center gap-2">
                            <span class="status-dot-pulse"></span>
                            <span class="text-success small fw-semibold">Đang trực tuyến</span>
                        </div>
                    </div>
                </div>

                <p class="text-muted small mb-3">
                    Đội ngũ chăm sóc khách hàng luôn sẵn sàng giải đáp thắc mắc về kích cỡ áo, tình trạng giao vận và chính sách ưu đãi.
                </p>

                <hr class="my-2 opacity-25">

                <div class="d-flex flex-column gap-2.5 my-2">
                    <div class="d-flex align-items-center gap-2 small">
                        <i class="fa-regular fa-clock text-primary" style="width: 18px;"></i>
                        <span>Giờ trực hỗ trợ: <strong>08:00 - 22:00</strong></span>
                    </div>
                    <div class="d-flex align-items-center gap-2 small">
                        <i class="fa-solid fa-phone text-success" style="width: 18px;"></i>
                        <span>Hotline tư vấn: <strong>1900 6868</strong></span>
                    </div>
                    <div class="d-flex align-items-center gap-2 small">
                        <i class="fa-solid fa-rotate-left text-warning" style="width: 18px;"></i>
                        <span>Đổi trả linh hoạt trong <strong>7 ngày</strong></span>
                    </div>
                    <div class="d-flex align-items-center gap-2 small">
                        <i class="fa-solid fa-truck-fast text-info" style="width: 18px;"></i>
                        <span>Giao hàng toàn quốc chuẩn <strong>GHN</strong></span>
                    </div>
                </div>

                <div class="mt-4 pt-2">
                    <a href="{{ route('account.orders.index') }}" class="btn btn-outline-secondary w-100 rounded-pill btn-sm fw-medium">
                        <i class="fa-solid fa-receipt me-1.5"></i>Xem đơn hàng của tôi
                    </a>
                </div>
            </div>
        </div>

        {{-- CỘT PHẢI: KHUNG CHAT TRỰC TIẾP --}}
        <div class="col-lg-8 col-md-7">
            <div class="chat-container border d-flex flex-column">
                {{-- Header Chat --}}
                <div class="p-3 border-bottom d-flex align-items-center justify-content-between bg-white">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="fa-solid fa-headset fs-5"></i>
                        </div>
                        <div>
                            <div class="fw-bold mb-0 text-dark">Hỗ trợ khách hàng – Shirt Store</div>
                            <small class="text-muted" id="chat-sub-status">Phản hồi thông thường trong vài phút</small>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-light rounded-circle text-muted" id="btn-refresh-chat" title="Tải lại tin nhắn">
                        <i class="fa-solid fa-rotate-right"></i>
                    </button>
                </div>

                {{-- Khung hiển thị tin nhắn --}}
                <div class="chat-messages-box" id="customer-chat-box">
                    <div class="text-center py-5 text-muted" id="chat-loading-placeholder">
                        <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                        <div class="small">Đang tải đoạn hội thoại...</div>
                    </div>
                </div>

                {{-- Gợi ý câu hỏi nhanh --}}
                <div class="px-3 pt-2 pb-1 bg-white border-top overflow-x-auto d-flex gap-2" style="scrollbar-width: none;" id="quick-prompts-wrapper">
                    <button type="button" class="quick-chip" data-text="👋 Xin chào Shop! Tư vấn giúp mình chọn size áo với ạ.">
                        👕 Tư vấn chọn size
                    </button>
                    <button type="button" class="quick-chip" data-text="📦 Cho mình hỏi tiến độ đơn hàng gần nhất của mình như thế nào rồi ạ?">
                        📦 Kiểm tra đơn hàng
                    </button>
                    <button type="button" class="quick-chip" data-text="🔄 Nếu áo không vừa mình có được hỗ trợ đổi size miễn phí không ạ?">
                        🔄 Chính sách đổi trả
                    </button>
                    <button type="button" class="quick-chip" data-text="🏷️ Hiện tại shop đang có mã giảm giá nào không ạ?">
                        🏷️ Mã khuyến mãi
                    </button>
                </div>

                {{-- Thanh nhập tin nhắn --}}
                <div class="p-3 bg-white border-top">
                    <form id="customer-chat-form" class="d-flex align-items-center gap-2">
                        @csrf
                        <input type="text"
                               id="customer-chat-input"
                               name="message"
                               class="form-control rounded-pill px-3 py-2 border"
                               placeholder="Nhập câu hỏi hoặc yêu cầu cần hỗ trợ..."
                               autocomplete="off"
                               maxlength="2000"
                               required>
                        <button type="submit"
                                class="btn btn-dark rounded-pill px-4 py-2 fw-semibold d-flex align-items-center gap-1.5 shadow-sm"
                                id="btn-send-chat">
                            <span>Gửi</span>
                            <i class="fa-solid fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const chatBox = document.getElementById('customer-chat-box');
    const chatForm = document.getElementById('customer-chat-form');
    const chatInput = document.getElementById('customer-chat-input');
    const btnSend = document.getElementById('btn-send-chat');
    const btnRefresh = document.getElementById('btn-refresh-chat');
    const quickChips = document.querySelectorAll('.quick-chip');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    let isPolling = true;
    let pollInterval = null;
    let lastRenderedIds = new Set();

    function scrollToBottom(smooth = true) {
        if (!chatBox) return;
        chatBox.scrollTo({
            top: chatBox.scrollHeight,
            behavior: smooth ? 'smooth' : 'auto'
        });
    }

    function renderMessage(msg) {
        const bubble = document.createElement('div');
        bubble.className = `chat-bubble ${msg.is_me ? 'mine' : 'theirs'}`;
        bubble.dataset.id = msg.id;

        const senderLabel = msg.is_me ? '' : `<div class="fw-bold small mb-1 text-primary"><i class="fa-solid fa-headset me-1"></i>${msg.sender_name}</div>`;
        const textContent = document.createElement('div');
        textContent.textContent = msg.message;

        bubble.innerHTML = `
            ${senderLabel}
            <div class="message-text">${textContent.innerHTML}</div>
            <div class="chat-meta">
                <span>${msg.created_time}</span>
                ${msg.is_me ? '<i class="fa-solid fa-check ms-1" style="font-size:0.65rem;"></i>' : ''}
            </div>
        `;
        return bubble;
    }

    async function loadMessages(initial = false) {
        try {
            const res = await fetch('{{ route('account.chat.messages') }}', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await res.json();

            if (data.success && Array.isArray(data.messages)) {
                const placeholder = document.getElementById('chat-loading-placeholder');
                if (placeholder) placeholder.remove();

                if (data.messages.length === 0) {
                    if (chatBox.children.length === 0) {
                        chatBox.innerHTML = `
                            <div class="text-center py-5 text-muted" id="chat-empty-state">
                                <div class="mb-2"><i class="fa-solid fa-comments text-secondary" style="font-size: 2.5rem; opacity: 0.5;"></i></div>
                                <h6 class="fw-bold text-dark mb-1">Chưa có tin nhắn nào</h6>
                                <p class="small mb-0">Gửi câu hỏi hoặc chọn một gợi ý nhanh bên dưới để bắt đầu trò chuyện với Shop!</p>
                            </div>
                        `;
                    }
                    return;
                }

                const emptyState = document.getElementById('chat-empty-state');
                if (emptyState) emptyState.remove();

                let hasNewMessage = false;
                data.messages.forEach(msg => {
                    if (!lastRenderedIds.has(msg.id)) {
                        lastRenderedIds.add(msg.id);
                        chatBox.appendChild(renderMessage(msg));
                        hasNewMessage = true;
                    }
                });

                if (hasNewMessage || initial) {
                    scrollToBottom(!initial);
                }
            }
        } catch (err) {
            console.warn('Lỗi khi tải tin nhắn chat:', err);
        }
    }

    // Gửi tin nhắn
    async function sendMessage(text) {
        const content = text.trim();
        if (!content) return;

        chatInput.value = '';
        if (btnSend) {
            btnSend.disabled = true;
            btnSend.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';
        }

        try {
            const res = await fetch('{{ route('account.chat.send') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ message: content })
            });

            const data = await res.json();

            if (data.success && data.message) {
                const emptyState = document.getElementById('chat-empty-state');
                if (emptyState) emptyState.remove();

                if (!lastRenderedIds.has(data.message.id)) {
                    lastRenderedIds.add(data.message.id);
                    chatBox.appendChild(renderMessage(data.message));
                    scrollToBottom(true);
                }
            }
        } catch (err) {
            alert('Không thể gửi tin nhắn. Vui lòng kiểm tra kết nối mạng và thử lại!');
        } finally {
            if (btnSend) {
                btnSend.disabled = false;
                btnSend.innerHTML = '<span>Gửi</span><i class="fa-solid fa-paper-plane"></i>';
            }
            chatInput.focus();
        }
    }

    if (chatForm) {
        chatForm.addEventListener('submit', function (e) {
            e.preventDefault();
            sendMessage(chatInput.value);
        });
    }

    // Gợi ý nhanh
    quickChips.forEach(chip => {
        chip.addEventListener('click', function () {
            const text = this.dataset.text;
            if (text) {
                sendMessage(text);
            }
        });
    });

    if (btnRefresh) {
        btnRefresh.addEventListener('click', function () {
            this.classList.add('fa-spin');
            loadMessages().finally(() => {
                setTimeout(() => this.classList.remove('fa-spin'), 600);
            });
        });
    }

    // Tải ban đầu và vòng lặp tự động cập nhật mỗi 4s
    loadMessages(true);
    pollInterval = setInterval(() => {
        if (isPolling) loadMessages();
    }, 4000);

    window.addEventListener('beforeunload', () => {
        clearInterval(pollInterval);
    });
});
</script>
@endpush
