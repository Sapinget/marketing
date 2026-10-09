<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PublicPromoPageTest extends TestCase
{
    use RefreshDatabase;

    protected bool $authenticateDashboard = false;

    public function test_guests_can_view_program_promo_in_the_public_promo_tab(): void
    {
        DB::table('program_promo')->insert([
            'source_id' => 'PRO-PUBLIC-1',
            'kategori' => 'Smartphone',
            'program' => 'Promo Galaxy A',
            'warna' => 'Blue',
            'harga' => 2499000,
            'periode' => '1-31 Oktober 2026',
            'rules' => 'Selama persediaan masih ada',
            'benefit' => 'Cashback Rp100.000',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->get('/promo')
            ->assertOk()
            ->assertSee('Promo Pilihan')
            ->assertSee('Program Promo')
            ->assertDontSee('overflow-x-auto border-b', false)
            ->assertSee('Smartphone')
            ->assertSee('Benefit')
            ->assertSee('Rules')
            ->assertSee('Harga')
            ->assertSee('Periode')
            ->assertSee('Promo Galaxy A')
            ->assertSee('Cashback Rp100.000')
            ->assertSee('Rp2.499.000');
    }

    public function test_category_dropdown_filters_public_promos(): void
    {
        DB::table('program_promo')->insert([
            ['source_id' => 'PRO-SMARTPHONE', 'kategori' => 'Smartphone', 'program' => 'Promo Smartphone', 'created_at' => now(), 'updated_at' => now()],
            ['source_id' => 'PRO-ACCESSORY', 'kategori' => 'Aksesoris', 'program' => 'Promo Aksesoris', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->get('/promo?kategori=Smartphone')
            ->assertOk()
            ->assertSee('Promo Smartphone')
            ->assertDontSee('Promo Aksesoris');
    }
}
