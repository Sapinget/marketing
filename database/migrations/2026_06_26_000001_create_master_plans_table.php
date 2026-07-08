<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_plans', function (Blueprint $table) {
            $table->id();
            $table->string('source_id')->unique();
            $table->string('title')->nullable();
            $table->string('format_konten')->nullable();
            $table->text('platforms')->nullable();
            $table->text('colab')->nullable();
            $table->string('editor')->nullable();
            $table->longText('script')->nullable();
            $table->longText('caption')->nullable();
            $table->string('status')->nullable();
            $table->date('tanggal_rencana')->nullable();
            $table->longText('distribution_meta')->nullable();
            $table->text('link_drive')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_plans');
    }
};
