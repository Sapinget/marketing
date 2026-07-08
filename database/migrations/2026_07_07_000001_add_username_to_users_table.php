<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('username')->nullable()->unique()->after('id');
        });

        DB::table('users')->orderBy('id')->get(['id', 'name', 'email', 'username'])->each(function (object $user): void {
            $baseUsername = Str::slug((string) ($user->name ?: Str::before((string) $user->email, '@') ?: 'user'), '_');
            $baseUsername = $baseUsername !== '' ? $baseUsername : 'user';
            $username = $baseUsername;
            $suffix = 1;

            while (DB::table('users')
                ->where('username', $username)
                ->where('id', '!=', $user->id)
                ->exists()) {
                $suffix++;
                $username = $baseUsername . '_' . $suffix;
            }

            DB::table('users')
                ->where('id', $user->id)
                ->update(['username' => $username]);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique(['username']);
            $table->dropColumn('username');
        });
    }
};
