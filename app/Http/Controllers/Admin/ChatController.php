<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    /**
     * Lấy danh sách hội thoại của khách hàng kèm tin nhắn mới nhất và số tin chưa đọc
     */
    public function getConversations(Request $request): JsonResponse
    {
        $query = ChatConversation::query()
            ->with(['user:id,name,email,phone,avatar', 'latestMessage.sender:id,name,role'])
            ->whereHas('user');

        if ($request->filled('q')) {
            $keyword = trim($request->q);
            $query->whereHas('user', function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('email', 'like', "%{$keyword}%")
                  ->orWhere('phone', 'like', "%{$keyword}%");
            });
        }

        $conversations = $query->orderByRaw('COALESCE(last_message_at, updated_at) DESC')
            ->get()
            ->map(function ($conv) {
                $latest = $conv->latestMessage;
                $unread = $conv->unreadCountForAdmin();

                return [
                    'id' => $conv->id,
                    'customer_id' => $conv->user_id,
                    'customer_name' => $conv->user ? $conv->user->name : 'Khách hàng',
                    'customer_email' => $conv->user ? $conv->user->email : '',
                    'customer_phone' => $conv->user ? ($conv->user->phone ?? 'Chưa có SĐT') : '',
                    'customer_initial' => $conv->user ? strtoupper(substr($conv->user->name, 0, 1)) : 'K',
                    'unread_count' => $unread,
                    'last_message' => $latest ? $latest->message : 'Chưa có tin nhắn',
                    'last_message_is_mine' => $latest ? ($latest->sender_id !== $conv->user_id) : false,
                    'last_time' => $latest ? $latest->created_at->diffForHumans() : ($conv->created_at ? $conv->created_at->diffForHumans() : ''),
                    'last_message_raw' => $conv->last_message_at ? $conv->last_message_at->toIso8601String() : null,
                ];
            });

        return response()->json([
            'success' => true,
            'conversations' => $conversations,
        ]);
    }

    /**
     * Lấy chi tiết tin nhắn trong một cuộc hội thoại và đánh dấu đã đọc
     */
    public function getMessages(ChatConversation $conversation): JsonResponse
    {
        // Đánh dấu các tin nhắn gửi từ khách hàng là đã đọc
        ChatMessage::where('conversation_id', $conversation->id)
            ->where('sender_id', $conversation->user_id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $adminId = auth()->id();
        $messages = $conversation->messages()
            ->with('sender:id,name,role')
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($msg) use ($conversation) {
                $isCustomer = ($msg->sender_id === $conversation->user_id);

                return [
                    'id' => $msg->id,
                    'is_customer' => $isCustomer,
                    'is_mine' => !$isCustomer,
                    'sender_name' => $msg->sender ? $msg->sender->name : ($isCustomer ? 'Khách hàng' : 'Admin'),
                    'message' => $msg->message,
                    'is_read' => $msg->is_read,
                    'created_time' => $msg->created_at->format('H:i'),
                    'created_date' => $msg->created_at->format('d/m/Y'),
                    'created_human' => $msg->created_at->diffForHumans(),
                ];
            });

        $customer = $conversation->user;

        return response()->json([
            'success' => true,
            'conversation_id' => $conversation->id,
            'customer' => [
                'id' => $customer ? $customer->id : null,
                'name' => $customer ? $customer->name : 'Khách hàng',
                'email' => $customer ? $customer->email : '',
                'phone' => $customer ? ($customer->phone ?? 'Chưa cập nhật') : '',
                'initial' => $customer ? strtoupper(substr($customer->name, 0, 1)) : 'K',
            ],
            'messages' => $messages,
        ]);
    }

    /**
     * Admin trả lời tin nhắn của khách hàng
     */
    public function reply(Request $request, ChatConversation $conversation): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|min:1|max:2000',
        ]);

        $adminId = auth()->id();

        $msg = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $adminId,
            'message' => trim($validated['message']),
            'is_read' => false,
        ]);

        $conversation->update([
            'last_message_at' => now(),
            'status' => 'open',
        ]);

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $msg->id,
                'is_customer' => false,
                'is_mine' => true,
                'sender_name' => auth()->user()->name,
                'message' => $msg->message,
                'is_read' => false,
                'created_time' => $msg->created_at->format('H:i'),
                'created_date' => $msg->created_at->format('d/m/Y'),
                'created_human' => $msg->created_at->diffForHumans(),
            ],
        ]);
    }

    /**
     * Đếm tổng số tin nhắn chưa đọc từ khách hàng cho toàn bộ hệ thống admin
     */
    public function unreadCount(): JsonResponse
    {
        $totalUnread = ChatMessage::whereHas('conversation', function ($q) {
            $q->whereColumn('chat_messages.sender_id', 'chat_conversations.user_id');
        })->where('is_read', false)->count();

        return response()->json([
            'success' => true,
            'unread' => $totalUnread,
        ]);
    }
}
