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
        // Anggaran: shell + script bersama + satu menu (+ script terisolasi miliknya). Baseline sebelum isolasi: 2,27 MB;
        // halaman biasa kini ±0,7 MB, halaman katalog ±0,94 MB karena membawa ±225 KB script katalog.
        $budgetBytes = 1_000_000;
        foreach ($this->dashboardPages() as [$uri]) {
            $size = strlen($this->html($uri));

            $this->assertLessThan($budgetBytes, $size, "{$uri} exceeds the dedicated page size budget.");
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

        foreach (['orderan_online', 'unit_ditanya', 'claim_garansi_asuransi', 'keep_barang'] as $tab) {
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

    public function test_service_menu_is_removed(): void
    {
        $this->get('/cs/service')->assertNotFound();
        $this->assertArrayNotHasKey('service', config('dashboard.tab_urls'));
        $this->assertFileDoesNotExist(resource_path('views/dashboard/partials/menus/service.blade.php'));
        $this->assertStringNotContainsString("navigateTab(\$event, 'service')", (string) file_get_contents(resource_path('views/dashboard/partials/shell/app-frame-sidebar-nav-cs.blade.php')));
    }

    /**
     * Menu yang sudah punya URL sebelum migrasi hash: markup root-nya (bukan teks sidebar/komentar).
     *
     * @return array<string, string> uri => penanda
     */
    private function preExistingUrlMenus(): array
    {
        return [
            '/katalog/android' => '<div v-if="activeTab === \'pricelist_katalog\'" class="space-y-4 animate-fadeIn xl:h-',
            '/katalog/template-background' => '<div v-if="activeTab === \'template_background\'" class="space-y-4 animate-fadeIn xl:h-',
            '/katalog/apple' => '<div v-show="activeTab === \'apple_katalog\'" class="space-y-4 animate-fadeIn xl:h-',
            '/repository-gambar' => '<div v-show="activeTab === \'img_repo\'"',
            '/inventory/asset-vendor' => '<!-- Asset Vendor Inventory tab -->',
            '/promo-pamflet' => '<!-- Promo Pamflet tab -->',
        ];
    }

    public function test_menus_with_pre_existing_urls_leave_the_legacy_dashboard(): void
    {
        $legacy = $this->html('/');

        foreach ($this->preExistingUrlMenus() as $uri => $marker) {
            $this->assertPageLacks($legacy, $marker, "/ must not render {$uri} markup.");
            $this->assertPageHas($this->html($uri), $marker, "{$uri} must render its own markup.");
        }
    }

    public function test_flag_off_restores_pre_existing_url_menus_on_the_legacy_dashboard(): void
    {
        config(['dashboard.url_routing' => false]);
        $legacy = $this->html('/');

        foreach ($this->preExistingUrlMenus() as $uri => $marker) {
            $this->assertPageHas($legacy, $marker, "flag off: / should render {$uri} markup again.");
        }
    }

    /**
     * Penjaga isolasi script: setiap nama di `return { ... }` setup() harus dideklarasikan di script halaman itu.
     * Kalau tidak, setup() melempar ReferenceError dan seluruh halaman kosong (pernah terjadi saat memindahkan script katalog).
     *
     * @return array<string, list<string>> uri => nama yang di-return tapi tidak dideklarasikan
     */
    private function undeclaredReturnNames(): array
    {
        $uris = array_merge(['/'], array_map(fn (array $p) => $p[0], array_values($this->dashboardPages())));
        $missing = [];

        foreach ($uris as $uri) {
            $html = $this->html($uri);
            $this->assertSame(1, preg_match('#<script type="module">(.*?)</script>#s', $html, $m), "{$uri} has no module script");
            $js = $m[1];

            $declared = [];
            preg_match_all('/\b(?:const|let|var)\s+([A-Za-z_$][\w$]*)/', $js, $a);
            $declared += array_flip($a[1]);
            preg_match_all('/\bfunction\s+([A-Za-z_$][\w$]*)/', $js, $a);
            $declared += array_flip($a[1]);
            preg_match_all('/\b(?:const|let|var)\s*[\{\[]([^\}\]]*)[\}\]]/', $js, $a);
            foreach ($a[1] as $group) {
                foreach (explode(',', $group) as $part) {
                    $segments = explode(':', $part);
                    $name = trim(preg_replace('/=.*/s', '', end($segments)));
                    if ($name !== '') {
                        $declared[$name] = 1;
                    }
                }
            }

            $returnAt = strrpos($js, 'return {');
            $this->assertNotFalse($returnAt, "{$uri} has no setup return");
            $body = substr($js, $returnAt + 8);
            $body = substr($body, 0, (int) strpos($body, "\n                };"));

            foreach (explode("\n", $body) as $line) {
                if (preg_match('/^\s*([A-Za-z_$][\w$]*),\s*$/', $line, $mm) && ! isset($declared[$mm[1]])) {
                    $missing[$uri][] = $mm[1];
                }
            }
        }

        return $missing;
    }

    public function test_every_page_declares_everything_its_setup_returns(): void
    {
        $this->assertSame([], $this->undeclaredReturnNames());
    }

    public function test_every_page_declares_everything_its_setup_returns_with_the_flag_off(): void
    {
        config(['dashboard.url_routing' => false]);

        $this->assertSame([], $this->undeclaredReturnNames());
    }

    public function test_shared_scripts_never_reference_names_declared_only_in_isolated_scripts(): void
    {
        $shellDir = resource_path('views/dashboard/partials/shell/');
        $isolated = [];

        foreach (glob($shellDir.'menu-scripts-*.blade.php') ?: [] as $wrapper) {
            preg_match_all("/@include\('dashboard\.partials\.shell\.([\w-]+)'\)/", (string) file_get_contents($wrapper), $m);
            foreach ($m[1] as $partial) {
                $isolated[$partial] = true;
            }
        }
        // market-intelligence hanya dimuat di `/` (menu tersembunyi) lewat @unless di body-app-assembly.
        $isolated['app-script-market-intelligence-operations'] = true;
        $this->assertNotEmpty($isolated);

        // Nama method pada objek API runner (`saveAvi(data) {}`) bukan referensi ke variabel setup().
        $apiMethodKeys = ['deleteCatalogTemplate', 'saveCatalogTemplate', 'syncPricelistProducts', 'deleteAvi', 'saveAvi'];

        $shared = [];
        foreach (glob($shellDir.'app-script-*.blade.php') ?: [] as $path) {
            $name = basename($path, '.blade.php');
            if (! isset($isolated[$name]) && $name !== 'app-script-return-block') {
                $shared[$name] = (string) file_get_contents($path);
            }
        }

        $leaks = [];
        foreach (array_keys($isolated) as $partial) {
            $source = (string) file_get_contents($shellDir.$partial.'.blade.php');
            preg_match_all('/^ {16,17}(?:const|let|var|async function|function)\s+([A-Za-z_$][\w$]*)/m', $source, $d);

            foreach (array_unique($d[1]) as $name) {
                if (strlen($name) < 3 || in_array($name, $apiMethodKeys, true)) {
                    continue;
                }

                foreach ($shared as $sharedName => $sharedSource) {
                    if (preg_match('/(?<![\w$.])'.preg_quote($name, '/').'(?![\w$])/', $sharedSource) === 1) {
                        $leaks[] = "{$sharedName} uses {$name} (declared in {$partial})";
                    }
                }
            }
        }

        $this->assertSame([], $leaks);
    }
}
