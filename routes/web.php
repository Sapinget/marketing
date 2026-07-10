<?php

use App\Support\MasterPlanDistributionSync;
use App\Support\DashboardAuth;
use App\Support\MetaIgImportNormalizer;
use Illuminate\Support\Facades\DB;
use App\Support\MarketingDashboardShell;
use Illuminate\Support\Facades\Route;

$nullableDate = fn ($value) => blank($value) ? null : $value;

$actor = fn () => trim((string) (request()->header('X-App-User') ?? '')) ?: null;
$actorUserId = function () use ($actor): ?int {
    $authenticatedUserId = auth()->id();
    if (is_int($authenticatedUserId)) {
        return $authenticatedUserId;
    }

    $actorValue = $actor();
    if ($actorValue === null) {
        return null;
    }

    $resolvedUserId = DB::table('users')
        ->where(function ($query) use ($actorValue) {
            $query->where('username', $actorValue)
                ->orWhere('email', $actorValue)
                ->orWhere('name', $actorValue);
        })
        ->value('id');

    return is_numeric($resolvedUserId) ? (int) $resolvedUserId : null;
};
$actorLabel = function () use ($actor): ?string {
    $user = auth()->user();
    if ($user instanceof \App\Models\User) {
        return $user->username ?: $user->name;
    }

    return $actor();
};
$masterPlanIdBySourceId = function (?string $sourceId): ?int {
    $normalizedSourceId = trim((string) $sourceId);
    if ($normalizedSourceId === '') {
        return null;
    }

    $masterPlanId = DB::table('master_plans')
        ->where('source_id', $normalizedSourceId)
        ->value('id');

    return is_numeric($masterPlanId) ? (int) $masterPlanId : null;
};
$lpjkIdBySourceId = function (?string $sourceId): ?int {
    $normalizedSourceId = trim((string) $sourceId);
    if ($normalizedSourceId === '') {
        return null;
    }

    $lpjkId = DB::table('lpjk')
        ->where('source_id', $normalizedSourceId)
        ->value('id');

    return is_numeric($lpjkId) ? (int) $lpjkId : null;
};
$requireMasterPlanIdBySourceId = function (?string $sourceId) use ($masterPlanIdBySourceId): int {
    $masterPlanId = $masterPlanIdBySourceId($sourceId);
    abort_if($masterPlanId === null, 422, 'Master plan tidak ditemukan.');

    return $masterPlanId;
};
$requireLpjkIdBySourceId = function (?string $sourceId) use ($lpjkIdBySourceId): int {
    $lpjkId = $lpjkIdBySourceId($sourceId);
    abort_if($lpjkId === null, 422, 'LPJK tidak ditemukan.');

    return $lpjkId;
};
$activityPayload = static fn ($value): ?string => $value === null
    ? null
    : json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$logCrudActivity = function (
    string $tableName,
    string $action,
    string|int $recordKey,
    ?int $recordId = null,
    mixed $before = null,
    mixed $after = null
) use ($activityPayload, $actorLabel, $actorUserId): void {
    DB::table('activity_logs')->insert([
        'user_id' => $actorUserId(),
        'actor_label' => $actorLabel(),
        'table_name' => $tableName,
        'action' => $action,
        'record_key' => (string) $recordKey,
        'record_id' => $recordId,
        'before_payload' => $activityPayload($before),
        'after_payload' => $activityPayload($after),
        'created_at' => now(),
    ]);
};

$stripTags = fn ($value) => is_string($value) ? strip_tags(trim($value)) : $value;
$rowValue = static fn ($row, string $key) => isset($row->{$key}) ? $row->{$key} : null;

$masterPlanValidate = function (array $payload): void {
    abort_if(blank($payload['Judul'] ?? null), 422, 'Judul wajib diisi.');
    abort_if(mb_strlen($payload['Judul'] ?? '') > 500, 422, 'Judul maksimal 500 karakter.');
    abort_if(blank($payload['Editor'] ?? null), 422, 'Editor wajib diisi.');
    abort_if(mb_strlen($payload['Editor'] ?? '') > 200, 422, 'Editor maksimal 200 karakter.');
    abort_if(mb_strlen($payload['Talent'] ?? '') > 1000, 422, 'Talent maksimal 1000 karakter.');
};

