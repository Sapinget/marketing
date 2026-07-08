<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lpjk', function (Blueprint $table) {
            $table->id();
            $table->string('source_id')->unique();
            $table->string('nama_event')->nullable();
            $table->date('tanggal')->nullable();
            $table->bigInteger('budget_rencana')->default(0);
            $table->bigInteger('realisasi_biaya')->default(0);
            $table->string('status')->nullable();
            $table->text('keterangan')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();
        });

        Schema::create('lpjk_detail', function (Blueprint $table) {
            $table->id();
            $table->string('source_id')->unique();
            $table->string('master_id');
            $table->string('kategori')->nullable();
            $table->string('nama_pengeluaran')->nullable();
            $table->string('satuan')->nullable();
            $table->integer('jumlah')->default(1);
            $table->bigInteger('total')->default(0);
            $table->string('bukti', 1000)->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();
            $table->index('master_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lpjk_detail');
        Schema::dropIfExists('lpjk');
    }
};
