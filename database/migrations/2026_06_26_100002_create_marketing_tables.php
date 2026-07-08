<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_promo', function (Blueprint $table) {
            $table->id();
            $table->string('source_id')->unique();
            $table->string('kategori')->nullable();
            $table->string('program')->nullable();
            $table->string('warna')->nullable();
            $table->bigInteger('harga')->nullable();
            $table->string('periode')->nullable();
            $table->text('rules')->nullable();
            $table->text('benefit')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();
        });

        Schema::create('sell_out_targets', function (Blueprint $table) {
            $table->id();
            $table->string('source_id')->unique();
            $table->string('vendor')->nullable();
            $table->string('kategori')->nullable();
            $table->string('brand')->nullable();
            $table->string('seri')->nullable();
            $table->string('nama_produk')->nullable();
            $table->integer('target_unit')->default(0);
            $table->bigInteger('bonus_nominal')->default(0);
            $table->integer('realisasi_unit')->default(0);
            $table->date('periode_start')->nullable();
            $table->date('periode_end')->nullable();
            $table->text('catatan')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();
        });

        Schema::create('ads_performance', function (Blueprint $table) {
            $table->id();
            $table->string('source_id')->unique();
            $table->string('nama')->nullable();
            $table->string('id_ads')->nullable();
            $table->date('tanggal')->nullable();
            $table->bigInteger('biaya')->default(0);
            $table->bigInteger('sisa_saldo')->nullable();
            $table->string('kategori')->nullable();
            $table->string('platform')->nullable();
            $table->bigInteger('jangkauan')->default(0);
            $table->integer('suka')->default(0);
            $table->integer('komentar')->default(0);
            $table->integer('share')->default(0);
            $table->json('raw_payload')->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();
        });

        Schema::create('harga_kompetitor', function (Blueprint $table) {
            $table->id();
            $table->string('source_id')->unique();
            $table->string('nama_produk')->nullable();
            $table->bigInteger('harga_distributor_1')->default(0);
            $table->bigInteger('harga_distributor_2')->default(0);
            $table->bigInteger('harga_kompetitor')->default(0);
            $table->bigInteger('margin_profit')->default(0);
            $table->bigInteger('harga_rencana_jual')->default(0);
            $table->date('tanggal_cek')->nullable();
            $table->text('catatan')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('harga_kompetitor');
        Schema::dropIfExists('ads_performance');
        Schema::dropIfExists('sell_out_targets');
        Schema::dropIfExists('program_promo');
    }
};
