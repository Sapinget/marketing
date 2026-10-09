<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class DashboardPageRoutesTest extends TestCase
{
    /**
     * @return array<string, array{0: string, 1: string}> uri => [uri, tab]
     */
    private function dashboardPages(): array
    {
        $pages = [];

        foreach (Route::getRoutes()->getRoutes() as $route) {
            $tab = $route->defaults['_dashboard_tab'] ?? null;

            if (is_string($tab) && in_array('GET', $route->methods(), true)) {
                $pages['/'.ltrim($route->uri(), '/')] = ['/'.ltrim($route->uri(), '/'), $tab];
            }
        }

        return $pages;
    }

    public function test_dashboard_pages_are_discovered(): void
    {
        $this->assertGreaterThanOrEqual(7, count($this->dashboardPages()));
    }

    public function test_every_dashboard_page_renders_its_own_active_tab_without_cache(): void
    {
        foreach ($this->dashboardPages() as [$uri, $tab]) {
            $response = $this->get($uri);

            $response->assertOk();
            $response->assertSee("activeTab === '{$tab}'", false);
            $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'), "{$uri} must not be cached.");
        }
    }

    public function test_dashboard_pages_are_public_shells_and_data_stays_behind_api_auth(): void
    {
        // Halaman hanya cangkang (login ditangani shell); data diamankan di API.
        foreach (['/api/auth/users', '/api/activity-logs', '/api/settings'] as $api) {
            $this->getJson($api)->assertStatus(401);
        }

        $this->putJson('/api/settings', [])->assertStatus(401);
    }

    public function test_trailing_slash_serves_the_same_page(): void
    {
        foreach ($this->dashboardPages() as [$uri]) {
            $this->get($uri.'/')->assertOk();
        }
    }

    public function test_menu_visit_is_tracked_on_direct_page_load_and_sidebar_switch(): void
    {
        $shell = resource_path('views/dashboard/partials/shell/');
        $settings = file_get_contents($shell.'app-script-protected-user-settings.blade.php');
        $tail = file_get_contents($shell.'app-script-runner-session-tail.blade.php');

        $this->assertIsString($settings);
        $this->assertIsString($tail);
        $this->assertSame(1, substr_count($settings, "/api/menu-visits"), 'menu visit POST must live in one helper.');
        $this->assertStringContainsString('trackMenuVisit(tab);', $settings);
        $this->assertStringContainsString('trackMenuVisit(activeTab.value);', $tail);
    }

    public function test_url_routing_flag_defaults_to_enabled(): void
    {
        $this->assertTrue(config('dashboard.url_routing'));
    }
}
