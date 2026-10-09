<?php

namespace Tests\Feature;

use App\Support\TiktokBatchTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class TiktokTemplateTest extends TestCase
{
    use RefreshDatabase;

    private function fixture(): string
    {
        return base_path('tests/Fixtures/tiktok-batchedit-template.xlsx');
    }

    private function upload(): UploadedFile
    {
        return new UploadedFile($this->fixture(), 'template.xlsx', null, null, true);
    }

    public function test_page_renders_in_dashboard_shell(): void
    {
        $this->get('/ecommerce/tiktok-template')
            ->assertOk()
            ->assertSee("activeTab === 'tiktok_template'", false);
    }

    public function test_parse_returns_columns_rows_and_readonly_flags(): void
    {
        $json = $this->postJson('/api/tiktok-template/parse', ['file' => $this->upload()])
            ->assertOk()
            ->json();

        $this->assertSame('product_id', $json['columns'][0]['key']);
        $this->assertTrue($json['columns'][0]['readonly']);
        $this->assertCount(8, $json['rows']);
        $this->assertContains('Aktif(1)', $json['statusOptions']);
    }

    public function test_export_applies_edits_and_keeps_workbook_structure(): void
    {
        $parsed = (new TiktokBatchTemplate)->parse($this->fixture());
        $rows = $parsed['rows'];
        $rows[0][8] = '12345000';
        $rows[0][2] = '[PURA PURA PONSEL] Judul & "uji" <tag> 123';
        array_pop($rows);

        $response = $this->post('/api/tiktok-template/export', [
            'file' => $this->upload(),
            'rows' => json_encode($rows),
        ]);
        $response->assertOk();

        $out = tempnam(sys_get_temp_dir(), 'tt-test-');
        copy($response->baseResponse->getFile()->getPathname(), $out);

        try {
            $reparsed = (new TiktokBatchTemplate)->parse($out);
            $this->assertCount(7, $reparsed['rows']);
            $this->assertSame('12345000', $reparsed['rows'][0][8]);
            $this->assertSame('[PURA PURA PONSEL] Judul & "uji" <tag> 123', $reparsed['rows'][0][2]);
            $this->assertSame($parsed['rows'][1], $reparsed['rows'][1]);

            $zip = new \ZipArchive;
            $zip->open($out);
            $workbook = $zip->getFromName('xl/workbook.xml');
            $zip->close();
            $this->assertSame(6, substr_count($workbook, 'state="hidden"'));
        } finally {
            @unlink($out);
        }
    }

    public function test_export_rejects_more_rows_than_template_capacity(): void
    {
        $parsed = (new TiktokBatchTemplate)->parse($this->fixture());
        $rows = array_merge($parsed['rows'], [$parsed['rows'][0]]);

        $this->postJson('/api/tiktok-template/export', [
            'file' => $this->upload(),
            'rows' => json_encode($rows),
        ])->assertStatus(422);
    }

    public function test_rejects_non_template_upload(): void
    {
        $file = UploadedFile::fake()->createWithContent('bad.xlsx', 'not a zip');

        $this->postJson('/api/tiktok-template/parse', ['file' => $file])->assertStatus(422);
    }

    public function test_requires_authentication(): void
    {
        $this->app['auth']->logout();

        $this->postJson('/api/tiktok-template/parse', ['file' => $this->upload()])->assertUnauthorized();
    }
}
