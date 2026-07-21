<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('harga_kompetitor', function (Blueprint $table) {
            $table->string('kategori')->nullable()->after('nama_produk');
            $table->string('brand')->nullable()->after('kategori');
            $table->string('seri')->nullable()->after('brand');
            $table->string('ram')->nullable()->after('seri');
            $table->string('internal')->nullable()->after('ram');
            $table->string('size')->nullable()->after('internal');
            $table->string('warna')->nullable()->after('size');
        });
    }

    public function down(): void
    {
        Schema::table('harga_kompetitor', function (Blueprint $table) {
            $table->dropColumn(['kategori', 'brand', 'seri', 'ram', 'internal', 'size', 'warna']);
        });
    }
};
