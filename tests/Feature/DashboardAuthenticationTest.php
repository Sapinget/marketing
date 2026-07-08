<?php

namespace Tests\Feature;

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

    public function test_session_endpoint_bootstraps_configured_dashboard_admin_for_web(): void
    {
        config()->set('app.env', 'local');
        config()->set('session.driver', 'database');

        $response = $this->getJson('/api/auth/session');

        $response->assertOk()
            ->assertJsonPath('authenticated', true)
            ->assertJsonPath('user.username', 'admin')
            ->assertJsonPath('user.role', 'Super Admin');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
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
}
