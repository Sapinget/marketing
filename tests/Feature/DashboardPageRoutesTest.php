<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class DashboardPageRoutesTest extends TestCase
{
    /**
     * Halaman dashboard 1-2 MB: assertSee/assertStringContainsString mencetak seluruh isi saat gagal
     * dan membuat PHPUnit macet. Pakai pengecekan boolean dengan pesan singkat.
     */
    private function assertPageHas(string $html, string $needle, string $message = ''): void
    {
        $this->assertTrue(str_contains($html, $needle), $message !== '' ? $message : "Page should contain [{$needle}].");
    }

    private function assertPageLacks(string $html, string $needle, string $message = ''): void
    {
        $this->assertFalse(str_contains($html, $needle), $message !== '' ? $message : "Page should not contain [{$needle}].");
    }

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

    private function html(string $uri): string
    {
        return (string) $this->get($uri)->assertOk()->getContent();
    }

    public function test_dashboard_pages_are_discovered(): void
    {
        $this->assertGreaterThanOrEqual(13, count($this->dashboardPages()));
    }

    public function test_every_dashboard_page_renders_its_own_active_tab_without_cache(): void
    {
        foreach ($this->dashboardPages() as [$uri, $tab]) {
            $response = $this->get($uri);

            $response->assertOk();
            $this->assertPageHas((string) $response->getContent(), "activeTab === '{$tab}'", "{$uri} must render its own tab.");
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
        $this->assertSame(1, substr_count($settings, '/api/menu-visits'), 'menu visit POST must live in one helper.');
        $this->assertStringContainsString('trackMenuVisit(tab);', $settings);
        $this->assertStringContainsString('trackMenuVisit(activeTab.value);', $tail);
    }

    public function test_url_routing_flag_defaults_to_enabled(): void
    {
        $this->assertTrue(config('dashboard.url_routing'));
    }

    public function test_dedicated_page_renders_only_its_own_menu_and_script(): void
    {
        $page = $this->html('/ecommerce/tiktok-template');
        $this->assertPageHas($page, "activeTab === 'tiktok_template'");
        $this->assertPageHas($page, 'const ttFile');
        // Penanda khusus markup menu lain (sidebar/header memuat `activeTab === ...` umum, jadi tidak dipakai).
        $this->assertPageLacks($page, '<!-- Profile Setting View -->');
        $this->assertPageLacks($page, "activeTab === 'budgeting' && !budgetConfigLoaded");

        $legacy = $this->html('/');
        $this->assertPageHas($legacy, '<!-- Profile Setting View -->');
        $this->assertPageLacks($legacy, 'const ttFile');
        $this->assertPageLacks($legacy, 'ttDownloadAudit');
    }

    public function test_dedicated_pages_stay_within_the_size_budget(): void
    {
        // Anggaran: shell + script bersama (~0,85 MB) + satu menu. Baseline sebelum isolasi: 2,27 MB.
        // Turunkan batas ini saat script menu mulai diisolasi (Fase 1.5).
        $budgetBytes = 1_150_000;
        $legacySize = strlen($this->html('/'));

        foreach ($this->dashboardPages() as [$uri]) {
            $size = strlen($this->html($uri));

            $this->assertLessThan($budgetBytes, $size, "{$uri} exceeds the dedicated page size budget.");
            $this->assertLessThan($legacySize, $size, "{$uri} should be lighter than the legacy dashboard.");
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

    public function test_tab_url_map_matches_routes_exactly(): void
    {
        $fromRoutes = [];
        foreach ($this->dashboardPages() as [$uri, $tab]) {
            $fromRoutes[$tab] = $uri;
        }

        $fromConfig = config('dashboard.tab_urls');
        ksort($fromRoutes);
        ksort($fromConfig);

        $this->assertSame($fromRoutes, $fromConfig, 'config/dashboard.php tab_urls must list every dashboard page route.');
    }

    public function test_tab_urls_and_flag_are_exposed_to_the_shell_script(): void
    {
        $html = $this->html('/');

        $this->assertPageHas($html, 'const urlRouting = true;');
        $this->assertPageHas($html, '"\\/settings\\/users"');
        $this->assertPageHas($html, 'window.location.replace(_prefix + _migratedUrl)');
    }

    public function test_batch_a_menus_leave_the_legacy_dashboard_and_render_on_their_own_urls(): void
    {
        $legacy = $this->html('/');
        // Penanda markup/overlay milik tiap menu Batch A (bukan nama state, yang masih ada di script bersama).
        $markers = [
            '/tools/harga-kompetitor' => 'for="harga-kompetitor-nama-produk"',
            '/tools/laporan-event' => 'v-if="lpjkDetailModalOpen"',
            '/settings/nama-stock' => 'v-if="showNamaStockFormModal"',
        ];

        foreach ($markers as $uri => $marker) {
            $this->assertPageLacks($legacy, $marker, "/ must not render {$uri} markup.");
            $this->assertPageHas($this->html($uri), $marker, "{$uri} must render its own markup.");
        }
    }

    public function test_every_dedicated_page_keeps_global_overlays(): void
    {
        // Konfirmasi hapus (confirmModal) dan popover kalender dipakai semua menu.
        foreach (array_merge([['/', 'dashboard']], $this->dashboardPages()) as [$uri]) {
            $html = $this->html($uri);

            $this->assertPageHas($html, 'v-if="confirmModal.open"', "{$uri} lost the confirm modal.");
            $this->assertPageHas($html, 'v-if="calendarOpen"', "{$uri} lost the calendar popover.");
        }
    }

    public function test_flag_off_restores_migrated_menus_on_the_legacy_dashboard(): void
    {
        config(['dashboard.url_routing' => false]);

        $html = $this->html('/');

        $this->assertPageHas($html, 'const urlRouting = false;');
        $this->assertPageHas($html, 'for="harga-kompetitor-nama-produk"');
        $this->assertPageHas($html, 'v-if="showNamaStockFormModal"');
        $this->assertPageHas($html, 'v-if="keepBarangModalOpen"');
    }

    public function test_sidebar_links_to_batch_a_urls(): void
    {
        $sidebar = (string) file_get_contents(resource_path('views/dashboard/partials/shell/app-frame-sidebar-nav-admin.blade.php'));

        foreach (['harga_kompetitor', 'laporan_event', 'settings', 'nama_stock', 'auth_users', 'activity_logs'] as $tab) {
            $url = config('dashboard.tab_urls')[$tab];

            $this->assertStringContainsString('href="'.$url.'" @click="navigateTab($event, \''.$tab.'\')"', $sidebar);
            $this->assertStringNotContainsString("switchTab('{$tab}')", $sidebar);
        }
    }

    public function test_batch_b_menus_leave_the_legacy_dashboard_and_keep_their_modals(): void
    {
        $legacy = $this->html('/');
        $markers = [
            '/cs/order-online' => 'v-if="orderanOnlineModalOpen"',
            '/cs/unit-ditanya' => 'v-if="unitDitanyaModalOpen"',
            '/cs/claim-garansi' => 'v-if="claimGaransiModalOpen"',
            '/cs/keep-barang' => 'v-if="keepBarangModalOpen"',
            '/cs/service' => 'v-if="serviceModalOpen"',
        ];

        foreach ($markers as $uri => $marker) {
            $this->assertPageLacks($legacy, $marker, "/ must not render {$uri} markup.");
            $this->assertPageHas($this->html($uri), $marker, "{$uri} must render its own modal.");
        }
    }

    public function test_old_unit_ditanya_url_redirects_permanently(): void
    {
        $this->get('/unit_ditanya')->assertStatus(301)->assertRedirect('/cs/unit-ditanya');
    }

    public function test_sidebar_links_to_batch_b_urls(): void
    {
        $sidebar = (string) file_get_contents(resource_path('views/dashboard/partials/shell/app-frame-sidebar-nav-cs.blade.php'));

        foreach (['orderan_online', 'unit_ditanya', 'service', 'claim_garansi_asuransi', 'keep_barang'] as $tab) {
            $url = config('dashboard.tab_urls')[$tab];

            $this->assertStringContainsString('href="'.$url.'" @click="navigateTab($event, \''.$tab.'\')"', $sidebar);
            $this->assertStringNotContainsString("switchTab('{$tab}')", $sidebar);
        }
    }

    public function test_dashboard_page_helper_accepts_extra_views(): void
    {
        $shell = (string) file_get_contents(resource_path('views/dashboard/partials/shell/app-frame.blade.php'));
        $routes = (string) file_get_contents(base_path('routes/web.php'));

        $this->assertStringContainsString('dedicatedExtraViews', $shell);
        $this->assertStringContainsString('array $extraViews = []', $routes);
    }
}
