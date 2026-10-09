<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PromoPamfletTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_manage_promo_pamflet_categories(): void
    {
        $response = $this->postJson('/api/promo-pamflet-categories', [
            'nama' => 'Smartphone Deals',
        ])->assertCreated();

        $sourceId = $response->json('data.source_id');
        $id = $response->json('data.id');

        $this->assertDatabaseHas('promo_pamflet_categories', [
            'nama' => 'Smartphone Deals',
        ]);

        $this->getJson('/api/promo-pamflet-categories')
            ->assertOk()
            ->assertJsonFragment(['nama' => 'Smartphone Deals']);

        $this->putJson("/api/promo-pamflet-categories/{$sourceId}", [
            'nama' => 'Smartphone Mega Deals',
        ])->assertOk();

        $this->assertDatabaseHas('promo_pamflet_categories', [
            'nama' => 'Smartphone Mega Deals',
        ]);

        $this->deleteJson("/api/promo-pamflet-categories/{$id}")
            ->assertOk();

        $this->assertDatabaseMissing('promo_pamflet_categories', [
            'nama' => 'Smartphone Mega Deals',
        ]);
    }

    public function test_can_upload_and_manage_promo_pamflet(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('pamflet-test.jpg', 600, 800);

        $response = $this->post('/api/promo-pamflets', [
            'nama' => 'Promo Diskon Merdeka',
            'kategori' => 'Event Spesial',
            'deskripsi' => 'Diskon hingga 50% untuk semua tipe',
            'desain' => $file,
        ], ['Accept' => 'application/json'])->assertCreated();

        $sourceId = $response->json('data.source_id');
        $filename = $response->json('data.file_path');

        $this->assertDatabaseHas('promo_pamflets', [
            'source_id' => $sourceId,
            'nama' => 'Promo Diskon Merdeka',
            'kategori' => 'Event Spesial',
        ]);

        $this->assertFileExists(storage_path('app/public/promo-pamflets/'.$filename));

        $this->get('/api/promo-pamflets/file/'.$filename)
            ->assertOk();

        $this->getJson('/api/promo-pamflets')
            ->assertOk()
            ->assertJsonFragment(['nama' => 'Promo Diskon Merdeka']);

        $this->post("/api/promo-pamflets/{$sourceId}", [
            'nama' => 'Promo Diskon Akhir Tahun',
            'kategori' => 'Event Spesial',
            'deskripsi' => 'Diskon akhir tahun',
        ], ['Accept' => 'application/json'])->assertOk();

        $this->assertDatabaseHas('promo_pamflets', [
            'source_id' => $sourceId,
            'nama' => 'Promo Diskon Akhir Tahun',
        ]);

        $this->deleteJson("/api/promo-pamflets/{$sourceId}")
            ->assertOk();

        $this->assertDatabaseMissing('promo_pamflets', [
            'source_id' => $sourceId,
        ]);
    }

    public function test_sidebar_includes_promo_pamflet_menu(): void
    {
        $response = $this->get('/');
        $response->assertOk();
        $response->assertSee('Promo Pamflet');
        $response->assertSee('switchTab(\'promo_pamflet\')', false);
    }

    public function test_public_promo_page_displays_pamflet_tab_and_category_filter(): void
    {
        DB::table('promo_pamflet_categories')->insert([
            ['source_id' => 'CAT-1', 'nama' => 'iPhone', 'created_at' => now(), 'updated_at' => now()],
            ['source_id' => 'CAT-2', 'nama' => 'Android', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('promo_pamflets')->insert([
            [
                'source_id' => 'PAM-1',
                'kategori' => 'iPhone',
                'nama' => 'Pamflet iPhone 16 Pro',
                'deskripsi' => 'Cicilan 0% 12 bulan',
                'file_path' => 'sample-iphone.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'source_id' => 'PAM-2',
                'kategori' => 'Android',
                'nama' => 'Pamflet Galaxy S25 Ultra',
                'deskripsi' => 'Bonus aksesoris',
                'file_path' => 'sample-samsung.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->get('/promo?tab=pamflet')
            ->assertOk()
            ->assertSee('Pamflet & Desain Promo')
            ->assertSee('Pamflet iPhone 16 Pro')
            ->assertSee('Pamflet Galaxy S25 Ultra');

        $this->get('/promo?tab=pamflet&pamflet_kategori=iPhone')
            ->assertOk()
            ->assertSee('Pamflet iPhone 16 Pro')
            ->assertDontSee('Pamflet Galaxy S25 Ultra');
    }

    public function test_dedicated_promo_pamflet_route_serves_blade_view(): void
    {
        $response = $this->get('/promo-pamflet');
        $response->assertOk();
        $response->assertSee('Promo Pamflet');
        $response->assertSee('activeTab === \'promo_pamflet\'', false);
    }

    public function test_e2e_admin_can_access_promo_pamflet_and_publish_to_public_promo(): void
    {
        Storage::fake('public');

        // 1. Admin accesses /promo-pamflet
        $this->get('/promo-pamflet')
            ->assertOk()
            ->assertSee('Promo Pamflet')
            ->assertSee('activeTab === \'promo_pamflet\'', false);

        // 2. Admin creates category
        $catResponse = $this->postJson('/api/promo-pamflet-categories', [
            'nama' => 'Flash Sale Mingguan',
        ])->assertCreated();

        $catName = $catResponse->json('data.nama');
        $this->assertSame('Flash Sale Mingguan', $catName);

        // 3. Admin uploads pamphlet
        $file = UploadedFile::fake()->image('flash-sale.webp', 1080, 1920);
        $pamfletResponse = $this->post('/api/promo-pamflets', [
            'nama' => 'Flash Sale iPhone 15',
            'kategori' => 'Flash Sale Mingguan',
            'deskripsi' => 'Hanya berlaku akhir pekan',
            'desain' => $file,
        ], ['Accept' => 'application/json'])->assertCreated();

        $pamfletName = $pamfletResponse->json('data.nama');
        $filePath = $pamfletResponse->json('data.file_path');
        $this->assertSame('Flash Sale iPhone 15', $pamfletName);

        // 4. Verify image file exists and serves publicly
        $this->get('/api/promo-pamflets/file/'.$filePath)
            ->assertOk();

        // 5. Verify public /promo page displays published pamphlet
        $this->get('/promo?tab=pamflet')
            ->assertOk()
            ->assertSee('Flash Sale iPhone 15')
            ->assertSee('Flash Sale Mingguan')
            ->assertSee('Hanya berlaku akhir pekan')
            ->assertSee('/api/promo-pamflets/file/'.$filePath);
    }
}
