<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('google_sheet_rows', function (Blueprint $table) {
            $table->id();
            $table->string('spreadsheet_id');
            $table->string('sheet_name');
            $table->unsignedInteger('row_number');
            $table->string('row_hash', 64);
            $table->json('payload');
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();

            $table->unique(['spreadsheet_id', 'sheet_name', 'row_number'], 'google_sheet_rows_source_unique');
            $table->index(['spreadsheet_id', 'sheet_name'], 'google_sheet_rows_sheet_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('google_sheet_rows');
    }
};
