<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConfigApiSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected bool $authenticateDashboard = false;

    public function test_bonus_and_budgeting_config_routes_require_settings_access(): void
    {
        foreach (['/api/bonus-config', '/api/budgeting-config'] as $uri) {
            $this->getJson($uri)->assertUnauthorized();
            $this->putJson($uri, ['meta' => ['costPerAd' => 1000]])->assertUnauthorized();
        }

        $this->actingAsDashboardUser(['role' => 'operasional']);

        foreach (['/api/bonus-config', '/api/budgeting-config'] as $uri) {
            $this->getJson($uri)->assertForbidden();
            $this->putJson($uri, ['meta' => ['costPerAd' => 1000]])->assertForbidden();
        }
    }

    public function test_settings_manager_can_save_object_configs_but_not_lists_or_oversized_payloads(): void
    {
        $this->actingAsDashboardUser(['role' => 'admin']);

        $this->putJson('/api/bonus-config', [
            'reelsNonColab' => [['minViews' => 1000, 'bonus' => 50000]],
            'engagement' => ['instagram' => ['likeUnit' => 100]],
        ])->assertOk()
            ->assertJsonPath('data.engagement.instagram.likeUnit', 100);

        $this->assertDatabaseHas('marketing_settings', [
            'key' => 'BONUS_CONFIG',
        ]);

        $this->putJson('/api/budgeting-config', [
            ['costPerAd' => 1000],
        ])->assertUnprocessable();

        $this->putJson('/api/budgeting-config', [
            'notes' => str_repeat('a', 65537),
        ])->assertUnprocessable();
    }
}
