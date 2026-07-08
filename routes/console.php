<?php

use App\Support\XlsxSheetReader;
use App\Support\DashboardAuth;
use App\Support\MasterPlanDistributionSync;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('marketing:user-create {username} {pin} {--name=} {--email=}', function (DashboardAuth $dashboardAuth): int {
    $user = $dashboardAuth->createUser(
        (string) $this->argument('username'),
        (string) $this->argument('pin'),
        $this->option('name') ? (string) $this->option('name') : null,
        $this->option('email') ? (string) $this->option('email') : null,
    );

    $this->info(sprintf('User %s is ready to login.', $user->username));

    return self::SUCCESS;
})->purpose('Create or update a dashboard login user');

Artisan::command('marketing:import-master-plan {path} {--sheet=Master_Plan} {--truncate}', function (XlsxSheetReader $reader): int {
    $path = (string) $this->argument('path');
    $sheet = (string) $this->option('sheet');
    $rows = $reader->rows($path, $sheet);
    $now = now();
    $blankToNull = static function (mixed $value): ?string {
        if ($value === null) {
            return null;
        }

        $string = trim((string) $value);

        return $string === '' ? null : $string;
    };

    DB::transaction(function () use ($rows, $now, $blankToNull): void {
        if ($this->option('truncate')) {
            DB::table('master_plans')->truncate();
        }

        foreach ($rows as $row) {
            $sourceId = trim((string) ($row['ID'] ?? ''));
            if ($sourceId === '') {
                continue;
            }

            DB::table('master_plans')->updateOrInsert(
                ['source_id' => $sourceId],
                [
                    'title' => $blankToNull($row['Judul'] ?? null),
                    'format_konten' => $blankToNull($row['Format_Konten'] ?? null),
                    'platforms' => $blankToNull($row['Platforms'] ?? null),
                    'colab' => $blankToNull($row['Colab'] ?? null),
                    'editor' => $blankToNull($row['Editor'] ?? null),
                    'talent' => $blankToNull($row['Talent'] ?? null),
                    'script' => $blankToNull($row['Skrip'] ?? null),
                    'caption' => $blankToNull($row['Caption'] ?? null),
                    'status' => $blankToNull($row['Status'] ?? null),
                    'tanggal_rencana' => $blankToNull($row['Tanggal_Rencana'] ?? null),
                    'distribution_meta' => $blankToNull($row['Distribution_Meta'] ?? null),
                    'link_drive' => $blankToNull($row['Link_Drive'] ?? null),
                    'raw_payload' => json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'imported_at' => $now,
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }
    });

    $count = DB::table('master_plans')->count();
    $this->info("Imported {$count} master plan rows from {$sheet}.");

    return self::SUCCESS;
})->purpose('Import the Master_Plan sheet from a PPK XLSX workbook into SQLite');

Artisan::command('marketing:import-settings {path} {--sheet=Settings} {--truncate}', function (XlsxSheetReader $reader): int {
    $path = (string) $this->argument('path');
    $sheet = (string) $this->option('sheet');
    $rows = $reader->rows($path, $sheet);
    $now = now();
    $settings = [];

    foreach ($rows as $row) {
        foreach ($row as $key => $value) {
            $key = trim((string) $key);
            $value = trim((string) ($value ?? ''));

            if ($key === '' || $value === '') {
                continue;
            }

            $settings[$key] ??= [];
            if (! in_array($value, $settings[$key], true)) {
                $settings[$key][] = $value;
            }
        }
    }

    DB::transaction(function () use ($settings, $now): void {
        if ($this->option('truncate')) {
            DB::table('marketing_settings')->truncate();
        }

        foreach ($settings as $key => $values) {
            DB::table('marketing_settings')->updateOrInsert(
                ['key' => $key],
                [
                    'values' => json_encode($values, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'imported_at' => $now,
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }
    });

    $count = DB::table('marketing_settings')->count();
    $this->info("Imported {$count} setting groups from {$sheet}.");

    return self::SUCCESS;
})->purpose('Import the Settings sheet from a PPK XLSX workbook into SQLite');

Artisan::command('marketing:import-remaining-workbook {path} {--truncate}', function (XlsxSheetReader $reader): int {
    $path = (string) $this->argument('path');
    $now = now();
    $structuredSheets = ['Master_Plan', 'Settings', 'Nama_Stock'];
    $sheetNames = array_values(array_filter(
        $reader->sheetNames($path),
        fn (string $sheetName): bool => ! in_array($sheetName, $structuredSheets, true)
    ));
    $stats = [];

    DB::transaction(function () use ($reader, $path, $now, $sheetNames, &$stats): void {
        if ($this->option('truncate')) {
            DB::table('marketing_excel_rows')->truncate();
            DB::table('stock_names')->truncate();
        }

        if (in_array('Nama_Stock', $reader->sheetNames($path), true)) {
            $namaStockRows = $reader->rows($path, 'Nama_Stock');
            $stats['Nama_Stock'] = count($namaStockRows);

            foreach ($namaStockRows as $row) {
                $kategori = preg_replace('/\s+/', ' ', strtoupper(trim((string) ($row['KATEGORI'] ?? ''))));
                $brand = preg_replace('/\s+/', ' ', strtoupper(trim((string) ($row['BRAND'] ?? ''))));
                $seri = preg_replace('/\s+/', ' ', strtoupper(trim((string) ($row['SERI'] ?? ''))));

                DB::table('stock_names')->updateOrInsert(
                    [
                        'kategori' => $kategori,
                        'brand' => $brand,
                        'seri' => $seri,
                    ],
                    [
                        'source_id' => filled($row['ID'] ?? null) ? (string) $row['ID'] : null,
                        'imported_at' => $now,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ],
                );
            }
        }

        foreach ($sheetNames as $sheetName) {
            $rows = $reader->rows($path, $sheetName);
            $stats[$sheetName] = count($rows);

            foreach ($rows as $index => $row) {
                $payload = json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                DB::table('marketing_excel_rows')->updateOrInsert(
                    [
                        'sheet_name' => $sheetName,
                        'row_number' => $index + 2,
                    ],
                    [
                        'row_hash' => hash('sha256', $payload),
                        'payload' => $payload,
                        'imported_at' => $now,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ],
                );
            }
        }
    });

    $total = DB::table('marketing_excel_rows')->count();
    $stockNamesTotal = DB::table('stock_names')->count();
    $this->info('Remaining workbook import audit');
    $this->line('excluded_structured_sheets: '.implode(', ', $structuredSheets));
    foreach ($stats as $sheetName => $count) {
        $this->line("{$sheetName}: {$count}");
    }
    $this->line("marketing_excel_rows_total: {$total}");
    $this->line("stock_names_total: {$stockNamesTotal}");

    return self::SUCCESS;
})->purpose('Import all non-structured PPK workbook sheets into raw JSON database rows');

Artisan::command('marketing:convert-master-plan {--truncate}', function (): int {
    $now = now();
    $stats = [
        'master_plans' => DB::table('master_plans')->count(),
        'distribution_upserts' => 0,
        'analytics_upserts' => 0,
        'invalid_json' => 0,
        'skipped_without_meta' => 0,
        'skipped_not_published' => 0,
    ];

    DB::transaction(function () use (&$stats, $now): void {
        if ($this->option('truncate')) {
            DB::table('analytics')->truncate();
            DB::table('distributions')->truncate();
        }

        DB::table('master_plans')
            ->orderBy('id')
            ->chunk(100, function ($rows) use (&$stats, $now): void {
                foreach ($rows as $row) {
                    $rowStats = MasterPlanDistributionSync::sync($row, $now);
                    foreach ($rowStats as $key => $value) {
                        if (array_key_exists($key, $stats)) {
                            $stats[$key] += $value;
                        }
                    }
                }
            });
    });

    $stats['distributions_total'] = DB::table('distributions')->count();
    $stats['analytics_total'] = DB::table('analytics')->count();

    $this->info('Master Plan conversion audit');
    foreach ($stats as $key => $value) {
        $this->line("{$key}: {$value}");
    }

    DB::table('distributions')
        ->orderByDesc('tanggal_publish')
        ->orderBy('master_id')
        ->limit(5)
        ->get(['master_id', 'title', 'platform', 'tanggal_publish', 'link'])
        ->each(function ($row): void {
            $this->line("sample_distribution: {$row->master_id} | {$row->platform} | {$row->tanggal_publish} | {$row->title}");
        });

    return self::SUCCESS;
})->purpose('Convert Master Plan distribution metadata into Distribution and Analytics tables');

Artisan::command('marketing:db-readiness {--write-doc=}', function (): int {
    $countMissingParent = static function (string $childTable, string $foreignKey, string $parentTable): int {
        return DB::table($childTable)
            ->whereNotNull($foreignKey)
            ->whereNotExists(function ($query) use ($childTable, $foreignKey, $parentTable): void {
                $query->select(DB::raw(1))
                    ->from($parentTable)
                    ->whereColumn("{$parentTable}.id", "{$childTable}.{$foreignKey}");
            })
            ->count();
    };

    $countBlankString = static function (string $table, string $column): int {
        return DB::table($table)
            ->where(function ($query) use ($column): void {
                $query->whereNull($column)->orWhere($column, '');
            })
            ->count();
    };

    $report = [
        'connection' => [
            'default' => (string) config('database.default'),
            'database' => (string) config('database.connections.'.config('database.default').'.database'),
        ],
        'tables' => [
            'users' => DB::table('users')->count(),
            'master_plans' => DB::table('master_plans')->count(),
            'distributions' => DB::table('distributions')->count(),
            'analytics' => DB::table('analytics')->count(),
            'lpjk' => DB::table('lpjk')->count(),
            'lpjk_detail' => DB::table('lpjk_detail')->count(),
            'activity_logs' => DB::table('activity_logs')->count(),
        ],
        'blockers' => [
            'distributions_missing_master_plan_id' => DB::table('distributions')->whereNull('master_plan_id')->count(),
            'analytics_missing_master_plan_id' => DB::table('analytics')->whereNull('master_plan_id')->count(),
            'lpjk_detail_missing_lpjk_id' => DB::table('lpjk_detail')->whereNull('lpjk_id')->count(),
            'distributions_orphan_master_plan_id' => $countMissingParent('distributions', 'master_plan_id', 'master_plans'),
            'analytics_orphan_master_plan_id' => $countMissingParent('analytics', 'master_plan_id', 'master_plans'),
            'lpjk_detail_orphan_lpjk_id' => $countMissingParent('lpjk_detail', 'lpjk_id', 'lpjk'),
            'activity_logs_orphan_user_id' => $countMissingParent('activity_logs', 'user_id', 'users'),
        ],
        'legacy_columns' => [
            'distributions_blank_master_id' => $countBlankString('distributions', 'master_id'),
            'analytics_blank_master_id' => $countBlankString('analytics', 'master_id'),
            'lpjk_detail_blank_master_id' => $countBlankString('lpjk_detail', 'master_id'),
            'master_plans_blank_created_by' => $countBlankString('master_plans', 'created_by'),
            'master_plans_blank_updated_by' => $countBlankString('master_plans', 'updated_by'),
        ],
        'notes' => [
            'SQLite masih aktif. MySQL belum dikonfigurasi selama DB_CONNECTION belum diubah ke mysql.',
            'Migrasi ke MySQL aman dilakukan setelah blocker relasi bernilai 0.',
            'Kolom legacy string boleh tetap ada sementara, tetapi write-path utama sekarang harus mengandalkan foreign key internal.',
        ],
    ];

    $hasBlockingIssues = collect($report['blockers'])->contains(fn (int $count): bool => $count > 0);
    $status = $hasBlockingIssues ? 'attention_required' : 'ready_for_mysql_structure';

    $this->info('Database migration readiness audit');
    $this->line('status: '.$status);
    $this->line('db_connection: '.$report['connection']['default']);
    $this->line('db_database: '.$report['connection']['database']);

    $this->newLine();
    $this->info('Table totals');
    foreach ($report['tables'] as $key => $value) {
        $this->line("{$key}: {$value}");
    }

    $this->newLine();
    $this->info('Blocking checks');
    foreach ($report['blockers'] as $key => $value) {
        $this->line("{$key}: {$value}");
    }

    $this->newLine();
    $this->info('Legacy compatibility checks');
    foreach ($report['legacy_columns'] as $key => $value) {
        $this->line("{$key}: {$value}");
    }

    $markdown = "# MySQL Migration Readiness Report\n\n"
        ."Generated at: ".now()->toDateTimeString()."\n\n"
        ."## Status\n\n"
        ."- status: `{$status}`\n"
        ."- db_connection: `".$report['connection']['default']."`\n"
        ."- db_database: `".$report['connection']['database']."`\n\n"
        ."## Table Totals\n\n"
        .collect($report['tables'])->map(fn ($value, $key) => "- `{$key}`: {$value}")->implode("\n")
        ."\n\n## Blocking Checks\n\n"
        .collect($report['blockers'])->map(fn ($value, $key) => "- `{$key}`: {$value}")->implode("\n")
        ."\n\n## Legacy Compatibility Checks\n\n"
        .collect($report['legacy_columns'])->map(fn ($value, $key) => "- `{$key}`: {$value}")->implode("\n")
        ."\n\n## Notes\n\n"
        .collect($report['notes'])->map(fn ($value) => "- {$value}")->implode("\n")
        ."\n";

    $writeDoc = trim((string) $this->option('write-doc'));
    if ($writeDoc !== '') {
        $targetPath = str_starts_with($writeDoc, DIRECTORY_SEPARATOR) ? $writeDoc : base_path($writeDoc);
        File::ensureDirectoryExists(dirname($targetPath));
        File::put($targetPath, $markdown);
        $this->newLine();
        $this->info('Report written to '.$writeDoc);
    }

    return self::SUCCESS;
})->purpose('Audit SQLite readiness before switching the Laravel app to MySQL');

Artisan::command('marketing:repair-lpjk-relations {--create-missing}', function (): int {
    $createMissing = (bool) $this->option('create-missing');
    $detailsFixed = 0;
    $parentsCreated = 0;
    $detailsUnresolved = 0;

    DB::transaction(function () use ($createMissing, &$detailsFixed, &$parentsCreated, &$detailsUnresolved): void {
        DB::table('lpjk_detail')
            ->whereNull('lpjk_id')
            ->orderBy('id')
            ->chunkById(100, function ($rows) use ($createMissing, &$detailsFixed, &$parentsCreated, &$detailsUnresolved): void {
                foreach ($rows as $detail) {
                    $masterId = trim((string) ($detail->master_id ?? ''));
                    if ($masterId === '') {
                        $detailsUnresolved++;
                        continue;
                    }

                    $parent = DB::table('lpjk')->where('source_id', $masterId)->first();

                    if ($parent === null && $createMissing) {
                        $parentId = DB::table('lpjk')->insertGetId([
                            'source_id' => $masterId,
                            'nama_event' => 'Recovered LPJK '.$masterId,
                            'status' => 'RECOVERED',
                            'keterangan' => 'Auto-created from lpjk_detail orphan repair.',
                            'raw_payload' => json_encode([
                                'source' => 'marketing:repair-lpjk-relations',
                                'detail_source_id' => $detail->source_id,
                            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                            'imported_at' => now(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        $parent = DB::table('lpjk')->where('id', $parentId)->first();
                        $parentsCreated++;
                    }

                    if ($parent === null) {
                        $detailsUnresolved++;
                        continue;
                    }

                    DB::table('lpjk_detail')
                        ->where('id', $detail->id)
                        ->update([
                            'lpjk_id' => $parent->id,
                            'updated_at' => now(),
                        ]);

                    $detailsFixed++;
                }
            });
    });

    $this->info('LPJK relation repair audit');
    $this->line('details_fixed: '.$detailsFixed);
    $this->line('parents_created: '.$parentsCreated);
    $this->line('details_unresolved: '.$detailsUnresolved);

    return self::SUCCESS;
})->purpose('Repair lpjk_detail foreign keys and optionally create missing LPJK parent rows');

Artisan::command('marketing:import-sqlite-to-mysql
    {--source=database/database.sqlite}
    {--target-host=127.0.0.1}
    {--target-port=3306}
    {--target-database=marketing_dashboard}
    {--target-username=root}
    {--target-password=}
    {--target-socket=/tmp/mysql.sock}
    {--truncate-target}', function (): int {
    $sourceOption = trim((string) $this->option('source'));
    $sourcePath = str_starts_with($sourceOption, DIRECTORY_SEPARATOR) ? $sourceOption : base_path($sourceOption);

    if (! File::exists($sourcePath)) {
        $this->error('SQLite source file not found: '.$sourcePath);

        return self::FAILURE;
    }

    config([
        'database.connections.mysql.host' => (string) $this->option('target-host'),
        'database.connections.mysql.port' => (string) $this->option('target-port'),
        'database.connections.mysql.database' => (string) $this->option('target-database'),
        'database.connections.mysql.username' => (string) $this->option('target-username'),
        'database.connections.mysql.password' => (string) $this->option('target-password'),
        'database.connections.mysql.unix_socket' => (string) $this->option('target-socket'),
    ]);

    DB::purge('mysql');
    $target = DB::connection('mysql');
    $target->getPdo();

    $source = new \PDO('sqlite:'.$sourcePath);
    $source->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    $source->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

    $sourceTables = collect($source->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%'")->fetchAll(\PDO::FETCH_COLUMN))
        ->map(fn ($name) => (string) $name)
        ->values()
        ->all();

    $targetTables = collect($target->select('SHOW TABLES'))
        ->map(fn ($row) => (string) array_values((array) $row)[0])
        ->values()
        ->all();

    $preferredOrder = [
        'users',
        'password_reset_tokens',
        'sessions',
        'cache',
        'cache_locks',
        'jobs',
        'job_batches',
        'failed_jobs',
        'marketing_settings',
        'master_plans',
        'distributions',
        'analytics',
        'stock_names',
        'marketing_excel_rows',
        'unboxing',
        'story_schedules',
        'calendar_events',
        'ideation',
        'program_promo',
        'sell_out_targets',
        'ads_performance',
        'harga_kompetitor',
        'orderan_online',
        'unit_ditanya',
        'claim_garansi',
        'keep_barang',
        'lpjk',
        'lpjk_detail',
        'meta_ig_posts',
        'activity_logs',
    ];

    $tables = collect($preferredOrder)
        ->filter(fn ($table) => in_array($table, $sourceTables, true) && in_array($table, $targetTables, true))
        ->values()
        ->all();

    $stats = [];
    $sanitizers = [
        'analytics' => static function (array $row): array {
            foreach (['views', 'likes', 'comments', 'shares'] as $column) {
                if (array_key_exists($column, $row)) {
                    $row[$column] = max(0, (int) $row[$column]);
                }
            }

            return $row;
        },
    ];

    $target->statement('SET FOREIGN_KEY_CHECKS=0');
    $insertedCounts = [];
    try {
        if ($this->option('truncate-target')) {
            foreach (array_reverse($tables) as $table) {
                $target->statement('TRUNCATE TABLE `'.$table.'`');
            }
        }

        foreach ($tables as $table) {
            $rows = $source->query('SELECT * FROM "'.$table.'"')->fetchAll();
            if (isset($sanitizers[$table])) {
                $rows = array_map($sanitizers[$table], $rows);
            }
            $sourceCount = count($rows);

            if ($sourceCount > 0) {
                foreach (array_chunk($rows, 200) as $chunk) {
                    $target->table($table)->insert($chunk);
                }
            }

            $insertedCounts[$table] = $sourceCount;
        }
    } finally {
        $target->statement('SET FOREIGN_KEY_CHECKS=1');
    }

    // Verify via separate PDO connection - confirms data visible outside original connection
    $host = (string) $this->option('target-host');
    $port = (string) $this->option('target-port');
    $database = (string) $this->option('target-database');
    $socket = (string) $this->option('target-socket');
    $dsn = $socket !== ''
        ? "mysql:unix_socket={$socket};dbname={$database};charset=utf8mb4"
        : "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
    $verify = new \PDO($dsn, (string) $this->option('target-username'), (string) $this->option('target-password'));
    $verify->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

    foreach ($tables as $table) {
        $targetCount = (int) $verify->query('SELECT COUNT(*) FROM `'.$table.'`')->fetchColumn();
        $stats[$table] = [
            'source' => $insertedCounts[$table],
            'target' => $targetCount,
        ];
    }

    $this->info('SQLite to MySQL import audit');
    $this->line('source: '.$sourcePath);
    $this->line('target_database: '.(string) $this->option('target-database'));
    foreach ($stats as $table => $counts) {
        $status = $counts['source'] === $counts['target'] ? 'OK' : 'MISMATCH';
        $this->line(sprintf('%s: source=%d target=%d [%s]', $table, $counts['source'], $counts['target'], $status));
    }

    $hasMismatch = collect($stats)->contains(fn (array $counts): bool => $counts['source'] !== $counts['target']);

    return $hasMismatch ? self::FAILURE : self::SUCCESS;
})->purpose('Import the current SQLite dataset into the target MySQL database');
