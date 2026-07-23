<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meta_ig_followers', function (Blueprint $table) {
            $table->id();
            $table->date('follow_date')->unique();
            $table->unsignedBigInteger('primary_count')->default(0);
            $table->json('raw_payload')->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_ig_followers');
    }
};
