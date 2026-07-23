<?php

namespace Tests\Feature;

use App\Support\DashboardAuth;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class DashboardAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected bool $authenticateDashboard = false;

    public function test_login_uses_configured_dashboard_admin_and_starts_session(): void
    {
        config()->set('app.env', 'local');
        config()->set('session.driver', 'database');

        $response = $this->postJson('/api/auth/login', [
            'username' => 'admin',
            'pin' => 'admin',
        ]);

        $response->assertOk()
            ->assertJsonPath('user.username', 'admin')
            ->assertJsonPath('user.role', 'Super Admin');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'username' => 'admin',
            'is_online' => true,
        ]);
    }

    public function test_login_records_online_presence_and_session_expiry(): void
    {
        Carbon::setTestNow('2026-07-22 10:00:00');
        config()->set('app.env', 'local');
        config()->set('session.driver', 'database');

        $response = $this->postJson('/api/auth/login', [
            'username' => 'admin',
            'pin' => 'admin',
        ]);

        $response->assertOk()
            ->assertJsonPath('user.is_online', true)
            ->assertJsonPath('user.session_expires_at', '2026-07-22T10:15:00+00:00');

        $this->assertDatabaseHas('users', [
            'username' => 'admin',
            'is_online' => true,
            'last_seen_at' => '2026-07-22 10:00:00',
            'session_expires_at' => '2026-07-22 10:15:00',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'table_name' => 'auth_sessions',
            'action' => 'login',
            'record_key' => 'admin',
        ]);

        Carbon::setTestNow();
    }

    public function test_session_endpoint_expires_idle_user_after_15_minutes(): void
    {
        Carbon::setTestNow('2026-07-22 10:00:00');
        config()->set('app.env', 'local');
        config()->set('session.driver', 'database');

        $this->postJson('/api/auth/login', [
            'username' => 'admin',
            'pin' => 'admin',
        ])->assertOk();

        Carbon::setTestNow('2026-07-22 10:16:00');

        $this->getJson('/api/auth/session')
            ->assertOk()
            ->assertJsonPath('authenticated', false)
            ->assertJsonPath('expired', true)
            ->assertJsonPath('user', null);

        $this->assertGuest();
        $this->assertDatabaseHas('users', [
            'username' => 'admin',
            'is_online' => false,
            'session_expires_at' => null,
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'table_name' => 'auth_sessions',
            'action' => 'timeout',
            'record_key' => 'admin',
        ]);

        Carbon::setTestNow();
    }

    public function test_heartbeat_extends_active_session_and_keeps_user_online(): void
    {
        Carbon::setTestNow('2026-07-22 10:00:00');
        config()->set('app.env', 'local');
        config()->set('session.driver', 'database');

        $this->postJson('/api/auth/login', [
            'username' => 'admin',
            'pin' => 'admin',
        ])->assertOk();

        Carbon::setTestNow('2026-07-22 10:10:00');

        $this->postJson('/api/auth/heartbeat')
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('user.is_online', true)
            ->assertJsonPath('user.session_expires_at', '2026-07-22T10:25:00+00:00');

        $this->assertDatabaseHas('users', [
            'username' => 'admin',
            'is_online' => true,
            'last_seen_at' => '2026-07-22 10:10:00',
            'session_expires_at' => '2026-07-22 10:25:00',
        ]);

        Carbon::setTestNow();
    }

    public function test_login_records_active_session_id_for_single_device_guard(): void
    {
        Carbon::setTestNow('2026-07-22 10:00:00');
        config()->set('app.env', 'local');
        config()->set('session.driver', 'database');

        $this->postJson('/api/auth/login', [
            'username' => 'admin',
            'pin' => 'admin',
        ])->assertOk()
            ->assertJsonPath('user.is_online', true);

        $sessionId = session('dashboard_active_session_id');

        $this->assertIsString($sessionId);
        $this->assertNotSame('', $sessionId);
        $this->assertDatabaseHas('users', [
            'username' => 'admin',
            'active_session_id' => $sessionId,
        ]);

        Carbon::setTestNow();
    }

    public function test_old_device_session_is_logged_out_when_account_logs_in_elsewhere(): void
    {
        Carbon::setTestNow('2026-07-22 10:00:00');
        config()->set('session.driver', 'database');

        $user = $this->actingAsDashboardUser([
            'username' => 'single-device-user',
        ]);

        $user->forceFill([
            'active_session_id' => 'new-device-session-id',
            'is_online' => true,
            'last_seen_at' => now(),
            'session_expires_at' => now()->addMinutes(15),
        ])->save();

        $this->getJson('/api/master-plans')
            ->assertUnauthorized()
            ->assertJsonPath('superseded', true)
            ->assertJsonPath('message', 'anda sudah login di perangkat lain');

        $this->assertGuest();
        $this->assertDatabaseHas('activity_logs', [
            'table_name' => 'auth_sessions',
            'action' => 'superseded',
            'record_key' => 'single-device-user',
        ]);

        Carbon::setTestNow();
    }

    public function test_logout_records_offline_presence(): void
    {
        Carbon::setTestNow('2026-07-22 10:00:00');
        config()->set('app.env', 'local');
        config()->set('session.driver', 'database');

        $this->postJson('/api/auth/login', [
            'username' => 'admin',
            'pin' => 'admin',
        ])->assertOk();

        Carbon::setTestNow('2026-07-22 10:03:00');

        $this->postJson('/api/auth/logout')
            ->assertOk()
            ->assertJsonPath('status', 'success');

        $this->assertGuest();
        $this->assertDatabaseHas('users', [
            'username' => 'admin',
            'is_online' => false,
            'session_expires_at' => null,
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'table_name' => 'auth_sessions',
            'action' => 'logout',
            'record_key' => 'admin',
        ]);

        Carbon::setTestNow();
    }

    public function test_dashboard_api_rejects_idle_session_after_15_minutes(): void
    {
        Carbon::setTestNow('2026-07-22 10:00:00');
        config()->set('session.driver', 'database');

        $this->actingAsDashboardUser([
            'username' => 'idle-user',
        ]);

        Carbon::setTestNow('2026-07-22 10:16:00');

        $this->getJson('/api/master-plans')
            ->assertUnauthorized()
            ->assertJsonPath('expired', true);

        $this->assertDatabaseHas('users', [
            'username' => 'idle-user',
            'is_online' => false,
            'session_expires_at' => null,
        ]);

        Carbon::setTestNow();
    }

    public function test_session_endpoint_does_not_bootstrap_configured_dashboard_admin_for_web(): void
    {
        config()->set('app.env', 'local');
        config()->set('session.driver', 'database');

        $response = $this->getJson('/api/auth/session');

        $response->assertOk()
            ->assertJsonPath('authenticated', false)
            ->assertJsonPath('user', null);

        $this->assertGuest();
        $this->assertDatabaseMissing('users', [
            'username' => 'admin',
        ]);
    }

    public function test_bootstrap_configured_admin_session_is_disabled_in_production(): void
    {
        config()->set('app.env', 'production');

        $user = app(DashboardAuth::class)->bootstrapConfiguredAdminSession();

        $this->assertNull($user);
        $this->assertGuest();
        $this->assertDatabaseMissing('users', [
            'username' => 'admin',
        ]);
    }

    public function test_configured_admin_user_is_not_created_in_production(): void
    {
        config()->set('app.env', 'production');

        $user = app(DashboardAuth::class)->ensureConfiguredAdminUser();

        $this->assertNull($user);
        $this->assertDatabaseMissing('users', [
            'username' => 'admin',
        ]);
    }

    public function test_session_endpoint_never_creates_admin_session_in_production(): void
    {
        config()->set('app.env', 'production');
        config()->set('session.driver', 'database');

        $response = $this->getJson('/api/auth/session');

        $response->assertOk()
            ->assertJsonPath('authenticated', false)
            ->assertJsonPath('user', null);

        $this->assertGuest();
        $this->assertDatabaseMissing('users', [
            'username' => 'admin',
        ]);
    }

    public function test_dashboard_api_requires_authenticated_session_when_proxy_secret_is_not_used(): void
    {
        $this->getJson('/api/master-plans')
            ->assertUnauthorized();
    }

    public function test_authenticated_user_can_access_dashboard_api(): void
    {
        $this->actingAsDashboardUser();

        $this->getJson('/api/master-plans')
            ->assertOk()
            ->assertJsonPath('data', []);
    }

    public function test_x_app_user_header_is_not_used_as_identity_source_for_activity_logs(): void
    {
        $user = $this->actingAsDashboardUser([
            'username' => 'real-user',
            'name' => 'Real User',
            'role' => 'super_admin',
        ]);

        $this->withHeader('X-App-User', 'spoofed-user')
            ->putJson('/api/auth/profile', [
                'nama' => 'Nama Baru',
            ])->assertOk();

        $this->assertDatabaseHas('activity_logs', [
            'table_name' => 'users',
            'action' => 'update',
            'record_key' => 'real-user',
            'user_id' => $user->id,
            'actor_label' => 'real-user',
        ]);
    }

    public function test_avatar_upload_returns_loadable_same_origin_url(): void
    {
        config()->set('app.url', 'http://configured-app-url.test');

        $user = $this->actingAsDashboardUser([
            'username' => 'avatar-user',
            'name' => 'Avatar User',
            'role' => 'super_admin',
        ]);

        $avatarDirectory = storage_path('app/public/avatars');
        File::ensureDirectoryExists($avatarDirectory);
        $existingAvatars = collect(File::files($avatarDirectory))
            ->map(fn (\SplFileInfo $file): string => $file->getFilename())
            ->all();

        $response = $this->post('/api/auth/avatar', [
            'avatar' => UploadedFile::fake()->image('avatar.png', 96, 96),
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success');

        $avatarUrl = $response->json('user.avatar_url');

        $this->assertIsString($avatarUrl);
        $this->assertStringStartsWith('/api/auth/avatar/', $avatarUrl);
        $this->assertStringNotContainsString('configured-app-url.test', $avatarUrl);

        $user->refresh();

        $this->assertNotNull($user->avatar);
        $this->assertFileExists($avatarDirectory . DIRECTORY_SEPARATOR . $user->avatar);

        $this->get($avatarUrl)->assertOk();

        collect(File::files($avatarDirectory))
            ->reject(fn (\SplFileInfo $file): bool => in_array($file->getFilename(), $existingAvatars, true))
            ->each(fn (\SplFileInfo $file): bool => File::delete($file->getPathname()));
    }
}
