<?php

namespace Tests\Feature;

use Tests\TestCase;

class DashboardBatchCTest extends TestCase
{
    /** Halaman dashboard 1-2 MB: hindari assertSee/assertStringContainsString pada seluruh halaman. */
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

    /** @return array<string, string> uri => penanda markup milik menu */
    private function markers(): array
    {
        return [
            '/complain/input-claim' => 'name="input_claim_search"',
            '/complain/garansi-cermati' => 'name="garansi_cermati_search"',
            '/complain/garansi-resmi' => 'name="garansi_resmi_search"',
        ];
    }

    public function test_batch_c_menus_leave_the_legacy_dashboard_and_render_on_their_own_urls(): void
    {
        $legacy = $this->html('/');

        foreach ($this->markers() as $uri => $marker) {
            $this->assertPageLacks($legacy, $marker, "/ must not render {$uri} markup.");
            $page = $this->html($uri);
            $this->assertPageHas($page, $marker, "{$uri} must render its own markup.");
            $this->assertPageHas($page, 'v-if="confirmModal.open"', "{$uri} lost the confirm modal.");
            $this->assertPageHas($page, 'v-if="calendarOpen"', "{$uri} lost the calendar popover.");
        }
    }

    public function test_each_batch_c_page_renders_only_its_own_menu(): void
    {
        foreach ($this->markers() as $uri => $marker) {
            $page = $this->html($uri);

            foreach ($this->markers() as $otherUri => $otherMarker) {
                if ($otherUri !== $uri) {
                    $this->assertPageLacks($page, $otherMarker, "{$uri} must not render {$otherUri} markup.");
                }
            }
        }
    }

    public function test_tab_urls_and_route_names_are_registered(): void
    {
        $expected = [
            'input_claim' => ['/complain/input-claim', 'dashboard.complain.input-claim'],
            'garansi_cermati' => ['/complain/garansi-cermati', 'dashboard.complain.garansi-cermati'],
            'garansi_resmi' => ['/complain/garansi-resmi', 'dashboard.complain.garansi-resmi'],
        ];

        foreach ($expected as $tab => [$url, $name]) {
            $this->assertSame($url, config('dashboard.tab_urls')[$tab]);
            $this->assertSame($url, '/'.ltrim(route($name, [], false), '/'));
        }
    }

    public function test_sidebar_links_to_batch_c_urls(): void
    {
        $sidebar = (string) file_get_contents(resource_path('views/dashboard/partials/shell/app-frame-sidebar-nav-complain-tracker.blade.php'));

        foreach (['input_claim', 'garansi_cermati', 'garansi_resmi'] as $tab) {
            $url = config('dashboard.tab_urls')[$tab];

            $this->assertStringContainsString('href="'.$url.'" @click="navigateTab($event, \''.$tab.'\')"', $sidebar);
            $this->assertStringNotContainsString("switchTab('{$tab}')", $sidebar);
        }
    }

    public function test_flag_off_restores_batch_c_menus_on_the_legacy_dashboard(): void
    {
        config(['dashboard.url_routing' => false]);

        $html = $this->html('/');

        foreach ($this->markers() as $marker) {
            $this->assertPageHas($html, $marker);
        }
    }
}
