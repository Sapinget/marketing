<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use ZipArchive;

class RemainingWorkbookImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_remaining_workbook_import_loads_non_structured_sheets_as_raw_rows(): void
    {
        $path = $this->createWorkbookFixture();

        $this->artisan('marketing:import-remaining-workbook', [
            'path' => $path,
            '--truncate' => true,
        ])->assertExitCode(0);

        $this->assertDatabaseCount('marketing_excel_rows', 3);
        $this->assertDatabaseHas('marketing_excel_rows', [
            'sheet_name' => 'Unboxing',
            'row_number' => 2,
        ]);
        $this->assertDatabaseHas('marketing_excel_rows', [
            'sheet_name' => 'Orderan_Online',
            'row_number' => 3,
        ]);
        $this->assertDatabaseMissing('marketing_excel_rows', [
            'sheet_name' => 'Master_Plan',
        ]);
        $this->assertDatabaseMissing('marketing_excel_rows', [
            'sheet_name' => 'Settings',
        ]);

        $payload = DB::table('marketing_excel_rows')
            ->where('sheet_name', 'Unboxing')
            ->where('row_number', 2)
            ->value('payload');

        $this->assertSame('UBX-001', json_decode($payload, true)['ID']);
    }

    public function test_raw_sheet_api_returns_and_replaces_rows(): void
    {
        DB::table('stock_names')->insert([
            'source_id' => 'NS-001',
            'kategori' => 'HP',
            'brand' => 'APPLE',
            'seri' => 'IPHONE 15',
            'imported_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->getJson('/api/raw-sheets/Nama_Stock')
            ->assertOk()
            ->assertJsonPath('data.0.ID', 'NS-001')
            ->assertJsonPath('data.0.BRAND', 'APPLE');

        $this->putJson('/api/raw-sheets/Nama_Stock', [
            'data' => [
                ['ID' => 'NS-002', 'KATEGORI' => 'HP', 'BRAND' => 'SAMSUNG', 'SERI' => 'A55'],
                ['ID' => 'NS-003', 'KATEGORI' => 'TABLET', 'BRAND' => 'APPLE', 'SERI' => 'IPAD'],
            ],
        ])->assertOk()->assertJsonPath('data.1.ID', 'NS-003');

        $this->assertDatabaseMissing('stock_names', ['source_id' => 'NS-001']);
        $this->assertDatabaseCount('stock_names', 2);
        $this->getJson('/api/raw-sheets/Nama_Stock')
            ->assertOk()
            ->assertJsonPath('data.0.ID', 'NS-002')
            ->assertJsonPath('data.1.SERI', 'IPAD');
    }

    public function test_nama_stock_raw_sheet_api_deduplicates_by_kategori_brand_and_seri(): void
    {
        $this->putJson('/api/raw-sheets/Nama_Stock', [
            'data' => [
                ['ID' => 'NS-001', 'KATEGORI' => 'HP', 'BRAND' => 'APPLE', 'SERI' => 'IPHONE 15'],
                ['ID' => 'NS-999', 'KATEGORI' => ' hp ', 'BRAND' => ' apple ', 'SERI' => ' iphone 15 '],
                ['ID' => 'NS-002', 'KATEGORI' => 'TABLET', 'BRAND' => 'APPLE', 'SERI' => 'IPAD'],
            ],
        ])->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.ID', 'NS-001')
            ->assertJsonPath('data.0.KATEGORI', 'HP')
            ->assertJsonPath('data.0.BRAND', 'APPLE')
            ->assertJsonPath('data.0.SERI', 'IPHONE 15')
            ->assertJsonPath('data.1.ID', 'NS-002');

        $this->assertDatabaseCount('stock_names', 2);

        $this->getJson('/api/raw-sheets/Nama_Stock')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.ID', 'NS-001')
            ->assertJsonPath('data.0.KATEGORI', 'HP')
            ->assertJsonPath('data.0.BRAND', 'APPLE')
            ->assertJsonPath('data.0.SERI', 'IPHONE 15')
            ->assertJsonPath('data.1.ID', 'NS-002');
    }

    public function test_nama_stock_api_no_longer_persists_rows_in_marketing_excel_rows(): void
    {
        $this->putJson('/api/raw-sheets/Nama_Stock', [
            'data' => [
                ['ID' => 'NS-010', 'KATEGORI' => 'HP', 'BRAND' => 'APPLE', 'SERI' => 'IPHONE 16'],
            ],
        ])->assertOk();

        $this->assertDatabaseCount('stock_names', 1);
        $this->assertDatabaseCount('marketing_excel_rows', 0);
    }

    private function createWorkbookFixture(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'remaining-workbook-').'.xlsx';
        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $zip->addFromString('[Content_Types].xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/worksheets/sheet2.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/worksheets/sheet3.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/worksheets/sheet4.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
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
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet2.xml"/>
  <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet3.xml"/>
  <Relationship Id="rId4" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet4.xml"/>
  <Relationship Id="rId5" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>
</Relationships>
XML);

        $zip->addFromString('xl/workbook.xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="Master_Plan" sheetId="1" r:id="rId1"/>
    <sheet name="Settings" sheetId="2" r:id="rId2"/>
    <sheet name="Unboxing" sheetId="3" r:id="rId3"/>
    <sheet name="Orderan_Online" sheetId="4" r:id="rId4"/>
  </sheets>
</workbook>
XML);

        $strings = ['ID', 'Judul', 'UBX-001', 'Unboxing A', 'ORD-001', 'Order A', 'ORD-002', 'Order B'];
        $shared = '<?xml version="1.0" encoding="UTF-8"?><sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="'.count($strings).'" uniqueCount="'.count($strings).'">';
        foreach ($strings as $value) {
            $shared .= '<si><t>'.htmlspecialchars($value, ENT_XML1).'</t></si>';
        }
        $shared .= '</sst>';
        $zip->addFromString('xl/sharedStrings.xml', $shared);

        $emptyStructuredSheet = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <sheetData><row r="1"><c r="A1" t="s"><v>0</v></c></row></sheetData>
</worksheet>
XML;
        $zip->addFromString('xl/worksheets/sheet1.xml', $emptyStructuredSheet);
        $zip->addFromString('xl/worksheets/sheet2.xml', $emptyStructuredSheet);
        $zip->addFromString('xl/worksheets/sheet3.xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <sheetData>
    <row r="1"><c r="A1" t="s"><v>0</v></c><c r="B1" t="s"><v>1</v></c></row>
    <row r="2"><c r="A2" t="s"><v>2</v></c><c r="B2" t="s"><v>3</v></c></row>
  </sheetData>
</worksheet>
XML);
        $zip->addFromString('xl/worksheets/sheet4.xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <sheetData>
    <row r="1"><c r="A1" t="s"><v>0</v></c><c r="B1" t="s"><v>1</v></c></row>
    <row r="2"><c r="A2" t="s"><v>4</v></c><c r="B2" t="s"><v>5</v></c></row>
    <row r="3"><c r="A3" t="s"><v>6</v></c><c r="B3" t="s"><v>7</v></c></row>
  </sheetData>
</worksheet>
XML);

        $zip->close();

        return $path;
    }
}