$masterPlanPayload = fn (array $payload, ?string $sourceId = null) => [
    'source_id' => $sourceId ?: (filled($payload['ID'] ?? null) ? (string) $payload['ID'] : 'master-'.now()->format('YmdHis').'-'.substr(md5((string) microtime(true)), 0, 6)),
    'title' => $stripTags($payload['Judul'] ?? null),
    'format_konten' => $stripTags($payload['Format_Konten'] ?? null),
    'platforms' => $stripTags($payload['Platforms'] ?? null),
    'colab' => $stripTags($payload['Colab'] ?? null),
    'editor' => $stripTags($payload['Editor'] ?? null),
    'talent' => $stripTags($payload['Talent'] ?? null),
    'script' => $payload['Skrip'] ?? null,
    'caption' => $payload['Caption'] ?? null,
    'status' => $stripTags($payload['Status'] ?? null),
    'tanggal_rencana' => $nullableDate($payload['Tanggal_Rencana'] ?? null),
    'distribution_meta' => is_array($payload['Distribution_Meta'] ?? null)
        ? json_encode($payload['Distribution_Meta'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        : ($payload['Distribution_Meta'] ?? null),
    'link_drive' => $payload['Link_Drive'] ?? null,
    'raw_payload' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    'updated_by' => $actor(),
    'imported_at' => now(),
    'updated_at' => now(),
];

$masterPlanResponse = fn ($row) => [
    'ID' => $row->source_id,
    'Judul' => $row->title,
    'Format_Konten' => $row->format_konten,
    'Platforms' => $row->platforms,
    'Colab' => $row->colab,
    'Editor' => $row->editor,
    'Talent' => $rowValue($row, 'talent'),
    'Skrip' => $row->script,
    'Caption' => $row->caption,
    'Status' => $row->status,
    'Tanggal_Rencana' => $row->tanggal_rencana,
    'Distribution_Meta' => $row->distribution_meta,
    'Link_Drive' => $row->link_drive,
    'Created_By' => $row->created_by ?? null,
    'Updated_By' => $row->updated_by ?? null,
    'Updated_At' => $row->updated_at ?? null,
];

$distributionPayload = fn (array $payload) => [
    'master_id' => trim((string) ($payload['Master_ID'] ?? '')),
    'title' => $stripTags($payload['Judul'] ?? null),
    'platform' => $stripTags((string) ($payload['Platform'] ?? '')),
    'tanggal_publish' => $nullableDate($payload['Tanggal_Publish'] ?? null),
    'link' => $payload['Link'] ?? null,
    'type' => $stripTags($payload['Type'] ?? null),
    'raw_payload' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    'converted_at' => now(),
    'updated_at' => now(),
];

$distributionResponse = fn ($row) => [
    'ID' => $row->id,
    'Master_ID' => $row->master_id,
    'Judul' => $row->title,
    'Platform' => $row->platform,
    'Tanggal_Publish' => $row->tanggal_publish,
    'Link' => $row->link,
    'Type' => $row->type,
];

$normalizeNamaStockValue = fn ($value) => preg_replace('/\s+/', ' ', strtoupper(trim((string) $value)));

$normalizeNamaStockRow = function (array $row) use ($normalizeNamaStockValue): array {
    return [
        ...$row,
        'ID' => filled($row['ID'] ?? null) ? (string) $row['ID'] : null,
        'KATEGORI' => $normalizeNamaStockValue($row['KATEGORI'] ?? ''),
        'BRAND' => $normalizeNamaStockValue($row['BRAND'] ?? ''),
        'SERI' => $normalizeNamaStockValue($row['SERI'] ?? ''),
    ];
};

$dedupeNamaStockRows = function (array $rows) use ($normalizeNamaStockRow): array {
    $seen = [];

    foreach ($rows as $row) {
        $normalized = $normalizeNamaStockRow((array) $row);
        $key = implode('|', [
            $normalized['KATEGORI'] ?? '',
            $normalized['BRAND'] ?? '',
            $normalized['SERI'] ?? '',
        ]);

        if (! isset($seen[$key])) {
            $seen[$key] = $normalized;
        }
    }

    return array_values($seen);
};

$analyticsPayload = fn (array $payload) => [
    'master_id' => trim((string) ($payload['Master_ID'] ?? '')),
    'title' => $stripTags($payload['Judul'] ?? null),
    'platform' => $stripTags((string) ($payload['Platform'] ?? '')),
    'tanggal_publish' => $nullableDate($payload['Tanggal_Publish'] ?? null),
    'views' => max(0, (int) ($payload['Views'] ?? 0)),
    'likes' => max(0, (int) ($payload['Likes'] ?? 0)),
    'comments' => max(0, (int) ($payload['Comments'] ?? 0)),
    'shares' => max(0, (int) ($payload['Shares'] ?? 0)),
    'raw_payload' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    'converted_at' => now(),
    'updated_at' => now(),
];

$analyticsResponse = fn ($row) => [
    'ID' => $row->id,
    'Master_ID' => $row->master_id,
    'Judul' => $row->title,
    'Platform' => $row->platform,
    'Tanggal_Publish' => $row->tanggal_publish,
    'Views' => $row->views,
    'Likes' => $row->likes,
    'Comments' => $row->comments,
    'Shares' => $row->shares,
];

// Print-job: store (POST) + serve (GET) for browser-native popup printing.
// Single-use random token is the access control for GET.
Route::get('/print-job/{token}', function (string $token) {
    $safeToken = preg_replace('/[^a-f0-9]/i', '', $token);
    if ($safeToken === '') {
        return response('Token tidak valid.', 404)->header('Content-Type', 'text/html; charset=UTF-8');
    }
    $cacheKey = 'ppp_print_job_' . $safeToken;
    $html = cache()->get($cacheKey);
    if (!$html) {
        return response('<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Print Tidak Ditemukan</title></head><body style="font-family:Arial,sans-serif;padding:40px;text-align:center"><p>Dokumen print tidak ditemukan atau sudah kedaluwarsa. Silakan coba cetak lagi.</p></body></html>', 404)
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }
    // Do NOT forget on first GET: the popup load itself is one GET, and a reload/refresh
    // would otherwise 404 ("Print Tidak Ditemukan"). The 5-minute cache TTL handles cleanup.
    return response($html, 200)
        ->header('Content-Type', 'text/html; charset=UTF-8')
        ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
        ->header('Pragma', 'no-cache');
})->withoutMiddleware([
    \App\Http\Middleware\VerifyCsrfToken::class,
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
]);

Route::get('/health', fn () => response()->json([
    'status' => 'ok',
    'app' => 'marketing-dashboard',
    'timestamp' => now()->toIso8601String(),
]))->withoutMiddleware([
    \App\Http\Middleware\VerifyCsrfToken::class,
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
]);

// POST /print-job - browser-native print flow submits final HTML here.
Route::post('/print-job', function () {
    $html = (string) request()->input('html', '');
    if ($html === '') {
        return response()->json(['error' => 'HTML required'], 422);
    }
    if (strlen($html) > 512000) {
        return response()->json(['error' => 'HTML payload too large (max 500KB)'], 413);
    }
    $sanitized = strip_tags($html, '<div><span><p><br><hr><table><thead><tbody><tr><th><td><h1><h2><h3><h4><h5><h6><ul><ol><li><img><a><strong><em><b><i><u><s><pre><code><blockquote><section><article><header><footer><main><aside><figure><figcaption><style><script><link><meta><title>');
    $sanitized = preg_replace('/<([a-z]+[a-z0-9]*)\s[^>]*?(on\w+)=["\'][^"\']*["\']/i', '<$1', $sanitized);
    $sanitized = preg_replace('/<script\b[^>]*>/i', '<script>', $sanitized);
    $token = bin2hex(random_bytes(16));
    cache()->put('ppp_print_job_' . $token, $sanitized, now()->addMinutes(5));
    return response()->json(['token' => $token]);
})->withoutMiddleware([
    \App\Http\Middleware\VerifyCsrfToken::class,
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
]);

Route::get('/api/auth/session', function (DashboardAuth $dashboardAuth) {
    $dashboardAuth->bootstrapConfiguredAdminSession();

    if (! auth()->check()) {
        return response()->json([
            'authenticated' => false,
            'user' => null,
        ]);
    }

    return response()->json([
        'authenticated' => true,
        'user' => $dashboardAuth->userPayload(auth()->user()),
    ]);
});

Route::post('/api/auth/login', function (DashboardAuth $dashboardAuth) {
    $payload = request()->validate([
        'username' => ['required', 'string', 'max:100'],
        'pin' => ['required', 'string', 'max:100'],
    ]);

    $user = $dashboardAuth->attemptLogin($payload['username'], $payload['pin']);

    if ($user === null) {
        return response()->json([
            'message' => 'Username atau PIN salah.',
        ], 422);
    }

    return response()->json([
        'status' => 'success',
        'user' => $dashboardAuth->userPayload($user),
    ]);
})->middleware('throttle:10,1');

Route::post('/api/auth/logout', function (DashboardAuth $dashboardAuth) {
    if (auth()->check()) {
        $dashboardAuth->logout();
    }

    return response()->json([
        'status' => 'success',
    ]);
});

Route::prefix('__db')->group(function (): void {
    $assertLocalRequest = static function (): void {
        $isProxyAuthed = request()->header('X-GAS-PROXY-SECRET') === env('GAS_PROXY_SECRET');
        abort_unless(app()->environment('local') || $isProxyAuthed, 404);
        abort_unless(in_array(request()->ip(), ['127.0.0.1', '::1'], true) || $isProxyAuthed, 403);
    };

    Route::get('/tables', function () use ($assertLocalRequest) {
        $assertLocalRequest();

        $database = DB::getDatabaseName();
        $tables = collect(DB::select('SHOW TABLES'))
            ->map(function (object $row): array {
                $table = (string) array_values((array) $row)[0];

                return [
                    'name' => $table,
                    'count' => DB::table($table)->count(),
                ];
            })
            ->sortBy('name')
            ->values();

        return view('db.tables', [
            'database' => $database,
            'tables' => $tables,
        ]);
    });

    Route::get('/tables/{table}', function (string $table) use ($assertLocalRequest) {
        $assertLocalRequest();

        $allowedTables = collect(DB::select('SHOW TABLES'))
            ->map(fn (object $row): string => (string) array_values((array) $row)[0]);

        abort_unless($allowedTables->contains($table), 404);

        $columns = collect(DB::select("SHOW COLUMNS FROM `{$table}`"))
            ->map(fn (object $column): string => (string) $column->Field)
            ->values();

        return view('db.table-preview', [
            'table' => $table,
            'columns' => $columns,
            'rows' => DB::table($table)->limit(50)->get(),
            'totalRows' => DB::table($table)->count(),
        ]);
    });
});

Route::middleware('gas.proxy')->group(function () use (
    $actor,
    $actorLabel,
    $actorUserId,
    $analyticsPayload,
    $analyticsResponse,
    $dedupeNamaStockRows,
    $distributionPayload,
    $distributionResponse,
    $logCrudActivity,
    $lpjkIdBySourceId,
    $requireLpjkIdBySourceId,
    $masterPlanPayload,
    $masterPlanIdBySourceId,
    $requireMasterPlanIdBySourceId,
    $masterPlanResponse,
    $masterPlanValidate,
    $nullableDate,
    $rowValue
): void {
Route::get('/', function (MarketingDashboardShell $dashboardShell) {
    $backendUrl = rtrim(url('/'), '/');

    // No-store: always serve the freshest dashboard frontend so browser caching
    // can't keep stale print/export code after a deploy.
    return response()
        ->view('dashboard.index', $dashboardShell->build($backendUrl), 200)
        ->header('Content-Type', 'text/html; charset=UTF-8')
        ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
        ->header('Pragma', 'no-cache');
});

Route::get('/design-system', function () {
    return response()->view('reference.design-system');
});

Route::middleware('dashboard.auth')->group(function () use (
    $actor,
    $actorLabel,
    $actorUserId,
    $analyticsPayload,
    $analyticsResponse,
    $dedupeNamaStockRows,
    $distributionPayload,
    $distributionResponse,
    $logCrudActivity,
    $lpjkIdBySourceId,
    $requireLpjkIdBySourceId,
    $masterPlanPayload,
    $masterPlanIdBySourceId,
    $requireMasterPlanIdBySourceId,
    $masterPlanResponse,
    $masterPlanValidate,
    $nullableDate,
    $rowValue
): void {
Route::get('/api/auth/users', function (DashboardAuth $dashboardAuth) {
    return response()->json([
        'data' => $dashboardAuth->listUsers(),
    ]);
});

Route::post('/api/auth/users', function (DashboardAuth $dashboardAuth) use ($logCrudActivity) {
    $payload = request()->validate([
        'username' => ['required', 'string', 'min:3', 'max:100'],
        'nama' => ['required', 'string', 'max:255'],
        'email' => ['nullable', 'email', 'max:255'],
        'pin' => ['required', 'string', 'min:6', 'max:100', 'confirmed'],
    ], [
        'username.required' => 'Username wajib diisi.',
        'username.min' => 'Username minimal 3 karakter.',
        'nama.required' => 'Nama wajib diisi.',
        'email.email' => 'Format email tidak valid.',
        'pin.required' => 'PIN wajib diisi.',
        'pin.min' => 'PIN minimal 6 karakter.',
        'pin.confirmed' => 'Konfirmasi PIN tidak cocok.',
    ]);

    $user = DB::transaction(function () use ($dashboardAuth, $payload, $logCrudActivity) {
        $before = \App\Models\User::query()->where('username', trim((string) $payload['username']))->lockForUpdate()->first();

        $user = $dashboardAuth->createUser(
            $payload['username'],
            $payload['pin'],
            $payload['nama'],
            $payload['email'] ?? null,
        );

        $logCrudActivity(
            'users',
            $before === null ? 'create' : 'update',
            (string) $user->username,
            $user->getKey(),
            $before?->only(['id', 'username', 'name', 'email']),
            $user->only(['id', 'username', 'name', 'email'])
        );

        return $user;
    });

    return response()->json([
        'status' => 'success',
        'data' => $dashboardAuth->userPayload($user),
    ]);
});

Route::put('/api/auth/profile', function (DashboardAuth $dashboardAuth) use ($logCrudActivity) {
    $payload = request()->validate([
        'nama' => ['required', 'string', 'max:255'],
    ]);

    $user = auth()->user();
    abort_unless($user instanceof \App\Models\User, 401);
    $before = $user->only(['id', 'username', 'name', 'email']);
    $updatedUser = $dashboardAuth->updateProfileName($user, $payload['nama']);
    $logCrudActivity('users', 'update', (string) $updatedUser->username, $updatedUser->getKey(), $before, $updatedUser->only(['id', 'username', 'name', 'email']));

    return response()->json([
        'status' => 'success',
        'user' => $dashboardAuth->userPayload($updatedUser),
    ]);
});

Route::put('/api/auth/pin', function (DashboardAuth $dashboardAuth) use ($logCrudActivity) {
    $payload = request()->validate([
        'old_pin' => ['required', 'string', 'max:100'],
        'new_pin' => ['required', 'string', 'min:6', 'max:100', 'confirmed'],
    ]);

    $user = auth()->user();
    abort_unless($user instanceof \App\Models\User, 401);

    if (! $dashboardAuth->changePin($user, $payload['old_pin'], $payload['new_pin'])) {
        return response()->json([
            'message' => 'PIN saat ini salah.',
        ], 422);
    }

    $logCrudActivity(
        'users',
        'update',
        (string) $user->username,
        $user->getKey(),
        ['pin_updated' => false],
        ['pin_updated' => true, 'updated_at' => now()->toIso8601String()]
    );

    return response()->json([
        'status' => 'success',
    ]);
});

Route::get('/api/activity-logs', function () {
    $query = DB::table('activity_logs')
        ->orderByDesc('created_at')
        ->orderByDesc('id');

    $tableName = trim((string) request()->query('table_name', ''));
    if ($tableName !== '') {
        $query->where('table_name', $tableName);
    }

    $action = trim((string) request()->query('action', ''));
    if ($action !== '') {
        $query->where('action', $action);
    }

    $recordKey = trim((string) request()->query('record_key', ''));
    if ($recordKey !== '') {
        $query->where('record_key', $recordKey);
    }

    $rows = $query->limit(200)->get()->map(function ($row) {
        return [
            'ID' => $row->id,
            'user_id' => $row->user_id,
            'actor_label' => $row->actor_label,
            'table_name' => $row->table_name,
            'action' => $row->action,
            'record_key' => $row->record_key,
            'record_id' => $row->record_id,
            'before_payload' => json_decode($row->before_payload ?? 'null', true),
            'after_payload' => json_decode($row->after_payload ?? 'null', true),
            'created_at' => $row->created_at,
        ];
    });

    return response()->json(['data' => $rows]);
});

Route::get('/api/master-plans', function () use ($rowValue) {
    $rows = DB::table('master_plans')
        ->orderByDesc('tanggal_rencana')
        ->orderBy('source_id')
        ->get()
        ->map(fn ($row) => [
            'ID' => $row->source_id,
            'Judul' => $row->title,
            'Format_Konten' => $row->format_konten,
            'Platforms' => $row->platforms,
            'Colab' => $row->colab,
            'Editor' => $row->editor,
            'Talent' => $rowValue($row, 'talent'),
            'Skrip' => $row->script,
            'Caption' => $row->caption,
            'Status' => $row->status,
            'Tanggal_Rencana' => $row->tanggal_rencana,
            'Distribution_Meta' => $row->distribution_meta,
            'Link_Drive' => $row->link_drive,
            'Created_By' => $row->created_by ?? null,
            'Updated_By' => $row->updated_by ?? null,
            'Updated_At' => $row->updated_at ?? null,
        ]);

    return response()->json(['data' => $rows]);
});

Route::post('/api/master-plans', function () use ($actorLabel, $actorUserId, $logCrudActivity, $masterPlanPayload, $masterPlanResponse, $masterPlanValidate) {
    $payload = request()->all();
    $masterPlanValidate($payload);
    $row = $masterPlanPayload($payload);
    $row['created_at'] = now();
    $row['created_by'] = $actorLabel();
    $row['created_by_user_id'] = $actorUserId();
    $row['updated_by'] = $actorLabel();
    $row['updated_by_user_id'] = $actorUserId();

    DB::table('master_plans')->insert($row);

    $stored = DB::table('master_plans')->where('source_id', $row['source_id'])->first();
    MasterPlanDistributionSync::sync($stored);
    $logCrudActivity('master_plans', 'create', $stored->source_id, (int) $stored->id, null, (array) $stored);

    return response()->json(['status' => 'success', 'data' => $masterPlanResponse($stored)], 201);
});

Route::put('/api/master-plans/{sourceId}', function (string $sourceId) use ($actorLabel, $actorUserId, $logCrudActivity, $masterPlanPayload, $masterPlanResponse, $masterPlanValidate) {
    abort_unless(DB::table('master_plans')->where('source_id', $sourceId)->exists(), 404);
    $before = DB::table('master_plans')->where('source_id', $sourceId)->first();
    $payload = request()->all();
    $masterPlanValidate($payload);

    $row = $masterPlanPayload($payload, $sourceId);
    $row['updated_by'] = $actorLabel();
    $row['updated_by_user_id'] = $actorUserId();

    DB::table('master_plans')->where('source_id', $sourceId)->update($row);

    $stored = DB::table('master_plans')->where('source_id', $sourceId)->first();
    MasterPlanDistributionSync::sync($stored);
    $logCrudActivity('master_plans', 'update', $stored->source_id, (int) $stored->id, $before ? (array) $before : null, (array) $stored);

    return response()->json(['status' => 'success', 'data' => $masterPlanResponse($stored)]);
});

Route::delete('/api/master-plans/{sourceId}', function (string $sourceId) use ($logCrudActivity) {
    abort_unless(DB::table('master_plans')->where('source_id', $sourceId)->exists(), 404);
    $stored = DB::table('master_plans')->where('source_id', $sourceId)->first();

    MasterPlanDistributionSync::deleteDerivedRows($sourceId);
    DB::table('master_plans')->where('source_id', $sourceId)->delete();
    if ($stored !== null) {
        $logCrudActivity('master_plans', 'delete', $stored->source_id, (int) $stored->id, (array) $stored, null);
    }

    return response()->json(['status' => 'success']);
});

Route::get('/api/settings', function () {
    $settings = DB::table('marketing_settings')
        ->orderBy('key')
        ->get(['key', 'values'])
        ->mapWithKeys(function ($row) {
            $values = json_decode($row->values, true);

            return [$row->key => is_array($values) ? $values : []];
        });

    return response()->json(['data' => $settings]);
});

Route::get('/api/raw-sheets/{sheetName}', function (string $sheetName) use ($dedupeNamaStockRows) {
    $sheetName = urldecode($sheetName);
    if ($sheetName === 'Nama_Stock') {
        $rows = DB::table('stock_names')
            ->orderBy('kategori')
            ->orderBy('brand')
            ->orderBy('seri')
            ->get(['source_id', 'kategori', 'brand', 'seri'])
            ->values()
            ->map(fn ($row, $index) => [
                '_row_number' => $index + 2,
                'ID' => $row->source_id,
                'KATEGORI' => $row->kategori,
                'BRAND' => $row->brand,
                'SERI' => $row->seri,
            ]);

        return response()->json(['data' => $rows]);
    }

    $rows = DB::table('marketing_excel_rows')
        ->where('sheet_name', $sheetName)
        ->orderBy('row_number')
        ->get(['row_number', 'payload'])
        ->map(function ($row) {
            $payload = json_decode($row->payload, true);

            return is_array($payload) ? ['_row_number' => $row->row_number, ...$payload] : ['_row_number' => $row->row_number];
        });

    return response()->json(['data' => $rows]);
});

Route::put('/api/raw-sheets/{sheetName}', function (string $sheetName) use ($dedupeNamaStockRows, $logCrudActivity) {
    $sheetName = urldecode($sheetName);
    $rows = request()->all('data')['data'] ?? request()->all();
    abort_unless(is_array($rows), 422, 'Data harus berupa array.');
    if ($sheetName === 'Nama_Stock') {
        $rows = $dedupeNamaStockRows($rows);
    }
    $now = now();
    $targetTable = $sheetName === 'Nama_Stock' ? 'stock_names' : 'marketing_excel_rows';
    $beforeCount = $sheetName === 'Nama_Stock'
        ? DB::table('stock_names')->count()
        : DB::table('marketing_excel_rows')->where('sheet_name', $sheetName)->count();

    DB::transaction(function () use ($sheetName, $rows, $now): void {
        if ($sheetName === 'Nama_Stock') {
            DB::table('stock_names')->delete();

            foreach (array_values($rows) as $row) {
                DB::table('stock_names')->insert([
                    'source_id' => filled($row['ID'] ?? null) ? (string) $row['ID'] : null,
                    'kategori' => $row['KATEGORI'] ?? null,
                    'brand' => $row['BRAND'] ?? null,
                    'seri' => $row['SERI'] ?? null,
                    'imported_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            return;
        }

        DB::table('marketing_excel_rows')->where('sheet_name', $sheetName)->delete();

        foreach (array_values($rows) as $index => $row) {
            $payload = json_encode((array) $row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            DB::table('marketing_excel_rows')->insert([
                'sheet_name' => $sheetName,
                'row_number' => $index + 2,
                'row_hash' => hash('sha256', $payload),
                'payload' => $payload,
                'imported_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    });

    $logCrudActivity($targetTable, 'update', $sheetName, null, [
        'sheet_name' => $sheetName,
        'row_count' => $beforeCount,
    ], [
        'sheet_name' => $sheetName,
        'row_count' => count($rows),
    ]);

    return response()->json(['status' => 'success', 'data' => array_values($rows)]);
});

// Meta IG Analytics (story / feed)
// Variable/reordering export headers are mapped to canonical columns on the
// client; rows are upserted by post_id (re-pull replaces with latest numbers).
$metaCols = ['account', 'account_name', 'description', 'duration', 'publish_time', 'permalink', 'post_type', 'views', 'reach', 'likes', 'shares', 'comments', 'saves', 'follows', 'profile_visits', 'replies', 'navigation', 'link_clicks', 'sticker_taps'];
$inspectMetaPostDuplicates = function (array $rows, string $dataset): array {
    $postIds = collect($rows)
        ->map(fn ($row) => trim((string) ((array) $row)['post_id'] ?? ''))
        ->filter()
        ->unique()
        ->values();

    if ($postIds->isEmpty()) {
        return ['duplicates' => 0, 'sample_post_ids' => [], 'new_rows' => 0];
    }

    $existingIds = DB::table('meta_ig_posts')
        ->where('dataset', $dataset)
        ->whereIn('post_id', $postIds->all())
        ->pluck('post_id')
        ->map(fn ($id) => (string) $id)
        ->all();

    $duplicateIds = array_values($existingIds);

    return [
        'duplicates' => count($duplicateIds),
        'sample_post_ids' => array_slice($duplicateIds, 0, 5),
        'new_rows' => max(0, $postIds->count() - count($duplicateIds)),
    ];
};
$upsertMetaPosts = function (array $rows, string $dataset) use ($metaCols): array {
    $now = now();
    $inserted = 0;
    $updated = 0;

    DB::transaction(function () use ($rows, $dataset, $metaCols, $now, &$inserted, &$updated): void {
        foreach ($rows as $raw) {
            $r = (array) $raw;
            $pid = trim((string) ($r['post_id'] ?? ''));
            if ($pid === '') {
                continue;
            }
            $rec = [
                'dataset' => $dataset,
                'raw_payload' => json_encode($r['raw_payload'] ?? $r, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'imported_at' => $now,
                'updated_at' => $now,
            ];
            foreach ($metaCols as $c) {
                if (array_key_exists($c, $r)) {
                    $v = $r[$c];
                    $rec[$c] = ($v === '' || $v === null) ? null : $v;
                }
            }
            if (DB::table('meta_ig_posts')->where('post_id', $pid)->exists()) {
                DB::table('meta_ig_posts')->where('post_id', $pid)->update($rec);
                $updated++;
            } else {
                $rec['post_id'] = $pid;
                $rec['created_at'] = $now;
                DB::table('meta_ig_posts')->insert($rec);
                $inserted++;
            }
        }
    });

    return ['inserted' => $inserted, 'updated' => $updated, 'total' => $inserted + $updated];
};

Route::get('/api/meta-posts/{dataset}', function (string $dataset) {
    $rows = DB::table('meta_ig_posts')->where('dataset', $dataset)->orderByDesc('publish_time')->get()->map(function ($r) {
        $extra = json_decode($r->raw_payload ?? '{}', true) ?: [];

        return array_merge($extra, [
            'ID' => $r->post_id, 'post_id' => $r->post_id, 'dataset' => $r->dataset,
            'account' => $r->account, 'account_name' => $r->account_name, 'description' => $r->description,
            'duration' => $r->duration, 'publish_time' => $r->publish_time, 'permalink' => $r->permalink, 'post_type' => $r->post_type,
            'views' => (int) $r->views, 'reach' => (int) $r->reach, 'likes' => (int) $r->likes, 'shares' => (int) $r->shares,
            'comments' => (int) $r->comments, 'saves' => (int) $r->saves, 'follows' => (int) $r->follows,
            'profile_visits' => (int) $r->profile_visits, 'replies' => (int) $r->replies, 'navigation' => (int) $r->navigation,
            'link_clicks' => (int) $r->link_clicks, 'sticker_taps' => (int) $r->sticker_taps,
        ]);
    });

    return response()->json(['data' => $rows]);
});

Route::post('/api/meta-posts/{dataset}/import', function (string $dataset) use ($inspectMetaPostDuplicates, $logCrudActivity, $upsertMetaPosts) {
    abort_unless(in_array($dataset, ['story', 'feed'], true), 404);

    $rawRows = request()->input('rows', request()->input('data', []));
    abort_unless(is_array($rawRows), 422, 'rows harus berupa array.');

    $rows = app(MetaIgImportNormalizer::class)->normalizeImportRows($rawRows, $dataset);
    $overwrite = filter_var(request()->input('overwrite', false), FILTER_VALIDATE_BOOLEAN);
    $duplicates = $inspectMetaPostDuplicates($rows, $dataset);

    if (($duplicates['duplicates'] ?? 0) > 0 && !$overwrite) {
        return response()->json([
            'status' => 'confirm_required',
            'requires_confirmation' => true,
            'inserted' => 0,
            'updated' => 0,
            'total' => 0,
            ...$duplicates,
        ]);
    }

    $summary = $upsertMetaPosts($rows, $dataset);

    if (($summary['total'] ?? 0) > 0) {
        $logCrudActivity('meta_ig_posts', 'update', $dataset, null, null, [
            'dataset' => $dataset,
            ...$summary,
        ]);
    }

    return response()->json(['status' => 'success', ...$summary]);
});

Route::post('/api/meta-posts/{dataset}/import-folder', function (string $dataset) use ($inspectMetaPostDuplicates, $logCrudActivity, $upsertMetaPosts) {
    abort_unless(in_array($dataset, ['story', 'feed'], true), 404);

    $directory = (string) request()->input('directory', base_path('export-meta'));
    $result = app(MetaIgImportNormalizer::class)->loadImportRowsFromDirectory($directory, $dataset);
    $rows = app(MetaIgImportNormalizer::class)->normalizeImportRows($result['rows'], $dataset);
    $overwrite = filter_var(request()->input('overwrite', false), FILTER_VALIDATE_BOOLEAN);
    $duplicates = $inspectMetaPostDuplicates($rows, $dataset);

    if (($duplicates['duplicates'] ?? 0) > 0 && !$overwrite) {
        return response()->json([
            'status' => 'confirm_required',
            'requires_confirmation' => true,
            'inserted' => 0,
            'updated' => 0,
            'total' => 0,
            'files_scanned' => $result['files_scanned'],
            'files_matched' => $result['files_matched'],
            ...$duplicates,
        ]);
    }

    $summary = $upsertMetaPosts($rows, $dataset);

    if (($summary['total'] ?? 0) > 0) {
        $logCrudActivity('meta_ig_posts', 'update', $dataset, null, null, [
            'dataset' => $dataset,
            ...$summary,
            'files_scanned' => $result['files_scanned'],
            'files_matched' => $result['files_matched'],
        ]);
    }

    return response()->json([
        'status' => 'success',
        ...$summary,
        'files_scanned' => $result['files_scanned'],
        'files_matched' => $result['files_matched'],
    ]);
});

Route::delete('/api/meta-posts/{dataset}', function (string $dataset) use ($logCrudActivity) {
    $beforeCount = DB::table('meta_ig_posts')->where('dataset', $dataset)->count();
    DB::table('meta_ig_posts')->where('dataset', $dataset)->delete();
    $logCrudActivity('meta_ig_posts', 'delete', $dataset, null, [
        'dataset' => $dataset,
        'row_count' => $beforeCount,
    ], null);

    return response()->json(['status' => 'success']);
});

Route::put('/api/settings', function () use ($logCrudActivity) {
    $payload = request()->all();
    $settings = is_array($payload['data'] ?? null) ? $payload['data'] : $payload;
    $before = DB::table('marketing_settings')
        ->orderBy('key')
        ->get(['key', 'values'])
        ->mapWithKeys(fn ($row) => [$row->key => json_decode($row->values, true) ?: []])
        ->all();

    DB::transaction(function () use ($settings) {
        foreach ($settings as $key => $values) {
            $normalizedValues = is_array($values)
                ? (array_is_list($values) ? array_values($values) : $values)
                : [];
            DB::table('marketing_settings')->updateOrInsert(
                ['key' => $key],
                [
                    'values' => json_encode($normalizedValues, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'imported_at' => now(),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    });

    $logCrudActivity('marketing_settings', 'update', 'settings', null, $before, $settings);

    return response()->json(['status' => 'success', 'data' => $settings]);
});

Route::get('/api/distributions', function () {
    $rows = DB::table('distributions')
        ->leftJoin('master_plans', 'master_plans.id', '=', 'distributions.master_plan_id')
        ->orderByDesc('tanggal_publish')
        ->orderByRaw("COALESCE(NULLIF(distributions.master_id, ''), master_plans.source_id, '')")
        ->get([
            'distributions.*',
            DB::raw("COALESCE(NULLIF(distributions.master_id, ''), master_plans.source_id) as resolved_master_id"),
        ])
        ->map(fn ($row) => [
            'ID' => $row->id,
            'Master_ID' => $row->resolved_master_id,
            'Judul' => $row->title,
            'Platform' => $row->platform,
            'Tanggal_Publish' => $row->tanggal_publish,
            'Link' => $row->link,
            'Type' => $row->type,
        ]);

    return response()->json(['data' => $rows]);
});

Route::post('/api/distributions', function () use ($actorUserId, $distributionPayload, $distributionResponse, $logCrudActivity, $requireMasterPlanIdBySourceId) {
    $row = $distributionPayload(request()->all());
    abort_if(blank($row['master_id']) || blank($row['platform']), 422, 'Master_ID dan Platform wajib diisi.');
    $row['created_at'] = now();
    $row['master_plan_id'] = $requireMasterPlanIdBySourceId($row['master_id']);
    $row['created_by_user_id'] = $actorUserId();
    $row['updated_by_user_id'] = $actorUserId();

    DB::table('distributions')->insert($row);
    $stored = DB::table('distributions')->where('id', DB::getPdo()->lastInsertId())->first();
    $logCrudActivity('distributions', 'create', (string) $stored->id, (int) $stored->id, null, (array) $stored);

    return response()->json(['status' => 'success', 'data' => $distributionResponse($stored)], 201);
});

Route::put('/api/distributions/{id}', function (int $id) use ($actorUserId, $distributionPayload, $distributionResponse, $logCrudActivity, $requireMasterPlanIdBySourceId) {
    abort_unless(DB::table('distributions')->where('id', $id)->exists(), 404);
    $before = DB::table('distributions')->where('id', $id)->first();

    $row = $distributionPayload(request()->all());
    abort_if(blank($row['master_id']) || blank($row['platform']), 422, 'Master_ID dan Platform wajib diisi.');
    $row['master_plan_id'] = $requireMasterPlanIdBySourceId($row['master_id']);
    $row['updated_by_user_id'] = $actorUserId();
    DB::table('distributions')->where('id', $id)->update($row);

    $stored = DB::table('distributions')->where('id', $id)->first();
    $logCrudActivity('distributions', 'update', (string) $stored->id, (int) $stored->id, $before ? (array) $before : null, (array) $stored);

    return response()->json(['status' => 'success', 'data' => $distributionResponse($stored)]);
});

Route::delete('/api/distributions/{id}', function (int $id) use ($logCrudActivity) {
    abort_unless(DB::table('distributions')->where('id', $id)->exists(), 404);
    $stored = DB::table('distributions')->where('id', $id)->first();

    DB::table('distributions')->where('id', $id)->delete();
    if ($stored !== null) {
        $logCrudActivity('distributions', 'delete', (string) $stored->id, (int) $stored->id, (array) $stored, null);
    }

    return response()->json(['status' => 'success']);
});

Route::get('/api/analytics', function () {
    $rows = DB::table('analytics')
        ->leftJoin('master_plans', 'master_plans.id', '=', 'analytics.master_plan_id')
        ->whereNotIn('platform', ['contentType'])
        ->orderByDesc('tanggal_publish')
        ->orderByRaw("COALESCE(NULLIF(analytics.master_id, ''), master_plans.source_id, '')")
        ->get([
            'analytics.*',
            DB::raw("COALESCE(NULLIF(analytics.master_id, ''), master_plans.source_id) as resolved_master_id"),
        ])
        ->map(fn ($row) => [
            'ID' => $row->id,
            'Master_ID' => $row->resolved_master_id,
            'Judul' => $row->title,
            'Platform' => $row->platform,
            'Tanggal_Publish' => $row->tanggal_publish,
            'Views' => $row->views,
            'Likes' => $row->likes,
            'Comments' => $row->comments,
            'Shares' => $row->shares,
        ]);

    return response()->json(['data' => $rows]);
});

Route::post('/api/analytics', function () use ($actorUserId, $analyticsPayload, $analyticsResponse, $logCrudActivity, $requireMasterPlanIdBySourceId) {
    $row = $analyticsPayload(request()->all());
    abort_if(blank($row['master_id']) || blank($row['platform']), 422, 'Master_ID dan Platform wajib diisi.');
    $row['created_at'] = now();
    $row['master_plan_id'] = $requireMasterPlanIdBySourceId($row['master_id']);
    $row['created_by_user_id'] = $actorUserId();
    $row['updated_by_user_id'] = $actorUserId();

    DB::table('analytics')->insert($row);
    $stored = DB::table('analytics')->where('id', DB::getPdo()->lastInsertId())->first();
    $logCrudActivity('analytics', 'create', (string) $stored->id, (int) $stored->id, null, (array) $stored);

    return response()->json(['status' => 'success', 'data' => $analyticsResponse($stored)], 201);
});

Route::put('/api/analytics/{id}', function (int $id) use ($actorUserId, $analyticsPayload, $analyticsResponse, $logCrudActivity, $requireMasterPlanIdBySourceId) {
    abort_unless(DB::table('analytics')->where('id', $id)->exists(), 404);
    $before = DB::table('analytics')->where('id', $id)->first();

    $row = $analyticsPayload(request()->all());
    abort_if(blank($row['master_id']) || blank($row['platform']), 422, 'Master_ID dan Platform wajib diisi.');
    $row['master_plan_id'] = $requireMasterPlanIdBySourceId($row['master_id']);
    $row['updated_by_user_id'] = $actorUserId();
    DB::table('analytics')->where('id', $id)->update($row);

    $stored = DB::table('analytics')->where('id', $id)->first();
    $logCrudActivity('analytics', 'update', (string) $stored->id, (int) $stored->id, $before ? (array) $before : null, (array) $stored);

    return response()->json(['status' => 'success', 'data' => $analyticsResponse($stored)]);
});

Route::delete('/api/analytics/{id}', function (int $id) use ($logCrudActivity) {
    abort_unless(DB::table('analytics')->where('id', $id)->exists(), 404);
    $stored = DB::table('analytics')->where('id', $id)->first();

    DB::table('analytics')->where('id', $id)->delete();
    if ($stored !== null) {
        $logCrudActivity('analytics', 'delete', (string) $stored->id, (int) $stored->id, (array) $stored, null);
    }

    return response()->json(['status' => 'success']);
});

// Generic table CRUD helper
//
// Each table in this section uses the same pattern:
//   GET    /api/{resource}         -> list all rows
//   POST   /api/{resource}         -> insert one row
//   PUT    /api/{resource}/{id}    -> update one row (by source_id)
//   DELETE /api/{resource}/{id}    -> delete one row (by source_id)
//
// Rows carry a `source_id` (from the Excel ID column) as the stable identifier,
// plus key columns for querying and a `raw_payload` JSON blob for everything else.

$genericList = fn (string $table, callable $map, string $orderBy = 'created_at', string $dir = 'desc') => function () use ($table, $map, $orderBy, $dir) {
    return response()->json(['data' => DB::table($table)->orderBy($orderBy, $dir)->get()->map($map)]);
};

$genericUpsert = fn (string $table, callable $build) => function () use ($build, $logCrudActivity, $table) {
    $payload = request()->all();
    $row = $build($payload);
    $row['created_at'] = now();
    DB::table($table)->insert($row);
    $stored = DB::table($table)->where('source_id', $row['source_id'])->first();
    $logCrudActivity($table, 'create', (string) $stored->source_id, is_numeric($stored->id ?? null) ? (int) $stored->id : null, null, (array) $stored);

    return response()->json(['status' => 'success', 'data' => $stored], 201);
};

$genericUpdate = fn (string $table, callable $build) => function (string $sourceId) use ($build, $logCrudActivity, $table) {
    abort_unless(DB::table($table)->where('source_id', $sourceId)->exists(), 404);
    $before = DB::table($table)->where('source_id', $sourceId)->first();
    DB::table($table)->where('source_id', $sourceId)->update($build(request()->all(), $sourceId));
    $stored = DB::table($table)->where('source_id', $sourceId)->first();
    $logCrudActivity($table, 'update', (string) $stored->source_id, is_numeric($stored->id ?? null) ? (int) $stored->id : null, $before ? (array) $before : null, (array) $stored);

    return response()->json(['status' => 'success', 'data' => $stored]);
};

$genericDelete = fn (string $table) => function (string $sourceId) use ($logCrudActivity, $table) {
    abort_unless(DB::table($table)->where('source_id', $sourceId)->exists(), 404);
    $stored = DB::table($table)->where('source_id', $sourceId)->first();
    DB::table($table)->where('source_id', $sourceId)->delete();
    if ($stored !== null) {
        $logCrudActivity($table, 'delete', (string) $stored->source_id, is_numeric($stored->id ?? null) ? (int) $stored->id : null, (array) $stored, null);
    }

    return response()->json(['status' => 'success']);
};

$makeSourceId = fn (string $prefix, ?string $id) => filled($id) ? (string) $id : $prefix.'-'.now()->format('YmdHis').'-'.substr(md5(microtime(true)), 0, 6);

$encodePayload = fn (array $p) => json_encode($p, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

// Content tables

// Decode raw_payload (original PascalCase fields) and merge DB-authoritative columns on top.
// This preserves all original Excel fields while ensuring edits are reflected.
$fromDb = fn ($row, array $override = []) => array_merge(
    json_decode($row->raw_payload ?? '{}', true) ?: [],
    ['ID' => $row->source_id],
    $override
);

Route::get('/api/unboxing', function () use ($fromDb) {
    return response()->json(['data' => DB::table('unboxing')->orderByDesc('upload_date')->get()->map(fn ($r) => $fromDb($r, [
        'Nama'        => $r->nama,
        'Editor'      => $r->editor,
        'Status'      => $r->status,
        'Upload_Date' => $r->upload_date,
        'Link'        => $r->link,
    ]))]);
});
Route::post('/api/unboxing', $genericUpsert('unboxing', function (array $p) use ($encodePayload, $makeSourceId, $nullableDate) {
    return ['source_id' => $makeSourceId('UBX', $p['ID'] ?? null), 'nama' => $p['Nama'] ?? null, 'editor' => $p['Editor'] ?? null, 'status' => $p['Status'] ?? null, 'upload_date' => $nullableDate($p['Upload_Date'] ?? null), 'link' => $p['Link'] ?? null, 'raw_payload' => $encodePayload($p), 'imported_at' => now(), 'updated_at' => now()];
}));
Route::put('/api/unboxing/{sourceId}', $genericUpdate('unboxing', function (array $p) use ($encodePayload, $nullableDate) {
    return ['nama' => $p['Nama'] ?? null, 'editor' => $p['Editor'] ?? null, 'status' => $p['Status'] ?? null, 'upload_date' => $nullableDate($p['Upload_Date'] ?? null), 'link' => $p['Link'] ?? null, 'raw_payload' => $encodePayload($p), 'updated_at' => now()];
}));
Route::delete('/api/unboxing/{sourceId}', $genericDelete('unboxing'));

Route::get('/api/story-schedules', function () use ($fromDb) {
    return response()->json(['data' => DB::table('story_schedules')->orderBy('tanggal')->get()->map(fn ($r) => $fromDb($r, [
        'Tanggal' => $r->tanggal,
        'Jam'     => $r->jam,
        'Story'   => $r->story,
        'Catatan' => $r->catatan,
        'Link'    => $r->link,
        'is_genap'=> $r->is_genap,
        'Status'  => $r->status,
    ]))]);
});
Route::post('/api/story-schedules', $genericUpsert('story_schedules', function (array $p) use ($encodePayload, $makeSourceId, $nullableDate) {
    return ['source_id' => $makeSourceId('STR', $p['ID'] ?? null), 'tanggal' => $nullableDate($p['Tanggal'] ?? null), 'jam' => $p['Jam'] ?? null, 'story' => $p['Story'] ?? null, 'catatan' => $p['Catatan'] ?? null, 'link' => $p['Link'] ?? null, 'is_genap' => $p['is_genap'] ?? null, 'status' => $p['Status'] ?? null, 'raw_payload' => $encodePayload($p), 'imported_at' => now(), 'updated_at' => now()];
}));
Route::put('/api/story-schedules/{sourceId}', $genericUpdate('story_schedules', function (array $p) use ($encodePayload, $nullableDate) {
    return ['tanggal' => $nullableDate($p['Tanggal'] ?? null), 'jam' => $p['Jam'] ?? null, 'story' => $p['Story'] ?? null, 'catatan' => $p['Catatan'] ?? null, 'link' => $p['Link'] ?? null, 'is_genap' => $p['is_genap'] ?? null, 'status' => $p['Status'] ?? null, 'raw_payload' => $encodePayload($p), 'updated_at' => now()];
}));
Route::delete('/api/story-schedules/{sourceId}', $genericDelete('story_schedules'));

Route::get('/api/calendar-events', function () use ($fromDb) {
    return response()->json(['data' => DB::table('calendar_events')->orderBy('tanggal')->get()->map(fn ($r) => $fromDb($r, [
        'Nama_Event' => $r->nama_event,
        'Tanggal'    => $r->tanggal,
        'Warna'      => $r->warna,
    ]))]);
});
Route::post('/api/calendar-events', $genericUpsert('calendar_events', function (array $p) use ($encodePayload, $makeSourceId, $nullableDate) {
    return ['source_id' => $makeSourceId('CAL', $p['ID'] ?? null), 'nama_event' => $p['Nama_Event'] ?? null, 'tanggal' => $nullableDate($p['Tanggal'] ?? null), 'warna' => $p['Warna'] ?? null, 'raw_payload' => $encodePayload($p), 'imported_at' => now(), 'updated_at' => now()];
}));
Route::put('/api/calendar-events/{sourceId}', $genericUpdate('calendar_events', function (array $p) use ($encodePayload, $nullableDate) {
    return ['nama_event' => $p['Nama_Event'] ?? null, 'tanggal' => $nullableDate($p['Tanggal'] ?? null), 'warna' => $p['Warna'] ?? null, 'raw_payload' => $encodePayload($p), 'updated_at' => now()];
}));
Route::delete('/api/calendar-events/{sourceId}', $genericDelete('calendar_events'));

Route::get('/api/ideation', function () use ($fromDb) {
    return response()->json(['data' => DB::table('ideation')->orderByDesc('created_at')->get()->map(fn ($r) => $fromDb($r, [
        'Judul'     => $r->judul,
        'Kategori'  => $r->kategori,
        'Platform'  => $r->platform,
        'Deskripsi' => $r->deskripsi,
        'Status'    => $r->status,
    ]))]);
});
Route::post('/api/ideation', $genericUpsert('ideation', function (array $p) use ($encodePayload, $makeSourceId) {
    return ['source_id' => $makeSourceId('IDE', $p['ID'] ?? null), 'judul' => $p['Judul'] ?? null, 'kategori' => $p['Kategori'] ?? null, 'platform' => $p['Platform'] ?? null, 'deskripsi' => $p['Deskripsi'] ?? null, 'status' => $p['Status'] ?? null, 'raw_payload' => $encodePayload($p), 'imported_at' => now(), 'updated_at' => now()];
}));
Route::put('/api/ideation/{sourceId}', $genericUpdate('ideation', function (array $p) use ($encodePayload) {
    return ['judul' => $p['Judul'] ?? null, 'kategori' => $p['Kategori'] ?? null, 'platform' => $p['Platform'] ?? null, 'deskripsi' => $p['Deskripsi'] ?? null, 'status' => $p['Status'] ?? null, 'raw_payload' => $encodePayload($p), 'updated_at' => now()];
}));
Route::delete('/api/ideation/{sourceId}', $genericDelete('ideation'));

// Marketing tables

Route::get('/api/program-promo', function () use ($fromDb) {
    return response()->json(['data' => DB::table('program_promo')->orderByDesc('created_at')->get()->map(fn ($r) => $fromDb($r, [
        'Kategori' => $r->kategori,
        'Program'  => $r->program,
        'Warna'    => $r->warna,
        'Harga'    => $r->harga,
        'Periode'  => $r->periode,
        'Rules'    => $r->rules,
        'Benefit'  => $r->benefit,
    ]))]);
});
Route::post('/api/program-promo', $genericUpsert('program_promo', function (array $p) use ($encodePayload, $makeSourceId) {
    return ['source_id' => $makeSourceId('PRO', $p['ID'] ?? null), 'kategori' => $p['Kategori'] ?? null, 'program' => $p['Program'] ?? null, 'warna' => $p['Warna'] ?? null, 'harga' => (int) ($p['Harga'] ?? 0), 'periode' => $p['Periode'] ?? null, 'rules' => $p['Rules'] ?? null, 'benefit' => $p['Benefit'] ?? null, 'raw_payload' => $encodePayload($p), 'imported_at' => now(), 'updated_at' => now()];
}));
Route::put('/api/program-promo/{sourceId}', $genericUpdate('program_promo', function (array $p) use ($encodePayload) {
    return ['kategori' => $p['Kategori'] ?? null, 'program' => $p['Program'] ?? null, 'warna' => $p['Warna'] ?? null, 'harga' => (int) ($p['Harga'] ?? 0), 'periode' => $p['Periode'] ?? null, 'rules' => $p['Rules'] ?? null, 'benefit' => $p['Benefit'] ?? null, 'raw_payload' => $encodePayload($p), 'updated_at' => now()];
}));
Route::delete('/api/program-promo/{sourceId}', $genericDelete('program_promo'));

Route::get('/api/sell-out-targets', function () use ($fromDb) {
    return response()->json(['data' => DB::table('sell_out_targets')->orderByDesc('periode_start')->get()->map(fn ($r) => $fromDb($r, [
        'Vendor'          => $r->vendor,
        'Kategori'        => $r->kategori,
        'Brand'           => $r->brand,
        'Seri'            => $r->seri,
        'Nama_Produk'     => $r->nama_produk,
        'Target_Unit'     => $r->target_unit,
        'Bonus_Nominal'   => $r->bonus_nominal,
        'Realisasi_Unit'  => $r->realisasi_unit,
        'Periode_Start'   => $r->periode_start,
        'Periode_End'     => $r->periode_end,
        'Catatan'         => $r->catatan,
    ]))]);
});
Route::post('/api/sell-out-targets', $genericUpsert('sell_out_targets', function (array $p) use ($encodePayload, $makeSourceId, $nullableDate) {
    return ['source_id' => $makeSourceId('SOT', $p['ID'] ?? null), 'vendor' => $p['Vendor'] ?? null, 'kategori' => $p['Kategori'] ?? null, 'brand' => $p['Brand'] ?? null, 'seri' => $p['Seri'] ?? null, 'nama_produk' => $p['Nama_Produk'] ?? null, 'target_unit' => (int) ($p['Target_Unit'] ?? 0), 'bonus_nominal' => (int) ($p['Bonus_Nominal'] ?? 0), 'realisasi_unit' => (int) ($p['Realisasi_Unit'] ?? 0), 'periode_start' => $nullableDate($p['Periode_Start'] ?? null), 'periode_end' => $nullableDate($p['Periode_End'] ?? null), 'catatan' => $p['Catatan'] ?? null, 'raw_payload' => $encodePayload($p), 'imported_at' => now(), 'updated_at' => now()];
}));
Route::put('/api/sell-out-targets/{sourceId}', $genericUpdate('sell_out_targets', function (array $p) use ($encodePayload, $nullableDate) {
    return ['vendor' => $p['Vendor'] ?? null, 'kategori' => $p['Kategori'] ?? null, 'brand' => $p['Brand'] ?? null, 'seri' => $p['Seri'] ?? null, 'nama_produk' => $p['Nama_Produk'] ?? null, 'target_unit' => (int) ($p['Target_Unit'] ?? 0), 'bonus_nominal' => (int) ($p['Bonus_Nominal'] ?? 0), 'realisasi_unit' => (int) ($p['Realisasi_Unit'] ?? 0), 'periode_start' => $nullableDate($p['Periode_Start'] ?? null), 'periode_end' => $nullableDate($p['Periode_End'] ?? null), 'catatan' => $p['Catatan'] ?? null, 'raw_payload' => $encodePayload($p), 'updated_at' => now()];
}));
Route::delete('/api/sell-out-targets/{sourceId}', $genericDelete('sell_out_targets'));

Route::get('/api/ads-performance', function () use ($fromDb) {
    return response()->json(['data' => DB::table('ads_performance')->orderByDesc('tanggal')->get()->map(fn ($r) => $fromDb($r, [
        'Nama'      => $r->nama,
        'ID_Ads'    => $r->id_ads,
        'Tanggal'   => $r->tanggal,
        'Biaya'     => $r->biaya,
        'Sisa_Saldo'=> $r->sisa_saldo,
        'Kategori'  => $r->kategori,
        'Platform'  => $r->platform,
        'Jangkauan' => $r->jangkauan,
        'Suka'      => $r->suka,
        'Komentar'  => $r->komentar,
        'Share'     => $r->share,
    ]))]);
});
Route::post('/api/ads-performance', $genericUpsert('ads_performance', function (array $p) use ($encodePayload, $makeSourceId, $nullableDate) {
    return ['source_id' => $makeSourceId('ADS', $p['ID'] ?? null), 'nama' => $p['Nama'] ?? null, 'id_ads' => $p['ID_Ads'] ?? null, 'tanggal' => $nullableDate($p['Tanggal'] ?? null), 'biaya' => (int) ($p['Biaya'] ?? 0), 'sisa_saldo' => isset($p['Sisa_Saldo']) ? (int) $p['Sisa_Saldo'] : null, 'kategori' => $p['Kategori'] ?? null, 'platform' => $p['Platform'] ?? null, 'jangkauan' => (int) ($p['Jangkauan'] ?? 0), 'suka' => (int) ($p['Suka'] ?? 0), 'komentar' => (int) ($p['Komentar'] ?? 0), 'share' => (int) ($p['Share'] ?? 0), 'raw_payload' => $encodePayload($p), 'imported_at' => now(), 'updated_at' => now()];
}));
Route::put('/api/ads-performance/{sourceId}', $genericUpdate('ads_performance', function (array $p) use ($encodePayload, $nullableDate) {
    return ['nama' => $p['Nama'] ?? null, 'id_ads' => $p['ID_Ads'] ?? null, 'tanggal' => $nullableDate($p['Tanggal'] ?? null), 'biaya' => (int) ($p['Biaya'] ?? 0), 'sisa_saldo' => isset($p['Sisa_Saldo']) ? (int) $p['Sisa_Saldo'] : null, 'kategori' => $p['Kategori'] ?? null, 'platform' => $p['Platform'] ?? null, 'jangkauan' => (int) ($p['Jangkauan'] ?? 0), 'suka' => (int) ($p['Suka'] ?? 0), 'komentar' => (int) ($p['Komentar'] ?? 0), 'share' => (int) ($p['Share'] ?? 0), 'raw_payload' => $encodePayload($p), 'updated_at' => now()];
}));
Route::delete('/api/ads-performance/{sourceId}', $genericDelete('ads_performance'));

Route::get('/api/harga-kompetitor', function () use ($fromDb) {
    return response()->json(['data' => DB::table('harga_kompetitor')->orderByDesc('tanggal_cek')->get()->map(fn ($r) => $fromDb($r, [
        'Nama_Produk'        => $r->nama_produk,
        'Harga_Distributor_1'=> $r->harga_distributor_1,
        'Harga_Distributor_2'=> $r->harga_distributor_2,
        'Harga_Kompetitor'   => $r->harga_kompetitor,
        'Margin_Profit'      => $r->margin_profit,
        'Harga_Rencana_Jual' => $r->harga_rencana_jual,
        'Tanggal_Cek'        => $r->tanggal_cek,
        'Catatan'            => $r->catatan,
    ]))]);
});
Route::post('/api/harga-kompetitor', $genericUpsert('harga_kompetitor', function (array $p) use ($encodePayload, $makeSourceId, $nullableDate) {
    return ['source_id' => $makeSourceId('HK', $p['ID'] ?? null), 'nama_produk' => $p['Nama_Produk'] ?? null, 'harga_distributor_1' => (int) ($p['Harga_Distributor_1'] ?? 0), 'harga_distributor_2' => (int) ($p['Harga_Distributor_2'] ?? 0), 'harga_kompetitor' => (int) ($p['Harga_Kompetitor'] ?? 0), 'margin_profit' => (int) ($p['Margin_Profit'] ?? 0), 'harga_rencana_jual' => (int) ($p['Harga_Rencana_Jual'] ?? 0), 'tanggal_cek' => $nullableDate($p['Tanggal_Cek'] ?? null), 'catatan' => $p['Catatan'] ?? null, 'raw_payload' => $encodePayload($p), 'imported_at' => now(), 'updated_at' => now()];
}));
Route::put('/api/harga-kompetitor/{sourceId}', $genericUpdate('harga_kompetitor', function (array $p) use ($encodePayload, $nullableDate) {
    return ['nama_produk' => $p['Nama_Produk'] ?? null, 'harga_distributor_1' => (int) ($p['Harga_Distributor_1'] ?? 0), 'harga_distributor_2' => (int) ($p['Harga_Distributor_2'] ?? 0), 'harga_kompetitor' => (int) ($p['Harga_Kompetitor'] ?? 0), 'margin_profit' => (int) ($p['Margin_Profit'] ?? 0), 'harga_rencana_jual' => (int) ($p['Harga_Rencana_Jual'] ?? 0), 'tanggal_cek' => $nullableDate($p['Tanggal_Cek'] ?? null), 'catatan' => $p['Catatan'] ?? null, 'raw_payload' => $encodePayload($p), 'updated_at' => now()];
}));
Route::delete('/api/harga-kompetitor/{sourceId}', $genericDelete('harga_kompetitor'));

// Customer service tables

Route::get('/api/orderan-online', function () use ($fromDb) {
    return response()->json(['data' => DB::table('orderan_online')->orderByDesc('tanggal')->get()->map(function ($r) use ($fromDb) {
        $p = json_decode($r->raw_payload ?? '{}', true) ?: [];
        return $fromDb($r, [
            'TANGGAL'       => $r->tanggal,
            'ECOMMERCE'     => $r->ecommerce,
            'HANDLE'        => $r->handle,
            'NAMA'          => $r->nama,
            'TYPE UNIT'     => $r->type_unit,
            'TYPE_UNIT'     => $r->type_unit,
            'HARGA ONLINE'  => $r->harga_online,
            'HARGA_ONLINE'  => $r->harga_online,
            'NOMINAL CAIR'  => $r->nominal_cair,
            'NOMINAL_CAIR'  => $r->nominal_cair,
            'STATUS'        => $r->status,
            'NO_PESANAN'    => $p['NO PESANAN'] ?? null,
            'NO_RESI'       => $p['NO RESI'] ?? null,
            'IMEI_SN'       => $p['IMEI/SN'] ?? null,
            'NO_NOTA'       => $p['NO NOTA'] ?? null,
        ]);
    })]);
});
Route::post('/api/orderan-online', $genericUpsert('orderan_online', function (array $p) use ($encodePayload, $makeSourceId, $nullableDate) {
    return ['source_id' => $makeSourceId('OO', $p['ID'] ?? null), 'tanggal' => $nullableDate($p['TANGGAL'] ?? $p['Tanggal'] ?? null), 'ecommerce' => $p['ECOMMERCE'] ?? $p['Ecommerce'] ?? null, 'handle' => $p['HANDLE'] ?? $p['Handle'] ?? null, 'nama' => $p['NAMA'] ?? $p['Nama'] ?? null, 'type_unit' => $p['TYPE UNIT'] ?? $p['Type_Unit'] ?? null, 'harga_online' => (int) ($p['HARGA ONLINE'] ?? $p['Harga_Online'] ?? 0), 'nominal_cair' => isset($p['NOMINAL CAIR']) ? (int) $p['NOMINAL CAIR'] : null, 'status' => $p['STATUS'] ?? $p['Status'] ?? null, 'raw_payload' => $encodePayload($p), 'imported_at' => now(), 'updated_at' => now()];
}));
Route::put('/api/orderan-online/{sourceId}', $genericUpdate('orderan_online', function (array $p) use ($encodePayload, $nullableDate) {
    return ['tanggal' => $nullableDate($p['TANGGAL'] ?? $p['Tanggal'] ?? null), 'ecommerce' => $p['ECOMMERCE'] ?? $p['Ecommerce'] ?? null, 'handle' => $p['HANDLE'] ?? $p['Handle'] ?? null, 'nama' => $p['NAMA'] ?? $p['Nama'] ?? null, 'type_unit' => $p['TYPE UNIT'] ?? $p['Type_Unit'] ?? null, 'harga_online' => (int) ($p['HARGA ONLINE'] ?? $p['Harga_Online'] ?? 0), 'nominal_cair' => isset($p['NOMINAL CAIR']) ? (int) $p['NOMINAL CAIR'] : null, 'status' => $p['STATUS'] ?? $p['Status'] ?? null, 'raw_payload' => $encodePayload($p), 'updated_at' => now()];
}));
Route::delete('/api/orderan-online/{sourceId}', $genericDelete('orderan_online'));

Route::get('/api/unit-ditanya', function () use ($fromDb) {
    return response()->json(['data' => DB::table('unit_ditanya')->orderByDesc('tanggal')->get()->map(fn ($r) => $fromDb($r, [
        'TANGGAL'  => $r->tanggal,
        'KATEGORI' => $r->kategori,
        'BRAND'    => $r->brand,
        'SERI'     => $r->seri,
        'KONDISI'  => $r->kondisi,
        'TIPE'     => $r->tipe,
        'DITANYA'  => $r->ditanya,
        'AVAILABLE'=> $r->available,
    ]))]);
});
Route::post('/api/unit-ditanya', $genericUpsert('unit_ditanya', function (array $p) use ($encodePayload, $makeSourceId, $nullableDate) {
    return ['source_id' => $makeSourceId('UD', $p['ID'] ?? null), 'tanggal' => $nullableDate($p['TANGGAL'] ?? $p['Tanggal'] ?? null), 'kategori' => $p['KATEGORI'] ?? $p['Kategori'] ?? null, 'brand' => $p['BRAND'] ?? $p['Brand'] ?? null, 'seri' => (string) ($p['SERI'] ?? $p['Seri'] ?? ''), 'kondisi' => $p['KONDISI'] ?? $p['Kondisi'] ?? null, 'tipe' => $p['TIPE'] ?? $p['Tipe'] ?? null, 'ditanya' => (int) ($p['DITANYA'] ?? $p['Ditanya'] ?? 0), 'available' => $p['AVAILABLE'] ?? $p['Available'] ?? null, 'raw_payload' => $encodePayload($p), 'imported_at' => now(), 'updated_at' => now()];
}));
Route::put('/api/unit-ditanya/{sourceId}', $genericUpdate('unit_ditanya', function (array $p) use ($encodePayload, $nullableDate) {
    return ['tanggal' => $nullableDate($p['TANGGAL'] ?? $p['Tanggal'] ?? null), 'kategori' => $p['KATEGORI'] ?? $p['Kategori'] ?? null, 'brand' => $p['BRAND'] ?? $p['Brand'] ?? null, 'seri' => (string) ($p['SERI'] ?? $p['Seri'] ?? ''), 'kondisi' => $p['KONDISI'] ?? $p['Kondisi'] ?? null, 'tipe' => $p['TIPE'] ?? $p['Tipe'] ?? null, 'ditanya' => (int) ($p['DITANYA'] ?? $p['Ditanya'] ?? 0), 'available' => $p['AVAILABLE'] ?? $p['Available'] ?? null, 'raw_payload' => $encodePayload($p), 'updated_at' => now()];
}));
Route::delete('/api/unit-ditanya/{sourceId}', $genericDelete('unit_ditanya'));

Route::get('/api/claim-garansi', function () use ($fromDb) {
    return response()->json(['data' => DB::table('claim_garansi')->orderByDesc('tanggal_masuk')->get()->map(fn ($r) => $fromDb($r, [
        'NAMA_CUSTOMER'    => $r->nama_customer,
        'NO_SERVICE'       => $r->no_service,
        'NO_TRANSAKSI'     => $r->no_transaksi,
        'TANGGAL_MASUK'    => $r->tanggal_masuk,
        'WA_CUSTOMER'      => $r->wa_customer,
        'TIPE'             => $r->tipe,
        'SERI'             => $r->seri,
        'MODEL'            => $r->model,
        'STATUS'           => $r->status,
        'LOKASI_KLAIM'     => $r->lokasi_klaim,
        'TANGGAL_ESTIMASI' => $r->tanggal_estimasi,
        'TANGGAL_DIAMBIL'  => $r->tanggal_diambil,
        'GARANSI'          => $r->garansi,
        'KERUSAKAN'        => $r->kerusakan,
        'KETERANGAN'       => $r->keterangan,
    ]))]);
});
Route::post('/api/claim-garansi', $genericUpsert('claim_garansi', function (array $p) use ($encodePayload, $makeSourceId, $nullableDate) {
    return ['source_id' => $makeSourceId('CG', $p['ID'] ?? null), 'nama_customer' => $p['NAMA_CUSTOMER'] ?? null, 'no_service' => $p['NO_SERVICE'] ?? null, 'no_transaksi' => $p['NO_TRANSAKSI'] ?? null, 'tanggal_masuk' => $nullableDate($p['TANGGAL_MASUK'] ?? null), 'wa_customer' => (string) ($p['WA_CUSTOMER'] ?? ''), 'tipe' => $p['TIPE'] ?? null, 'seri' => $p['SERI'] ?? null, 'model' => $p['MODEL'] ?? null, 'status' => $p['STATUS'] ?? null, 'lokasi_klaim' => $p['LOKASI_KLAIM'] ?? null, 'tanggal_estimasi' => $nullableDate($p['TANGGAL_ESTIMASI'] ?? null), 'tanggal_diambil' => $nullableDate($p['TANGGAL_DIAMBIL'] ?? null), 'garansi' => $p['GARANSI'] ?? null, 'kerusakan' => $p['KERUSAKAN'] ?? null, 'keterangan' => $p['KETERANGAN'] ?? null, 'raw_payload' => $encodePayload($p), 'imported_at' => now(), 'updated_at' => now()];
}));
Route::put('/api/claim-garansi/{sourceId}', $genericUpdate('claim_garansi', function (array $p) use ($encodePayload, $nullableDate) {
    return ['nama_customer' => $p['NAMA_CUSTOMER'] ?? null, 'no_service' => $p['NO_SERVICE'] ?? null, 'no_transaksi' => $p['NO_TRANSAKSI'] ?? null, 'tanggal_masuk' => $nullableDate($p['TANGGAL_MASUK'] ?? null), 'wa_customer' => (string) ($p['WA_CUSTOMER'] ?? ''), 'tipe' => $p['TIPE'] ?? null, 'seri' => $p['SERI'] ?? null, 'model' => $p['MODEL'] ?? null, 'status' => $p['STATUS'] ?? null, 'lokasi_klaim' => $p['LOKASI_KLAIM'] ?? null, 'tanggal_estimasi' => $nullableDate($p['TANGGAL_ESTIMASI'] ?? null), 'tanggal_diambil' => $nullableDate($p['TANGGAL_DIAMBIL'] ?? null), 'garansi' => $p['GARANSI'] ?? null, 'kerusakan' => $p['KERUSAKAN'] ?? null, 'keterangan' => $p['KETERANGAN'] ?? null, 'raw_payload' => $encodePayload($p), 'updated_at' => now()];
}));
Route::delete('/api/claim-garansi/{sourceId}', $genericDelete('claim_garansi'));

Route::get('/api/keep-barang', function () use ($fromDb) {
    return response()->json(['data' => DB::table('keep_barang')->orderByDesc('tanggal_keep')->get()->map(fn ($r) => $fromDb($r, [
        'TANGGAL_KEEP'         => $r->tanggal_keep,
        'NAMA'                 => $r->nama,
        'NOMOR_HP'             => $r->nomor_hp,
        'TYPE_HP'              => $r->type_hp,
        'DP_UANG_MUKA'         => $r->dp_uang_muka,
        'HARGA_JUAL'           => $r->harga_jual,
        'RENCANA_PENGAMBILAN'  => $r->rencana_pengambilan,
        'HANDLE_BY'            => $r->handle_by,
        'STATUS'               => $r->status,
        'TANGGAL_EXPIRED'      => $r->tanggal_expired,
    ]))]);
});
Route::post('/api/keep-barang', $genericUpsert('keep_barang', function (array $p) use ($encodePayload, $makeSourceId, $nullableDate) {
    return ['source_id' => $makeSourceId('KB', $p['ID'] ?? null), 'tanggal_keep' => $nullableDate($p['TANGGAL_KEEP'] ?? null), 'nama' => $p['NAMA'] ?? null, 'nomor_hp' => (string) ($p['NOMOR_HP'] ?? ''), 'type_hp' => $p['TYPE_HP'] ?? null, 'dp_uang_muka' => (int) ($p['DP_UANG_MUKA'] ?? 0), 'harga_jual' => (int) ($p['HARGA_JUAL'] ?? 0), 'rencana_pengambilan' => $nullableDate($p['RENCANA_PENGAMBILAN'] ?? null), 'handle_by' => $p['HANDLE_BY'] ?? null, 'status' => $p['STATUS'] ?? null, 'tanggal_expired' => $nullableDate($p['TANGGAL_EXPIRED'] ?? null), 'raw_payload' => $encodePayload($p), 'imported_at' => now(), 'updated_at' => now()];
}));
Route::put('/api/keep-barang/{sourceId}', $genericUpdate('keep_barang', function (array $p) use ($encodePayload, $nullableDate) {
    return ['tanggal_keep' => $nullableDate($p['TANGGAL_KEEP'] ?? null), 'nama' => $p['NAMA'] ?? null, 'nomor_hp' => (string) ($p['NOMOR_HP'] ?? ''), 'type_hp' => $p['TYPE_HP'] ?? null, 'dp_uang_muka' => (int) ($p['DP_UANG_MUKA'] ?? 0), 'harga_jual' => (int) ($p['HARGA_JUAL'] ?? 0), 'rencana_pengambilan' => $nullableDate($p['RENCANA_PENGAMBILAN'] ?? null), 'handle_by' => $p['HANDLE_BY'] ?? null, 'status' => $p['STATUS'] ?? null, 'tanggal_expired' => $nullableDate($p['TANGGAL_EXPIRED'] ?? null), 'raw_payload' => $encodePayload($p), 'updated_at' => now()];
}));
Route::delete('/api/keep-barang/{sourceId}', $genericDelete('keep_barang'));

// Event / LPJK tables

Route::get('/api/lpjk', function () use ($fromDb) {
    return response()->json(['data' => DB::table('lpjk')->orderByDesc('tanggal')->get()->map(fn ($r) => $fromDb($r, [
        'Nama_Event'      => $r->nama_event,
        'Tanggal'         => $r->tanggal,
        'Budget_Rencana'  => $r->budget_rencana,
        'Realisasi_Biaya' => $r->realisasi_biaya,
        'Status'          => $r->status,
        'Keterangan'      => $r->keterangan,
    ]))]);
});
Route::post('/api/lpjk', function () use ($actorUserId, $logCrudActivity, $makeSourceId, $encodePayload, $nullableDate) {
    $p = request()->all();
    $row = ['source_id' => $makeSourceId('LPJK', $p['ID'] ?? null), 'nama_event' => $p['Nama_Event'] ?? null, 'tanggal' => $nullableDate($p['Tanggal'] ?? null), 'budget_rencana' => (int) ($p['Budget_Rencana'] ?? 0), 'realisasi_biaya' => (int) ($p['Realisasi_Biaya'] ?? 0), 'status' => $p['Status'] ?? null, 'keterangan' => $p['Keterangan'] ?? null, 'raw_payload' => $encodePayload($p), 'imported_at' => now(), 'created_at' => now(), 'updated_at' => now(), 'created_by_user_id' => $actorUserId(), 'updated_by_user_id' => $actorUserId()];
    DB::table('lpjk')->insert($row);
    $stored = DB::table('lpjk')->where('source_id', $row['source_id'])->first();
    $logCrudActivity('lpjk', 'create', $stored->source_id, (int) $stored->id, null, (array) $stored);
    return response()->json(['status' => 'success', 'data' => $stored], 201);
});
Route::put('/api/lpjk/{sourceId}', function (string $sourceId) use ($actorUserId, $encodePayload, $logCrudActivity, $nullableDate) {
    abort_unless(DB::table('lpjk')->where('source_id', $sourceId)->exists(), 404);
    $before = DB::table('lpjk')->where('source_id', $sourceId)->first();
    $p = request()->all();
    DB::table('lpjk')->where('source_id', $sourceId)->update(['nama_event' => $p['Nama_Event'] ?? null, 'tanggal' => $nullableDate($p['Tanggal'] ?? null), 'budget_rencana' => (int) ($p['Budget_Rencana'] ?? 0), 'realisasi_biaya' => (int) ($p['Realisasi_Biaya'] ?? 0), 'status' => $p['Status'] ?? null, 'keterangan' => $p['Keterangan'] ?? null, 'raw_payload' => $encodePayload($p), 'updated_at' => now(), 'updated_by_user_id' => $actorUserId()]);
    $stored = DB::table('lpjk')->where('source_id', $sourceId)->first();
    $logCrudActivity('lpjk', 'update', $stored->source_id, (int) $stored->id, $before ? (array) $before : null, (array) $stored);
    return response()->json(['status' => 'success', 'data' => $stored]);
});
Route::delete('/api/lpjk/{sourceId}', function (string $sourceId) use ($logCrudActivity) {
    abort_unless(DB::table('lpjk')->where('source_id', $sourceId)->exists(), 404);
    $stored = DB::table('lpjk')->where('source_id', $sourceId)->first();
    DB::table('lpjk_detail')->where(function ($query) use ($sourceId) {
        $query->where('master_id', $sourceId)->orWhere('lpjk_id', function ($query) use ($sourceId) {
            $query->select('id')->from('lpjk')->where('source_id', $sourceId)->limit(1);
        });
    })->delete();
    DB::table('lpjk')->where('source_id', $sourceId)->delete();
    if ($stored !== null) {
        $logCrudActivity('lpjk', 'delete', $stored->source_id, (int) $stored->id, (array) $stored, null);
    }
    return response()->json(['status' => 'success']);
});

Route::get('/api/lpjk-detail', function () use ($fromDb) {
    $masterId = request()->query('master_id');
    $q = DB::table('lpjk_detail')
        ->leftJoin('lpjk', 'lpjk.id', '=', 'lpjk_detail.lpjk_id');
    if ($masterId) {
        $q->where(function ($query) use ($masterId) {
            $query->where('lpjk_detail.master_id', $masterId)
                ->orWhere('lpjk.source_id', $masterId);
        });
    }
    return response()->json(['data' => $q->orderBy('lpjk_detail.id')->get(['lpjk_detail.*', 'lpjk.source_id as lpjk_source_id'])->map(fn ($r) => $fromDb($r, [
        'Master_ID'        => $r->master_id ?: $r->lpjk_source_id,
        'Kategori'         => $r->kategori,
        'Nama_Pengeluaran' => $r->nama_pengeluaran,
        'Satuan'           => $r->satuan,
        'Jumlah'           => $r->jumlah,
        'Total'            => $r->total,
        'Bukti'            => $r->bukti,
    ]))]);
});
Route::post('/api/lpjk-detail', function () use ($actorUserId, $logCrudActivity, $requireLpjkIdBySourceId, $makeSourceId, $encodePayload) {
    $p = request()->all();
    $masterId = trim((string) ($p['Master_ID'] ?? ''));
    abort_if($masterId === '', 422, 'Master_ID wajib diisi.');
    $row = ['source_id' => $makeSourceId('LPJKD', $p['ID'] ?? null), 'master_id' => $masterId, 'lpjk_id' => $requireLpjkIdBySourceId($masterId), 'kategori' => $p['Kategori'] ?? null, 'nama_pengeluaran' => $p['Nama_Pengeluaran'] ?? null, 'satuan' => $p['Satuan'] ?? null, 'jumlah' => (int) ($p['Jumlah'] ?? 1), 'total' => (int) ($p['Total'] ?? 0), 'bukti' => $p['Bukti'] ?? null, 'raw_payload' => $encodePayload($p), 'imported_at' => now(), 'created_at' => now(), 'updated_at' => now(), 'created_by_user_id' => $actorUserId(), 'updated_by_user_id' => $actorUserId()];
    DB::table('lpjk_detail')->insert($row);
    $stored = DB::table('lpjk_detail')->where('source_id', $row['source_id'])->first();
    $logCrudActivity('lpjk_detail', 'create', $stored->source_id, (int) $stored->id, null, (array) $stored);
    return response()->json(['status' => 'success', 'data' => $stored], 201);
});
Route::put('/api/lpjk-detail/{sourceId}', function (string $sourceId) use ($actorUserId, $encodePayload, $logCrudActivity, $requireLpjkIdBySourceId) {
    abort_unless(DB::table('lpjk_detail')->where('source_id', $sourceId)->exists(), 404);
    $before = DB::table('lpjk_detail')->where('source_id', $sourceId)->first();
    $p = request()->all();
    $masterId = trim((string) ($p['Master_ID'] ?? ''));
    abort_if($masterId === '', 422, 'Master_ID wajib diisi.');
    DB::table('lpjk_detail')->where('source_id', $sourceId)->update(['master_id' => $masterId, 'lpjk_id' => $requireLpjkIdBySourceId($masterId), 'kategori' => $p['Kategori'] ?? null, 'nama_pengeluaran' => $p['Nama_Pengeluaran'] ?? null, 'satuan' => $p['Satuan'] ?? null, 'jumlah' => (int) ($p['Jumlah'] ?? 1), 'total' => (int) ($p['Total'] ?? 0), 'bukti' => $p['Bukti'] ?? null, 'raw_payload' => $encodePayload($p), 'updated_at' => now(), 'updated_by_user_id' => $actorUserId()]);
    $stored = DB::table('lpjk_detail')->where('source_id', $sourceId)->first();
    $logCrudActivity('lpjk_detail', 'update', $stored->source_id, (int) $stored->id, $before ? (array) $before : null, (array) $stored);
    return response()->json(['status' => 'success', 'data' => $stored]);
});
Route::delete('/api/lpjk-detail/{sourceId}', function (string $sourceId) use ($logCrudActivity) {
    abort_unless(DB::table('lpjk_detail')->where('source_id', $sourceId)->exists(), 404);
    $stored = DB::table('lpjk_detail')->where('source_id', $sourceId)->first();
    DB::table('lpjk_detail')->where('source_id', $sourceId)->delete();
    if ($stored !== null) {
        $logCrudActivity('lpjk_detail', 'delete', $stored->source_id, (int) $stored->id, (array) $stored, null);
    }
    return response()->json(['status' => 'success']);
});

// Asset Vendor Inventory

Route::get('/api/asset-vendor-inventory', $genericList('asset_vendor_inventory', function ($r) use ($fromDb) {
    return $fromDb($r, [
        'Vendor'        => $r->vendor,
        'Brand'         => $r->brand,
        'Seri'          => $r->seri,
        'IMEI'          => $r->imei,
        'Quantity'      => $r->quantity,
        'Condition'     => $r->condition,
        'Purchase_Date' => $r->purchase_date,
        'Notes'         => $r->notes,
    ]);
}));
Route::post('/api/asset-vendor-inventory', $genericUpsert('asset_vendor_inventory', function (array $p) use ($encodePayload, $makeSourceId, $nullableDate) {
    return [
        'source_id'     => $makeSourceId('AVI', $p['ID'] ?? null),
        'vendor'        => $p['Vendor'] ?? null,
        'brand'         => $p['Brand'] ?? null,
        'seri'          => $p['Seri'] ?? null,
        'imei'          => $p['IMEI'] ?? null,
        'quantity'      => (int) ($p['Quantity'] ?? 1),
        'condition'     => $p['Condition'] ?? null,
        'purchase_date' => $nullableDate($p['Purchase_Date'] ?? null),
        'notes'         => $p['Notes'] ?? null,
        'raw_payload'   => $encodePayload($p),
        'imported_at'   => now(),
        'updated_at'    => now(),
    ];
}));
Route::put('/api/asset-vendor-inventory/{sourceId}', $genericUpdate('asset_vendor_inventory', function (array $p) use ($encodePayload, $nullableDate) {
    return [
        'vendor'        => $p['Vendor'] ?? null,
        'brand'         => $p['Brand'] ?? null,
        'seri'          => $p['Seri'] ?? null,
        'imei'          => $p['IMEI'] ?? null,
        'quantity'      => (int) ($p['Quantity'] ?? 1),
        'condition'     => $p['Condition'] ?? null,
        'purchase_date' => $nullableDate($p['Purchase_Date'] ?? null),
        'notes'         => $p['Notes'] ?? null,
        'raw_payload'   => $encodePayload($p),
        'updated_at'    => now(),
    ];
}));
Route::delete('/api/asset-vendor-inventory/{sourceId}', $genericDelete('asset_vendor_inventory'));

Route::get('/api/bonus-config', function () {
    $row = DB::table('marketing_settings')->where('key', 'BONUS_CONFIG')->first(['values']);
    $data = $row ? json_decode($row->values, true) : null;
    return response()->json(['data' => (is_array($data) && !array_is_list($data)) ? $data : null]);
});

Route::put('/api/bonus-config', function () use ($logCrudActivity) {
    $cfg = request()->all();
    $before = DB::table('marketing_settings')->where('key', 'BONUS_CONFIG')->first(['values']);
    $val = json_encode($cfg, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    DB::table('marketing_settings')->updateOrInsert(['key' => 'BONUS_CONFIG'], ['values' => $val, 'imported_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
    $logCrudActivity('marketing_settings', 'update', 'BONUS_CONFIG', null, $before ? json_decode($before->values, true) : null, $cfg);
    return response()->json(['status' => 'success', 'data' => $cfg]);
});

Route::get('/api/budgeting-config', function () {
    $row = DB::table('marketing_settings')->where('key', 'BUDGET_CONFIG')->first(['values']);
    $data = $row ? json_decode($row->values, true) : null;
    return response()->json(['data' => (is_array($data) && !array_is_list($data)) ? $data : null]);
});

Route::put('/api/budgeting-config', function () use ($logCrudActivity) {
    $cfg = request()->all();
    $before = DB::table('marketing_settings')->where('key', 'BUDGET_CONFIG')->first(['values']);
    $val = json_encode($cfg, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    DB::table('marketing_settings')->updateOrInsert(['key' => 'BUDGET_CONFIG'], ['values' => $val, 'imported_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
    $logCrudActivity('marketing_settings', 'update', 'BUDGET_CONFIG', null, $before ? json_decode($before->values, true) : null, $cfg);
    return response()->json(['status' => 'success', 'data' => $cfg]);
});

Route::get('/api/all-data', function () use ($fromDb, $rowValue) {
    $settings = DB::table('marketing_settings')->orderBy('key')->get(['key', 'values'])
        ->mapWithKeys(fn ($row) => [$row->key => json_decode($row->values, true) ?: []]);

    $bonusConfigRow = DB::table('marketing_settings')->where('key', 'BONUS_CONFIG')->first(['values']);
    $bonusConfig = $bonusConfigRow ? json_decode($bonusConfigRow->values, true) : null;

    $budgetConfigRow = DB::table('marketing_settings')->where('key', 'BUDGET_CONFIG')->first(['values']);
    $budgetingConfig = $budgetConfigRow ? json_decode($budgetConfigRow->values, true) : null;

    $masterPlan = DB::table('master_plans')->orderByDesc('tanggal_rencana')->orderBy('source_id')->get()
        ->map(fn ($r) => ['ID' => $r->source_id, 'Judul' => $r->title, 'Format_Konten' => $r->format_konten, 'Platforms' => $r->platforms, 'Colab' => $r->colab, 'Editor' => $r->editor, 'Talent' => $rowValue($r, 'talent'), 'Skrip' => $r->script, 'Caption' => $r->caption, 'Status' => $r->status, 'Tanggal_Rencana' => $r->tanggal_rencana, 'Distribution_Meta' => $r->distribution_meta, 'Link_Drive' => $r->link_drive]);

    $analytics = DB::table('analytics')
        ->leftJoin('master_plans', 'master_plans.id', '=', 'analytics.master_plan_id')
        ->orderByDesc('tanggal_publish')
        ->orderByRaw("COALESCE(NULLIF(analytics.master_id, ''), master_plans.source_id, '')")
        ->get([
            'analytics.*',
            DB::raw("COALESCE(NULLIF(analytics.master_id, ''), master_plans.source_id) as resolved_master_id"),
        ])
        ->map(fn ($r) => ['ID' => $r->id, 'Master_ID' => $r->resolved_master_id, 'Judul' => $r->title, 'Platform' => $r->platform, 'Tanggal_Publish' => $r->tanggal_publish, 'Views' => $r->views, 'Likes' => $r->likes, 'Comments' => $r->comments, 'Shares' => $r->shares]);

    $distribution = DB::table('distributions')
        ->leftJoin('master_plans', 'master_plans.id', '=', 'distributions.master_plan_id')
        ->orderByDesc('tanggal_publish')
        ->orderByRaw("COALESCE(NULLIF(distributions.master_id, ''), master_plans.source_id, '')")
        ->get([
            'distributions.*',
            DB::raw("COALESCE(NULLIF(distributions.master_id, ''), master_plans.source_id) as resolved_master_id"),
        ])
        ->map(fn ($r) => ['ID' => $r->id, 'Master_ID' => $r->resolved_master_id, 'Judul' => $r->title, 'Platform' => $r->platform, 'Tanggal_Publish' => $r->tanggal_publish, 'Link' => $r->link, 'Type' => $r->type]);

    $unboxing = DB::table('unboxing')->orderByDesc('upload_date')->get()
        ->map(fn ($r) => $fromDb($r, ['Nama' => $r->nama, 'Editor' => $r->editor, 'Status' => $r->status, 'Upload_Date' => $r->upload_date, 'Link' => $r->link]));

    $story = DB::table('story_schedules')->orderBy('tanggal')->get()
        ->map(fn ($r) => $fromDb($r, ['Tanggal' => $r->tanggal, 'Jam' => $r->jam, 'Story' => $r->story, 'Catatan' => $r->catatan, 'Link' => $r->link, 'is_genap' => $r->is_genap, 'Status' => $r->status]));

    $ideation = DB::table('ideation')->orderByDesc('created_at')->get()
        ->map(fn ($r) => $fromDb($r, ['Judul' => $r->judul, 'Kategori' => $r->kategori, 'Platform' => $r->platform, 'Deskripsi' => $r->deskripsi, 'Status' => $r->status]));

    $promo = DB::table('program_promo')->orderByDesc('created_at')->get()
        ->map(fn ($r) => $fromDb($r, ['Kategori' => $r->kategori, 'Program' => $r->program, 'Warna' => $r->warna, 'Harga' => $r->harga, 'Periode' => $r->periode, 'Rules' => $r->rules, 'Benefit' => $r->benefit]));

    $sellOut = DB::table('sell_out_targets')->orderByDesc('periode_start')->get()
        ->map(fn ($r) => $fromDb($r, ['Vendor' => $r->vendor, 'Kategori' => $r->kategori, 'Brand' => $r->brand, 'Seri' => $r->seri, 'Nama_Produk' => $r->nama_produk, 'Target_Unit' => $r->target_unit, 'Bonus_Nominal' => $r->bonus_nominal, 'Realisasi_Unit' => $r->realisasi_unit, 'Periode_Start' => $r->periode_start, 'Periode_End' => $r->periode_end, 'Catatan' => $r->catatan]));

    $ads = DB::table('ads_performance')->orderByDesc('tanggal')->get()
        ->map(fn ($r) => $fromDb($r, ['Nama' => $r->nama, 'ID_Ads' => $r->id_ads, 'Tanggal' => $r->tanggal, 'Biaya' => $r->biaya, 'Sisa_Saldo' => $r->sisa_saldo, 'Kategori' => $r->kategori, 'Platform' => $r->platform, 'Jangkauan' => $r->jangkauan, 'Suka' => $r->suka, 'Komentar' => $r->komentar, 'Share' => $r->share]));

    $hargaKompetitor = DB::table('harga_kompetitor')->orderByDesc('tanggal_cek')->get()
        ->map(fn ($r) => $fromDb($r, ['Nama_Produk' => $r->nama_produk, 'Harga_Distributor_1' => $r->harga_distributor_1, 'Harga_Distributor_2' => $r->harga_distributor_2, 'Harga_Kompetitor' => $r->harga_kompetitor, 'Margin_Profit' => $r->margin_profit, 'Harga_Rencana_Jual' => $r->harga_rencana_jual, 'Tanggal_Cek' => $r->tanggal_cek, 'Catatan' => $r->catatan]));

    $orderanOnline = DB::table('orderan_online')->orderByDesc('tanggal')->get()
        ->map(function ($r) use ($fromDb) {
            $p = json_decode($r->raw_payload ?? '{}', true) ?: [];
            return $fromDb($r, ['TANGGAL' => $r->tanggal, 'ECOMMERCE' => $r->ecommerce, 'HANDLE' => $r->handle, 'NAMA' => $r->nama, 'TYPE UNIT' => $r->type_unit, 'TYPE_UNIT' => $r->type_unit, 'HARGA ONLINE' => $r->harga_online, 'HARGA_ONLINE' => $r->harga_online, 'NOMINAL CAIR' => $r->nominal_cair, 'NOMINAL_CAIR' => $r->nominal_cair, 'STATUS' => $r->status, 'NO_PESANAN' => $p['NO PESANAN'] ?? null, 'NO_RESI' => $p['NO RESI'] ?? null, 'IMEI_SN' => $p['IMEI/SN'] ?? null, 'NO_NOTA' => $p['NO NOTA'] ?? null]);
        });

    $unitDitanya = DB::table('unit_ditanya')->orderByDesc('tanggal')->get()
        ->map(fn ($r) => $fromDb($r, ['TANGGAL' => $r->tanggal, 'KATEGORI' => $r->kategori, 'BRAND' => $r->brand, 'SERI' => $r->seri, 'KONDISI' => $r->kondisi, 'TIPE' => $r->tipe, 'DITANYA' => $r->ditanya, 'AVAILABLE' => $r->available]));

    $claimGaransi = DB::table('claim_garansi')->orderByDesc('tanggal_masuk')->get()
        ->map(fn ($r) => $fromDb($r, ['NAMA_CUSTOMER' => $r->nama_customer, 'NO_SERVICE' => $r->no_service, 'NO_TRANSAKSI' => $r->no_transaksi, 'TANGGAL_MASUK' => $r->tanggal_masuk, 'WA_CUSTOMER' => $r->wa_customer, 'TIPE' => $r->tipe, 'SERI' => $r->seri, 'MODEL' => $r->model, 'STATUS' => $r->status, 'LOKASI_KLAIM' => $r->lokasi_klaim, 'TANGGAL_ESTIMASI' => $r->tanggal_estimasi, 'TANGGAL_DIAMBIL' => $r->tanggal_diambil, 'GARANSI' => $r->garansi, 'KERUSAKAN' => $r->kerusakan, 'KETERANGAN' => $r->keterangan]));

    $keepBarang = DB::table('keep_barang')->orderByDesc('tanggal_keep')->get()
        ->map(fn ($r) => $fromDb($r, ['TANGGAL_KEEP' => $r->tanggal_keep, 'NAMA' => $r->nama, 'NOMOR_HP' => $r->nomor_hp, 'TYPE_HP' => $r->type_hp, 'DP_UANG_MUKA' => $r->dp_uang_muka, 'HARGA_JUAL' => $r->harga_jual, 'RENCANA_PENGAMBILAN' => $r->rencana_pengambilan, 'HANDLE_BY' => $r->handle_by, 'STATUS' => $r->status, 'TANGGAL_EXPIRED' => $r->tanggal_expired]));

    $lpjk = DB::table('lpjk')->orderByDesc('tanggal')->get()
        ->map(fn ($r) => $fromDb($r, ['Nama_Event' => $r->nama_event, 'Tanggal' => $r->tanggal, 'Budget_Rencana' => $r->budget_rencana, 'Realisasi_Biaya' => $r->realisasi_biaya, 'Status' => $r->status, 'Keterangan' => $r->keterangan]));

    $lpjkDetail = DB::table('lpjk_detail')
        ->leftJoin('lpjk', 'lpjk.id', '=', 'lpjk_detail.lpjk_id')
        ->orderBy('lpjk_detail.id')
        ->get(['lpjk_detail.*', 'lpjk.source_id as lpjk_source_id'])
        ->map(fn ($r) => $fromDb($r, ['Master_ID' => $r->master_id ?: $r->lpjk_source_id, 'Kategori' => $r->kategori, 'Nama_Pengeluaran' => $r->nama_pengeluaran, 'Satuan' => $r->satuan, 'Jumlah' => $r->jumlah, 'Total' => $r->total, 'Bukti' => $r->bukti]));

    $calendarEvents = DB::table('calendar_events')->orderBy('tanggal')->get()
        ->map(fn ($r) => $fromDb($r, ['Nama_Event' => $r->nama_event, 'Tanggal' => $r->tanggal, 'Warna' => $r->warna]));

    $assetVendorInventory = DB::table('asset_vendor_inventory')->orderByDesc('created_at')->get()
        ->map(fn ($r) => $fromDb($r, ['Vendor' => $r->vendor, 'Brand' => $r->brand, 'Seri' => $r->seri, 'IMEI' => $r->imei, 'Quantity' => $r->quantity, 'Condition' => $r->condition, 'Purchase_Date' => $r->purchase_date, 'Notes' => $r->notes]));

    $namaStock = DB::table('stock_names')
        ->orderBy('kategori')
        ->orderBy('brand')
        ->orderBy('seri')
        ->get(['source_id', 'kategori', 'brand', 'seri'])
        ->map(fn ($row) => [
            'ID' => $row->source_id,
            'KATEGORI' => $row->kategori,
            'BRAND' => $row->brand,
            'SERI' => $row->seri,
        ]);

    return response()->json([
        'settings'             => $settings,
        'masterPlan'      => $masterPlan,
        'analytics'       => $analytics,
        'distribution'    => $distribution,
        'unboxing'        => $unboxing,
        'story'           => $story,
        'ideation'        => $ideation,
        'promo'           => $promo,
        'sellOut'         => $sellOut,
        'ads'             => $ads,
        'hargaKompetitor' => $hargaKompetitor,
        'orderanOnline'   => $orderanOnline,
        'unitDitanya'     => $unitDitanya,
        'claimGaransi'    => $claimGaransi,
        'keepBarang'      => $keepBarang,
        'lpjk'            => $lpjk,
        'lpjkDetail'      => $lpjkDetail,
        'calendarEvents'       => $calendarEvents,
        'assetVendorInventory' => $assetVendorInventory,
        'namaStock'           => $namaStock,
        'bonusConfig'          => $bonusConfig,
        'budgetingConfig'      => $budgetingConfig,
    ]);
});
});
});
