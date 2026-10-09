<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('promo_pamflet_categories', function (Blueprint $table) {
            $table->id();
            $table->string('source_id')->unique();
            $table->string('nama');
            $table->timestamps();
        });

        Schema::create('promo_pamflets', function (Blueprint $table) {
            $table->id();
            $table->string('source_id')->unique();
            $table->string('kategori')->nullable();
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->string('file_path');
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promo_pamflets');
        Schema::dropIfExists('promo_pamflet_categories');
    }
};
