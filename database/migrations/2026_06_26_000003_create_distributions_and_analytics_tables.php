<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distributions', function (Blueprint $table) {
            $table->id();
            $table->string('master_id');
            $table->string('title')->nullable();
            $table->string('platform');
            $table->date('tanggal_publish')->nullable();
            $table->text('link')->nullable();
            $table->string('type')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->timestamps();

            $table->unique(['master_id', 'platform']);
        });

        Schema::create('analytics', function (Blueprint $table) {
            $table->id();
            $table->string('master_id');
            $table->string('title')->nullable();
            $table->string('platform');
            $table->date('tanggal_publish')->nullable();
            $table->unsignedBigInteger('views')->default(0);
            $table->unsignedBigInteger('likes')->default(0);
            $table->unsignedBigInteger('comments')->default(0);
            $table->unsignedBigInteger('shares')->default(0);
            $table->json('raw_payload')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->timestamps();

            $table->unique(['master_id', 'platform']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics');
        Schema::dropIfExists('distributions');
    }
};
