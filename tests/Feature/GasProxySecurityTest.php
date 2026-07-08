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

    public function test_proxy_routes_reject_requests_without_secret_when_configured(): void
    {
        config()->set('services.gas_proxy.secret', 'shared-secret');

        $this->get('/')->assertForbidden();
        $this->getJson('/api/master-plans')
            ->assertForbidden()
            ->assertJsonPath('message', 'Forbidden');
    }

    public function test_proxy_routes_accept_requests_with_matching_secret(): void
    {
        config()->set('services.gas_proxy.secret', 'shared-secret');

        $this->withHeader('X-GAS-PROXY-SECRET', 'shared-secret')
            ->get('/')
            ->assertOk()
            ->assertSee('Marketing Dashboard', false);

        $this->withHeader('X-GAS-PROXY-SECRET', 'shared-secret')
            ->getJson('/api/master-plans')
            ->assertOk()
            ->assertJsonPath('data', []);
    }
}
