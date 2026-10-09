<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthAndChatHardeningSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected bool $authenticateDashboard = false;

    public function test_profile_update_rejects_html_only_payload_after_sanitization(): void
    {
        $user = $this->actingAsDashboardUser([
            'username' => 'profile-tester',
            'name' => 'Original Name',
        ]);

        $response = $this->putJson('/api/auth/profile', [
            'nama' => '   <br>   ',
        ]);

        $response->assertStatus(422);

        $user->refresh();
        $this->assertSame('Original Name', $user->name);
    }

    public function test_profile_update_sanitizes_html_and_updates_clean_name(): void
    {
        $user = $this->actingAsDashboardUser([
            'username' => 'clean-profile',
            'name' => 'Old Name',
        ]);

        $response = $this->putJson('/api/auth/profile', [
            'nama' => '<b>Budi Pratama</b>',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('user.nama', 'Budi Pratama');

        $user->refresh();
        $this->assertSame('Budi Pratama', $user->name);
    }

    public function test_avatar_upload_rejects_non_image_binary_disguised_as_image(): void
    {
        $this->actingAsDashboardUser([
            'username' => 'avatar-tester',
            'role' => 'operasional',
        ]);

        $fakeFile = UploadedFile::fake()->createWithContent('malicious.png', '<?php phpinfo(); ?>');

        $response = $this->post('/api/auth/avatar', [
            'avatar' => $fakeFile,
        ]);

        $response->assertStatus(422);
    }

    public function test_avatar_upload_target_user_check_blocks_non_admin_updating_other_user(): void
    {
        $target = User::factory()->create([
            'username' => 'target-user',
        ]);

        $this->actingAsDashboardUser([
            'username' => 'regular-user',
            'role' => 'operasional',
        ]);

        $validImage = UploadedFile::fake()->image('avatar.jpg', 64, 64);

        $response = $this->post('/api/auth/avatar', [
            'avatar' => $validImage,
            'user_id' => $target->id,
        ]);

        $response->assertForbidden();
    }

    public function test_avatar_upload_allows_super_admin_to_update_target_user_avatar(): void
    {
        $target = User::factory()->create([
            'username' => 'target-super',
        ]);

        $this->actingAsDashboardUser([
            'username' => 'superadmin-user',
            'role' => 'super_admin',
        ]);

        $avatarDir = storage_path('app/public/avatars');
        File::ensureDirectoryExists($avatarDir);

        $validImage = UploadedFile::fake()->image('avatar.png', 64, 64);

        $response = $this->post('/api/auth/avatar', [
            'avatar' => $validImage,
            'user_id' => $target->id,
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('user.username', 'target-super');

        $target->refresh();
        $this->assertNotNull($target->avatar);
        $this->assertFileExists($avatarDir.DIRECTORY_SEPARATOR.$target->avatar);

        if (File::exists($avatarDir.DIRECTORY_SEPARATOR.$target->avatar)) {
            File::delete($avatarDir.DIRECTORY_SEPARATOR.$target->avatar);
        }
    }

    public function test_pin_update_rejects_null_bytes_and_blank_pin(): void
    {
        $user = $this->actingAsDashboardUser([
            'username' => 'pin-tester',
            'password' => Hash::make('123456'),
        ]);

        $response = $this->putJson('/api/auth/pin', [
            'old_pin' => "123456\0",
            'new_pin' => '654321',
            'new_pin_confirmation' => '654321',
        ]);

        $response->assertStatus(422);

        $blankResponse = $this->putJson('/api/auth/pin', [
            'old_pin' => '123456',
            'new_pin' => '      ',
            'new_pin_confirmation' => '      ',
        ]);

        $blankResponse->assertStatus(422);
    }

    public function test_heartbeat_requires_authentication_and_extends_session(): void
    {
        $unauthResponse = $this->postJson('/api/auth/heartbeat');
        $unauthResponse->assertUnauthorized();

        $this->actingAsDashboardUser([
            'username' => 'heartbeat-user',
        ]);

        $authResponse = $this->postJson('/api/auth/heartbeat');
        $authResponse->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('user.is_online', true);
    }

    public function test_logout_endpoint_succeeds_and_marks_user_offline(): void
    {
        $user = $this->actingAsDashboardUser([
            'username' => 'logout-tester',
        ]);

        $response = $this->postJson('/api/auth/logout');
        $response->assertOk()
            ->assertJsonPath('status', 'success');

        $user->refresh();
        $this->assertFalse((bool) $user->is_online);
    }

    public function test_chat_typing_target_user_checks_prevent_self_checks(): void
    {
        $user = $this->actingAsDashboardUser([
            'username' => 'self-typer',
        ]);

        $postResponse = $this->postJson('/api/chat/typing/'.$user->id);
        $postResponse->assertStatus(422);

        $getResponse = $this->getJson('/api/chat/typing/'.$user->id);
        $getResponse->assertStatus(422);
    }

    public function test_chat_typing_post_and_get_between_distinct_users_succeeds(): void
    {
        $sender = $this->actingAsDashboardUser(['username' => 'typer-a']);
        $receiver = User::factory()->create(['username' => 'typer-b']);

        $postResponse = $this->postJson('/api/chat/typing/'.$receiver->id);
        $postResponse->assertOk()->assertJsonPath('status', 'ok');

        $this->actingAs($receiver);
        $this->withSession([
            'dashboard_last_activity_at' => now()->timestamp,
            'dashboard_active_session_id' => 'test-session-'.$receiver->id,
        ]);
        $receiver->forceFill([
            'is_online' => true,
            'last_seen_at' => now(),
            'session_expires_at' => now()->addMinutes(15),
            'active_session_id' => 'test-session-'.$receiver->id,
        ])->save();

        $getResponse = $this->getJson('/api/chat/typing/'.$sender->id);
        $getResponse->assertOk()->assertJsonPath('typing', true);
    }

    public function test_chat_messages_rejects_html_only_body_after_sanitization(): void
    {
        $this->actingAsDashboardUser(['username' => 'chat-sanitizer']);
        $peer = User::factory()->create(['username' => 'chat-peer']);

        $response = $this->postJson('/api/chat/messages', [
            'receiver_id' => $peer->id,
            'message' => '   <b><script>alert(1)</script></b>   ',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('chat_messages', [
            'receiver_id' => $peer->id,
        ]);
    }

    public function test_chat_messages_target_user_check_blocks_sending_to_self(): void
    {
        $user = $this->actingAsDashboardUser(['username' => 'chat-lonely']);

        $response = $this->postJson('/api/chat/messages', [
            'receiver_id' => $user->id,
            'message' => 'Halo diri sendiri',
        ]);

        $response->assertStatus(422);
    }

    public function test_chat_messages_target_user_check_blocks_non_existent_user(): void
    {
        $this->actingAsDashboardUser(['username' => 'chat-ghost-hunter']);

        $response = $this->postJson('/api/chat/messages', [
            'receiver_id' => 999999,
            'message' => 'Halo ghost',
        ]);

        $response->assertStatus(422);
    }

    public function test_chat_messages_get_conversation_blocks_self_target(): void
    {
        $user = $this->actingAsDashboardUser(['username' => 'chat-reader-self']);

        $response = $this->getJson('/api/chat/messages/'.$user->id);
        $response->assertStatus(422);
    }
}
