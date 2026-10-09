<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    protected bool $authenticateDashboard = false;

    public function test_dashboard_pages_are_not_indexable_and_keep_a_tight_csp(): void
    {
        $response = $this->get('/')->assertOk();

        $this->assertSame('noindex, nofollow, noarchive', $response->headers->get('X-Robots-Tag'));
        $this->assertSame('DENY', $response->headers->get('X-Frame-Options'));

        $csp = (string) $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("frame-ancestors 'none'", $csp);
        $this->assertStringNotContainsString('perplexity', $csp, 'unused third-party font host must not be allowed');
        $this->assertStringContainsString("font-src 'self' data:", $csp);
    }

    public function test_robots_txt_disallows_everything(): void
    {
        $this->assertStringContainsString('Disallow: /', (string) file_get_contents(public_path('robots.txt')));
        $this->assertDoesNotMatchRegularExpression('/^Disallow:\s*$/m', (string) file_get_contents(public_path('robots.txt')));
    }

    public function test_public_directory_has_no_stray_files(): void
    {
        $this->assertFileDoesNotExist(public_path('.DS_Store'));
    }
}
