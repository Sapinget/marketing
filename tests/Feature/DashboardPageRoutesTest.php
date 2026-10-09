<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class DashboardPageRoutesTest extends TestCase
{
    /** Global browser/JS dan helper template yang sah dipakai ekspresi Vue tanpa dideklarasikan di setup(). */
    private const TEMPLATE_GLOBALS = [
        'window' => 1, 'document' => 1, 'Math' => 1, 'Number' => 1, 'String' => 1, 'Date' => 1, 'JSON' => 1, 'Array' => 1, 'Object' => 1,
        'console' => 1, 'localStorage' => 1, 'navigator' => 1, 'true' => 1, 'false' => 1, 'null' => 1, 'undefined' => 1, 'NaN' => 1,
        'Boolean' => 1, 'parseInt' => 1, 'parseFloat' => 1, 'isNaN' => 1, 'Intl' => 1, 'typeof' => 1, 'new' => 1, 'void' => 1, 'return' => 1,
        'event' => 1, '$event' => 1, '$refs' => 1, '$emit' => 1, '$nextTick' => 1, 'this' => 1, 'else' => 1, 'delete' => 1, 'function' => 1, 'let' => 1, 'const' => 1, 'var' => 1,
    ];

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
        $this->assertPageLacks($page, "<div v-if=\"activeTab === 'dashboard'\" class=\"space-y-4\">");
        $this->assertPageLacks($page, "activeTab === 'budgeting' && !budgetConfigLoaded");

        $legacy = $this->html('/');
        $this->assertPageHas($legacy, "<div v-if=\"activeTab === 'dashboard'\" class=\"space-y-4\">");
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

            // Nama yang diekspor script menu (`Object.assign(menuExports, { ... })`) juga harus dideklarasikan.
            if (preg_match_all('/Object\.assign\(menuExports,\s*\{(.*?)\}\s*\)/s', $js, $exports)) {
                foreach ($exports[1] as $list) {
                    foreach (array_filter(array_map('trim', explode(',', $list))) as $name) {
                        if (preg_match('/^[A-Za-z_$][\w$]*$/', $name) === 1 && ! isset($declared[$name])) {
                            $missing[$uri][] = $name;
                        }
                    }
                }
            }

            $returnAt = strrpos($js, 'return {');
            $this->assertNotFalse($returnAt, "{$uri} has no setup return");
            $body = substr($js, $returnAt + 8);
            $body = substr($body, 0, (int) strpos($body, "\n                };"));

            foreach (explode("\n", $body) as $line) {
                // Satu baris bisa memuat beberapa nama (`a, b, c,`).
                if (preg_match('/^\s*([A-Za-z_$][\w$]*(?:\s*,\s*[A-Za-z_$][\w$]*)*)\s*,?\s*$/', $line, $mm)) {
                    foreach (array_map('trim', explode(',', $mm[1])) as $name) {
                        if (! isset($declared[$name])) {
                            $missing[$uri][] = $name;
                        }
                    }
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
        // Script per menu (`menu-script-<menu>`) dimuat otomatis oleh App\Support\MenuScripts.
        foreach (glob($shellDir.'menu-script-*.blade.php') ?: [] as $unit) {
            $isolated[basename($unit, '.blade.php')] = true;
        }
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

    public function test_menu_templates_have_no_inline_style_or_script_tags(): void
    {
        // Vue mengabaikan <style>/<script> di dalam template ("Tags with side effect ... are ignored"); CSS harus di resources/css.
        foreach (glob(resource_path('views/dashboard/partials/menus/*.blade.php')) ?: [] as $path) {
            $source = (string) file_get_contents($path);

            $this->assertSame(0, preg_match('/<(style|script)\b/i', $source), basename($path).' contains an inline <style>/<script> tag.');
        }
    }

    public function test_denied_user_management_redirect_keeps_its_notice_across_the_reload(): void
    {
        $dir = resource_path('views/dashboard/partials/shell/');
        $settings = (string) file_get_contents($dir.'app-script-protected-user-settings.blade.php');
        $watchers = (string) file_get_contents($dir.'app-script-lifecycle-watchers.blade.php');
        $tail = (string) file_get_contents($dir.'app-script-runner-session-tail.blade.php');

        $this->assertStringContainsString("sessionStorage.setItem(FLASH_NOTICE_KEY", $settings);
        $this->assertStringContainsString("redirectWithNotice('settings'", $settings);
        $this->assertStringContainsString("redirectWithNotice('settings'", $watchers);
        $this->assertStringContainsString('showFlashNotice();', $tail);
        $this->assertSame(2, substr_count($settings.$watchers, 'Akses manajemen user hanya untuk Super Admin'), 'one message per guard (switchTab + watcher)');
    }

    public function test_templates_never_assign_active_tab_directly(): void
    {
        // `@click="activeTab = 'x'"` mengganti tab tanpa membuka URL-nya; menu yang sudah dimigrasi akan kosong.
        // Pakai switchTab('x') agar tab ber-URL dibuka lewat URL-nya.
        foreach (glob(resource_path('views/dashboard/partials/{menus,shell}/*.blade.php'), GLOB_BRACE) ?: [] as $path) {
            $source = (string) file_get_contents($path);

            $this->assertSame(0, preg_match('/@click="[^"]*\bactiveTab\s*=\s*[\'"]/', $source), basename($path).' assigns activeTab directly in a template.');
        }
    }

    public function test_hidden_menus_have_their_own_urls_and_root_renders_only_the_dashboard(): void
    {
        $tabs = ['bonus_report', 'talent_bonus', 'editor_performance', 'market_pasar', 'market_intelijen_harga', 'market_audit_harga',
            'market_eksternal', 'market_ext_goodponsel', 'market_ext_devstore', 'market_ext_rumahgadget',
            'top_content_platform', 'low_content_platform', 'analisa_insight', 'profile'];

        foreach ($tabs as $tab) {
            $this->assertArrayHasKey($tab, config('dashboard.tab_urls'));
        }

        $root = $this->html('/');
        $this->assertPageHas($root, "<div v-if=\"activeTab === 'dashboard'\" class=\"space-y-4\">");
        $this->assertPageLacks($root, '<!-- Profile Setting View -->');
        $this->assertPageLacks($root, '<!-- Talent Bonus tab -->');
        $this->assertPageHas($this->html('/profil'), '<!-- Profile Setting View -->');
        $this->assertPageHas($this->html('/performa/talent-bonus'), '<!-- Talent Bonus tab -->');
    }

    public function test_flag_off_restores_hidden_menus_on_root(): void
    {
        config(['dashboard.url_routing' => false]);
        $root = $this->html('/');

        $this->assertPageHas($root, '<!-- Profile Setting View -->');
        $this->assertPageHas($root, '<!-- Talent Bonus tab -->');
        $this->assertPageHas($root, 'v-if="modalOpen"');
    }

    public function test_created_rows_take_their_id_from_the_api_response(): void
    {
        // API membalas {status, data: {source_id, ...}}; membaca `res.id` saja memberi id sementara (HK..., AVI..., LJ...)
        // sehingga ubah/hapus baris yang baru dibuat menghasilkan 404 sampai halaman dimuat ulang.
        $sources = [
            resource_path('views/dashboard/partials/shell/app-script-price-competitor-operations.blade.php'),
            resource_path('views/dashboard/partials/shell/app-script-asset-vendor-inventory-operations.blade.php'),
            resource_path('js/dashboard/menu/lpjk.js'),
        ];

        foreach ($sources as $path) {
            $source = (string) file_get_contents($path);

            $this->assertStringContainsString('res.data.source_id', $source, basename($path).' must read the new id from data.source_id.');
        }
    }

    /**
     * Penjaga "1 menu = 1 script": setiap nama yang dipakai ekspresi Vue di markup sebuah halaman (`@click`, `:class`, `v-if`,
     * `{{ }}`) harus dideklarasikan di script halaman itu, kecuali variabel lokal template (alias v-for, parameter arrow) dan
     * global browser/Vue (TEMPLATE_GLOBALS). Kalau tidak, template membaca properti yang tidak ada (diam-diam `undefined` di
     * build produksi Vue). Memindahkan script antar menu lalu lupa memuatnya langsung ketahuan di sini.
     */
    private function unresolvedTemplateNames(bool $flagOn = true, bool $strict = true): array
    {
        config(['dashboard.url_routing' => $flagOn]);

        // Semua nama yang dideklarasikan di partial script mana pun.
        $everywhere = [];
        $partials = array_merge(
            glob(resource_path('views/dashboard/partials/shell/*.blade.php')) ?: [],
            glob(resource_path('views/dashboard/partials/scripts/*.blade.php')) ?: []
        );
        foreach ($partials as $path) {
            if (! str_contains(basename($path), 'script') && ! str_contains($path, '/scripts/')) {
                continue;
            }
            preg_match_all('/^\s{0,20}(?:const|let|var|(?:async\s+)?function)\s+([A-Za-z_$][\w$]{3,})/m', (string) file_get_contents($path), $m);
            $everywhere += array_flip($m[1]);
        }

        $unresolved = [];
        foreach (array_merge([['/', 'dashboard']], array_values($this->dashboardPages())) as [$uri]) {
            $html = $this->html($uri);
            preg_match('#<script type="module">(.*?)</script>#s', $html, $mod);
            $script = $mod[1] ?? '';
            $markup = str_replace($script, '', $html);

            $declared = [];
            preg_match_all('/\b(?:const|let|var)\s+([A-Za-z_$][\w$]*)|\bfunction\s+([A-Za-z_$][\w$]*)/', $script, $d);
            $declared = array_flip(array_filter(array_merge($d[1], $d[2])));
            preg_match_all('/\b(?:const|let|var)\s*\{([^}]*)\}/', $script, $d2);
            foreach ($d2[1] as $group) {
                foreach (explode(',', $group) as $part) {
                    $segments = explode(':', $part);
                    $declared[trim(preg_replace('/=.*/s', '', end($segments)))] = 1;
                }
            }

            // Ekspresi Vue: atribut @x / :x / v-x dan {{ ... }}.
            $expressions = [];
            if (preg_match_all('/\s(?:@|:|v-)[\w.:-]+="([^"]*)"/', $markup, $a)) {
                $expressions = array_merge($expressions, $a[1]);
            }
            if (preg_match_all('/\{\{(.*?)\}\}/s', $markup, $b)) {
                $expressions = array_merge($expressions, $b[1]);
            }

            // Variabel lokal template (alias v-for, parameter arrow function, slot) bukan state setup().
            $locals = [];
            if (preg_match_all('/v-for="\\(?\\s*([^")]*?)\\s*\\)?\\s+(?:in|of)\\s/', $markup, $f)) {
                foreach ($f[1] as $aliases) {
                    foreach (explode(',', $aliases) as $alias) {
                        $locals[trim($alias)] = true;
                    }
                }
            }
            foreach ($expressions as $expression) {
                if (preg_match_all('/(?:\\(([\\w$,\\s{}]*)\\)|\\b([A-Za-z_$][\\w$]*))\\s*=>/', $expression, $arrows)) {
                    foreach (array_merge($arrows[1], $arrows[2]) as $params) {
                        foreach (preg_split('/[,\\s{}]+/', (string) $params) ?: [] as $param) {
                            $locals[$param] = true;
                        }
                    }
                }
            }

            foreach (array_unique($expressions) as $expression) {
                $clean = str_replace('...', ' ', $expression);
                // Teks literal template (`abc-${x}`) dibuang, isi ${...} dipertahankan; string biasa dibuang; kunci objek ({ a: 1 }) bukan referensi.
                $clean = preg_replace_callback('/`([^`]*)`/s', fn (array $m) => ' '.implode(' ', preg_match_all('/\\$\\{([^}]*)\\}/', $m[1], $inner) ? $inner[1] : []).' ', (string) $clean);
                $clean = preg_replace('/\'[^\']*\'|"[^"]*"/', ' ', (string) $clean);
                $clean = preg_replace('/([{,]\\s*)[A-Za-z_$][\\w$]*\\s*:/', '$1 ', (string) $clean);
                preg_match_all('/(?<![\w$.])([A-Za-z_$][\w$]{3,})/', (string) $clean, $ids);
                foreach (array_unique($ids[1]) as $id) {
                    if (($strict || isset($everywhere[$id])) && ! isset($declared[$id]) && ! isset($locals[$id]) && ! ($strict && isset(self::TEMPLATE_GLOBALS[$id]))) {
                        $unresolved[$uri][$id] = true;
                    }
                }
            }
        }

        return array_map(fn (array $ids) => array_keys($ids), $unresolved);
    }

    public function test_every_template_name_that_comes_from_a_script_is_declared_on_that_page(): void
    {
        $this->assertSame([], $this->unresolvedTemplateNames());
    }

    public function test_every_template_name_is_declared_on_every_page_with_the_flag_off(): void
    {
        $this->assertSame([], $this->unresolvedTemplateNames(false));
    }
}
