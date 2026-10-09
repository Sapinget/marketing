<?php

namespace Tests\Feature;

use App\Support\DashboardAuth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HighRiskDomainRouteAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_talent_cannot_mutate_master_plan_analytics_or_lpjk_records(): void
    {
        $this->actingAsDashboardUser(['role' => DashboardAuth::ROLE_TALENT]);

        $this->postJson('/api/master-plans', ['Judul' => 'Test'])->assertForbidden();
        $this->putJson('/api/master-plans/MP-1', ['Judul' => 'Test'])->assertForbidden();
        $this->deleteJson('/api/master-plans/MP-1')->assertForbidden();
        $this->postJson('/api/distributions', ['Master_ID' => 'MP-1', 'Platform' => 'Instagram'])->assertForbidden();
        $this->putJson('/api/distributions/1', ['Master_ID' => 'MP-1', 'Platform' => 'Instagram'])->assertForbidden();
        $this->deleteJson('/api/distributions/1')->assertForbidden();
        $this->postJson('/api/analytics', ['Master_ID' => 'MP-1', 'Platform' => 'Instagram'])->assertForbidden();
        $this->putJson('/api/analytics/1', ['Master_ID' => 'MP-1', 'Platform' => 'Instagram'])->assertForbidden();
        $this->deleteJson('/api/analytics/1')->assertForbidden();
        $this->postJson('/api/lpjk', ['Nama_Event' => 'Test'])->assertForbidden();
        $this->putJson('/api/lpjk/LPJK-1', ['Nama_Event' => 'Test'])->assertForbidden();
        $this->deleteJson('/api/lpjk/LPJK-1')->assertForbidden();
        $this->postJson('/api/lpjk-detail', ['Master_ID' => 'LPJK-1'])->assertForbidden();
        $this->putJson('/api/lpjk-detail/LPJKD-1', ['Master_ID' => 'LPJK-1'])->assertForbidden();
        $this->deleteJson('/api/lpjk-detail/LPJKD-1')->assertForbidden();
    }

    public function test_talent_cannot_mutate_generic_content_or_customer_claim_records(): void
    {
        $this->actingAsDashboardUser(['role' => DashboardAuth::ROLE_TALENT]);

        $this->postJson('/api/unboxing', ['Nama' => 'Test'])->assertForbidden();
        $this->putJson('/api/unboxing/UBX-1', ['Nama' => 'Test'])->assertForbidden();
        $this->deleteJson('/api/unboxing/UBX-1')->assertForbidden();

        $this->postJson('/api/story-schedules', ['Story' => 'Test'])->assertForbidden();
        $this->putJson('/api/story-schedules/STR-1', ['Story' => 'Test'])->assertForbidden();
        $this->deleteJson('/api/story-schedules/STR-1')->assertForbidden();

        $this->postJson('/api/calendar-events', ['Nama_Event' => 'Test'])->assertForbidden();
        $this->putJson('/api/calendar-events/CAL-1', ['Nama_Event' => 'Test'])->assertForbidden();
        $this->deleteJson('/api/calendar-events/CAL-1')->assertForbidden();

        $this->postJson('/api/ideation', ['Judul' => 'Test'])->assertForbidden();
        $this->putJson('/api/ideation/IDE-1', ['Judul' => 'Test'])->assertForbidden();
        $this->deleteJson('/api/ideation/IDE-1')->assertForbidden();

        $this->postJson('/api/program-promo', ['Program' => 'Test'])->assertForbidden();
        $this->putJson('/api/program-promo/PRO-1', ['Program' => 'Test'])->assertForbidden();
        $this->deleteJson('/api/program-promo/PRO-1')->assertForbidden();

        $this->postJson('/api/sell-out-targets', ['Nama_Produk' => 'Test'])->assertForbidden();
        $this->putJson('/api/sell-out-targets/SOT-1', ['Nama_Produk' => 'Test'])->assertForbidden();
        $this->deleteJson('/api/sell-out-targets/SOT-1')->assertForbidden();

        $this->postJson('/api/ads-performance', ['Nama' => 'Test'])->assertForbidden();
        $this->putJson('/api/ads-performance/ADS-1', ['Nama' => 'Test'])->assertForbidden();
        $this->deleteJson('/api/ads-performance/ADS-1')->assertForbidden();

        $this->postJson('/api/harga-kompetitor', ['Nama_Produk' => 'Test'])->assertForbidden();
        $this->putJson('/api/harga-kompetitor/HK-1', ['Nama_Produk' => 'Test'])->assertForbidden();
        $this->deleteJson('/api/harga-kompetitor/HK-1')->assertForbidden();

        $this->postJson('/api/orderan-online', ['NAMA' => 'Test'])->assertForbidden();
        $this->putJson('/api/orderan-online/OO-1', ['NAMA' => 'Test'])->assertForbidden();
        $this->deleteJson('/api/orderan-online/OO-1')->assertForbidden();

        $this->postJson('/api/unit-ditanya', ['KATEGORI' => 'Test'])->assertForbidden();
        $this->putJson('/api/unit-ditanya/UD-1', ['KATEGORI' => 'Test'])->assertForbidden();
        $this->deleteJson('/api/unit-ditanya/UD-1')->assertForbidden();

        $this->putJson('/api/service/SVC-1', ['no_service' => 'Test'])->assertForbidden();

        $this->postJson('/api/service-claims/transfer', ['service_source_id' => 'SVC-1'])->assertForbidden();
        $this->putJson('/api/service-claims/PSC-1', ['no_transaksi' => 'Test'])->assertForbidden();
        $this->deleteJson('/api/service-claims/PSC-1')->assertForbidden();

        $this->postJson('/api/claim-garansi', ['NAMA_CUSTOMER' => 'Test'])->assertForbidden();
        $this->putJson('/api/claim-garansi/CG-1', ['NAMA_CUSTOMER' => 'Test'])->assertForbidden();
        $this->deleteJson('/api/claim-garansi/CG-1')->assertForbidden();
    }

    public function test_talent_cannot_mutate_generic_customer_service_and_later_inventory_routes(): void
    {
        $this->actingAsDashboardUser(['role' => DashboardAuth::ROLE_TALENT]);

        foreach ([
            ['post', '/api/claim-garansi', ['NAMA_CUSTOMER' => 'Test']],
            ['put', '/api/claim-garansi/CG-1', ['NAMA_CUSTOMER' => 'Test']],
            ['delete', '/api/claim-garansi/CG-1', []],
            ['post', '/api/keep-barang', ['NAMA' => 'Test']],
            ['put', '/api/keep-barang/KB-1', ['NAMA' => 'Test']],
            ['delete', '/api/keep-barang/KB-1', []],
            ['post', '/api/asset-vendor-inventory', ['Vendor' => 'Test']],
            ['put', '/api/asset-vendor-inventory/AVI-1', ['Vendor' => 'Test']],
            ['delete', '/api/asset-vendor-inventory/AVI-1', []],
            ['delete', '/api/catalog-templates/CT-1', []],
        ] as [$method, $uri, $payload]) {
            $this->{$method.'Json'}($uri, $payload)->assertForbidden();
        }
    }

    public function test_operasional_cannot_mutate_generic_content_or_customer_claim_records(): void
    {
        $this->actingAsDashboardUser(['role' => DashboardAuth::ROLE_OPERASIONAL]);

        $this->postJson('/api/master-plans', ['Judul' => 'Test'])->assertForbidden();
        $this->postJson('/api/distributions', ['Master_ID' => 'MP-1', 'Platform' => 'Instagram'])->assertForbidden();
        $this->postJson('/api/analytics', ['Master_ID' => 'MP-1', 'Platform' => 'Instagram'])->assertForbidden();
        $this->postJson('/api/lpjk', ['Nama_Event' => 'Test'])->assertForbidden();
        $this->postJson('/api/lpjk-detail', ['Master_ID' => 'LPJK-1'])->assertForbidden();
        $this->postJson('/api/unboxing', ['Nama' => 'Test'])->assertForbidden();
        $this->postJson('/api/claim-garansi', ['NAMA_CUSTOMER' => 'Test'])->assertForbidden();
    }

    public function test_admin_can_mutate_master_plan_analytics_and_lpjk_records(): void
    {
        $this->actingAsDashboardUser(['role' => DashboardAuth::ROLE_ADMIN]);

        $mp = $this->postJson('/api/master-plans', [
            'ID' => 'MP-AUTH-1',
            'Judul' => 'Master Auth Test',
            'Tanggal_Rencana' => '2026-06-26',
        ])->assertCreated();

        $this->assertDatabaseHas('master_plans', ['source_id' => 'MP-AUTH-1']);

        $dist = $this->postJson('/api/distributions', [
            'Master_ID' => 'MP-AUTH-1',
            'Judul' => 'Dist Auth Test',
            'Platform' => 'Instagram',
            'Tanggal_Publish' => '2026-06-26',
        ])->assertCreated();

        $distId = $dist->json('data.ID');
        $this->assertDatabaseHas('distributions', ['id' => $distId]);

        $analytics = $this->postJson('/api/analytics', [
            'Master_ID' => 'MP-AUTH-1',
            'Judul' => 'Analytics Auth Test',
            'Platform' => 'Instagram',
            'Tanggal_Publish' => '2026-06-26',
            'Views' => 50,
        ])->assertCreated();

        $analyticsId = $analytics->json('data.ID');
        $this->assertDatabaseHas('analytics', ['id' => $analyticsId, 'views' => 50]);

        $lpjk = $this->postJson('/api/lpjk', [
            'ID' => 'LPJK-AUTH-1',
            'Nama_Event' => 'LPJK Auth Test',
            'Tanggal' => '2026-06-26',
            'Budget_Rencana' => 1000000,
            'Realisasi_Biaya' => 950000,
        ])->assertCreated();

        $this->assertDatabaseHas('lpjk', [
            'source_id' => 'LPJK-AUTH-1',
            'budget_rencana' => 1000000,
            'realisasi_biaya' => 950000,
        ]);

        $detail = $this->postJson('/api/lpjk-detail', [
            'Master_ID' => 'LPJK-AUTH-1',
            'Nama_Pengeluaran' => 'Sewa Venue',
            'Jumlah' => 2,
            'Total' => 500000,
        ])->assertCreated();

        $detailSourceId = $detail->json('data.source_id');
        $this->assertDatabaseHas('lpjk_detail', [
            'source_id' => $detailSourceId,
            'master_id' => 'LPJK-AUTH-1',
            'jumlah' => 2,
            'total' => 500000,
        ]);
    }

    public function test_focused_validation_rejects_invalid_date_and_financial_payloads(): void
    {
        $this->actingAsDashboardUser(['role' => DashboardAuth::ROLE_ADMIN]);

        // Invalid date on master-plans
        $this->postJson('/api/master-plans', [
            'Judul' => 'Valid Title',
            'Tanggal_Rencana' => '26-06-2026',
        ])->assertStatus(422);

        // Valid parent for downstream tests
        $this->postJson('/api/master-plans', [
            'ID' => 'MP-VAL-1',
            'Judul' => 'Valid Master Plan',
            'Tanggal_Rencana' => '2026-06-26',
        ])->assertCreated();

        // Invalid date on distributions
        $this->postJson('/api/distributions', [
            'Master_ID' => 'MP-VAL-1',
            'Platform' => 'Instagram',
            'Tanggal_Publish' => 'invalid-date',
        ])->assertStatus(422);

        // Invalid date on analytics
        $this->postJson('/api/analytics', [
            'Master_ID' => 'MP-VAL-1',
            'Platform' => 'Instagram',
            'Tanggal_Publish' => 'not-a-date',
        ])->assertStatus(422);

        // Invalid financial / negative views on analytics
        $this->postJson('/api/analytics', [
            'Master_ID' => 'MP-VAL-1',
            'Platform' => 'Instagram',
            'Views' => -10,
        ])->assertStatus(422);

        // Invalid date on lpjk
        $this->postJson('/api/lpjk', [
            'Nama_Event' => 'LPJK Validation Event',
            'Tanggal' => 'invalid-date',
        ])->assertStatus(422);

        // Invalid financial / negative budget on lpjk
        $this->postJson('/api/lpjk', [
            'Nama_Event' => 'LPJK Validation Event',
            'Budget_Rencana' => -5000,
        ])->assertStatus(422);

        // Valid LPJK parent
        $this->postJson('/api/lpjk', [
            'ID' => 'LPJK-VAL-1',
            'Nama_Event' => 'LPJK Validation Event',
            'Budget_Rencana' => 100000,
        ])->assertCreated();

        // Invalid financial / negative total on lpjk-detail
        $this->postJson('/api/lpjk-detail', [
            'Master_ID' => 'LPJK-VAL-1',
            'Total' => -1000,
        ])->assertStatus(422);

        // Invalid quantity / zero or negative jumlah on lpjk-detail
        $this->postJson('/api/lpjk-detail', [
            'Master_ID' => 'LPJK-VAL-1',
            'Jumlah' => 0,
            'Total' => 1000,
        ])->assertStatus(422);
    }

    public function test_distribution_update_and_service_claim_transfer_validate_payloads(): void
    {
        $this->actingAsDashboardUser(['role' => DashboardAuth::ROLE_ADMIN]);

        $masterPlanId = DB::table('master_plans')->insertGetId([
            'source_id' => 'MP-ROUTE-VALIDATION-1',
            'title' => 'Validation Parent',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $distributionId = DB::table('distributions')->insertGetId([
            'master_id' => 'MP-ROUTE-VALIDATION-1',
            'master_plan_id' => $masterPlanId,
            'platform' => 'Instagram',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->putJson("/api/distributions/{$distributionId}", [
            'Master_ID' => 'MP-ROUTE-VALIDATION-1',
            'Platform' => 'Instagram',
            'Tanggal_Publish' => 'invalid-date',
        ])->assertUnprocessable();

        DB::table('services')->insert([
            'source_id' => 'SVC-ROUTE-VALIDATION-1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->postJson('/api/service-claims/transfer', [
            'service_source_id' => 'SVC-ROUTE-VALIDATION-1',
            'TANGGAL_ESTIMASI' => 'invalid-date',
        ])->assertUnprocessable();
    }

    public function test_service_claims_join_service_details_and_report_transfer_state(): void
    {
        $this->actingAsDashboardUser(['role' => DashboardAuth::ROLE_ADMIN]);

        DB::table('services')->insert([
            'source_id' => 'SVC-CLAIM-JOIN-1',
            'no_service' => 'SRV-JOIN-001',
            'tanggal' => '2026-09-07',
            'nama_customer' => 'Claim Customer',
            'wa_customer' => '08123456789',
            'type_unit' => 'Phone Pro',
            'imei_sn' => 'IMEI-JOIN-1',
            'kerusakan' => 'Tidak menyala',
            'status' => 'Diproses',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->postJson('/api/service-claims/transfer', [
            'service_source_id' => 'SVC-CLAIM-JOIN-1',
            'NO_TRANSAKSI' => 'TRX-JOIN-001',
            'GARANSI' => 'Resmi',
        ])->assertCreated()
            ->assertJsonPath('transferred', true);

        $this->postJson('/api/service-claims/transfer', [
            'service_source_id' => 'SVC-CLAIM-JOIN-1',
        ])->assertOk()
            ->assertJsonPath('transferred', false);

        $this->getJson('/api/service-claims')->assertOk()
            ->assertJsonPath('data.0.ID', DB::table('service_claims')->where('service_source_id', 'SVC-CLAIM-JOIN-1')->value('source_id'))
            ->assertJsonPath('data.0.NO_SERVICE', 'SRV-JOIN-001')
            ->assertJsonPath('data.0.TANGGAL_MASUK', '2026-09-07')
            ->assertJsonPath('data.0.NAMA_CUSTOMER', 'Claim Customer')
            ->assertJsonPath('data.0.WA_CUSTOMER', '08123456789')
            ->assertJsonPath('data.0.TIPE', 'Phone Pro')
            ->assertJsonPath('data.0.IMEI_SN', 'IMEI-JOIN-1')
            ->assertJsonPath('data.0.KERUSAKAN', 'Tidak menyala')
            ->assertJsonPath('data.0.STATUS', 'Diproses')
            ->assertJsonPath('data.0.NO_TRANSAKSI', 'TRX-JOIN-001')
            ->assertJsonPath('data.0.GARANSI', 'Resmi');
    }

    public function test_admin_can_mutate_generic_content_and_customer_claim_records(): void
    {
        $this->actingAsDashboardUser(['role' => DashboardAuth::ROLE_ADMIN]);

        $this->postJson('/api/unboxing', [
            'ID' => 'content-1',
            'Nama' => 'Unboxing Video',
        ])->assertCreated();

        $this->assertDatabaseHas('unboxing', [
            'source_id' => 'content-1',
            'nama' => 'Unboxing Video',
        ]);

        $this->putJson('/api/unboxing/content-1', [
            'Nama' => 'Unboxing Video Updated',
        ])->assertOk();

        $this->assertDatabaseHas('unboxing', [
            'source_id' => 'content-1',
            'nama' => 'Unboxing Video Updated',
        ]);

        $this->postJson('/api/claim-garansi', [
            'ID' => 'claim-1',
            'NAMA_CUSTOMER' => 'Customer Claim',
        ])->assertCreated();

        $this->assertDatabaseHas('claim_garansi', [
            'source_id' => 'claim-1',
            'nama_customer' => 'Customer Claim',
        ]);

        $this->putJson('/api/claim-garansi/claim-1', [
            'NAMA_CUSTOMER' => 'Customer Claim Updated',
        ])->assertOk();

        $this->assertDatabaseHas('claim_garansi', [
            'source_id' => 'claim-1',
            'nama_customer' => 'Customer Claim Updated',
        ]);

        // Service claim transfer by admin
        DB::table('services')->insert([
            'source_id' => 'SVC-TEST-1',
            'no_service' => 'SRV-001',
            'nama_customer' => 'Service User',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->postJson('/api/service-claims/transfer', [
            'service_source_id' => 'SVC-TEST-1',
            'NO_TRANSAKSI' => 'TRX-123',
        ])->assertCreated();

        $this->assertDatabaseHas('service_claims', [
            'service_source_id' => 'SVC-TEST-1',
            'no_transaksi' => 'TRX-123',
        ]);
    }
}
