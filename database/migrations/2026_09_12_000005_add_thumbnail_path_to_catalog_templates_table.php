<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('catalog_templates', function (Blueprint $table) {
            $table->string('thumbnail_path')->nullable()->after('background_path');
        });
    }

    public function down(): void
    {
        Schema::table('catalog_templates', function (Blueprint $table) {
            $table->dropColumn('thumbnail_path');
        });
    }
};
