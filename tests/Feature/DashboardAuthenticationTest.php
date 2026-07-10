<?php

namespace Tests\Feature;

use App\Support\DashboardAuth;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        ]);
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
}
