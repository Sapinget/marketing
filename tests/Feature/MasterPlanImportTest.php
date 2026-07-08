<?php

namespace Tests\Feature;

use App\Support\XlsxSheetReader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use ZipArchive;

class MasterPlanImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_xlsx_reader_reads_master_plan_rows_with_dates(): void
    {
        $path = $this->createWorkbookFixture();

        $rows = (new XlsxSheetReader)->rows($path, 'Master_Plan');

        $this->assertCount(2, $rows);
        $this->assertSame('Konten-00001', $rows[0]['ID']);
        $this->assertSame('Silent Call', $rows[0]['Judul']);
        $this->assertSame('2025-12-26', $rows[0]['Tanggal_Rencana']);
        $this->assertSame('{"Instagram":{"link":"https://example.test/reel","date":"2025-12-26","type":"Regular"}}', $rows[0]['Distribution_Meta']);
    }

    public function test_import_command_upserts_master_plan_sheet_to_database(): void
    {
        $path = $this->createWorkbookFixture();

        $this->artisan('marketing:import-master-plan', [
            'path' => $path,
            '--truncate' => true,
        ])->assertExitCode(0);

        $this->assertDatabaseCount('master_plans', 2);
        $this->assertDatabaseHas('master_plans', [
            'source_id' => 'Konten-00001',
            'title' => 'Silent Call',
            'format_konten' => 'EDUKASI',
            'platforms' => 'Instagram, Tiktok Official',
            'editor' => 'Sudana',
            'status' => 'PUBLISHED',
            'tanggal_rencana' => '2025-12-26',
        ]);

        $payload = DB::table('master_plans')->where('source_id', 'Konten-00001')->value('raw_payload');
        $this->assertSame('https://drive.google.com/file/d/test/view', json_decode($payload, true)['Link_Drive']);

        $this->artisan('marketing:import-master-plan', [
            'path' => $path,
        ])->assertExitCode(0);

        $this->assertDatabaseCount('master_plans', 2);
    }

    private function createWorkbookFixture(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'master-plan-').'.xlsx';
        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $zip->addFromString('[Content_Types].xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>
</Types>
XML);

        $zip->addFromString('_rels/.rels', <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>
XML);

        $zip->addFromString('xl/_rels/workbook.xml.rels', <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>
</Relationships>
XML);

        $zip->addFromString('xl/workbook.xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="Master_Plan" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>
XML);

        $strings = [
            'ID', 'Judul', 'Format_Konten', 'Platforms', 'Colab', 'Editor', 'Skrip', 'Caption', 'Status', 'Tanggal_Rencana', 'Distribution_Meta', 'Link_Drive',
            'Konten-00001', 'Silent Call', 'EDUKASI', 'Instagram, Tiktok Official', 'Sudana', 'PUBLISHED', '{"Instagram":{"link":"https://example.test/reel","date":"2025-12-26","type":"Regular"}}', 'https://drive.google.com/file/d/test/view',
            'Konten-00002', 'iP 11 VS iP 12', 'VELOCITY', 'Youtube', 'Abi', '{"Youtube":{"link":"","date":"2025-12-27","type":"Regular"}}',
        ];
        $shared = '<?xml version="1.0" encoding="UTF-8"?><sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="'.count($strings).'" uniqueCount="'.count($strings).'">';
        foreach ($strings as $value) {
            $shared .= '<si><t>'.htmlspecialchars($value, ENT_XML1).'</t></si>';
        }
        $shared .= '</sst>';
        $zip->addFromString('xl/sharedStrings.xml', $shared);

        $zip->addFromString('xl/worksheets/sheet1.xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <sheetData>
    <row r="1"><c r="A1" t="s"><v>0</v></c><c r="B1" t="s"><v>1</v></c><c r="C1" t="s"><v>2</v></c><c r="D1" t="s"><v>3</v></c><c r="E1" t="s"><v>4</v></c><c r="F1" t="s"><v>5</v></c><c r="G1" t="s"><v>6</v></c><c r="H1" t="s"><v>7</v></c><c r="I1" t="s"><v>8</v></c><c r="J1" t="s"><v>9</v></c><c r="K1" t="s"><v>10</v></c><c r="L1" t="s"><v>11</v></c></row>
    <row r="2"><c r="A2" t="s"><v>12</v></c><c r="B2" t="s"><v>13</v></c><c r="C2" t="s"><v>14</v></c><c r="D2" t="s"><v>15</v></c><c r="F2" t="s"><v>16</v></c><c r="I2" t="s"><v>17</v></c><c r="J2"><v>46017</v></c><c r="K2" t="s"><v>18</v></c><c r="L2" t="s"><v>19</v></c></row>
    <row r="3"><c r="A3" t="s"><v>20</v></c><c r="B3" t="s"><v>21</v></c><c r="C3" t="s"><v>22</v></c><c r="D3" t="s"><v>23</v></c><c r="F3" t="s"><v>24</v></c><c r="I3" t="s"><v>17</v></c><c r="J3"><v>46018</v></c><c r="K3" t="s"><v>25</v></c></row>
  </sheetData>
</worksheet>
XML);

        $zip->close();

        return $path;
    }
}
