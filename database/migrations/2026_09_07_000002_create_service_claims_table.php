<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_claims', function (Blueprint $table) {
            $table->id();
            $table->string('source_id')->unique();
            $table->string('service_source_id')->unique();
            $table->string('no_transaksi')->nullable();
            $table->string('seri')->nullable();
            $table->string('model')->nullable();
            $table->string('lokasi_klaim')->nullable();
            $table->date('tanggal_estimasi')->nullable();
            $table->date('tanggal_diambil')->nullable();
            $table->string('garansi')->nullable();
            $table->text('keterangan_tambahan')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_claims');
    }
};
