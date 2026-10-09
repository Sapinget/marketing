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

    public function test_dedicated_page_renders_only_its_own_menu_and_script(): void
    {
        $page = $this->get('/ecommerce/tiktok-template')->assertOk();
        $page->assertSee("activeTab === 'tiktok_template'", false);
        $page->assertSee('const ttFile', false);
        // Penanda khusus markup menu lain (sidebar/header memuat `activeTab === ...` umum, jadi tidak dipakai).
        $page->assertDontSee('<!-- Budgeting tab -->', false);
        $page->assertDontSee("activeTab === 'budgeting' && !budgetConfigLoaded", false);

        $legacy = $this->get('/')->assertOk();
        $legacy->assertSee('<!-- Budgeting tab -->', false);
        $legacy->assertDontSee('const ttFile', false);
        $legacy->assertDontSee('ttDownloadAudit', false);
    }

    public function test_dedicated_pages_are_much_lighter_than_the_legacy_dashboard(): void
    {
        $legacySize = strlen($this->get('/')->getContent());

        foreach ($this->dashboardPages() as [$uri]) {
            $this->assertLessThan($legacySize * 0.6, strlen($this->get($uri)->getContent()), "{$uri} should render far less than the full dashboard.");
        }
    }

    public function test_isolated_menu_script_registers_through_menu_exports(): void
    {
        $shell = resource_path('views/dashboard/partials/shell/');
        $script = file_get_contents($shell.'app-script-tiktok-template-operations.blade.php');
        $returnBlock = file_get_contents($shell.'app-script-return-block.blade.php');
        $assembly = file_get_contents($shell.'body-app-assembly.blade.php');

        $this->assertIsString($script);
        $this->assertIsString($returnBlock);
        $this->assertIsString($assembly);
        $this->assertStringContainsString('Object.assign(menuExports, {', $script);
        $this->assertStringContainsString('...menuExports,', $returnBlock);
        $this->assertStringNotContainsString('ttFile', $returnBlock);
        $this->assertStringContainsString("@stack('menu-scripts')", $assembly);
        $this->assertStringNotContainsString('app-script-tiktok-template-operations', $assembly);
    }
}
