<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseReadinessCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_readiness_command_prints_summary_and_can_write_markdown_report(): void
    {
        $reportPath = sys_get_temp_dir().'/marketing-db-readiness-test.md';
        @unlink($reportPath);

        $this->artisan('marketing:db-readiness', [
            '--write-doc' => $reportPath,
        ])
            ->expectsOutput('Database migration readiness audit')
            ->expectsOutputToContain('status:')
            ->expectsOutputToContain('db_connection: sqlite')
            ->expectsOutputToContain('Blocking checks')
            ->expectsOutputToContain('Legacy compatibility checks')
            ->assertExitCode(0);

        $this->assertFileExists($reportPath);
        $this->assertStringContainsString('# MySQL Migration Readiness Report', (string) file_get_contents($reportPath));
        $this->assertStringContainsString('db_connection: `sqlite`', (string) file_get_contents($reportPath));

        @unlink($reportPath);
    }
}
