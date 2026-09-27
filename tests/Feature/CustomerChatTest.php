<?php

namespace Tests\Feature;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerChatTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->create([
            'role' => 'customer',
            'status' => 'active',
            'name' => 'Nguyễn Văn Khách',
            'email' => 'customer@example.com',
            'phone' => '0901234567',
        ]);

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'name' => 'Quản Trị Viên',
            'email' => 'admin@example.com',
        ]);
    }

    public function test_guest_cannot_access_chat(): void
    {
        $response = $this->get(route('account.chat.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_customer_can_view_chat_page(): void
    {
        $response = $this->actingAs($this->customer)->get(route('account.chat.index'));
        $response->assertStatus(200);
        $response->assertSee('Chat với Shop');
        $response->assertSee('Tư vấn chọn size');
    }

    public function test_customer_can_send_message_and_creates_conversation(): void
    {
        $response = $this->actingAs($this->customer)->postJson(route('account.chat.send'), [
            'message' => 'Shop ơi tư vấn size L giúp mình với!',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('message.message', 'Shop ơi tư vấn size L giúp mình với!');

        $this->assertDatabaseHas('chat_conversations', [
            'user_id' => $this->customer->id,
            'status' => 'open',
        ]);

        $this->assertDatabaseHas('chat_messages', [
            'sender_id' => $this->customer->id,
            'message' => 'Shop ơi tư vấn size L giúp mình với!',
            'is_read' => false,
        ]);
    }

    public function test_empty_message_validation(): void
    {
        $response = $this->actingAs($this->customer)->postJson(route('account.chat.send'), [
            'message' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['message']);
    }

    public function test_admin_can_view_conversations_and_unread_count(): void
    {
        // Customer sends message
        $this->actingAs($this->customer)->postJson(route('account.chat.send'), [
            'message' => 'Chào shop nhé',
        ]);

        // Admin checks unread count
        $responseUnread = $this->actingAs($this->admin)->getJson(route('admin.chat.unread'));
        $responseUnread->assertStatus(200);
        $responseUnread->assertJsonPath('success', true);
        $responseUnread->assertJsonPath('unread', 1);

        // Admin checks conversations list
        $responseList = $this->actingAs($this->admin)->getJson(route('admin.chat.conversations'));
        $responseList->assertStatus(200);
        $responseList->assertJsonPath('success', true);
        $responseList->assertJsonCount(1, 'conversations');
        $responseList->assertJsonPath('conversations.0.customer_name', 'Nguyễn Văn Khách');
        $responseList->assertJsonPath('conversations.0.unread_count', 1);
    }

    public function test_admin_viewing_messages_marks_them_as_read(): void
    {
        $this->actingAs($this->customer)->postJson(route('account.chat.send'), [
            'message' => 'Cần hỗ trợ gấp',
        ]);

        $conversation = ChatConversation::where('user_id', $this->customer->id)->firstOrFail();

        // Admin views messages
        $response = $this->actingAs($this->admin)->getJson(route('admin.chat.messages', $conversation));
        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonCount(1, 'messages');

        // Customer message should now be read
        $this->assertDatabaseHas('chat_messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $this->customer->id,
            'is_read' => true,
        ]);
    }

    public function test_admin_can_reply_to_customer(): void
    {
        $this->actingAs($this->customer)->postJson(route('account.chat.send'), [
            'message' => 'Shop còn size M không?',
        ]);

        $conversation = ChatConversation::where('user_id', $this->customer->id)->firstOrFail();

        $response = $this->actingAs($this->admin)->postJson(route('admin.chat.reply', $conversation), [
            'message' => 'Chào bạn, size M vẫn còn sẵn hàng nhé!',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('message.message', 'Chào bạn, size M vẫn còn sẵn hàng nhé!');
        $response->assertJsonPath('message.is_mine', true);

        $this->assertDatabaseHas('chat_messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $this->admin->id,
            'message' => 'Chào bạn, size M vẫn còn sẵn hàng nhé!',
            'is_read' => false,
        ]);

        // Customer checks unread count
        $responseCustomerUnread = $this->actingAs($this->customer)->getJson(route('account.chat.unread'));
        $responseCustomerUnread->assertStatus(200);
        $responseCustomerUnread->assertJsonPath('unread', 1);

        // Customer retrieves messages -> should mark admin reply as read
        $responseCustomerMessages = $this->actingAs($this->customer)->getJson(route('account.chat.messages'));
        $responseCustomerMessages->assertStatus(200);
        $responseCustomerMessages->assertJsonCount(2, 'messages');

        $this->assertDatabaseHas('chat_messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $this->admin->id,
            'is_read' => true,
        ]);
    }
}
