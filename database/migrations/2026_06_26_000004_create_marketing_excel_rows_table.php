<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_excel_rows', function (Blueprint $table) {
            $table->id();
            $table->string('sheet_name');
            $table->unsignedInteger('row_number');
            $table->string('row_hash', 64);
            $table->json('payload');
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();

            $table->unique(['sheet_name', 'row_number']);
            $table->index('sheet_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_excel_rows');
    }
};
