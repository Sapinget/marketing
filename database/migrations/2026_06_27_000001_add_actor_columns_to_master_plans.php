<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_plans', function (Blueprint $table) {
            $table->string('created_by')->nullable()->after('imported_at');
            $table->string('updated_by')->nullable()->after('created_by');
        });
    }

    public function down(): void
    {
        Schema::table('master_plans', function (Blueprint $table) {
            $table->dropColumn(['created_by', 'updated_by']);
        });
    }
};
