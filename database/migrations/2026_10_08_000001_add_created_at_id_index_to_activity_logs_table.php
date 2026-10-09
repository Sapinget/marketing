<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const INDEX = 'activity_logs_created_at_id_index';

    public function up(): void
    {
        if (Schema::hasIndex('activity_logs', self::INDEX)) {
            return;
        }

        // /api/activity-logs sorts by created_at, id; without this index MySQL
        // filesorts the wide payload rows and fails with "Out of sort memory".
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->index(['created_at', 'id'], self::INDEX);
        });
    }

    public function down(): void
    {
        if (Schema::hasIndex('activity_logs', self::INDEX)) {
            Schema::table('activity_logs', function (Blueprint $table) {
                $table->dropIndex(self::INDEX);
            });
        }
    }
};
