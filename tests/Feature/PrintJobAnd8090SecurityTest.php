<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrintJobAnd8090SecurityTest extends TestCase
{
    use RefreshDatabase;

    protected bool $authenticateDashboard = false;

    public function test_print_jobs_are_publicly_accessible(): void
    {
        $response = $this->postJson('/print-job', ['html' => '<p>Print Document</p>']);
        $response->assertOk()->assertJsonStructure(['token']);
        $token = (string) $response->json('token');
        $this->get('/print-job/'.$token)->assertOk()->assertSee('Print Document');
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
