<?php

namespace Tests\Feature;

use App\Support\DashboardAuth;
use App\Support\PricelistSheetImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
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

    public function test_non_settings_managers_cannot_mutate_pricelist_or_catalog_templates(): void
    {
        $nonAdminRoles = [
            DashboardAuth::ROLE_KASIR,
            DashboardAuth::ROLE_OPERASIONAL,
            DashboardAuth::ROLE_BRAND_AMBASADOR,
            DashboardAuth::ROLE_TALENT,
        ];

        foreach ($nonAdminRoles as $role) {
            $this->actingAsDashboardUser(['role' => $role]);

            $this->postJson('/api/pricelist-products/sync')->assertForbidden();
            $this->putJson('/api/pricelist-products/PL-1', ['is_active' => false])->assertForbidden();
            $this->postJson('/api/apple-products/sync')->assertForbidden();
            $this->postJson('/api/catalog-templates', ['name' => 'Blocked', 'format' => 'story'])->assertForbidden();
            $this->putJson('/api/catalog-templates/CT-1', ['name' => 'Blocked', 'format' => 'story'])->assertForbidden();
            $this->deleteJson('/api/catalog-templates/CT-1')->assertForbidden();
            $this->post('/api/catalog-templates/CT-1/background')->assertForbidden();
            $this->postJson('/api/catalog-templates/CT-1/thumbnail', ['thumbnail_data' => 'data:image/png;base64,eA=='])->assertForbidden();

            $this->getJson('/api/pricelist-products')->assertOk();
            $this->getJson('/api/apple-products')->assertOk();
            $this->getJson('/api/catalog-templates')->assertOk();
        }
    }

    public function test_settings_managers_can_mutate_pricelist_and_catalog_templates(): void
    {
        foreach ([DashboardAuth::ROLE_SUPER_ADMIN, DashboardAuth::ROLE_ADMIN] as $role) {
            $this->actingAsDashboardUser(['role' => $role]);

            $sourceId = 'PL-'.$role;
            DB::table('pricelist_products')->insert([
                'source_id' => $sourceId,
                'source_sheet' => 'SAMSUNG',
                'source_row' => $role === DashboardAuth::ROLE_SUPER_ADMIN ? 1 : 2,
                'nama_produk' => 'Galaxy '.$role,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->putJson('/api/pricelist-products/'.$sourceId, ['is_active' => false])
                ->assertOk()
                ->assertJsonPath('data.is_active', 0);

            $template = $this->postJson('/api/catalog-templates', ['name' => 'Allowed '.$role, 'format' => 'story'])
                ->assertCreated()
                ->json('data');

            $this->putJson('/api/catalog-templates/'.$template['ID'], ['name' => 'Allowed Updated '.$role, 'format' => 'story'])
                ->assertOk()
                ->assertJsonPath('data.name', 'Allowed Updated '.$role);

            $this->deleteJson('/api/catalog-templates/'.$template['ID'])
                ->assertOk();
        }
    }

    public function test_unauthenticated_requests_cannot_access_pricelist_or_catalog_routes(): void
    {
        auth()->logout();
        session()->flush();

        $this->getJson('/api/pricelist-products')->assertUnauthorized();
        $this->postJson('/api/pricelist-products/sync')->assertUnauthorized();
        $this->putJson('/api/pricelist-products/PL-1', ['is_active' => false])->assertUnauthorized();
        $this->getJson('/api/apple-products')->assertUnauthorized();
        $this->postJson('/api/apple-products/sync')->assertUnauthorized();
        $this->getJson('/api/catalog-templates')->assertUnauthorized();
        $this->getJson('/api/catalog-templates/background/test.png')->assertUnauthorized();
        $this->getJson('/api/catalog-templates/thumbnail/test.png')->assertUnauthorized();
        $this->postJson('/api/catalog-templates', ['name' => 'Blocked', 'format' => 'story'])->assertUnauthorized();
        $this->putJson('/api/catalog-templates/CT-1', ['name' => 'Blocked', 'format' => 'story'])->assertUnauthorized();
        $this->deleteJson('/api/catalog-templates/CT-1')->assertUnauthorized();
        $this->post('/api/catalog-templates/CT-1/background')->assertUnauthorized();
        $this->postJson('/api/catalog-templates/CT-1/thumbnail', ['thumbnail_data' => 'data:image/png;base64,eA=='])->assertUnauthorized();
    }

    public function test_catalog_template_api_creates_template_and_uploads_background(): void
    {
        $this->actingAsDashboardUser();

        $template = $this->postJson('/api/catalog-templates', [
            'name' => 'Story Android',
            'format' => 'story',
            'output_mode' => 'katalog',
            'layout_config' => ['x' => 80, 'y' => 520, 'cardColumns' => 4],
        ])->assertCreated()
            ->assertJsonPath('data.name', 'Story Android')
            ->assertJsonPath('data.output_mode', 'katalog')
            ->assertJsonPath('data.layout_config.cardColumns', 4)
            ->assertJsonPath('data.canvas_width', 1080)
            ->assertJsonPath('data.canvas_height', 1920)
            ->json('data');

        $this->post('/api/catalog-templates/'.$template['ID'].'/background', [
            'background' => UploadedFile::fake()->image('background.png', 1080, 1920),
        ])->assertOk()
            ->assertJsonPath('status', 'success');

        $this->postJson('/api/catalog-templates/'.$template['ID'].'/thumbnail', [
            'thumbnail_data' => 'data:image/png;base64,'.base64_encode('fake-png-content'),
        ])->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.thumbnail_url', fn ($val) => filled($val));

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
