<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meta_ig_posts', function (Blueprint $table) {
            $table->id();
            $table->string('post_id')->unique();
            $table->string('dataset', 16)->index(); // story | feed
            $table->string('account')->nullable();
            $table->string('account_name')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('duration')->nullable();
            $table->dateTime('publish_time')->nullable()->index();
            $table->text('permalink')->nullable();
            $table->string('post_type')->nullable();

            // Canonical metrics (header order in export may change; mapped by name on import)
            $table->unsignedBigInteger('views')->nullable();
            $table->unsignedBigInteger('reach')->nullable();
            $table->unsignedBigInteger('likes')->nullable();
            $table->unsignedBigInteger('shares')->nullable();
            $table->unsignedBigInteger('comments')->nullable();
            $table->unsignedBigInteger('saves')->nullable();
            $table->unsignedBigInteger('follows')->nullable();
            $table->unsignedBigInteger('profile_visits')->nullable();
            $table->unsignedBigInteger('replies')->nullable();
            $table->unsignedBigInteger('navigation')->nullable();
            $table->unsignedBigInteger('link_clicks')->nullable();
            $table->unsignedBigInteger('sticker_taps')->nullable();

            // Original row (incl. unknown/extra columns) so nothing is lost on header change
            $table->json('raw_payload')->nullable();

            $table->timestamp('imported_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_ig_posts');
    }
};
