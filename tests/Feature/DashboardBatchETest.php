<?php

namespace Tests\Feature;

use Tests\TestCase;

class DashboardBatchETest extends TestCase
{
    /** @var array<string, array{0: string, 1: string}> uri => [tab, marker] */
    private const PAGES = [
        '/analisa/story-ig' => ['meta_story', "v-if=\"activeTab === 'meta_story' && metaStoryLoaded\""],
        '/analisa/feed-konten' => ['meta_feed', 'v-if="metaFeedManualModalOpen"'],
        '/analisa/followers-ig' => ['meta_followers', "v-if=\"activeTab === 'meta_followers' && metaFollowersLoaded\""],
    ];

    /** Jangan pakai assertSee/assertStringContainsString pada halaman penuh (1-2 MB) agar PHPUnit tidak macet. */
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
        $response = $this->get($uri);
        $response->assertOk();

        return (string) $response->getContent();
    }

    public function test_batch_e_pages_render_their_own_markup_and_global_overlays(): void
    {
        foreach (self::PAGES as $uri => [$tab, $marker]) {
            $html = $this->html($uri);

            $this->assertPageHas($html, $marker, "{$uri} must render its own menu markup.");
            $this->assertPageHas($html, 'v-if="confirmModal.open"', "{$uri} must have the global confirm.");
            $this->assertSame($uri, config('dashboard.tab_urls')[$tab]);
        }
    }

    public function test_batch_e_markup_is_not_rendered_on_the_legacy_dashboard(): void
    {
        $legacy = $this->html('/');

        foreach (self::PAGES as $uri => [$tab, $marker]) {
            $this->assertPageLacks($legacy, $marker, "/ must not render {$uri} markup.");
        }
    }

    public function test_feed_page_does_not_render_other_batch_e_menus(): void
    {
        $html = $this->html('/analisa/feed-konten');

        $this->assertPageLacks($html, "v-if=\"activeTab === 'meta_story' && metaStoryLoaded\"");
        $this->assertPageLacks($html, "v-if=\"activeTab === 'meta_followers' && metaFollowersLoaded\"");
    }

    public function test_sidebar_links_to_batch_e_urls(): void
    {
        $sidebar = (string) file_get_contents(resource_path('views/dashboard/partials/shell/app-frame-sidebar-nav-analysis.blade.php'));

        foreach (['meta_story', 'meta_feed', 'meta_followers'] as $tab) {
            $url = config('dashboard.tab_urls')[$tab];

            $this->assertStringContainsString('href="'.$url.'" @click="navigateTab($event, \''.$tab.'\')"', $sidebar);
            $this->assertStringNotContainsString("switchTab('{$tab}')", $sidebar);
        }
    }

    public function test_flag_off_renders_batch_e_menus_on_the_legacy_dashboard_again(): void
    {
        config(['dashboard.url_routing' => false]);
        $html = $this->html('/');

        foreach (self::PAGES as $uri => [$tab, $marker]) {
            $this->assertPageHas($html, $marker, "Flag off: / must render {$uri} markup again.");
        }
    }
}
