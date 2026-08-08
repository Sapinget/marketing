<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apple_products', function (Blueprint $table) {
            $table->id();
            $table->string('source_id')->unique();
            $table->string('source_sheet');
            $table->unsignedInteger('source_row');
            $table->unsignedInteger('urut')->nullable();
            $table->string('model')->nullable();
            $table->string('storage')->nullable();
            $table->bigInteger('harga_nasional')->nullable();
            $table->bigInteger('special_price')->nullable();
            $table->string('normalized_key')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('raw_payload')->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();

            $table->unique(['source_sheet', 'source_row']);
            $table->index(['source_sheet', 'urut']);
            $table->index(['source_sheet', 'model']);
            $table->index('normalized_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apple_products');
    }
};
