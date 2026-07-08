<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LpjkRelationRepairCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_lpjk_relation_repair_command_creates_missing_parent_and_backfills_foreign_key(): void
    {
        DB::table('lpjk_detail')->insert([
            'source_id' => 'LPJKD-TEST-001',
            'master_id' => 'LJ-MISSING-001',
            'lpjk_id' => null,
            'kategori' => 'Promosi',
            'nama_pengeluaran' => 'GOPAY',
            'satuan' => 'Paket',
            'jumlah' => 1,
            'total' => 150000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->artisan('marketing:repair-lpjk-relations', ['--create-missing' => true])
            ->expectsOutputToContain('details_fixed:')
            ->expectsOutputToContain('parents_created:')
            ->assertExitCode(0);

        $parent = DB::table('lpjk')->where('source_id', 'LJ-MISSING-001')->first();

        $this->assertNotNull($parent);
        $this->assertSame('Recovered LPJK LJ-MISSING-001', $parent->nama_event);
        $this->assertDatabaseHas('lpjk_detail', [
            'source_id' => 'LPJKD-TEST-001',
            'master_id' => 'LJ-MISSING-001',
            'lpjk_id' => $parent->id,
        ]);
    }
}
