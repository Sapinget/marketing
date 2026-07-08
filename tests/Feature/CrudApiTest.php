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

        $id = $this->postJson('/api/analytics', [
            'Master_ID' => 'CRUD-MASTER-003',
            'Judul' => 'Analytics CRUD',
            'Platform' => 'Youtube',
            'Tanggal_Publish' => '2026-06-26',
            'Views' => 10,
            'Likes' => 2,
            'Comments' => 1,
            'Shares' => 0,
        ])->assertCreated()->json('data.ID');

        $this->assertDatabaseHas('analytics', [
            'id' => $id,
            'master_id' => 'CRUD-MASTER-003',
            'master_plan_id' => $masterPlanId,
            'views' => 10,
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

        $this->assertDatabaseMissing('marketing_settings', ['key' => 'Old_Key']);
        $this->assertDatabaseHas('marketing_settings', ['key' => 'Format_Konten']);
        $this->assertDatabaseHas('marketing_settings', ['key' => 'Talent']);
        $this->getJson('/api/settings')
            ->assertOk()
            ->assertJsonPath('data.Format_Konten.1', 'VIDEO')
            ->assertJsonPath('data.Platforms.0', 'Instagram')
            ->assertJsonPath('data.Talent.1', 'Talent B');

        $this->putJson('/api/settings', [])->assertOk();
        $this->assertDatabaseCount('marketing_settings', 0);
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
}
