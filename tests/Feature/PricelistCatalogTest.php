<?php

namespace Tests\Feature;

use App\Support\PricelistSheetImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PricelistCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_pricelist_importer_imports_only_rows_with_urut_and_maps_prices(): void
    {
        $this->actingAsDashboardUser();

        $csv = <<<'CSV'
SAMSUNG KATEGORI,BRAND,SERI,RAM,INTERNAL,SIZE,TIPE,HARGA NASIONAL,CASHBACK,HARGA JUAL,KREDIT,SPESIAL PRICE,STP,PERSENTASE,ONLINE,URUT,LANGKA,SHORTAGE
ANDROID,SAMSUNG,GALAXY A07,4GB,64GB,,SAMSUNG GALAXY A07 4GB/64GB,2.499.000,0,2.499.000,2.649.000,2.379.000,2.324.000,2,2.499.000,1,FALSE,FALSE
ANDROID,SAMSUNG,GALAXY A07,4GB,128GB,,SAMSUNG GALAXY A07 4GB/128GB,2.899.000,0,2.899.000,3.049.000,2.749.000,2.696.000,2,2.899.000,,FALSE,FALSE
CSV;

        $summary = app(PricelistSheetImporter::class)
            ->setCsvFetcher(fn () => $csv)
            ->import(['SAMSUNG']);

        $this->assertSame('success', $summary['status']);
        $this->assertSame(1, $summary['imported']);
        $this->assertSame(1, $summary['skipped']);
        $this->assertDatabaseHas('pricelist_products', [
            'source_sheet' => 'SAMSUNG',
            'source_row' => 2,
            'urut' => 1,
            'kategori' => 'ANDROID',
            'brand' => 'SAMSUNG',
            'nama_produk' => 'SAMSUNG GALAXY A07 4GB/64GB',
            'harga_nasional' => 2499000,
            'special_price' => 2379000,
        ]);
    }

    public function test_catalog_template_api_creates_template_and_uploads_background(): void
    {
        $this->actingAsDashboardUser();

        $template = $this->postJson('/api/catalog-templates', [
            'name' => 'Story Android',
            'format' => 'story',
            'layout_config' => ['x' => 80, 'y' => 520],
        ])->assertCreated()
            ->assertJsonPath('data.name', 'Story Android')
            ->assertJsonPath('data.canvas_width', 1080)
            ->assertJsonPath('data.canvas_height', 1920)
            ->json('data');

        $this->post('/api/catalog-templates/'.$template['ID'].'/background', [
            'background' => UploadedFile::fake()->image('background.png', 1080, 1920),
        ])->assertOk()
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('catalog_templates', [
            'source_id' => $template['ID'],
            'name' => 'Story Android',
            'format' => 'story',
        ]);
    }

    public function test_feed_catalog_template_uses_1080x1350_vertical_canvas(): void
    {
        $this->actingAsDashboardUser();

        $template = $this->postJson('/api/catalog-templates', [
            'name' => 'Feed Android',
            'format' => 'feed',
            'layout_config' => ['x' => 80, 'y' => 400],
        ])->assertCreated()
            ->assertJsonPath('data.name', 'Feed Android')
            ->assertJsonPath('data.canvas_width', 1080)
            ->assertJsonPath('data.canvas_height', 1350)
            ->json('data');

        $this->assertDatabaseHas('catalog_templates', [
            'source_id' => $template['ID'],
            'name' => 'Feed Android',
            'format' => 'feed',
            'canvas_width' => 1080,
            'canvas_height' => 1350,
        ]);
    }
}
