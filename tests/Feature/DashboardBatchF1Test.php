<?php

namespace Tests\Feature;

use Tests\TestCase;

class DashboardBatchF1Test extends TestCase
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

    private function html(string $uri): string
    {
        return (string) $this->get($uri)->assertOk()->getContent();
    }

    /**
     * @return array<string, array{0: string, 1: string}> tab => [uri, penanda markup milik menu]
     */
    private function pages(): array
    {
        return [
            'unboxing' => ['/konten/unboxing', 'v-if="unboxingModalOpen"'],
            'ideation' => ['/konten/ideation', "<div v-if=\"activeTab === 'ideation'\" class=\"space-y-4 animate-fadeIn\">"],
            'distribution' => ['/konten/distribution', 'v-if="distModalOpen"'],
            'analytics' => ['/konten/analytics', 'v-if="analyticsModalOpen"'],
            'calendar' => ['/konten/calendar', 'v-if="calendarDayModalOpen"'],
            'story' => ['/konten/story', 'v-if="storyModalOpen"'],
        ];
    }

    public function test_tab_urls_and_route_names_are_registered(): void
    {
        foreach ($this->pages() as $tab => [$uri]) {
            $this->assertSame($uri, config("dashboard.tab_urls.{$tab}"));
            $this->assertSame($uri, '/'.ltrim(route("dashboard.konten.{$tab}", [], false), '/'));
        }
    }

    public function test_each_page_renders_its_own_menu_and_the_legacy_dashboard_does_not(): void
    {
        $legacy = $this->html('/');

        foreach ($this->pages() as $tab => [$uri, $marker]) {
            $page = $this->html($uri);
            $this->assertPageHas($page, "activeTab === '{$tab}'", "{$uri} must render its tab.");
            $this->assertPageHas($page, $marker, "{$uri} must render its own markup.");
            $this->assertPageLacks($legacy, $marker, "/ must not render {$uri} markup.");
        }
    }

    public function test_pages_do_not_render_other_menus_of_the_batch(): void
    {
        $pages = $this->pages();

        foreach ($pages as $tab => [$uri]) {
            $html = $this->html($uri);
            foreach ($pages as $otherTab => [, $marker]) {
                // Modal story ikut dirender di kalender (kalender membuka openEditStoryModal).
                if ($otherTab === $tab || ($tab === 'calendar' && $otherTab === 'story')) {
                    continue;
                }
                $this->assertPageLacks($html, $marker, "{$uri} must not render the {$otherTab} menu.");
            }
        }
    }

    public function test_pages_that_open_the_generic_content_modal_include_it(): void
    {
        foreach (['/konten/ideation', '/konten/calendar'] as $uri) {
            $html = $this->html($uri);
            $this->assertPageHas($html, 'Riwayat Konten Colab', "{$uri} needs the colab list modal.");
            $this->assertPageHas($html, 'v-if="modalOpen"', "{$uri} needs the generic content modal.");
        }

        foreach (['/konten/unboxing', '/konten/distribution', '/konten/analytics', '/konten/story'] as $uri) {
            $this->assertPageLacks($this->html($uri), 'Riwayat Konten Colab', "{$uri} does not use the generic content modal.");
        }
    }

    public function test_calendar_includes_the_story_modal_it_opens(): void
    {
        $this->assertPageHas($this->html('/konten/calendar'), 'v-if="storyModalOpen"');
        $this->assertPageHas($this->html('/konten/story'), 'v-if="storyModalOpen"');
    }

    public function test_legacy_dashboard_keeps_the_generic_content_modal(): void
    {
        $this->assertPageHas($this->html('/'), 'Riwayat Konten Colab');
    }

    public function test_every_page_keeps_global_overlays(): void
    {
        foreach ($this->pages() as [$uri]) {
            $this->assertPageHas($this->html($uri), 'v-if="confirmModal.open"', "{$uri} needs the confirm modal.");
        }
    }

    public function test_sidebar_links_to_batch_f1_urls(): void
    {
        $sidebar = (string) file_get_contents(resource_path('views/dashboard/partials/shell/app-frame-sidebar-nav-dashboard-content.blade.php'));

        foreach ($this->pages() as $tab => [$uri]) {
            $this->assertStringContainsString("href=\"{$uri}\" @click=\"navigateTab(\$event, '{$tab}')\"", $sidebar);
            $this->assertStringNotContainsString("switchTab('{$tab}')", $sidebar);
        }

        $this->assertStringContainsString("switchTab('dashboard')", $sidebar);
        $this->assertStringContainsString("switchTab('master')", $sidebar);
    }

    public function test_calendar_and_analytics_load_master_plan_data_when_opened_directly(): void
    {
        $tail = (string) file_get_contents(resource_path('views/dashboard/partials/shell/app-script-runner-session-tail.blade.php'));

        $this->assertStringContainsString("if (tab === 'calendar' || tab === 'analytics') {", $tail);
    }

    public function test_flag_off_restores_batch_f1_menus_on_the_legacy_dashboard(): void
    {
        config(['dashboard.url_routing' => false]);

        $html = $this->html('/');

        foreach ($this->pages() as [$uri, $marker]) {
            $this->assertPageHas($html, $marker, "Flag off must render {$uri} menu on /.");
        }
    }
}
