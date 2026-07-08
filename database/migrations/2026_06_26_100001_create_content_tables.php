<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unboxing', function (Blueprint $table) {
            $table->id();
            $table->string('source_id')->unique();
            $table->string('nama')->nullable();
            $table->string('editor')->nullable();
            $table->string('status')->nullable();
            $table->date('upload_date')->nullable();
            $table->string('link', 1000)->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();
        });

        Schema::create('story_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('source_id')->unique();
            $table->date('tanggal')->nullable();
            $table->string('jam')->nullable();
            $table->string('story')->nullable();
            $table->text('catatan')->nullable();
            $table->string('link', 1000)->nullable();
            $table->string('is_genap')->nullable();
            $table->string('status')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();
        });

        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->string('source_id')->unique();
            $table->string('nama_event')->nullable();
            $table->date('tanggal')->nullable();
            $table->string('warna')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();
        });

        Schema::create('ideation', function (Blueprint $table) {
            $table->id();
            $table->string('source_id')->unique();
            $table->string('judul')->nullable();
            $table->string('kategori')->nullable();
            $table->string('platform')->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('status')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ideation');
        Schema::dropIfExists('calendar_events');
        Schema::dropIfExists('story_schedules');
        Schema::dropIfExists('unboxing');
    }
};
