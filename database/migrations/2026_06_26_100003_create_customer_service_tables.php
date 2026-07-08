<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orderan_online', function (Blueprint $table) {
            $table->id();
            $table->string('source_id')->unique();
            $table->date('tanggal')->nullable();
            $table->string('ecommerce')->nullable();
            $table->string('handle')->nullable();
            $table->string('nama')->nullable();
            $table->string('type_unit')->nullable();
            $table->bigInteger('harga_online')->default(0);
            $table->bigInteger('nominal_cair')->nullable();
            $table->string('status')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();
        });

        Schema::create('unit_ditanya', function (Blueprint $table) {
            $table->id();
            $table->string('source_id')->unique();
            $table->date('tanggal')->nullable();
            $table->string('kategori')->nullable();
            $table->string('brand')->nullable();
            $table->string('seri')->nullable();
            $table->string('kondisi')->nullable();
            $table->string('tipe')->nullable();
            $table->integer('ditanya')->default(0);
            $table->string('available')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();
        });

        Schema::create('claim_garansi', function (Blueprint $table) {
            $table->id();
            $table->string('source_id')->unique();
            $table->string('nama_customer')->nullable();
            $table->string('no_service')->nullable();
            $table->string('no_transaksi')->nullable();
            $table->date('tanggal_masuk')->nullable();
            $table->string('wa_customer')->nullable();
            $table->string('tipe')->nullable();
            $table->string('seri')->nullable();
            $table->string('model')->nullable();
            $table->string('status')->nullable();
            $table->string('lokasi_klaim')->nullable();
            $table->date('tanggal_estimasi')->nullable();
            $table->date('tanggal_diambil')->nullable();
            $table->string('garansi')->nullable();
            $table->text('kerusakan')->nullable();
            $table->text('keterangan')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();
        });

        Schema::create('keep_barang', function (Blueprint $table) {
            $table->id();
            $table->string('source_id')->unique();
            $table->date('tanggal_keep')->nullable();
            $table->string('nama')->nullable();
            $table->string('nomor_hp')->nullable();
            $table->string('type_hp')->nullable();
            $table->bigInteger('dp_uang_muka')->default(0);
            $table->bigInteger('harga_jual')->default(0);
            $table->date('rencana_pengambilan')->nullable();
            $table->string('handle_by')->nullable();
            $table->string('status')->nullable();
            $table->date('tanggal_expired')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keep_barang');
        Schema::dropIfExists('claim_garansi');
        Schema::dropIfExists('unit_ditanya');
        Schema::dropIfExists('orderan_online');
    }
};
