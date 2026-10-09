<?php

namespace Tests\Feature;

use Tests\TestCase;

class DedicatedMenuBladeViewsTest extends TestCase
{
    public function test_each_external_market_source_has_its_own_page_with_its_menu(): void
    {
        $pages = [
            '/intelijen/kompetitor' => ["activeTab === 'market_eksternal'", 'Summary Eksternal'],
            '/intelijen/kompetitor/good-ponsel' => ["activeTab === 'market_ext_goodponsel'", 'Good Ponsel'],
            '/intelijen/kompetitor/devstore' => ["activeTab === 'market_ext_devstore'", 'Devstore'],
            '/intelijen/kompetitor/rumah-gadget' => ["activeTab === 'market_ext_rumahgadget'", 'Rumah Gadget Bali'],
        ];

        foreach ($pages as $uri => [$marker, $title]) {
            $html = (string) $this->get($uri)->assertOk()->getContent();

            $this->assertTrue(str_contains($html, $marker), "{$uri} must render {$marker}");
            $this->assertTrue(str_contains($html, $title), "{$uri} must mention {$title}");
        }
    }

    public function test_external_market_navigation_has_source_mapping(): void
    {
        // Pemetaan tab -> sumber ada di script market-intel, yang dimuat di halaman market (dan di `/` bila flag mati).
        $content = (string) $this->get('/intelijen/kompetitor/good-ponsel')->assertOk()->getContent();

        $this->assertMatchesRegularExpression("/market_ext_goodponsel:\s*'goodponsel'/", $content);
        $this->assertMatchesRegularExpression("/market_ext_devstore:\s*'devstore'/", $content);
        $this->assertMatchesRegularExpression("/market_ext_rumahgadget:\s*'rumahgadget'/", $content);
    }

    public function test_proses_claim_navigation_loads_service_claim_data(): void
    {
        $navigation = file_get_contents(resource_path('views/dashboard/partials/shell/app-script-protected-user-settings.blade.php'));

        $this->assertIsString($navigation);
        $this->assertStringContainsString("proses_claim: 'serviceClaims'", $navigation);
        $this->assertStringContainsString('if (tabDataKey) loadTabData(tabDataKey);', $navigation);
    }

    public function test_no_menu_blade_handles_multiple_active_tabs(): void
    {
        $dir = resource_path('views/dashboard/partials/menus');
        $files = array_filter(glob($dir.'/*.blade.php'), 'is_file');

        foreach ($files as $file) {
            $content = file_get_contents($file);
            $filename = basename($file);

            // Ensure no root menu blade file checks an array of tabs
            $this->assertFalse(
                (bool) preg_match('/\[[^\]]+\]\.includes\(\s*activeTab\s*\)/', $content),
                "Menu blade file {$filename} still handles multiple tabs via array includes."
            );

            // Extract all tab matches
            preg_match_all('/activeTab\s*===\s*[\'"]([^\'"]+)[\'"]/', $content, $matches);
            $tabs = array_unique($matches[1] ?? []);

            // Each menu file should represent only 1 primary activeTab
            $this->assertLessThanOrEqual(
                1,
                count($tabs),
                "Menu blade {$filename} handles multiple distinct activeTab values: ".implode(', ', $tabs)
            );
        }
    }

    public function test_cluster_8_dedicated_routes_render_expected_blade_views(): void
    {
        $routes = [
            '/katalog/android' => "activeTab === 'pricelist_katalog'",
            '/katalog/apple' => "activeTab === 'apple_katalog'",
            '/katalog/template-background' => "activeTab === 'template_background'",
            '/repository-gambar' => "activeTab === 'img_repo'",
            '/inventory/asset-vendor' => "activeTab === 'asset_vendor_inventory'",
            '/promo-pamflet' => "activeTab === 'promo_pamflet'",
        ];

        foreach ($routes as $route => $needle) {
            $response = $this->get($route);
            $this->assertSame(200, $response->getStatusCode(), "Failed on route: {$route}");
            $this->assertTrue(str_contains($response->getContent(), $needle), "Route {$route} missing needle: {$needle}");
        }
    }
}
