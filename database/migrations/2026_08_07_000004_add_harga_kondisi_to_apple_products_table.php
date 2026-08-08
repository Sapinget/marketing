<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('apple_products', function (Blueprint $table) {
            $table->json('harga_kondisi')->nullable()->after('special_price');
        });
    }

    public function down(): void
    {
        Schema::table('apple_products', function (Blueprint $table) {
            $table->dropColumn('harga_kondisi');
        });
    }
};
