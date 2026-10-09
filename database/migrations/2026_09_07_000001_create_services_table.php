<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('source_id')->unique();
            $table->string('no_service')->nullable();
            $table->date('tanggal')->nullable();
            $table->string('nama_customer')->nullable();
            $table->string('wa_customer')->nullable();
            $table->string('type_unit')->nullable();
            $table->string('imei_sn')->nullable();
            $table->text('kerusakan')->nullable();
            $table->string('status')->nullable();
            $table->text('keterangan')->nullable();
            $table->bigInteger('total')->nullable();
            $table->string('handle_by')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamp('source_updated_at')->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
