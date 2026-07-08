<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MasterPlanConversionTest extends TestCase
{
    use RefreshDatabase;

    public function test_convert_master_plan_creates_distribution_and_analytics_rows(): void
    {
        Carbon::setTestNow('2026-06-29 10:00:00');

        DB::table('master_plans')->insert([
            'source_id' => 'Konten-001',
            'title' => 'Konten Database',
            'format_konten' => 'EDUKASI',
            'platforms' => 'Instagram, Youtube',
            'editor' => 'Editor A',
            'status' => 'PUBLISHED',
            'tanggal_rencana' => '2026-06-26',
            'distribution_meta' => json_encode([
                'Instagram' => ['link' => 'https://instagram.test/post', 'date' => '2026-06-27', 'type' => 'Regular'],
                'Youtube' => ['link' => '', 'date' => '2026-06-28', 'type' => 'Colab'],
                'contentType' => 'Regular',
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'raw_payload' => json_encode(['ID' => 'Konten-001']),
            'imported_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->artisan('marketing:convert-master-plan', [
            '--truncate' => true,
        ])->assertExitCode(0);

        $this->assertDatabaseCount('distributions', 2);
        $this->assertDatabaseHas('distributions', [
            'master_id' => 'Konten-001',
            'title' => 'Konten Database',
            'platform' => 'Instagram',
            'tanggal_publish' => '2026-06-27',
            'link' => 'https://instagram.test/post',
            'type' => 'Regular',
        ]);
        $this->assertDatabaseHas('distributions', [
            'master_id' => 'Konten-001',
            'platform' => 'Youtube',
            'tanggal_publish' => '2026-06-28',
            'link' => null,
            'type' => 'Colab',
        ]);

        $this->assertDatabaseCount('analytics', 2);
        $this->assertDatabaseHas('analytics', [
            'master_id' => 'Konten-001',
            'title' => 'Konten Database',
            'platform' => 'Instagram',
            'tanggal_publish' => '2026-06-27',
            'views' => 0,
            'likes' => 0,
            'comments' => 0,
            'shares' => 0,
        ]);

        $this->artisan('marketing:convert-master-plan')->assertExitCode(0);

        $this->assertDatabaseCount('distributions', 2);
        $this->assertDatabaseCount('analytics', 2);
    }

    public function test_distribution_and_analytics_apis_return_converted_rows(): void
    {
        DB::table('distributions')->insert([
            'master_id' => 'Konten-API',
            'title' => 'Distribusi API',
            'platform' => 'Instagram',
            'tanggal_publish' => '2026-06-27',
            'link' => 'https://instagram.test/post',
            'type' => 'Regular',
            'raw_payload' => json_encode(['source' => 'test']),
            'converted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('analytics')->insert([
            'master_id' => 'Konten-API',
            'title' => 'Analytics API',
            'platform' => 'Instagram',
            'tanggal_publish' => '2026-06-27',
            'views' => 10,
            'likes' => 2,
            'comments' => 1,
            'shares' => 0,
            'raw_payload' => json_encode(['source' => 'test']),
            'converted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->getJson('/api/distributions')
            ->assertOk()
            ->assertJsonPath('data.0.Master_ID', 'Konten-API')
            ->assertJsonPath('data.0.Judul', 'Distribusi API')
            ->assertJsonPath('data.0.Platform', 'Instagram');

        $this->getJson('/api/analytics')
            ->assertOk()
            ->assertJsonPath('data.0.Master_ID', 'Konten-API')
            ->assertJsonPath('data.0.Judul', 'Analytics API')
            ->assertJsonPath('data.0.Views', 10);
    }

    public function test_master_plan_conversion_follows_published_and_distribution_date_rules(): void
    {
        Carbon::setTestNow('2026-06-26 10:00:00');

        DB::table('master_plans')->insert([
            [
                'source_id' => 'RULE-PUBLISHED',
                'title' => 'Published Rule',
                'status' => 'PUBLISHED',
                'tanggal_rencana' => '2026-06-26',
                'distribution_meta' => json_encode([
                    'Instagram' => ['link' => 'https://example.test/today', 'date' => '2026-06-26', 'type' => 'Regular'],
                    'Youtube' => ['link' => 'https://example.test/past', 'date' => '2026-06-25', 'type' => 'Regular'],
                    'Tiktok' => ['link' => 'https://example.test/future', 'date' => '2026-06-27', 'type' => 'Regular'],
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'source_id' => 'RULE-DRAFT',
                'title' => 'Draft Rule',
                'status' => 'IDE',
                'tanggal_rencana' => '2026-06-26',
                'distribution_meta' => json_encode([
                    'Instagram' => ['link' => 'https://example.test/draft', 'date' => '2026-06-26', 'type' => 'Regular'],
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->artisan('marketing:convert-master-plan', ['--truncate' => true])->assertExitCode(0);

        $this->assertDatabaseCount('distributions', 3);
        $this->assertDatabaseMissing('distributions', ['master_id' => 'RULE-DRAFT']);
        $this->assertDatabaseCount('analytics', 2);
        $this->assertDatabaseHas('analytics', ['master_id' => 'RULE-PUBLISHED', 'platform' => 'Instagram']);
        $this->assertDatabaseHas('analytics', ['master_id' => 'RULE-PUBLISHED', 'platform' => 'Youtube']);
        $this->assertDatabaseMissing('analytics', ['master_id' => 'RULE-PUBLISHED', 'platform' => 'Tiktok']);
    }

    public function test_master_plan_api_syncs_distribution_and_analytics_rules_on_save(): void
    {
        Carbon::setTestNow('2026-06-26 10:00:00');

        $this->postJson('/api/master-plans', [
            'ID' => 'RULE-API',
            'Judul' => 'Rule API',
            'Format_Konten' => 'REELS',
            'Platforms' => 'Instagram, Youtube',
            'Editor' => 'Editor Rule',
            'Status' => 'PUBLISHED',
            'Tanggal_Rencana' => '2026-06-26',
            'Distribution_Meta' => json_encode([
                'Instagram' => ['link' => 'https://example.test/today', 'date' => '2026-06-26', 'type' => 'Regular'],
                'Youtube' => ['link' => 'https://example.test/past', 'date' => '2026-06-25', 'type' => 'Regular'],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ])->assertCreated();

        $this->assertDatabaseCount('distributions', 2);
        $this->assertDatabaseCount('analytics', 2);
        $this->assertDatabaseHas('analytics', ['master_id' => 'RULE-API', 'platform' => 'Instagram']);
        $this->assertDatabaseHas('analytics', ['master_id' => 'RULE-API', 'platform' => 'Youtube']);

        $this->putJson('/api/master-plans/RULE-API', [
            'Judul' => 'Rule API Draft',
            'Format_Konten' => 'REELS',
            'Platforms' => 'Instagram',
            'Editor' => 'Editor Rule',
            'Status' => 'IDE',
            'Tanggal_Rencana' => '2026-06-26',
            'Distribution_Meta' => json_encode([
                'Instagram' => ['link' => 'https://example.test/today', 'date' => '2026-06-26', 'type' => 'Regular'],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ])->assertOk();

        $this->assertDatabaseMissing('distributions', ['master_id' => 'RULE-API']);
        $this->assertDatabaseMissing('analytics', ['master_id' => 'RULE-API']);
    }

    public function test_master_plan_api_sync_deletes_derived_rows_that_only_keep_internal_foreign_key(): void
    {
        Carbon::setTestNow('2026-06-26 10:00:00');

        $masterPlanId = DB::table('master_plans')->insertGetId([
            'source_id' => 'RULE-FK-ONLY',
            'title' => 'Rule FK Only',
            'format_konten' => 'REELS',
            'platforms' => 'Instagram',
            'editor' => 'Editor Rule',
            'status' => 'PUBLISHED',
            'tanggal_rencana' => '2026-06-26',
            'distribution_meta' => json_encode([
                'Instagram' => ['link' => 'https://example.test/today', 'date' => '2026-06-26', 'type' => 'Regular'],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('distributions')->insert([
            'master_id' => '',
            'master_plan_id' => $masterPlanId,
            'title' => 'Distribusi FK Only',
            'platform' => 'Instagram',
            'tanggal_publish' => '2026-06-26',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('analytics')->insert([
            'master_id' => '',
            'master_plan_id' => $masterPlanId,
            'title' => 'Analytics FK Only',
            'platform' => 'Instagram',
            'tanggal_publish' => '2026-06-26',
            'views' => 1,
            'likes' => 0,
            'comments' => 0,
            'shares' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->putJson('/api/master-plans/RULE-FK-ONLY', [
            'Judul' => 'Rule FK Only Draft',
            'Format_Konten' => 'REELS',
            'Platforms' => 'Instagram',
            'Editor' => 'Editor Rule',
            'Status' => 'IDE',
            'Tanggal_Rencana' => '2026-06-26',
            'Distribution_Meta' => json_encode([
                'Instagram' => ['link' => 'https://example.test/today', 'date' => '2026-06-26', 'type' => 'Regular'],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ])->assertOk();

        $this->assertDatabaseMissing('distributions', ['master_plan_id' => $masterPlanId]);
        $this->assertDatabaseMissing('analytics', ['master_plan_id' => $masterPlanId]);
    }
}
