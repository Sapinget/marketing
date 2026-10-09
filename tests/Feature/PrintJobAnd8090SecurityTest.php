<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrintJobAnd8090SecurityTest extends TestCase
{
    use RefreshDatabase;

    protected bool $authenticateDashboard = false;

    public function test_print_jobs_require_a_dashboard_login(): void
    {
        $this->postJson('/print-job', ['html' => '<p>Print Document</p>'])->assertUnauthorized();
        $this->get('/print-job/'.str_repeat('a', 32))->assertStatus(401);
    }

    public function test_print_job_round_trip_for_the_user_who_created_it(): void
    {
        $this->actingAsDashboardUser();

        $token = (string) $this->postJson('/print-job', ['html' => '<p>Print Document</p>'])
            ->assertOk()
            ->assertJsonStructure(['token'])
            ->json('token');

        $this->get('/print-job/'.$token)->assertOk()->assertSee('Print Document');
    }

    public function test_print_job_token_cannot_be_opened_by_another_account(): void
    {
        $owner = $this->actingAsDashboardUser(['username' => 'print-owner']);
        $token = (string) $this->postJson('/print-job', ['html' => '<p>Rahasia</p>'])->json('token');
        $this->assertNotSame('', $token);

        $this->actingAsDashboardUser(['username' => 'print-intruder']);

        $this->get('/print-job/'.$token)->assertNotFound()->assertDontSee('Rahasia')->assertSee('Print Tidak Ditemukan');
        $this->assertNotNull($owner);
    }

    public function test_print_job_page_has_its_own_nonce_csp_and_no_inline_handlers(): void
    {
        $this->actingAsDashboardUser();

        $token = (string) $this->postJson('/print-job', [
            'html' => '<p>Safe</p><img src=x onerror=alert(1)><div onmouseover=alert(2)>m</div>',
        ])->json('token');

        $response = $this->get('/print-job/'.$token)->assertOk();
        $csp = (string) $response->headers->get('Content-Security-Policy');

        $this->assertMatchesRegularExpression("/script-src 'nonce-[A-Za-z0-9+\/=]+'/", $csp);
        $this->assertStringNotContainsString('unsafe-inline\'; style', $csp);
        $this->assertStringNotContainsString("'unsafe-eval'", $csp);

        preg_match("/'nonce-([^']+)'/", $csp, $m);
        $this->assertStringContainsString('<script nonce="'.$m[1].'">', $response->getContent());
        $response->assertDontSee('onerror', false)->assertDontSee('onmouseover', false);
    }

    public function test_print_job_endpoints_are_throttled(): void
    {
        $this->actingAsDashboardUser();

        $postMiddleware = app('router')->getRoutes()->match(request()->create('/print-job', 'POST'))->gatherMiddleware();
        $this->assertContains('throttle:30,1', $postMiddleware);
        $this->assertContains('dashboard.auth', $postMiddleware);
    }

    public function test_print_job_strips_executable_markup_before_serving_it(): void
    {
        $this->actingAsDashboardUser();

        $token = $this->postJson('/print-job', [
            'html' => '<p onclick="alert(1)">Safe</p><script>alert(1)</script><a href="javascript:alert(1)">Link</a><img src="x" onerror="alert(1)">',
        ])->assertOk()->json('token');

        $this->assertIsString($token);

        $this->get('/print-job/'.$token)
            ->assertOk()
            ->assertSee('Safe', false)
            ->assertSee('const waitForAssets = () => {', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee('onclick=', false)
            ->assertDontSee('onerror=', false)
            ->assertDontSee('javascript:', false);
    }

    public function test_8090_rejects_path_traversal_attempts(): void
    {
        $this->get('/8090/../.env')->assertNotFound();
        $this->get('/8090/%2e%2e/.env')->assertNotFound();
    }

    public function test_8090_proxy_only_accepts_read_methods(): void
    {
        $this->post('/8090/api/auth/login')->assertMethodNotAllowed();
        $this->put('/8090/api/auth/login')->assertMethodNotAllowed();
    }
}
