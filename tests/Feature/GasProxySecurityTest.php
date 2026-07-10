<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GasProxySecurityTest extends TestCase
{
    use RefreshDatabase;

    protected bool $authenticateDashboard = false;

    public function test_health_endpoint_returns_status_payload(): void
    {
        $this->getJson('/health')
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('app', 'marketing-dashboard')
            ->assertJsonStructure(['timestamp']);
    }

    public function test_dashboard_shell_is_public_without_gas_proxy_secret(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Marketing Dashboard', false);
    }

    public function test_dashboard_api_does_not_accept_legacy_gas_proxy_secret_header(): void
    {
        $this->withHeader('X-GAS-PROXY-SECRET', 'legacy-secret')
            ->getJson('/api/master-plans')
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Unauthenticated.');
    }

    public function test_db_preview_routes_are_local_only_even_with_legacy_gas_header(): void
    {
        config()->set('app.env', 'production');

        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.5'])
            ->withHeader('X-GAS-PROXY-SECRET', 'legacy-secret')
            ->get('/__db/tables')
            ->assertNotFound();
    }

    public function test_db_preview_blocks_sensitive_tables_in_local(): void
    {
        config()->set('app.env', 'local');

        $this->get('/__db/tables/users')
            ->assertForbidden()
            ->assertSee('Preview tabel sensitif tidak diizinkan.');
    }

    public function test_db_preview_access_is_logged_in_local(): void
    {
        config()->set('app.env', 'local');

        $this->get('/__db/tables')
            ->assertOk();

        $this->assertDatabaseHas('activity_logs', [
            'table_name' => '__db',
            'action' => 'browse',
            'record_key' => 'tables',
            'actor_label' => 'local-db-preview',
        ]);
    }
}
