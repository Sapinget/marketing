<?php

namespace Tests\Feature;

use Tests\TestCase;

class DashboardBatchDTest extends TestCase
{
    /** Halaman dashboard 1-2 MB: jangan pakai assertSee/assertStringContainsString pada HTML penuh. */
    private function assertPageHas(string $html, string $needle, string $message = ''): void
    {
        $this->assertTrue(str_contains($html, $needle), $message !== '' ? $message : "Page should contain [{$needle}].");
    }

    private function assertPageLacks(string $html, string $needle, string $message = ''): void
    {
        $this->assertFalse(str_contains($html, $needle), $message !== '' ? $message : "Page should not contain [{$needle}].");
    }

    private function html(string $uri): string
    {
        return (string) $this->get($uri)->assertOk()->getContent();
    }

    /** @return array<string, array{0: string, 1: string, 2: string}> */
    private function pages(): array
    {
        return [
            'program_promo' => ['/marketing/program-promo', 'v-if="promoModalOpen"', 'program_promo'],
            'sell_out' => ['/marketing/sell-out', 'v-if="sellOutModalOpen"', 'sell_out'],
            'ads_log' => ['/marketing/ads-log', 'v-if="adsModalOpen"', 'ads_log'],
            'budgeting' => ['/marketing/budgeting', "activeTab === 'budgeting' && budgetConfigLoaded", 'budgeting'],
        ];
    }

    public function test_batch_d_pages_render_their_own_markup(): void
    {
        foreach ($this->pages() as [$uri, $marker, $tab]) {
            $html = $this->html($uri);

            $this->assertPageHas($html, $marker, "{$uri} must render its own markup.");
            $this->assertPageHas($html, "activeTab === '{$tab}'", "{$uri} must render its active tab.");
        }
    }

    public function test_batch_d_menus_leave_the_legacy_dashboard(): void
    {
        $legacy = $this->html('/');

        foreach ($this->pages() as [$uri, $marker]) {
            $this->assertPageLacks($legacy, $marker, "/ must not render {$uri} markup.");
        }
    }

    public function test_flag_off_restores_batch_d_menus_on_the_legacy_dashboard(): void
    {
        config(['dashboard.url_routing' => false]);
        $legacy = $this->html('/');

        foreach ($this->pages() as [$uri, $marker]) {
            $this->assertPageHas($legacy, $marker, "Flag off must render {$uri} markup on /.");
        }
    }

    public function test_public_promo_route_is_untouched(): void
    {
        $this->get('/promo')->assertOk();
    }

    public function test_sidebar_links_to_batch_d_urls(): void
    {
        $sidebar = (string) file_get_contents(resource_path('views/dashboard/partials/shell/app-frame-sidebar-nav-marketing.blade.php'));

        foreach (['program_promo', 'sell_out', 'ads_log', 'budgeting'] as $tab) {
            $url = config('dashboard.tab_urls')[$tab];

            $this->assertStringContainsString('href="'.$url.'" @click="navigateTab($event, \''.$tab.'\')"', $sidebar);
            $this->assertStringNotContainsString("switchTab('{$tab}')", $sidebar);
        }
    }
}
