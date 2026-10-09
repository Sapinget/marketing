<?php

namespace Tests\Feature;

use Tests\TestCase;

class DashboardBatchF2Test extends TestCase
{
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

    private const MASTER_MARKER = '<div v-if="activeTab === \'master\'" class="space-y-4 animate-fadeIn">';

    public function test_master_plan_has_its_own_url_with_the_content_modal(): void
    {
        $page = $this->html('/konten/master-plan');

        $this->assertPageHas($page, self::MASTER_MARKER);
        $this->assertPageHas($page, 'v-if="modalOpen"', 'master plan opens the generic content modal');
        $this->assertPageHas($page, 'v-if="confirmModal.open"');
        $this->assertPageLacks($page, '<!-- Profile Setting View -->');
        $this->assertSame('/konten/master-plan', config('dashboard.tab_urls')['master']);
    }

    public function test_master_plan_leaves_the_legacy_dashboard_but_dashboard_stays_on_root(): void
    {
        $legacy = $this->html('/');

        $this->assertPageLacks($legacy, self::MASTER_MARKER);
        $this->assertPageLacks($legacy, 'v-if="modalOpen"', 'content modal only travels with the pages that need it');
        $this->assertArrayNotHasKey('dashboard', config('dashboard.tab_urls'), 'dashboard tab lives on /');
    }

    public function test_flag_off_restores_master_plan_on_root(): void
    {
        config(['dashboard.url_routing' => false]);

        $this->assertPageHas($this->html('/'), self::MASTER_MARKER);
    }

    public function test_sidebar_links_master_plan_to_its_url(): void
    {
        $sidebar = (string) file_get_contents(resource_path('views/dashboard/partials/shell/app-frame-sidebar-nav-dashboard-content.blade.php'));

        $this->assertStringContainsString('href="/konten/master-plan" @click="navigateTab($event, \'master\')"', $sidebar);
        $this->assertStringNotContainsString("switchTab('master')", $sidebar);
        $this->assertStringContainsString("switchTab('dashboard')", $sidebar);
    }
}
