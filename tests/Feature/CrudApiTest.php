<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CrudApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_master_plan_crud_api_works(): void
    {
        $userId = auth()->id();

        $create = $this->postJson('/api/master-plans', [
            'ID' => 'CRUD-MASTER-001',
            'Judul' => 'Konten CRUD',
            'Format_Konten' => 'REELS',
            'Platforms' => 'Instagram',
            'Editor' => 'Editor CRUD',
            'Talent' => 'Talent A, Talent B',
            'Status' => 'IDE',
            'Tanggal_Rencana' => '2026-06-26',
        ])->assertCreated();

        $create->assertJsonPath('data.ID', 'CRUD-MASTER-001');
        $create->assertJsonPath('data.Talent', 'Talent A, Talent B');
        $this->assertDatabaseHas('master_plans', [
            'source_id' => 'CRUD-MASTER-001',
            'title' => 'Konten CRUD',
            'talent' => 'Talent A, Talent B',
            'created_by_user_id' => $userId,
            'updated_by_user_id' => $userId,
        ]);

        $this->putJson('/api/master-plans/CRUD-MASTER-001', [
            'Judul' => 'Konten CRUD Update',
            'Format_Konten' => 'VIDEO',
            'Platforms' => 'Youtube',
            'Editor' => 'Editor Update',
            'Talent' => 'Talent C',
            'Status' => 'DONE',
            'Tanggal_Rencana' => '2026-06-27',
        ])->assertOk()
            ->assertJsonPath('data.Judul', 'Konten CRUD Update')
            ->assertJsonPath('data.Talent', 'Talent C');

        $this->assertDatabaseHas('master_plans', [
            'source_id' => 'CRUD-MASTER-001',
            'title' => 'Konten CRUD Update',
            'platforms' => 'Youtube',
            'talent' => 'Talent C',
            'updated_by_user_id' => $userId,
        ]);

        $this->deleteJson('/api/master-plans/CRUD-MASTER-001')->assertOk();
        $this->assertDatabaseMissing('master_plans', ['source_id' => 'CRUD-MASTER-001']);
    }

    public function test_master_plan_create_and_update_use_laravel_validated_payloads(): void
    {
        $response = $this->postJson('/api/master-plans', [
            'ID' => 'VALIDATED-MASTER-001',
            'Judul' => 'Validated Master Plan',
            'Editor' => 'Editor Test',
            'Tanggal_Rencana' => '2026-06-26',
            'Unexpected' => 'discarded',
        ]);
        $response->assertCreated();

        $this->assertDatabaseMissing('master_plans', [
            'source_id' => 'VALIDATED-MASTER-001',
            'raw_payload' => json_encode([
                'ID' => 'VALIDATED-MASTER-001',
                'Judul' => 'Validated Master Plan',
                'Tanggal_Rencana' => '2026-06-26',
                'Unexpected' => 'discarded',
            ]),
        ]);

        $this->putJson('/api/master-plans/VALIDATED-MASTER-001', [
            'Judul' => ['not a string'],
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('Judul');
    }

    public function test_distribution_crud_api_works(): void
    {
        $masterPlanId = DB::table('master_plans')->insertGetId([
            'source_id' => 'CRUD-MASTER-002',
            'title' => 'Parent Master',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $userId = auth()->id();

        $id = $this->postJson('/api/distributions', [
            'Master_ID' => 'CRUD-MASTER-002',
            'Judul' => 'Distribusi CRUD',
            'Platform' => 'Instagram',
            'Tanggal_Publish' => '2026-06-26',
            'Link' => 'https://example.test/original',
            'Type' => 'Regular',
        ])->assertCreated()->json('data.ID');

        $this->assertDatabaseHas('distributions', [
            'id' => $id,
            'master_id' => 'CRUD-MASTER-002',
            'master_plan_id' => $masterPlanId,
            'link' => 'https://example.test/original',
            'created_by_user_id' => $userId,
            'updated_by_user_id' => $userId,
        ]);

        $this->putJson("/api/distributions/{$id}", [
            'Master_ID' => 'CRUD-MASTER-002',
            'Judul' => 'Distribusi CRUD Update',
            'Platform' => 'Instagram',
            'Tanggal_Publish' => '2026-06-27',
            'Link' => 'https://example.test/update',
            'Type' => 'Boost',
        ])->assertOk()->assertJsonPath('data.Link', 'https://example.test/update');

        $this->assertDatabaseHas('distributions', [
            'id' => $id,
            'updated_by_user_id' => $userId,
        ]);

        $this->deleteJson("/api/distributions/{$id}")->assertOk();
        $this->assertDatabaseMissing('distributions', ['id' => $id]);
    }

    public function test_analytics_crud_api_works(): void
    {
        $masterPlanId = DB::table('master_plans')->insertGetId([
            'source_id' => 'CRUD-MASTER-003',
            'title' => 'Parent Analytics',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $userId = auth()->id();

        DB::table('meta_ig_posts')->insert([
            'post_id' => '17912380476180805',
            'dataset' => 'feed',
            'views' => 321,
            'likes' => 45,
            'comments' => 6,
            'shares' => 7,
            'imported_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $id = $this->postJson('/api/analytics', [
            'Master_ID' => 'CRUD-MASTER-003',
            'Judul' => 'Analytics CRUD',
            'Platform' => 'Youtube',
            'ID_Post' => '17912380476180805',
            'Tanggal_Publish' => '2026-06-26',
            'Views' => 10,
            'Likes' => 2,
            'Comments' => 1,
            'Shares' => 0,
        ])->assertCreated()->assertJsonPath('data.ID_Post', '17912380476180805')->assertJsonPath('data.Views', 321)->json('data.ID');

        $this->assertDatabaseHas('analytics', [
            'id' => $id,
            'master_id' => 'CRUD-MASTER-003',
            'master_plan_id' => $masterPlanId,
            'id_post' => '17912380476180805',
            'views' => 321,
            'likes' => 45,
            'comments' => 6,
            'shares' => 7,
            'created_by_user_id' => $userId,
            'updated_by_user_id' => $userId,
        ]);

        $this->putJson("/api/analytics/{$id}", [
            'Master_ID' => 'CRUD-MASTER-003',
            'Judul' => 'Analytics CRUD Update',
            'Platform' => 'Youtube',
            'Tanggal_Publish' => '2026-06-27',
            'Views' => 100,
            'Likes' => 20,
            'Comments' => 3,
            'Shares' => 1,
        ])->assertOk()->assertJsonPath('data.Views', 100);

        $this->assertDatabaseHas('analytics', [
            'id' => $id,
            'updated_by_user_id' => $userId,
        ]);

        $this->deleteJson("/api/analytics/{$id}")->assertOk();
        $this->assertDatabaseMissing('analytics', ['id' => $id]);
    }

    public function test_lpjk_detail_crud_tracks_parent_id_and_actor_user(): void
    {
        $lpjkId = DB::table('lpjk')->insertGetId([
            'source_id' => 'LPJK-001',
            'nama_event' => 'Event A',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $userId = auth()->id();

        $sourceId = $this->postJson('/api/lpjk-detail', [
            'Master_ID' => 'LPJK-001',
            'Kategori' => 'Transport',
            'Nama_Pengeluaran' => 'Grab',
            'Satuan' => 'Trip',
            'Jumlah' => 1,
            'Total' => 50000,
        ])->assertCreated()->json('data.source_id');

        $this->assertDatabaseHas('lpjk_detail', [
            'source_id' => $sourceId,
            'master_id' => 'LPJK-001',
            'lpjk_id' => $lpjkId,
            'created_by_user_id' => $userId,
            'updated_by_user_id' => $userId,
        ]);
    }

    public function test_analytics_api_excludes_content_type_pseudo_platform_rows(): void
    {
        DB::table('analytics')->insert([
            [
                'master_id' => 'KONTEN-BUG-001',
                'title' => 'Bug Row',
                'platform' => 'contentType',
                'tanggal_publish' => null,
                'views' => 0,
                'likes' => 0,
                'comments' => 0,
                'shares' => 0,
                'raw_payload' => json_encode(['Platform' => 'contentType']),
                'converted_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'master_id' => 'KONTEN-BUG-001',
                'title' => 'Bug Row',
                'platform' => 'Instagram',
                'tanggal_publish' => '2026-06-26',
                'views' => 1,
                'likes' => 2,
                'comments' => 3,
                'shares' => 4,
                'raw_payload' => json_encode(['Platform' => 'Instagram']),
                'converted_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->getJson('/api/analytics')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.Platform', 'Instagram');
    }

    public function test_analytics_id_post_syncs_metrics_from_meta_ig(): void
    {
        DB::table('master_plans')->insert([
            'source_id' => 'SYNC-MASTER-001',
            'title' => 'Sync Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('meta_ig_posts')->insert([
            'post_id' => 'POST-SYNC-001',
            'dataset' => 'feed',
            'views' => 999,
            'likes' => 88,
            'comments' => 77,
            'shares' => 66,
            'imported_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $id = $this->postJson('/api/analytics', [
            'Master_ID' => 'SYNC-MASTER-001',
            'Judul' => 'Sync Test',
            'Platform' => 'Instagram',
            'ID_Post' => 'POST-SYNC-001',
            'Tanggal_Publish' => '2026-07-01',
            'Views' => 1,
            'Likes' => 2,
            'Comments' => 3,
            'Shares' => 4,
        ])->assertCreated()->json('data.ID');

        $this->assertDatabaseHas('analytics', [
            'id' => $id,
            'id_post' => 'POST-SYNC-001',
            'views' => 999,
            'likes' => 88,
            'comments' => 77,
            'shares' => 66,
        ]);

        $this->getJson('/api/analytics')
            ->assertOk()
            ->assertJsonPath('data.0.ID_Post', 'POST-SYNC-001')
            ->assertJsonPath('data.0.Views', 999);

        $this->deleteJson("/api/analytics/{$id}")->assertOk();
    }

    public function test_settings_crud_api_works(): void
    {
        DB::table('marketing_settings')->insert([
            'key' => 'Old_Key',
            'values' => json_encode(['OLD']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->putJson('/api/settings', [
            'Format_Konten' => ['REELS', 'VIDEO'],
            'Platforms' => ['Instagram'],
            'Talent' => ['Talent A', 'Talent B'],
        ])->assertOk()->assertJsonPath('data.Format_Konten.0', 'REELS');

        $this->assertDatabaseHas('marketing_settings', ['key' => 'Old_Key']);
        $this->assertDatabaseHas('marketing_settings', ['key' => 'Format_Konten']);
        $this->assertDatabaseHas('marketing_settings', ['key' => 'Talent']);
        $this->getJson('/api/settings')
            ->assertOk()
            ->assertJsonPath('data.Format_Konten.1', 'VIDEO')
            ->assertJsonPath('data.Platforms.0', 'Instagram')
            ->assertJsonPath('data.Talent.1', 'Talent B');

        $this->putJson('/api/settings', [])->assertOk();
        $this->assertDatabaseHas('marketing_settings', ['key' => 'Old_Key']);
        $this->assertDatabaseHas('marketing_settings', ['key' => 'Format_Konten']);
        $this->assertDatabaseHas('marketing_settings', ['key' => 'Talent']);
    }

    public function test_settings_api_rejects_non_array_values(): void
    {
        $this->putJson('/api/settings', ['data' => ['Format_Konten' => 'REELS']])
            ->assertUnprocessable();
    }

    public function test_settings_api_rejects_more_than_one_hundred_keys(): void
    {
        $this->putJson('/api/settings', ['data' => array_fill_keys(range(1, 101), [])])
            ->assertUnprocessable();
    }

    public function test_distribution_and_analytics_reads_fall_back_to_foreign_key_parent_source_id(): void
    {
        $masterPlanId = DB::table('master_plans')->insertGetId([
            'source_id' => 'FK-MASTER-001',
            'title' => 'FK Parent',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('distributions')->insert([
            'master_id' => '',
            'master_plan_id' => $masterPlanId,
            'title' => 'Distribusi FK',
            'platform' => 'Instagram',
            'tanggal_publish' => '2026-06-26',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('analytics')->insert([
            'master_id' => '',
            'master_plan_id' => $masterPlanId,
            'title' => 'Analytics FK',
            'platform' => 'Youtube',
            'tanggal_publish' => '2026-06-26',
            'views' => 5,
            'likes' => 1,
            'comments' => 0,
            'shares' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->getJson('/api/distributions')
            ->assertOk()
            ->assertJsonPath('data.0.Master_ID', 'FK-MASTER-001');

        $this->getJson('/api/analytics')
            ->assertOk()
            ->assertJsonPath('data.0.Master_ID', 'FK-MASTER-001');

        $this->getJson('/api/all-data')
            ->assertOk()
            ->assertJsonPath('distribution.0.Master_ID', 'FK-MASTER-001')
            ->assertJsonPath('analytics.0.Master_ID', 'FK-MASTER-001');
    }

    public function test_lpjk_detail_read_falls_back_to_foreign_key_parent_source_id(): void
    {
        $lpjkId = DB::table('lpjk')->insertGetId([
            'source_id' => 'FK-LPJK-001',
            'nama_event' => 'Event FK',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('lpjk_detail')->insert([
            'source_id' => 'FK-LPJKD-001',
            'master_id' => '',
            'lpjk_id' => $lpjkId,
            'kategori' => 'Transport',
            'nama_pengeluaran' => 'Grab',
            'satuan' => 'Trip',
            'jumlah' => 1,
            'total' => 50000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->getJson('/api/lpjk-detail')
            ->assertOk()
            ->assertJsonPath('data.0.Master_ID', 'FK-LPJK-001');

        $this->getJson('/api/all-data')
            ->assertOk()
            ->assertJsonPath('lpjkDetail.0.Master_ID', 'FK-LPJK-001');
    }

    public function test_distribution_and_analytics_reject_unknown_master_plan_parent(): void
    {
        $this->postJson('/api/distributions', [
            'Master_ID' => 'UNKNOWN-MASTER-001',
            'Judul' => 'Distribusi Invalid',
            'Platform' => 'Instagram',
        ])->assertStatus(422)
            ->assertSee('Master plan tidak ditemukan.');

        $this->postJson('/api/analytics', [
            'Master_ID' => 'UNKNOWN-MASTER-001',
            'Judul' => 'Analytics Invalid',
            'Platform' => 'Youtube',
        ])->assertStatus(422)
            ->assertSee('Master plan tidak ditemukan.');
    }

    public function test_lpjk_detail_rejects_unknown_lpjk_parent(): void
    {
        $this->postJson('/api/lpjk-detail', [
            'Master_ID' => 'UNKNOWN-LPJK-001',
            'Kategori' => 'Transport',
            'Nama_Pengeluaran' => 'Grab',
        ])->assertStatus(422)
            ->assertSee('LPJK tidak ditemukan.');
    }

    public function test_crud_actions_write_activity_logs(): void
    {
        $userId = auth()->id();

        $this->postJson('/api/master-plans', [
            'ID' => 'LOG-MASTER-001',
            'Judul' => 'Konten Audit',
            'Format_Konten' => 'REELS',
            'Platforms' => 'Instagram',
            'Editor' => 'Editor Audit',
            'Status' => 'IDE',
            'Tanggal_Rencana' => '2026-06-26',
        ])->assertCreated();

        $this->putJson('/api/master-plans/LOG-MASTER-001', [
            'Judul' => 'Konten Audit Update',
            'Format_Konten' => 'VIDEO',
            'Platforms' => 'Youtube',
            'Editor' => 'Editor Audit',
            'Status' => 'DONE',
            'Tanggal_Rencana' => '2026-06-27',
        ])->assertOk();

        $this->deleteJson('/api/master-plans/LOG-MASTER-001')->assertOk();

        $this->assertDatabaseHas('activity_logs', [
            'table_name' => 'master_plans',
            'action' => 'create',
            'record_key' => 'LOG-MASTER-001',
            'user_id' => $userId,
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'table_name' => 'master_plans',
            'action' => 'update',
            'record_key' => 'LOG-MASTER-001',
            'user_id' => $userId,
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'table_name' => 'master_plans',
            'action' => 'delete',
            'record_key' => 'LOG-MASTER-001',
            'user_id' => $userId,
        ]);

        $this->postJson('/api/unboxing', [
            'ID' => 'LOG-UNBOX-001',
            'Nama' => 'Unit Demo',
            'Editor' => 'Editor Demo',
            'Status' => 'Draft',
            'Upload_Date' => '2026-06-26',
        ])->assertCreated();

        $this->putJson('/api/unboxing/LOG-UNBOX-001', [
            'Nama' => 'Unit Demo Update',
            'Editor' => 'Editor Demo',
            'Status' => 'Publish',
            'Upload_Date' => '2026-06-27',
        ])->assertOk();

        $this->deleteJson('/api/unboxing/LOG-UNBOX-001')->assertOk();

        $this->assertDatabaseHas('activity_logs', [
            'table_name' => 'unboxing',
            'action' => 'create',
            'record_key' => 'LOG-UNBOX-001',
            'user_id' => $userId,
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'table_name' => 'unboxing',
            'action' => 'update',
            'record_key' => 'LOG-UNBOX-001',
            'user_id' => $userId,
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'table_name' => 'unboxing',
            'action' => 'delete',
            'record_key' => 'LOG-UNBOX-001',
            'user_id' => $userId,
        ]);
    }

    public function test_activity_logs_api_returns_latest_logs_with_filters(): void
    {
        DB::table('activity_logs')->insert([
            [
                'user_id' => auth()->id(),
                'actor_label' => 'admin',
                'table_name' => 'master_plans',
                'action' => 'create',
                'record_key' => 'LOG-001',
                'record_id' => 1,
                'before_payload' => null,
                'after_payload' => json_encode(['foo' => 'bar']),
                'created_at' => now()->subMinute(),
            ],
            [
                'user_id' => auth()->id(),
                'actor_label' => 'admin',
                'table_name' => 'analytics',
                'action' => 'update',
                'record_key' => 'LOG-002',
                'record_id' => 2,
                'before_payload' => json_encode(['views' => 1]),
                'after_payload' => json_encode(['views' => 2]),
                'created_at' => now(),
            ],
        ]);

        $this->getJson('/api/activity-logs')
            ->assertOk()
            ->assertJsonPath('data.0.table_name', 'analytics')
            ->assertJsonPath('data.0.action', 'update')
            ->assertJsonPath('data.0.record_key', 'LOG-002')
            ->assertJsonPath('data.1.table_name', 'master_plans');

        $this->getJson('/api/activity-logs?table_name=master_plans')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.record_key', 'LOG-001');

        $this->getJson('/api/activity-logs?action=update')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.table_name', 'analytics');
    }

    public function test_harga_kompetitor_api_persists_product_detail_fields(): void
    {
        $this->postJson('/api/harga-kompetitor', [
            'ID' => 'HK-DETAIL-001',
            'Nama_Produk' => 'Samsung S25 12GB 256GB Garansi TAM',
            'KATEGORI' => 'SMARTPHONE',
            'BRAND' => 'Samsung',
            'SERI' => 'S25',
            'RAM' => '12GB',
            'INTERNAL' => '256GB',
            'SIZE' => 'Garansi TAM',
            'WARNA' => 'Navy',
            'Harga_Distributor_1' => 12000000,
            'Harga_Distributor_2' => 12100000,
            'Harga_Kompetitor' => 12500000,
            'Margin_Profit' => 400000,
            'Harga_Rencana_Jual' => 12400000,
            'Tanggal_Cek' => '2026-07-21',
        ])->assertCreated();

        $this->assertDatabaseHas('harga_kompetitor', [
            'source_id' => 'HK-DETAIL-001',
            'kategori' => 'SMARTPHONE',
            'brand' => 'Samsung',
            'seri' => 'S25',
            'ram' => '12GB',
            'internal' => '256GB',
            'size' => 'Garansi TAM',
            'warna' => 'Navy',
        ]);

        $this->putJson('/api/harga-kompetitor/HK-DETAIL-001', [
            'Nama_Produk' => 'Samsung S25 Ultra 12GB 512GB Garansi TAM',
            'KATEGORI' => 'SMARTPHONE',
            'BRAND' => 'Samsung',
            'SERI' => 'S25 Ultra',
            'RAM' => '12GB',
            'INTERNAL' => '512GB',
            'SIZE' => 'Garansi TAM',
            'WARNA' => 'Silver',
            'Harga_Distributor_1' => 15000000,
            'Harga_Distributor_2' => 15100000,
            'Harga_Kompetitor' => 15600000,
            'Margin_Profit' => 400000,
            'Harga_Rencana_Jual' => 15500000,
            'Tanggal_Cek' => '2026-07-21',
        ])->assertOk();

        $this->getJson('/api/harga-kompetitor')
            ->assertOk()
            ->assertJsonPath('data.0.ID', 'HK-DETAIL-001')
            ->assertJsonPath('data.0.SERI', 'S25 Ultra')
            ->assertJsonPath('data.0.INTERNAL', '512GB')
            ->assertJsonPath('data.0.WARNA', 'Silver');
    }
}
