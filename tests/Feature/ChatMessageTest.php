<?php

namespace Tests\Feature;

use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ChatMessageTest extends TestCase
{
    use RefreshDatabase;

    protected bool $authenticateDashboard = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (Schema::hasTable('chat_messages')) {
            $this->actingAsDashboardUser();
        }
    }

    public function test_send_message_between_two_users(): void
    {
        $sender = $this->actingAsDashboardUser(['username' => 'sender_user']);
        $receiver = User::factory()->create(['username' => 'receiver_user']);

        $response = $this->postJson('/api/chat/messages', [
            'receiver_id' => $receiver->getKey(),
            'message' => 'Halo, apa kabar?',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.message', 'Halo, apa kabar?')
            ->assertJsonPath('data.is_mine', true);

        $this->assertDatabaseHas('chat_messages', [
            'sender_id' => $sender->getKey(),
            'receiver_id' => $receiver->getKey(),
            'message' => 'Halo, apa kabar?',
        ]);
    }

    public function test_user_can_read_own_conversation(): void
    {
        $sender = $this->actingAsDashboardUser(['username' => 'conv_sender']);
        $receiver = User::factory()->create(['username' => 'conv_receiver']);

        ChatMessage::query()->create([
            'sender_id' => $sender->getKey(),
            'receiver_id' => $receiver->getKey(),
            'message' => 'Pesan pertama',
        ]);

        $response = $this->getJson('/api/chat/messages/' . $receiver->getKey());

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.message', 'Pesan pertama');
    }

    public function test_mark_read_clears_unread(): void
    {
        $sender = $this->actingAsDashboardUser(['username' => 'markread_sender']);
        $receiver = User::factory()->create(['username' => 'markread_receiver']);

        // Sender sends two messages to receiver
        ChatMessage::query()->create(['sender_id' => $sender->getKey(), 'receiver_id' => $receiver->getKey(), 'message' => 'msg1']);
        ChatMessage::query()->create(['sender_id' => $sender->getKey(), 'receiver_id' => $receiver->getKey(), 'message' => 'msg2']);

        // Switch to receiver session
        $this->actingAs($receiver);
        $this->withSession([
            'dashboard_last_activity_at' => now()->timestamp,
            'dashboard_active_session_id' => 'test-session-' . $receiver->getKey(),
        ]);

        // Receiver reads — should mark as read
        $this->getJson('/api/chat/messages/' . $sender->getKey())->assertOk();

        // Unread for sender should be 0 now
        $unreadCount = ChatMessage::query()
            ->where('sender_id', $sender->getKey())
            ->where('receiver_id', $receiver->getKey())
            ->whereNull('read_at')
            ->count();

        $this->assertEquals(0, $unreadCount);
    }

    public function test_sender_conversation_response_includes_read_at_after_receiver_reads(): void
    {
        $sender = $this->actingAsDashboardUser(['username' => 'read_receipt_sender']);
        $receiver = User::factory()->create(['username' => 'read_receipt_receiver']);

        $message = ChatMessage::query()->create([
            'sender_id' => $sender->getKey(),
            'receiver_id' => $receiver->getKey(),
            'message' => 'cek read receipt',
        ]);

        $this->actingAs($receiver);
        $this->withSession([
            'dashboard_last_activity_at' => now()->timestamp,
            'dashboard_active_session_id' => 'test-session-' . $receiver->getKey(),
        ]);
        $receiver->forceFill([
            'is_online' => true,
            'last_seen_at' => now(),
            'session_expires_at' => now()->addMinutes(15),
            'active_session_id' => 'test-session-' . $receiver->getKey(),
        ])->save();
        $this->getJson('/api/chat/messages/' . $sender->getKey())->assertOk();
        $this->assertNotNull(ChatMessage::query()->find($message->getKey())?->read_at);

        $this->actingAs($sender);
        $this->withSession([
            'dashboard_last_activity_at' => now()->timestamp,
            'dashboard_active_session_id' => 'test-session-' . $sender->getKey(),
        ]);
        $sender->forceFill([
            'is_online' => true,
            'last_seen_at' => now(),
            'session_expires_at' => now()->addMinutes(15),
            'active_session_id' => 'test-session-' . $sender->getKey(),
        ])->save();

        $response = $this->getJson('/api/chat/messages/' . $receiver->getKey());

        $response->assertOk()
            ->assertJsonPath('data.0.id', $message->getKey());

        $this->assertNotNull($response->json('data.0.read_at'));
    }

    public function test_unread_endpoint_returns_correct_total_and_by_user(): void
    {
        $sender = $this->actingAsDashboardUser(['username' => 'unread_sender']);
        $receiver = User::factory()->create(['username' => 'unread_receiver']);

        // Switch to receiver
        $this->actingAs($receiver);
        $this->withSession([
            'dashboard_last_activity_at' => now()->timestamp,
            'dashboard_active_session_id' => 'test-session-' . $receiver->getKey(),
        ]);

        // Sender sends 3 messages
        ChatMessage::query()->create(['sender_id' => $sender->getKey(), 'receiver_id' => $receiver->getKey(), 'message' => 'a']);
        ChatMessage::query()->create(['sender_id' => $sender->getKey(), 'receiver_id' => $receiver->getKey(), 'message' => 'b']);
        ChatMessage::query()->create(['sender_id' => $sender->getKey(), 'receiver_id' => $receiver->getKey(), 'message' => 'c']);

        $response = $this->getJson('/api/chat/unread');

        $response->assertOk()
            ->assertJsonPath('total', 3)
            ->assertJsonPath('by_user.' . $sender->getKey(), 3);
    }

    public function test_empty_message_is_rejected(): void
    {
        $sender = $this->actingAsDashboardUser(['username' => 'empty_msg_sender']);
        $receiver = User::factory()->create(['username' => 'empty_msg_receiver']);

        $response = $this->postJson('/api/chat/messages', [
            'receiver_id' => $receiver->getKey(),
            'message' => '',
        ]);

        $response->assertUnprocessable();
    }

    public function test_message_too_long_is_rejected(): void
    {
        $sender = $this->actingAsDashboardUser(['username' => 'long_msg_sender']);
        $receiver = User::factory()->create(['username' => 'long_msg_receiver']);

        $response = $this->postJson('/api/chat/messages', [
            'receiver_id' => $receiver->getKey(),
            'message' => str_repeat('a', 1001),
        ]);

        $response->assertUnprocessable();
    }

    public function test_send_message_to_self_is_rejected(): void
    {
        $user = $this->actingAsDashboardUser(['username' => 'self_msg_user']);

        $response = $this->postJson('/api/chat/messages', [
            'receiver_id' => $user->getKey(),
            'message' => 'test send to self',
        ]);

        $response->assertStatus(422);
    }

    public function test_unauthenticated_user_cannot_access_chat(): void
    {
        auth()->logout();
        session()->invalidate();
        session()->regenerateToken();

        $response = $this->getJson('/api/chat/users');

        $response->assertUnauthorized();
    }
}
