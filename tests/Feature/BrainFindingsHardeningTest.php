<?php

namespace Tests\Feature;

use App\Support\DashboardAuth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class BrainFindingsHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected bool $authenticateDashboard = false;

    public function test_auth_routes_have_rate_limiting_middleware(): void
    {
        $routes = [
            ['POST', 'api/auth/logout', 'throttle:10,1'],
            ['POST', 'api/auth/heartbeat', 'throttle:60,1'],
            ['GET', 'api/auth/avatar/{filename}', 'throttle:60,1'],
            ['POST', 'api/auth/avatar', 'throttle:30,1'],
            ['PUT', 'api/auth/pin', 'throttle:15,1'],
            ['PUT', 'api/auth/profile', 'throttle:30,1'],
            ['GET', 'api/auth/users', 'throttle:30,1'],
            ['POST', 'api/auth/users', 'throttle:10,1'],
            ['PUT', 'api/auth/users/{user}', 'throttle:10,1'],
            ['DELETE', 'api/auth/users/{user}', 'throttle:30,1'],
        ];

        foreach ($routes as [$method, $uri, $expectedMiddleware]) {
            $route = collect(Route::getRoutes()->get($method))->first(fn ($r) => $r->uri() === $uri);
            $this->assertNotNull($route, "Route {$method} {$uri} not found.");
            $this->assertContains(
                $expectedMiddleware,
                $route->gatherMiddleware(),
                "Route {$method} {$uri} does not have {$expectedMiddleware}"
            );
        }
    }

    public function test_public_login_and_print_job_auth_preserved(): void
    {
        $loginResponse = $this->postJson('/api/auth/login', [
            'username' => 'nonexistent',
            'pin' => 'wrongpin',
        ]);
        $loginResponse->assertStatus(422);

        // Print job dulu publik; kini wajib login (lihat PrintJobAnd8090SecurityTest).
        $this->postJson('/print-job', ['html' => '<p>Public Print Document</p>'])->assertUnauthorized();

        $this->actingAsDashboardUser();
        $printResponse = $this->postJson('/print-job', [
            'html' => '<p>Print Document</p>',
        ]);
        $printResponse->assertOk()->assertJsonStructure(['token']);
        $token = (string) $printResponse->json('token');

        $this->get('/print-job/'.$token)
            ->assertOk()
            ->assertSee('Print Document');
    }

    public function test_8090_route_is_method_restricted_and_prevents_path_traversal(): void
    {
        $this->get('/8090')->assertOk();
        $this->post('/8090')->assertStatus(405);
        $this->put('/8090')->assertStatus(405);
        $this->delete('/8090')->assertStatus(405);

        $this->get('/8090/../.env')->assertNotFound();
        $this->get('/8090/%2e%2e/.env')->assertNotFound();
    }

    public function test_distributions_request_validation(): void
    {
        $this->actingAsDashboardUser(['role' => DashboardAuth::ROLE_ADMIN]);

        $mpId = DB::table('master_plans')->insertGetId([
            'source_id' => 'MP-DIST-VAL',
            'title' => 'Master Plan Dist Test',
            'editor' => 'Editor Dist',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->postJson('/api/distributions', [
            'Master_ID' => '',
            'Platform' => 'Instagram',
        ])->assertStatus(422);

        $this->postJson('/api/distributions', [
            'Master_ID' => 'MP-DIST-VAL',
            'Platform' => 'Instagram',
            'Tanggal_Publish' => 'not-a-date',
        ])->assertStatus(422);

        $res = $this->postJson('/api/distributions', [
            'Master_ID' => 'MP-DIST-VAL',
            'Judul' => 'Valid Distribution',
            'Platform' => 'Instagram',
            'Tanggal_Publish' => '2026-07-01',
        ])->assertCreated();

        $distId = $res->json('data.ID');
        $this->assertDatabaseHas('distributions', ['id' => $distId, 'platform' => 'Instagram']);

        $this->putJson("/api/distributions/{$distId}", [
            'Master_ID' => 'MP-DIST-VAL',
            'Platform' => 'YouTube',
            'Tanggal_Publish' => 'invalid-date',
        ])->assertStatus(422);

        $this->putJson("/api/distributions/{$distId}", [
            'Master_ID' => 'MP-DIST-VAL',
            'Platform' => 'YouTube',
            'Tanggal_Publish' => '2026-07-02',
        ])->assertOk();

        $this->assertDatabaseHas('distributions', ['id' => $distId, 'platform' => 'YouTube']);
    }

    public function test_analytics_request_validation(): void
    {
        $this->actingAsDashboardUser(['role' => DashboardAuth::ROLE_ADMIN]);

        DB::table('master_plans')->insert([
            'source_id' => 'MP-ANALYTICS-VAL',
            'title' => 'Master Plan Analytics Test',
            'editor' => 'Editor Analytics',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->postJson('/api/analytics', [
            'Master_ID' => 'MP-ANALYTICS-VAL',
            'Platform' => 'Instagram',
            'Tanggal_Publish' => 'bad-date',
        ])->assertStatus(422);

        $this->postJson('/api/analytics', [
            'Master_ID' => 'MP-ANALYTICS-VAL',
            'Platform' => 'Instagram',
            'Views' => -5,
        ])->assertStatus(422);

        $res = $this->postJson('/api/analytics', [
            'Master_ID' => 'MP-ANALYTICS-VAL',
            'Platform' => 'Instagram',
            'Tanggal_Publish' => '2026-07-01',
            'Views' => 1500,
            'Likes' => 200,
        ])->assertCreated();

        $analyticsId = $res->json('data.ID');
        $this->assertDatabaseHas('analytics', ['id' => $analyticsId, 'views' => 1500]);

        $this->putJson("/api/analytics/{$analyticsId}", [
            'Master_ID' => 'MP-ANALYTICS-VAL',
            'Platform' => 'Instagram',
            'Likes' => -1,
        ])->assertStatus(422);

        $this->putJson("/api/analytics/{$analyticsId}", [
            'Master_ID' => 'MP-ANALYTICS-VAL',
            'Platform' => 'Instagram',
            'Views' => 2500,
            'Likes' => 350,
        ])->assertOk();

        $this->assertDatabaseHas('analytics', ['id' => $analyticsId, 'views' => 2500]);
    }

    public function test_lpjk_and_lpjk_detail_request_validation(): void
    {
        $this->actingAsDashboardUser(['role' => DashboardAuth::ROLE_ADMIN]);

        $this->postJson('/api/lpjk', [
            'Nama_Event' => '',
        ])->assertStatus(422);

        $this->postJson('/api/lpjk', [
            'Nama_Event' => 'LPJK Event Test',
            'Tanggal' => 'invalid-date',
        ])->assertStatus(422);

        $this->postJson('/api/lpjk', [
            'Nama_Event' => 'LPJK Event Test',
            'Budget_Rencana' => -100,
        ])->assertStatus(422);

        $lpjkRes = $this->postJson('/api/lpjk', [
            'ID' => 'LPJK-VAL-TEST-1',
            'Nama_Event' => 'LPJK Event Test',
            'Tanggal' => '2026-07-01',
            'Budget_Rencana' => 5000000,
        ])->assertCreated();

        $this->assertDatabaseHas('lpjk', ['source_id' => 'LPJK-VAL-TEST-1']);

        $this->putJson('/api/lpjk/LPJK-VAL-TEST-1', [
            'Nama_Event' => 'LPJK Event Updated',
            'Budget_Rencana' => -50,
        ])->assertStatus(422);

        $this->putJson('/api/lpjk/LPJK-VAL-TEST-1', [
            'Nama_Event' => 'LPJK Event Updated',
            'Tanggal' => '2026-07-05',
            'Budget_Rencana' => 6000000,
        ])->assertOk();

        $this->assertDatabaseHas('lpjk', ['source_id' => 'LPJK-VAL-TEST-1', 'budget_rencana' => 6000000]);

        $this->postJson('/api/lpjk-detail', [
            'Master_ID' => 'LPJK-VAL-TEST-1',
            'Jumlah' => 0,
            'Total' => 1000,
        ])->assertStatus(422);

        $this->postJson('/api/lpjk-detail', [
            'Master_ID' => 'LPJK-VAL-TEST-1',
            'Jumlah' => 2,
            'Total' => -500,
        ])->assertStatus(422);

        $detailRes = $this->postJson('/api/lpjk-detail', [
            'ID' => 'LPJKD-VAL-TEST-1',
            'Master_ID' => 'LPJK-VAL-TEST-1',
            'Nama_Pengeluaran' => 'Pengeluaran Konsumsi',
            'Jumlah' => 10,
            'Total' => 500000,
        ])->assertCreated();

        $this->assertDatabaseHas('lpjk_detail', ['source_id' => 'LPJKD-VAL-TEST-1', 'total' => 500000]);

        $this->putJson('/api/lpjk-detail/LPJKD-VAL-TEST-1', [
            'Master_ID' => 'LPJK-VAL-TEST-1',
            'Jumlah' => 0,
        ])->assertStatus(422);

        $this->putJson('/api/lpjk-detail/LPJKD-VAL-TEST-1', [
            'Master_ID' => 'LPJK-VAL-TEST-1',
            'Jumlah' => 12,
            'Total' => 600000,
        ])->assertOk();

        $this->assertDatabaseHas('lpjk_detail', ['source_id' => 'LPJKD-VAL-TEST-1', 'total' => 600000]);
    }

    public function test_bonus_and_budgeting_config_validation(): void
    {
        $this->actingAsDashboardUser(['role' => DashboardAuth::ROLE_ADMIN]);

        $this->putJson('/api/bonus-config', [
            ['minViews' => 1000],
        ])->assertStatus(422);

        $this->putJson('/api/budgeting-config', [
            ['costPerAd' => 2000],
        ])->assertStatus(422);

        $this->putJson('/api/bonus-config', [
            'rule1' => ['minViews' => 1000, 'bonus' => 50000],
        ])->assertOk();

        $this->putJson('/api/budgeting-config', [
            'monthly' => ['limit' => 10000000],
        ])->assertOk();
    }
}
