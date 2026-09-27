<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatController extends Controller
{
    /**
     * Trang chat với Shop của khách hàng
     */
    public function index(): View
    {
        $user = auth()->user();
        $conversation = ChatConversation::firstOrCreate([
            'user_id' => $user->id,
        ]);

        return view('customer.chat.index', compact('conversation'));
    }

    /**
     * Lấy danh sách tin nhắn của khách hàng hiện tại
     */
    public function getMessages(): JsonResponse
    {
        $userId = auth()->id();
        $conversation = ChatConversation::where('user_id', $userId)->first();

        if (!$conversation) {
            return response()->json([
                'success' => true,
                'messages' => [],
            ]);
        }

        // Đánh dấu các tin nhắn gửi từ Shop (Admin) là đã đọc
        ChatMessage::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = $conversation->messages()
            ->with('sender:id,name,role,avatar')
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($msg) use ($userId) {
                return [
                    'id' => $msg->id,
                    'is_me' => ($msg->sender_id === $userId),
                    'sender_name' => $msg->sender ? $msg->sender->name : 'Hỗ trợ viên',
                    'sender_role' => $msg->sender ? $msg->sender->role : 'admin',
                    'message' => $msg->message,
                    'is_read' => $msg->is_read,
                    'created_time' => $msg->created_at->format('H:i'),
                    'created_date' => $msg->created_at->format('d/m/Y'),
                    'created_human' => $msg->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'success' => true,
            'messages' => $messages,
        ]);
    }

    /**
     * Khách hàng gửi tin nhắn tới Shop
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|min:1|max:2000',
        ]);

        $userId = auth()->id();
        $conversation = ChatConversation::firstOrCreate([
            'user_id' => $userId,
        ]);

        $msg = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $userId,
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
                'is_me' => true,
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
     * Đếm số tin nhắn chưa đọc từ Shop gửi tới khách hàng
     */
    public function unreadCount(): JsonResponse
    {
        $userId = auth()->id();
        $conversation = ChatConversation::where('user_id', $userId)->first();

        $count = 0;
        if ($conversation) {
            $count = $conversation->unreadCountForCustomer();
        }

        return response()->json([
            'success' => true,
            'unread' => $count,
        ]);
    }
}
