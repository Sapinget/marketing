<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_plans', function (Blueprint $table) {
            $table->foreignId('created_by_user_id')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->after('updated_by')->constrained('users')->nullOnDelete();
        });

        Schema::table('distributions', function (Blueprint $table) {
            $table->foreignId('master_plan_id')->nullable()->after('master_id')->constrained('master_plans')->nullOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->after('converted_at')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->after('created_by_user_id')->constrained('users')->nullOnDelete();
            $table->index('master_plan_id');
        });

        Schema::table('analytics', function (Blueprint $table) {
            $table->foreignId('master_plan_id')->nullable()->after('master_id')->constrained('master_plans')->nullOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->after('converted_at')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->after('created_by_user_id')->constrained('users')->nullOnDelete();
            $table->index('master_plan_id');
        });

        Schema::table('lpjk', function (Blueprint $table) {
            $table->foreignId('created_by_user_id')->nullable()->after('imported_at')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->after('created_by_user_id')->constrained('users')->nullOnDelete();
        });

        Schema::table('lpjk_detail', function (Blueprint $table) {
            $table->foreignId('lpjk_id')->nullable()->after('master_id')->constrained('lpjk')->nullOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->after('imported_at')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->after('created_by_user_id')->constrained('users')->nullOnDelete();
            $table->index('lpjk_id');
        });

        $usersByIdentity = DB::table('users')
            ->get(['id', 'username', 'email', 'name'])
            ->reduce(function (array $carry, object $user): array {
                foreach ([$user->username ?? null, $user->email ?? null, $user->name ?? null] as $identity) {
                    $normalizedIdentity = trim((string) $identity);
                    if ($normalizedIdentity !== '' && ! isset($carry[$normalizedIdentity])) {
                        $carry[$normalizedIdentity] = (int) $user->id;
                    }
                }

                return $carry;
            }, []);

        DB::table('master_plans')
            ->orderBy('id')
            ->get(['id', 'created_by', 'updated_by'])
            ->each(function (object $row) use ($usersByIdentity): void {
                $createdByUserId = $usersByIdentity[trim((string) ($row->created_by ?? ''))] ?? null;
                $updatedByUserId = $usersByIdentity[trim((string) ($row->updated_by ?? ''))] ?? null;

                DB::table('master_plans')
                    ->where('id', $row->id)
                    ->update([
                        'created_by_user_id' => $createdByUserId,
                        'updated_by_user_id' => $updatedByUserId ?? $createdByUserId,
                    ]);
            });

        DB::table('distributions')
            ->orderBy('id')
            ->get(['id', 'master_id'])
            ->each(function (object $row): void {
                $masterPlan = DB::table('master_plans')
                    ->where('source_id', $row->master_id)
                    ->first(['id', 'created_by_user_id', 'updated_by_user_id']);

                DB::table('distributions')
                    ->where('id', $row->id)
                    ->update([
                        'master_plan_id' => $masterPlan?->id,
                        'created_by_user_id' => $masterPlan?->created_by_user_id,
                        'updated_by_user_id' => $masterPlan?->updated_by_user_id ?? $masterPlan?->created_by_user_id,
                    ]);
            });

        DB::table('analytics')
            ->orderBy('id')
            ->get(['id', 'master_id'])
            ->each(function (object $row): void {
                $masterPlan = DB::table('master_plans')
                    ->where('source_id', $row->master_id)
                    ->first(['id', 'created_by_user_id', 'updated_by_user_id']);

                DB::table('analytics')
                    ->where('id', $row->id)
                    ->update([
                        'master_plan_id' => $masterPlan?->id,
                        'created_by_user_id' => $masterPlan?->created_by_user_id,
                        'updated_by_user_id' => $masterPlan?->updated_by_user_id ?? $masterPlan?->created_by_user_id,
                    ]);
            });

        DB::table('lpjk_detail')
            ->orderBy('id')
            ->get(['id', 'master_id'])
            ->each(function (object $row): void {
                $lpjk = DB::table('lpjk')
                    ->where('source_id', $row->master_id)
                    ->first(['id', 'created_by_user_id', 'updated_by_user_id']);

                DB::table('lpjk_detail')
                    ->where('id', $row->id)
                    ->update([
                        'lpjk_id' => $lpjk?->id,
                        'created_by_user_id' => $lpjk?->created_by_user_id,
                        'updated_by_user_id' => $lpjk?->updated_by_user_id ?? $lpjk?->created_by_user_id,
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('lpjk_detail', function (Blueprint $table) {
            $table->dropIndex(['lpjk_id']);
            $table->dropConstrainedForeignId('lpjk_id');
            $table->dropConstrainedForeignId('created_by_user_id');
            $table->dropConstrainedForeignId('updated_by_user_id');
        });

        Schema::table('lpjk', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by_user_id');
            $table->dropConstrainedForeignId('updated_by_user_id');
        });

        Schema::table('analytics', function (Blueprint $table) {
            $table->dropIndex(['master_plan_id']);
            $table->dropConstrainedForeignId('master_plan_id');
            $table->dropConstrainedForeignId('created_by_user_id');
            $table->dropConstrainedForeignId('updated_by_user_id');
        });

        Schema::table('distributions', function (Blueprint $table) {
            $table->dropIndex(['master_plan_id']);
            $table->dropConstrainedForeignId('master_plan_id');
            $table->dropConstrainedForeignId('created_by_user_id');
            $table->dropConstrainedForeignId('updated_by_user_id');
        });

        Schema::table('master_plans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by_user_id');
            $table->dropConstrainedForeignId('updated_by_user_id');
        });
    }
};
