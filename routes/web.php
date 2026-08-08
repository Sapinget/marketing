<?php

use App\Models\ChatMessage;
use App\Support\MasterPlanDistributionSync;
use App\Support\DashboardAuth;
use App\Support\MetaIgImportNormalizer;
use App\Support\AppleSheetImporter;
use App\Support\PricelistSheetImporter;
use App\Support\XlsxSheetReader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use App\Support\MarketingDashboardShell;
use Illuminate\Validation\Rule;

$nullableDate = fn ($value) => blank($value) ? null : $value;

$actor = function (): ?string {
    $user = auth()->user();

    if ($user instanceof \App\Models\User) {
        return $user->username ?: $user->email ?: $user->name;
    }

    return null;
};
$actorUserId = function () use ($actor): ?int {
    $authenticatedUserId = auth()->id();
    if (is_int($authenticatedUserId)) {
        return $authenticatedUserId;
    }

    return null;
};
$actorLabel = function () use ($actor): ?string {
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

$syncFromMetaIg = function (array &$row): void {
    $idPost = $row['id_post'] ?? null;
    if (blank($idPost)) return;
    $post = DB::table('meta_ig_posts')->where('post_id', $idPost)->first();
    if ($post === null) return;
    $row['views'] = max(0, (int) ($post->views ?? 0));
    $row['likes'] = max(0, (int) ($post->likes ?? 0));
    $row['comments'] = max(0, (int) ($post->comments ?? 0));
    $row['shares'] = max(0, (int) ($post->shares ?? 0));
};

$analyticsPayload = fn (array $payload) => [
    'master_id' => trim((string) ($payload['Master_ID'] ?? '')),
    'title' => $stripTags($payload['Judul'] ?? null),
    'platform' => $stripTags((string) ($payload['Platform'] ?? '')),
    'id_post' => $stripTags($payload['ID_Post'] ?? null),
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
    'ID_Post' => $rowValue($row, 'id_post'),
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

Route::get('/api/auth/avatar/{filename}', function (string $filename) {
    abort_unless(preg_match('/^[A-Za-z0-9._-]+$/', $filename) === 1, 404);

    $path = storage_path('app/public/avatars/' . $filename);
    abort_unless(File::exists($path), 404);

    return response()->file($path, [
        'Cache-Control' => 'public, max-age=86400',
    ]);
})->withoutMiddleware([
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
    if (! auth()->check()) {
        return response()->json([
            'authenticated' => false,
            'user' => null,
        ]);
    }

    if (! $dashboardAuth->ensureActiveSession(request())) {
        return response()->json([
            'authenticated' => false,
            'user' => null,
            ...$dashboardAuth->sessionFailurePayload(),
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
    $schema = \Illuminate\Support\Facades\Schema::connection(DB::getDefaultConnection());
    $assertLocalRequest = static function (): void {
        abort_unless((string) config('app.env') === 'local', 404);
        abort_unless(in_array(request()->ip(), ['127.0.0.1', '::1'], true), 403);
    };
    $sensitivePreviewTables = [
        'users',
        'sessions',
        'password_reset_tokens',
        'cache',
        'cache_locks',
        'jobs',
        'failed_jobs',
    ];
    $logDbPreviewAccess = static function (string $action, string $recordKey, ?array $afterPayload = null): void {
        if (! \Illuminate\Support\Facades\Schema::hasTable('activity_logs')) {
            return;
        }

        DB::table('activity_logs')->insert([
            'user_id' => auth()->id(),
            'actor_label' => 'local-db-preview',
            'table_name' => '__db',
            'action' => $action,
            'record_key' => $recordKey,
            'record_id' => null,
            'before_payload' => null,
            'after_payload' => $afterPayload === null ? null : json_encode($afterPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'created_at' => now(),
        ]);
    };
    $tableListing = static function () use ($schema): \Illuminate\Support\Collection {
        return collect($schema->getTableListing())
            ->map(fn ($table): string => (string) $table)
            ->sort()
            ->values();
    };
    $columnListing = static function (string $table) use ($schema): \Illuminate\Support\Collection {
        return collect($schema->getColumnListing($table))
            ->map(fn ($column): string => (string) $column)
            ->values();
    };

    Route::get('/tables', function () use ($assertLocalRequest, $logDbPreviewAccess, $tableListing) {
        $assertLocalRequest();

        $database = DB::getDatabaseName();
        $tables = $tableListing()
            ->map(function (string $table): array {
                return [
                    'name' => $table,
                    'count' => DB::table($table)->count(),
                ];
            })
            ->values();

        $logDbPreviewAccess('browse', 'tables', [
            'database' => $database,
            'table_count' => $tables->count(),
        ]);

        return view('db.tables', [
            'database' => $database,
            'tables' => $tables,
        ]);
    });

    Route::get('/tables/{table}', function (string $table) use ($assertLocalRequest, $logDbPreviewAccess, $sensitivePreviewTables, $tableListing, $columnListing) {
        $assertLocalRequest();

        $allowedTables = $tableListing();

        abort_unless($allowedTables->contains($table), 404);
        abort_if(in_array($table, $sensitivePreviewTables, true), 403, 'Preview tabel sensitif tidak diizinkan.');

        $columns = $columnListing($table);

        $logDbPreviewAccess('preview', $table, [
            'columns' => $columns->count(),
        ]);

        return view('db.table-preview', [
            'table' => $table,
            'columns' => $columns,
            'rows' => DB::table($table)->limit(50)->get(),
            'totalRows' => DB::table($table)->count(),
        ]);
    });
});


Route::any('/8090/{path?}', function (MarketingDashboardShell $dashboardShell, ?string $path = null) {
    $publicBaseUrl = request()->getSchemeAndHttpHost().'/8090';
    URL::forceRootUrl($publicBaseUrl);

    $normalizedPath = trim((string) $path, '/');

    if ($normalizedPath === '') {
        return response()
            ->view('dashboard.index', $dashboardShell->build($publicBaseUrl), 200)
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    $publicFile = public_path($normalizedPath);
    if (is_file($publicFile)) {
        $extension = strtolower(pathinfo($publicFile, PATHINFO_EXTENSION));
        $contentType = match ($extension) {
            'css' => 'text/css; charset=UTF-8',
            'js', 'mjs' => 'text/javascript; charset=UTF-8',
            'json' => 'application/json; charset=UTF-8',
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'ico' => 'image/x-icon',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            default => mime_content_type($publicFile) ?: 'application/octet-stream',
        };

        return response()->file($publicFile, ['Content-Type' => $contentType]);
    }

    $targetPath = '/'.$normalizedPath;
    $query = request()->getQueryString();
    $targetUri = $targetPath.($query ? '?'.$query : '');
    $server = request()->server->all();
    $server['REQUEST_URI'] = $targetUri;
    $server['PATH_INFO'] = $targetPath;

    $subRequest = Request::create(
        $targetUri,
        request()->method(),
        request()->query->all(),
        request()->cookies->all(),
        request()->files->all(),
        $server,
        request()->getContent()
    );

    foreach (request()->headers->all() as $name => $values) {
        $subRequest->headers->set($name, $values);
    }

    return app(\Illuminate\Contracts\Http\Kernel::class)->handle($subRequest);
})->where('path', '.*');


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
    $rowValue,
    $syncFromMetaIg
): void {
    $assertUserManagementAccess = static function (): void {
        $user = auth()->user();

        abort_unless($user instanceof \App\Models\User, 401);
        abort_unless(app(DashboardAuth::class)->canManageUsers($user), 403, 'Forbidden');
    };
    $assertSensitiveLogAccess = static function (): void {
        $user = auth()->user();

        abort_unless($user instanceof \App\Models\User, 401);
        abort_unless(app(DashboardAuth::class)->canAccessSensitiveLogs($user), 403, 'Forbidden');
    };
    $assertSettingsManagementAccess = static function (): void {
        $user = auth()->user();

        abort_unless($user instanceof \App\Models\User, 401);
        abort_unless(app(DashboardAuth::class)->canManageSettings($user), 403, 'Forbidden');
    };
    $assertRawSheetManagementAccess = static function (): void {
        $user = auth()->user();

        abort_unless($user instanceof \App\Models\User, 401);
        abort_unless(app(DashboardAuth::class)->canManageRawSheets($user), 403, 'Forbidden');
    };
    $assertAnalyticsImportAccess = static function (): void {
        $user = auth()->user();

        abort_unless($user instanceof \App\Models\User, 401);
        abort_unless(app(DashboardAuth::class)->canImportAnalytics($user), 403, 'Forbidden');
    };

Route::post('/api/auth/heartbeat', function (DashboardAuth $dashboardAuth) {
    $user = auth()->user();
    abort_unless($user instanceof \App\Models\User, 401);

    $updatedUser = $dashboardAuth->touchPresence($user);

    return response()->json([
        'status' => 'success',
        'user' => $dashboardAuth->userPayload($updatedUser),
    ]);
});

Route::get('/api/chat/users', function (DashboardAuth $dashboardAuth) {
    $user = auth()->user();
    abort_unless($user instanceof \App\Models\User, 401);

    $users = \App\Models\User::query()
        ->whereKeyNot($user->getKey())
        ->orderByRaw('is_online desc, COALESCE(last_seen_at, created_at) desc')
        ->limit(50)
        ->get()
        ->map(fn (\App\Models\User $member): array => $dashboardAuth->userPayload($member))
        ->values();

    return response()->json([
        'data' => $users,
    ]);
});

Route::get('/api/chat/recent', function (DashboardAuth $dashboardAuth) {
    $authUser = auth()->user();
    abort_unless($authUser instanceof \App\Models\User, 401);

    $messages = ChatMessage::query()
        ->where('sender_id', $authUser->getKey())
        ->orWhere('receiver_id', $authUser->getKey())
        ->orderByDesc('created_at')
        ->limit(200)
        ->get();

    $recent = $messages
        ->map(function (ChatMessage $message) use ($authUser) {
            $contactId = (int) ($message->sender_id === $authUser->getKey() ? $message->receiver_id : $message->sender_id);
            return [
                'contact_id' => $contactId,
                'message' => $message->message,
                'created_at' => optional($message->created_at)?->toIso8601String(),
            ];
        })
        ->unique('contact_id')
        ->values();

    $contacts = \App\Models\User::query()
        ->whereIn('id', $recent->pluck('contact_id')->all())
        ->get()
        ->keyBy('id');

    $unreadByUser = ChatMessage::query()
        ->select('sender_id', DB::raw('count(*) as unread_count'))
        ->where('receiver_id', $authUser->getKey())
        ->whereNull('read_at')
        ->groupBy('sender_id')
        ->pluck('unread_count', 'sender_id');

    return response()->json([
        'data' => $recent
            ->map(function (array $row) use ($contacts, $dashboardAuth, $unreadByUser) {
                $contact = $contacts->get($row['contact_id']);
                if (! $contact instanceof \App\Models\User) {
                    return null;
                }

                return [
                    'user' => $dashboardAuth->userPayload($contact),
                    'last_message' => $row['message'],
                    'last_message_at' => $row['created_at'],
                    'unread_count' => (int) ($unreadByUser[$contact->getKey()] ?? 0),
                ];
            })
            ->filter()
            ->values(),
    ]);
});

Route::get('/api/chat/messages/{user}', function (\App\Models\User $user, DashboardAuth $dashboardAuth) {
    $authUser = auth()->user();
    abort_unless($authUser instanceof \App\Models\User, 401);
    abort_if($authUser->is($user), 422, 'Tidak bisa chat ke akun sendiri.');

    ChatMessage::query()
        ->where('sender_id', $user->getKey())
        ->where('receiver_id', $authUser->getKey())
        ->whereNull('read_at')
        ->update(['read_at' => now()]);

    $messages = ChatMessage::query()
        ->where(function ($query) use ($authUser, $user) {
            $query->where('sender_id', $authUser->getKey())
                ->where('receiver_id', $user->getKey());
        })
        ->orWhere(function ($query) use ($authUser, $user) {
            $query->where('sender_id', $user->getKey())
                ->where('receiver_id', $authUser->getKey());
        })
        ->orderBy('created_at')
        ->limit(100)
        ->get()
        ->map(function (ChatMessage $message) use ($authUser) {
            return [
                'id' => $message->getKey(),
                'message' => $message->message,
                'sender_id' => $message->sender_id,
                'receiver_id' => $message->receiver_id,
                'is_mine' => (int) $message->sender_id === (int) $authUser->getKey(),
                'read_at' => optional($message->read_at)?->toIso8601String(),
                'created_at' => optional($message->created_at)?->toIso8601String(),
            ];
        })
        ->values();

    return response()->json([
        'user' => $dashboardAuth->userPayload($user),
        'data' => $messages,
    ]);
});

Route::post('/api/chat/messages', function (Request $request, DashboardAuth $dashboardAuth) {
    $authUser = auth()->user();
    abort_unless($authUser instanceof \App\Models\User, 401);

    $payload = $request->validate([
        'receiver_id' => ['required', 'integer', 'exists:users,id'],
        'message' => ['required', 'string', 'max:1000'],
    ], [
        'receiver_id.required' => 'Penerima wajib dipilih.',
        'receiver_id.exists' => 'Penerima tidak valid.',
        'message.required' => 'Pesan wajib diisi.',
        'message.max' => 'Pesan maksimal 1000 karakter.',
    ]);

    $receiver = \App\Models\User::query()->findOrFail((int) $payload['receiver_id']);
    abort_if($authUser->is($receiver), 422, 'Tidak bisa chat ke akun sendiri.');

    $message = trim(strip_tags((string) $payload['message']));
    abort_if($message === '', 422, 'Pesan wajib diisi.');

    $chatMessage = ChatMessage::query()->create([
        'sender_id' => $authUser->getKey(),
        'receiver_id' => $receiver->getKey(),
        'message' => $message,
    ]);

    return response()->json([
        'status' => 'success',
        'user' => $dashboardAuth->userPayload($receiver),
        'data' => [
            'id' => $chatMessage->getKey(),
            'message' => $chatMessage->message,
            'sender_id' => $chatMessage->sender_id,
            'receiver_id' => $chatMessage->receiver_id,
            'is_mine' => true,
            'read_at' => null,
            'created_at' => optional($chatMessage->created_at)?->toIso8601String(),
        ],
    ]);
})->middleware('throttle:30,1');

Route::get('/api/chat/unread', function () {
    $authUser = auth()->user();
    abort_unless($authUser instanceof \App\Models\User, 401);

    $total = ChatMessage::query()
        ->where('receiver_id', $authUser->getKey())
        ->whereNull('read_at')
        ->count();

    $byUser = ChatMessage::query()
        ->select('sender_id', DB::raw('count(*) as unread_count'))
        ->where('receiver_id', $authUser->getKey())
        ->whereNull('read_at')
        ->groupBy('sender_id')
        ->pluck('unread_count', 'sender_id')
        ->mapWithKeys(fn ($count, $senderId) => [(int) $senderId => (int) $count]);

    return response()->json([
        'total' => $total,
        'by_user' => $byUser,
    ]);
});

Route::post('/api/chat/typing/{user}', function (\App\Models\User $user) {
    $authUser = auth()->user();
    abort_unless($authUser instanceof \App\Models\User, 401);
    abort_if($authUser->is($user), 422);

    cache()->put(
        'chat_typing_' . $user->getKey() . '_from_' . $authUser->getKey(),
        true,
        now()->addSeconds(5)
    );

    return response()->json(['status' => 'ok']);
});

Route::get('/api/chat/typing/{user}', function (\App\Models\User $user) {
    $authUser = auth()->user();
    abort_unless($authUser instanceof \App\Models\User, 401);

    $isTyping = cache()->get('chat_typing_' . $authUser->getKey() . '_from_' . $user->getKey(), false);

    return response()->json(['typing' => $isTyping]);
});

Route::get('/api/auth/users', function (DashboardAuth $dashboardAuth) use ($assertUserManagementAccess) {
    $assertUserManagementAccess();

    return response()->json([
        'data' => $dashboardAuth->listUsers(),
    ]);
});

Route::post('/api/auth/users', function (DashboardAuth $dashboardAuth) use ($assertUserManagementAccess, $logCrudActivity) {
    $assertUserManagementAccess();

    $payload = request()->validate([
        'username' => ['required', 'string', 'min:3', 'max:100', Rule::unique('users', 'username')],
        'nama' => ['required', 'string', 'max:255'],
        'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')],
        'role' => ['nullable', Rule::in($dashboardAuth->assignableRoles())],
        'pin' => ['required', 'string', 'min:6', 'max:100', 'confirmed'],
    ], [
        'username.required' => 'Username wajib diisi.',
        'username.min' => 'Username minimal 3 karakter.',
        'username.unique' => 'Username sudah digunakan.',
        'nama.required' => 'Nama wajib diisi.',
        'email.email' => 'Format email tidak valid.',
        'email.unique' => 'Email sudah digunakan.',
        'role.in' => 'Role tidak valid.',
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
            $payload['role'] ?? null,
        );

        $logCrudActivity(
            'users',
            'create',
            (string) $user->username,
            $user->getKey(),
            $before?->only(['id', 'username', 'name', 'email', 'role']),
            $user->only(['id', 'username', 'name', 'email', 'role'])
        );

        return $user;
    });

    return response()->json([
        'status' => 'success',
        'data' => $dashboardAuth->userPayload($user),
    ]);
});

Route::put('/api/auth/users/{user}', function (\App\Models\User $user, DashboardAuth $dashboardAuth) use ($assertUserManagementAccess, $logCrudActivity) {
    $assertUserManagementAccess();

    $payload = request()->validate([
        'username' => ['required', 'string', 'min:3', 'max:100', Rule::unique('users', 'username')->ignore($user->getKey())],
        'nama' => ['required', 'string', 'max:255'],
        'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->getKey())],
        'role' => ['nullable', Rule::in($dashboardAuth->assignableRoles())],
        'pin' => ['nullable', 'string', 'min:6', 'max:100', 'confirmed'],
    ], [
        'username.required' => 'Username wajib diisi.',
        'username.min' => 'Username minimal 3 karakter.',
        'username.unique' => 'Username sudah digunakan.',
        'nama.required' => 'Nama wajib diisi.',
        'email.email' => 'Format email tidak valid.',
        'email.unique' => 'Email sudah digunakan.',
        'role.in' => 'Role tidak valid.',
        'pin.min' => 'PIN minimal 6 karakter.',
        'pin.confirmed' => 'Konfirmasi PIN tidak cocok.',
    ]);

    $before = $user->only(['id', 'username', 'name', 'email', 'role']);
    $updatedUser = $dashboardAuth->updateUser(
        $user,
        $payload['username'],
        $payload['pin'] ?? null,
        $payload['nama'],
        $payload['email'] ?? null,
        $payload['role'] ?? null,
    );

    $logCrudActivity(
        'users',
        'update',
        (string) $updatedUser->username,
        $updatedUser->getKey(),
        $before,
        $updatedUser->only(['id', 'username', 'name', 'email', 'role'])
    );

    return response()->json([
        'status' => 'success',
        'data' => $dashboardAuth->userPayload($updatedUser),
    ]);
});

Route::delete('/api/auth/users/{user}', function (\App\Models\User $user, DashboardAuth $dashboardAuth) use ($assertUserManagementAccess, $logCrudActivity) {
    $assertUserManagementAccess();

    abort_if(auth()->id() === $user->getKey(), 422, 'User yang sedang login tidak bisa dihapus.');

    $before = $user->only(['id', 'username', 'name', 'email', 'role']);
    $recordKey = (string) $user->username;
    $recordId = $user->getKey();

    $dashboardAuth->deleteUser($user);

    $logCrudActivity(
        'users',
        'delete',
        $recordKey,
        $recordId,
        $before,
        null
    );

    return response()->json([
        'status' => 'success',
    ]);
});

Route::put('/api/auth/profile', function (DashboardAuth $dashboardAuth) use ($logCrudActivity) {
    $payload = request()->validate([
        'nama' => ['required', 'string', 'max:255'],
    ]);

    $user = auth()->user();
    abort_unless($user instanceof \App\Models\User, 401);
    $before = $user->only(['id', 'username', 'name', 'email', 'avatar']);
    $updatedUser = $dashboardAuth->updateProfileName($user, $payload['nama']);
    $logCrudActivity('users', 'update', (string) $updatedUser->username, $updatedUser->getKey(), $before, $updatedUser->only(['id', 'username', 'name', 'email', 'avatar']));

    return response()->json([
        'status' => 'success',
        'user' => $dashboardAuth->userPayload($updatedUser),
    ]);
});

Route::post('/api/auth/avatar', function (Request $request, DashboardAuth $dashboardAuth) use ($logCrudActivity) {
    $payload = $request->validate([
        'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        'user_id' => ['nullable', 'integer', 'exists:users,id'],
    ]);

    $authUser = auth()->user();
    abort_unless($authUser instanceof \App\Models\User, 401);

    $targetUserId = (int) ($payload['user_id'] ?? $authUser->getKey());
    $isAdminEdit = $targetUserId !== $authUser->getKey();

    if ($isAdminEdit && ! $dashboardAuth->canManageUsers($authUser)) {
        abort(403, 'Hanya super admin yang bisa mengubah foto user lain.');
    }

    $user = $targetUserId === $authUser->getKey() ? $authUser : \App\Models\User::query()->findOrFail($targetUserId);

    $before = $user->only(['id', 'username', 'name', 'email', 'avatar']);
    $extension = strtolower((string) $payload['avatar']->getClientOriginalExtension());
    $filename = 'user-' . $user->getKey() . '-' . Str::lower(Str::random(12)) . '.' . $extension;
    $directory = storage_path('app/public/avatars');

    if (! File::isDirectory($directory)) {
        File::ensureDirectoryExists($directory);
    }

    $payload['avatar']->move($directory, $filename);

    if (filled($user->avatar)) {
        $oldPath = $directory . DIRECTORY_SEPARATOR . $user->avatar;
        if (File::exists($oldPath)) {
            File::delete($oldPath);
        }
    }

    $updatedUser = $dashboardAuth->updateUserAvatar($user, $filename);
    $logCrudActivity('users', 'update', (string) $updatedUser->username, $updatedUser->getKey(), $before, $updatedUser->only(['id', 'username', 'name', 'email', 'avatar']));

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

Route::get('/api/activity-logs', function () use ($assertSensitiveLogAccess) {
    $assertSensitiveLogAccess();

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

Route::get('/api/settings', function () use ($assertSettingsManagementAccess) {
    $assertSettingsManagementAccess();

    $settings = DB::table('marketing_settings')
        ->orderBy('key')
        ->get(['key', 'values'])
        ->mapWithKeys(function ($row) {
            $values = json_decode($row->values, true);

            return [$row->key => is_array($values) ? $values : []];
        });

    return response()->json(['data' => $settings]);
});

Route::get('/api/raw-sheets/{sheetName}', function (string $sheetName) use ($assertRawSheetManagementAccess, $dedupeNamaStockRows) {
    $assertRawSheetManagementAccess();

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

Route::put('/api/raw-sheets/{sheetName}', function (string $sheetName) use ($assertRawSheetManagementAccess, $dedupeNamaStockRows, $logCrudActivity) {
    $assertRawSheetManagementAccess();

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

Route::post('/api/meta-posts/{dataset}/import', function (string $dataset) use ($assertAnalyticsImportAccess, $inspectMetaPostDuplicates, $logCrudActivity, $upsertMetaPosts) {
    $assertAnalyticsImportAccess();

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

Route::post('/api/meta-posts/{dataset}/import-folder', function (string $dataset) use ($assertAnalyticsImportAccess, $inspectMetaPostDuplicates, $logCrudActivity, $upsertMetaPosts) {
    $assertAnalyticsImportAccess();

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

Route::delete('/api/meta-posts/{dataset}', function (string $dataset) use ($assertAnalyticsImportAccess, $logCrudActivity) {
    $assertAnalyticsImportAccess();

    $beforeCount = DB::table('meta_ig_posts')->where('dataset', $dataset)->count();
    DB::table('meta_ig_posts')->where('dataset', $dataset)->delete();
    $logCrudActivity('meta_ig_posts', 'delete', $dataset, null, [
        'dataset' => $dataset,
        'row_count' => $beforeCount,
    ], null);

    return response()->json(['status' => 'success']);
});

Route::get('/api/meta-followers', function () {
    $rows = DB::table('meta_ig_followers')->orderByDesc('follow_date')->get()->map(function ($r) {
        return [
            'id' => $r->id,
            'follow_date' => $r->follow_date,
            'count' => (int) $r->primary_count,
            'raw_payload' => $r->raw_payload ? json_decode($r->raw_payload, true) : null,
            'imported_at' => $r->imported_at,
        ];
    });
    return response()->json(['data' => $rows]);
});

Route::post('/api/meta-followers/import', function () use ($assertAnalyticsImportAccess, $logCrudActivity) {
    $assertAnalyticsImportAccess();
    $rawRows = request()->input('rows', []);
    abort_unless(is_array($rawRows), 422, 'rows harus berupa array.');
    $overwrite = filter_var(request()->input('overwrite', false), FILTER_VALIDATE_BOOLEAN);
    $inserted = 0;
    $updated = 0;
    $now = now();
    foreach ($rawRows as $row) {
        $date = $row['date'] ?? null;
        if (!$date) continue;
        $count = max(0, (int) ($row['count'] ?? 0));
        $existing = DB::table('meta_ig_followers')->where('follow_date', $date)->first();
        if ($existing) {
            if (!$overwrite) continue;
            DB::table('meta_ig_followers')->where('id', $existing->id)->update([
                'primary_count' => $count,
                'raw_payload' => json_encode($row),
                'imported_at' => $now,
                'updated_at' => $now,
            ]);
            $updated++;
        } else {
            DB::table('meta_ig_followers')->insert([
                'follow_date' => $date,
                'primary_count' => $count,
                'raw_payload' => json_encode($row),
                'imported_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $inserted++;
        }
    }
    if (($inserted + $updated) > 0) {
        $logCrudActivity('meta_ig_followers', 'update', 'followers', null, null, ['inserted' => $inserted, 'updated' => $updated]);
    }
    return response()->json(['status' => 'success', 'inserted' => $inserted, 'updated' => $updated]);
});

Route::post('/api/meta-followers/delete-all', function () use ($assertAnalyticsImportAccess, $logCrudActivity) {
    $assertAnalyticsImportAccess();
    $beforeCount = DB::table('meta_ig_followers')->count();
    DB::table('meta_ig_followers')->delete();
    $logCrudActivity('meta_ig_followers', 'delete', 'followers', null, ['row_count' => $beforeCount], null);
    return response()->json(['status' => 'success']);
});

Route::put('/api/settings', function () use ($assertSettingsManagementAccess, $logCrudActivity) {
    $assertSettingsManagementAccess();

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
        ->whereNotIn('analytics.platform', ['contentType'])
        ->orderByDesc('analytics.tanggal_publish')
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
            'ID_Post' => $row->id_post,
            'Tanggal_Publish' => $row->tanggal_publish,
            'Views' => $row->views,
            'Likes' => $row->likes,
            'Comments' => $row->comments,
            'Shares' => $row->shares,
        ]);

    return response()->json(['data' => $rows]);
});

Route::post('/api/analytics', function () use ($actorUserId, $analyticsPayload, $analyticsResponse, $logCrudActivity, $requireMasterPlanIdBySourceId, $syncFromMetaIg) {
    $row = $analyticsPayload(request()->all());
    abort_if(blank($row['master_id']) || blank($row['platform']), 422, 'Master_ID dan Platform wajib diisi.');
    $row['created_at'] = now();
    $row['master_plan_id'] = $requireMasterPlanIdBySourceId($row['master_id']);
    $row['created_by_user_id'] = $actorUserId();
    $row['updated_by_user_id'] = $actorUserId();
    $syncFromMetaIg($row);

    DB::table('analytics')->insert($row);
    $stored = DB::table('analytics')->where('id', DB::getPdo()->lastInsertId())->first();
    $logCrudActivity('analytics', 'create', (string) $stored->id, (int) $stored->id, null, (array) $stored);

    return response()->json(['status' => 'success', 'data' => $analyticsResponse($stored)], 201);
});

Route::put('/api/analytics/{id}', function (int $id) use ($actorUserId, $analyticsPayload, $analyticsResponse, $logCrudActivity, $requireMasterPlanIdBySourceId, $syncFromMetaIg) {
    abort_unless(DB::table('analytics')->where('id', $id)->exists(), 404);
    $before = DB::table('analytics')->where('id', $id)->first();

    $row = $analyticsPayload(request()->all());
    abort_if(blank($row['master_id']) || blank($row['platform']), 422, 'Master_ID dan Platform wajib diisi.');
    $row['master_plan_id'] = $requireMasterPlanIdBySourceId($row['master_id']);
    $row['updated_by_user_id'] = $actorUserId();
    $syncFromMetaIg($row);
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

$catalogTemplatePayload = function ($row): array {
    $layoutConfig = json_decode($row->layout_config ?? '{}', true) ?: [];

    return [
        'ID' => $row->source_id,
        'name' => $row->name,
        'format' => $row->format,
        'background_path' => $row->background_path,
        'background_url' => filled($row->background_path) ? '/api/catalog-templates/background/'.rawurlencode((string) $row->background_path) : null,
        'canvas_width' => (int) $row->canvas_width,
        'canvas_height' => (int) $row->canvas_height,
        'layout_config' => $layoutConfig,
        'is_active' => (bool) $row->is_active,
    ];
};

$pricelistProductPayload = function ($row): array {
    return [
        'ID' => $row->source_id,
        'source_sheet' => $row->source_sheet,
        'source_row' => (int) $row->source_row,
        'urut' => $row->urut === null ? null : (int) $row->urut,
        'kategori' => $row->kategori,
        'brand' => $row->brand,
        'nama_produk' => $row->nama_produk,
        'storage' => $row->storage,
        'ram' => $row->ram,
        'warna' => $row->warna,
        'harga_nasional' => $row->harga_nasional === null ? null : (int) $row->harga_nasional,
        'special_price' => $row->special_price === null ? null : (int) $row->special_price,
        'harga_spesial' => $row->harga_spesial === null ? null : (int) $row->harga_spesial,
        'harga_srp' => $row->harga_srp === null ? null : (int) $row->harga_srp,
        'harga_jual' => $row->harga_jual === null ? null : (int) $row->harga_jual,
        'harga_online' => $row->harga_online === null ? null : (int) $row->harga_online,
        'harga_modal' => $row->harga_modal === null ? null : (int) $row->harga_modal,
        'harga_lainnya' => json_decode($row->harga_lainnya ?? '{}', true) ?: [],
        'is_active' => (bool) $row->is_active,
        'raw_payload' => json_decode($row->raw_payload ?? '{}', true) ?: [],
    ];
};

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
        'KATEGORI'           => $r->kategori ?? null,
        'BRAND'              => $r->brand ?? null,
        'SERI'               => $r->seri ?? null,
        'RAM'                => $r->ram ?? null,
        'INTERNAL'           => $r->internal ?? null,
        'SIZE'               => $r->size ?? null,
        'WARNA'              => $r->warna ?? null,
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
    return ['source_id' => $makeSourceId('HK', $p['ID'] ?? null), 'nama_produk' => $p['Nama_Produk'] ?? null, 'kategori' => $p['KATEGORI'] ?? null, 'brand' => $p['BRAND'] ?? null, 'seri' => $p['SERI'] ?? null, 'ram' => $p['RAM'] ?? null, 'internal' => $p['INTERNAL'] ?? null, 'size' => $p['SIZE'] ?? null, 'warna' => $p['WARNA'] ?? null, 'harga_distributor_1' => (int) ($p['Harga_Distributor_1'] ?? 0), 'harga_distributor_2' => (int) ($p['Harga_Distributor_2'] ?? 0), 'harga_kompetitor' => (int) ($p['Harga_Kompetitor'] ?? 0), 'margin_profit' => (int) ($p['Margin_Profit'] ?? 0), 'harga_rencana_jual' => (int) ($p['Harga_Rencana_Jual'] ?? 0), 'tanggal_cek' => $nullableDate($p['Tanggal_Cek'] ?? null), 'catatan' => $p['Catatan'] ?? null, 'raw_payload' => $encodePayload($p), 'imported_at' => now(), 'updated_at' => now()];
}));
Route::put('/api/harga-kompetitor/{sourceId}', $genericUpdate('harga_kompetitor', function (array $p) use ($encodePayload, $nullableDate) {
    return ['nama_produk' => $p['Nama_Produk'] ?? null, 'kategori' => $p['KATEGORI'] ?? null, 'brand' => $p['BRAND'] ?? null, 'seri' => $p['SERI'] ?? null, 'ram' => $p['RAM'] ?? null, 'internal' => $p['INTERNAL'] ?? null, 'size' => $p['SIZE'] ?? null, 'warna' => $p['WARNA'] ?? null, 'harga_distributor_1' => (int) ($p['Harga_Distributor_1'] ?? 0), 'harga_distributor_2' => (int) ($p['Harga_Distributor_2'] ?? 0), 'harga_kompetitor' => (int) ($p['Harga_Kompetitor'] ?? 0), 'margin_profit' => (int) ($p['Margin_Profit'] ?? 0), 'harga_rencana_jual' => (int) ($p['Harga_Rencana_Jual'] ?? 0), 'tanggal_cek' => $nullableDate($p['Tanggal_Cek'] ?? null), 'catatan' => $p['Catatan'] ?? null, 'raw_payload' => $encodePayload($p), 'updated_at' => now()];
}));
Route::delete('/api/harga-kompetitor/{sourceId}', $genericDelete('harga_kompetitor'));

// Market Intelligence (reads from BOT SQLite — read-only)

$marketBrandFamilyMap = [
    'APPLE' => 'Apple', 'IPHONE' => 'Apple', 'IPAD' => 'Apple', 'MACBOOK' => 'Apple',
    'AW' => 'Apple', 'AIRPODS' => 'Apple', 'APPLE WATCH' => 'Apple', 'APPLE PENCIL' => 'Apple',
    'SAMSUNG' => 'Samsung', 'GALAXY' => 'Samsung',
    'XIAOMI' => 'Xiaomi', 'REDMI' => 'Xiaomi', 'POCO' => 'Xiaomi',
    'OPPO' => 'Oppo', 'VIVO' => 'Vivo', 'REALME' => 'Realme',
    'INFINIX' => 'Infinix', 'TECNO' => 'Tecno', 'ITEL' => 'Itel',
    'NUBIA' => 'Nubia', 'HONOR' => 'Honor', 'HUAWEI' => 'Huawei',
    'NOKIA' => 'Nokia', 'GOOGLE' => 'Google', 'PIXEL' => 'Google', 'MOTOROLA' => 'Motorola',
];

$marketCanonicalBrand = function (?string $value) use ($marketBrandFamilyMap): string {
    $first = strtoupper(trim(preg_split('/[\s\[]/u', trim((string) $value))[0] ?? ''));
    if ($first === '') {
        return '';
    }

    return $marketBrandFamilyMap[$first] ?? ucfirst(strtolower($first));
};

$marketNormalizeProductPart = function (?string $value) use ($marketBrandFamilyMap): string {
    $text = trim((string) $value);
    if ($text === '') {
        return '';
    }

    $text = str_replace(['_', '|'], ' ', $text);
    $text = preg_replace_callback('/\s*[\[\(]\s*([^\]\)]+)\s*[\]\)]/u', function ($matches) use ($marketBrandFamilyMap) {
        $tag = strtoupper(trim($matches[1] ?? ''));
        if (isset($marketBrandFamilyMap[$tag])) {
            return '';
        }
        if (preg_match('/\b(IPAD\s*&\s*TAB|TABLET|SMARTPHONE)\b/u', $tag)) {
            return '';
        }

        $tag = preg_replace('/\b(BRAND\s+NEW|NEW|BARU|REGULER)\b/u', '', $tag);
        $tag = preg_replace('/\s+/', ' ', trim($tag));

        return $tag === '' ? '' : ' ' . $tag . ' ';
    }, $text);
    $text = preg_replace('/[^\p{L}\p{N}\s\/,+\-.&]/u', ' ', $text);
    $text = preg_replace('/\s+/', ' ', trim($text));
    $text = strtoupper($text);
    $text = preg_replace('/\bGIFT\s+BOX\b/u', 'GIFTBOX', $text);
    $text = preg_replace('/\b(\d+)\s*\/\s*(\d+)\s*(GB|TB|MB)\b/u', '$1$3/$2$3', $text);
    $text = preg_replace('/\b(\d+)\s*\/\s*(\d+)(GB|TB|MB)\b/u', '$1$3/$2$3', $text);
    $text = preg_replace('/\b(\d+)(GB|TB|MB)\s+(\d+)(GB|TB|MB)\b/u', '$1$2/$3$4', $text);

    $tokens = preg_split('/(\s+|[\/,+-])/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
    $normalized = array_map(function ($token) {
        $upper = strtoupper(trim($token));
        if ($upper === '' || preg_match('/^(\s+|[\/,+-])$/', $token)) {
            return $token;
        }
        if (preg_match('/^\d+(GB|TB|MB)$/i', $token) || preg_match('/^\d+G$/i', $token)) {
            return $upper;
        }
        if (preg_match('/^(\d{1,2})(PROMAX|PRO|PLUS|MAX|MINI)$/i', $token, $matches)) {
            return $matches[1] . ' ' . strtoupper($matches[2] === 'PROMAX' ? 'PRO MAX' : $matches[2]);
        }
        if (preg_match('/^(S\d{2})(ULTRA|PLUS|FE)$/i', $token, $matches)) {
            return strtoupper($matches[1]) . ' ' . strtoupper($matches[2]);
        }

        return $upper;
    }, $tokens ?: []);

    return preg_replace('/\s+/', ' ', trim(implode('', $normalized)));
};

$marketNormalizeConditionPart = function (?string $kondisi, ?string $kondisiDetail = null) use ($marketNormalizeProductPart): string {
    $condition = $marketNormalizeProductPart(trim((string) $kondisi . ' ' . (string) $kondisiDetail));
    $condition = preg_replace('/\bNEW\b/u', 'BARU', $condition);
    $condition = preg_replace('/\bREGULER\b/u', '', $condition);
    $condition = preg_replace('/\s+/', ' ', trim(str_replace('-', ' ', $condition)));
    $tokens = array_values(array_unique(array_filter(explode(' ', $condition), fn ($token) => $token !== '')));

    return implode(' ', $tokens);
};

$marketInferConditionFromName = function (?string $nama) use ($marketNormalizeConditionPart): string {
    $name = strtoupper((string) $nama);
    if (preg_match('/\b(BRAND\s+NEW|NEW|BARU)\b/u', $name)) {
        return 'BARU';
    }
    if (preg_match('/\b(SECOND|BEKAS|SEKEN|EX)\b/u', $name)) {
        return $marketNormalizeConditionPart('SECOND');
    }

    return '';
};

$marketProductLabel = function (?string $nama, ?string $varian = null, ?string $kondisi = null, ?string $kondisiDetail = null) use ($marketNormalizeProductPart, $marketNormalizeConditionPart, $marketInferConditionFromName): string {
    $parts = [
        $marketNormalizeProductPart($nama),
        $marketNormalizeProductPart($varian),
        $marketNormalizeConditionPart(trim($marketInferConditionFromName($nama) . ' ' . (string) $kondisi), $kondisiDetail),
    ];

    $segments = [];
    foreach ($parts as $part) {
        if ($part === '') {
            continue;
        }
        $alreadyIncluded = false;
        foreach ($segments as $segment) {
            if ($segment === $part || str_contains($segment, $part) || str_contains($part, $segment)) {
                $alreadyIncluded = true;
                break;
            }
        }
        if (! $alreadyIncluded) {
            $segments[] = $part;
        }
    }

    return $segments !== [] ? implode(' · ', $segments) : '-';
};

$marketBrandFromProduct = function (?string $nama) use ($marketCanonicalBrand): string {
    $brand = $marketCanonicalBrand($nama);
    return $brand !== '' ? $brand : '-';
};

Route::get('/api/market/pasar', function () {
    $brandFamilyMap = [
        'APPLE' => 'Apple', 'IPHONE' => 'Apple', 'IPAD' => 'Apple', 'MACBOOK' => 'Apple',
        'AW' => 'Apple', 'AIRPODS' => 'Apple', 'APPLE WATCH' => 'Apple', 'APPLE PENCIL' => 'Apple',
        'SAMSUNG' => 'Samsung', 'GALAXY' => 'Samsung',
        'XIAOMI' => 'Xiaomi', 'REDMI' => 'Xiaomi', 'POCO' => 'Xiaomi',
        'OPPO' => 'Oppo', 'VIVO' => 'Vivo', 'REALME' => 'Realme',
        'INFINIX' => 'Infinix', 'TECNO' => 'Tecno', 'ITEL' => 'Itel',
        'NUBIA' => 'Nubia', 'HONOR' => 'Honor', 'HUAWEI' => 'Huawei',
        'NOKIA' => 'Nokia', 'GOOGLE' => 'Google', 'MOTOROLA' => 'Motorola',
    ];
    $canonicalBrand = function (string $nama) use ($brandFamilyMap): string {
        $first = strtoupper(trim(preg_split('/[\s\[]/u', trim($nama))[0] ?? ''));
        return $brandFamilyMap[$first] ?? ucfirst(strtolower($first));
    };
    $medianFn = function ($rows) {
        $sorted = $rows->sortBy('harga')->pluck('harga')->values();
        $cnt = $sorted->count();
        if ($cnt === 0) return 0;
        $mid = intdiv($cnt, 2);
        return (float) ($cnt % 2 === 0 ? ($sorted[$mid - 1] + $sorted[$mid]) / 2 : $sorted[$mid]);
    };

    $botDb  = DB::connection('sqlite_bot');
    $sources = $botDb->table('external_market_sources')->get();
    $prices  = $botDb->table('external_market_prices')->get();

    // Group by source for KPI cards
    $bySource = $prices->groupBy('sumber')->map(fn ($rows) => [
        'count'        => $rows->count(),
        'min_harga'    => $rows->min('harga'),
        'max_harga'    => $rows->max('harga'),
        'avg_harga'    => round($rows->avg('harga')),
        'median_harga' => round($medianFn($rows)),
    ]);

    // Group by canonical brand (matches bot's bali_competitor logic)
    $byBrand = $prices->groupBy(fn ($r) => $canonicalBrand($r->nama ?? ''))
        ->filter(fn ($rows, $brand) => $brand !== '')
        ->map(fn ($rows, $brand) => [
            'brand'        => $brand,
            'count'        => $rows->count(),
            'median_harga' => round($medianFn($rows)),
        ])
        ->sortByDesc('count')
        ->take(10)
        ->values();

    // StatCounter market share data
    $botDbPath = env('BOT_DB_DATABASE', '');
    $marketGlobal    = [];
    $marketIndonesia = [];
    if ($botDbPath) {
        $botDir = dirname(dirname(dirname($botDbPath)));
        $wwFile = $botDir . '/runtime/cache/market/statcounter_ww.json';
        $idFile = $botDir . '/runtime/cache/market/statcounter_id.json';
        if (file_exists($wwFile)) {
            $ww = json_decode(file_get_contents($wwFile), true) ?: [];
            $marketGlobal = array_slice($ww['items'] ?? [], 0, 10);
        }
        if (file_exists($idFile)) {
            $id = json_decode(file_get_contents($idFile), true) ?: [];
            $marketIndonesia = array_slice($id['items'] ?? [], 0, 10);
        }
    }

    // kita_mix: brand sales mix from bot SQLite sales table
    $kitaMix = [];
    try {
        $placeholder = ['', 'BARU', 'SECOND', 'NEW', 'BEKAS', 'SEKEN', 'LAINNYA'];
        $salesRows = $botDb->table('sales')
            ->selectRaw('brand, COUNT(*) as qty')
            ->groupBy('brand')
            ->orderByDesc('qty')
            ->limit(50)
            ->get()
            ->filter(fn ($sr) => !in_array(strtoupper(trim($sr->brand ?? '')), $placeholder));
        $byNormBrand = [];
        foreach ($salesRows as $sr) {
            $nb = $canonicalBrand($sr->brand ?? '');
            if ($nb) {
                $byNormBrand[$nb] = ($byNormBrand[$nb] ?? 0) + (int) $sr->qty;
            }
        }
        arsort($byNormBrand);
        $byNormBrand = array_slice($byNormBrand, 0, 15, true);
        $totalQty = array_sum($byNormBrand);
        foreach ($byNormBrand as $brand => $qty) {
            $kitaMix[] = ['brand' => $brand, 'qty' => $qty, 'pct' => $totalQty > 0 ? round($qty / $totalQty * 100, 2) : 0];
        }
    } catch (\Exception $e) {
        $kitaMix = [];
    }

    return response()->json([
        'sources'          => $sources,
        'by_source'        => $bySource,
        'bali_competitor'  => $byBrand,
        'competitors'      => $byBrand,
        'total'            => $prices->count(),
        'updated_at'       => $prices->max('captured_at') ?? null,
        'market_global'    => $marketGlobal,
        'market_indonesia' => $marketIndonesia,
        'kita_mix'         => $kitaMix,
    ]);
});

Route::get('/api/market/intelijen-harga', function () use ($marketProductLabel) {
    $botDb = DB::connection('sqlite_bot');
    $latestBatch = $botDb->table('pura_price_snapshots')->orderByDesc('_id')->value('batch_id');
    $snapshots = $latestBatch
        ? $botDb->table('pura_price_snapshots')->where('batch_id', $latestBatch)->get()
        : collect();
    $extPrices = $botDb->table('external_market_prices')
        ->orderBy('nama')
        ->get();
    $byNameSrc = $extPrices->groupBy(fn ($r) => strtolower(trim($r->nama ?? '')))->map(
        fn ($rows) => $rows->groupBy(fn ($r) => strtolower(trim($r->sumber ?? '')))->map(fn ($g) => $g->min('harga'))
    );
    $compared = $snapshots->map(function ($snap) use ($byNameSrc, $marketProductLabel) {
        $key     = strtolower(trim($snap->nama ?? ''));
        $srcMap  = $byNameSrc->get($key, collect());
        $hargaGood  = $srcMap->get('good ponsel');
        $hargaRumah = $srcMap->get('rumah gadget bali');
        $hargaDev   = $srcMap->get('devstore');
        $allExt  = array_filter([$hargaGood, $hargaRumah, $hargaDev], fn ($v) => $v !== null);
        $extMin  = count($allExt) > 0 ? min($allExt) : null;
        $puraHarga = (float) ($snap->harga ?? 0);
        $gap    = $extMin !== null ? $puraHarga - $extMin : null;
        $gapPct = ($extMin !== null && $extMin > 0) ? round(($puraHarga - $extMin) / $extMin * 100, 1) : null;
        $prioritas = match (true) {
            $gapPct !== null && $gapPct > 5  => 'Mahal',
            $gapPct !== null && $gapPct < -5 => 'Murah',
            $gapPct !== null                 => 'OK',
            default                          => null,
        };
        return [
            'nama'               => $snap->nama,
            'varian'             => $snap->varian ?? '',
            'kondisi'            => $snap->kondisi ?? '',
            'produk'             => $marketProductLabel($snap->nama ?? '', $snap->varian ?? '', $snap->kondisi ?? ''),
            'harga_pura'         => $puraHarga,
            'harga_goodponsel'   => $hargaGood   !== null ? (int) round($hargaGood) : null,
            'harga_rumahgadget'  => $hargaRumah  !== null ? (int) round($hargaRumah) : null,
            'harga_devstore'     => $hargaDev    !== null ? (int) round($hargaDev) : null,
            'harga_ext_min'      => $extMin,
            'selisih'            => $gap !== null ? (int) round($gap) : null,
            'gap_pct'            => $gapPct,
            'prioritas'          => $prioritas,
        ];
    })->filter(fn ($r) => $r['harga_ext_min'] !== null)->values();
    $tooExpensive = $compared->filter(fn ($r) => $r['gap_pct'] > 5)->count();
    $tooCheap     = $compared->filter(fn ($r) => $r['gap_pct'] < -5)->count();
    $matched      = $compared->filter(fn ($r) => abs($r['gap_pct']) <= 5)->count();
    return response()->json([
        'summary' => [
            'too_expensive' => $tooExpensive,
            'too_cheap'     => $tooCheap,
            'matched'       => $matched,
            'total'         => $compared->count(),
        ],
        'rows' => $compared->values(),
    ]);
});

Route::get('/api/market/pura-price-changes', function () use ($marketProductLabel) {
    $botDb     = DB::connection('sqlite_bot');
    $direction = request('direction', 'all');
    $query     = $botDb->table('pura_price_changes')->orderByDesc('detected_at');
    if ($direction === 'naik')  $query->where('selisih', '>', 0);
    if ($direction === 'turun') $query->where('selisih', '<', 0);
    $changes = $query->limit(500)->get()->map(fn ($r) => [
        'nama'        => $r->nama,
        'varian'      => $r->varian ?? '',
        'kondisi'     => $r->kondisi ?? '',
        'produk'      => $marketProductLabel($r->nama ?? '', $r->varian ?? '', $r->kondisi ?? ''),
        'gudang'      => $r->gudang ?? '',
        'harga_lama'  => (int) ($r->harga_lama ?? 0),
        'harga_baru'  => (int) ($r->harga_baru ?? 0),
        'selisih'     => (int) ($r->selisih ?? 0),
        'selisih_pct' => (float) ($r->selisih_pct ?? 0),
        'detected_at' => $r->detected_at ?? null,
    ]);
    $allChanges = $botDb->table('pura_price_changes')->get();
    return response()->json([
        'summary' => [
            'total' => $allChanges->count(),
            'naik'  => $allChanges->where('selisih', '>', 0)->count(),
            'turun' => $allChanges->where('selisih', '<', 0)->count(),
        ],
        'rows' => $changes->values(),
    ]);
});

Route::get('/api/market/audit-harga', function () use ($marketProductLabel) {
    $botDbPath = env('BOT_DB_DATABASE', '');
    $botRoot = $botDbPath ? dirname(dirname(dirname($botDbPath))) : '';
    $downloadDir = $botRoot ? $botRoot.'/Download' : '';
    $files = $downloadDir && is_dir($downloadDir)
        ? glob($downloadDir.'/AUDIT_HARGA_*.xlsx')
        : [];

    if (! $files) {
        return response()->json([
            'summary_text' => 'AUDIT HARGA PURA\nBelum ada cache audit harga ERP.',
            'summary' => [
                'diff_count' => 0,
                'web_diff_count' => 0,
                'sheet_diff_count' => 0,
            ],
            'rows' => [],
        ]);
    }

    usort($files, fn ($a, $b) => filemtime($b) <=> filemtime($a));
    $path = $files[0];
    $reader = new XlsxSheetReader();
    $sheetNames = $reader->sheetNames($path);
    $sheetName = in_array('AUDIT HARGA', $sheetNames, true) ? 'AUDIT HARGA' : ($sheetNames[0] ?? '');
    $rows = $sheetName ? collect($reader->rows($path, $sheetName))->take(500)->map(function ($row) use ($marketProductLabel) {
        $normalizeNumber = fn ($value) => $value === null || $value === ''
            ? null
            : (int) round((float) $value);
        $normalizePct = fn ($value) => $value === null || $value === ''
            ? null
            : (float) $value;

        $product = $marketProductLabel($row['Produk'] ?? '', $row['Varian'] ?? '', $row['Kondisi'] ?? '');

        return [
            'Produk'             => $product,
            'Varian'             => $row['Varian'] ?? '',
            'Kondisi'            => $row['Kondisi'] ?? '',
            'Toko'               => $row['Toko'] ?? '',
            'H. Dashboard'       => $normalizeNumber($row['H. Dashboard'] ?? null),
            'H. Website'         => $normalizeNumber($row['H. Website'] ?? null),
            'H. Spreadsheet'     => $normalizeNumber($row['H. Spreadsheet'] ?? null),
            'Selisih Web (Rp)'   => $normalizeNumber($row['Selisih Web (Rp)'] ?? null),
            'Selisih Web (%)'    => $normalizePct($row['Selisih Web (%)'] ?? null),
            'Selisih Sheet (Rp)' => $normalizeNumber($row['Selisih Sheet (Rp)'] ?? null),
            'Selisih Sheet (%)'  => $normalizePct($row['Selisih Sheet (%)'] ?? null),
        ];
    })->values() : collect();

    return response()->json([
        'summary_text' => 'AUDIT HARGA PURA\nData dari cache audit ERP terakhir.',
        'generated_at' => date('Y-m-d H:i:s', filemtime($path)),
        'summary' => [
            'diff_count' => $rows->count(),
            'web_diff_count' => $rows->filter(fn ($row) => $row['Selisih Web (Rp)'] !== null)->count(),
            'sheet_diff_count' => $rows->filter(fn ($row) => $row['Selisih Sheet (Rp)'] !== null)->count(),
        ],
        'rows' => $rows,
    ]);
});

Route::get('/api/market/eksternal', function () use ($marketProductLabel, $marketBrandFromProduct) {
    $botDb  = DB::connection('sqlite_bot');
    $sourceInput = request('source', request('market_source', ''));
    $sourceMap = [
        'goodponsel'       => 'Good Ponsel',
        'goodponselbali'   => 'Good Ponsel',
        'devstore'         => 'Devstore',
        'rumahgadget'      => 'Rumah Gadget Bali',
        'rumahgadgetbali'  => 'Rumah Gadget Bali',
    ];
    $sourceKey = strtolower(preg_replace('/[^a-z0-9]+/', '', (string) $sourceInput));
    $requestedSource = $sourceInput !== '' ? ($sourceMap[$sourceKey] ?? (string) $sourceInput) : '';
    $requestedBrand = strtoupper(trim((string) request('brand', request('market_brand', ''))));
    $requestedPriceBand = trim((string) request('price_band', request('market_price_band', '')));
    $extractModel = function (?string $text): string {
        $raw = strtoupper((string) $text);
        $patterns = [
            '/(IPHONE\s+\d+(?:\s+(?:PRO\s+MAX|PRO|PLUS|MINI|MAX))?)/u',
            '/(SAMSUNG\s+(?:GALAXY\s+)?[A-Z]\d+\+?)/u',
            '/(REDMI\s+NOTE\s+\d+\+?)/u',
            '/(XIAOMI\s+\d+[A-Z0-9\s]*)/u',
            '/(POCO\s+[A-Z0-9\s]+)/u',
            '/(INFINIX\s+[A-Z0-9\s]+)/u',
            '/(TECNO\s+[A-Z0-9\s]+)/u',
            '/(VIVO\s+[A-Z0-9\s]+)/u',
            '/(OPPO\s+[A-Z0-9\s]+)/u',
            '/(REALME\s+[A-Z0-9\s]+)/u',
        ];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $raw, $match)) {
                return preg_replace('/\s+/', ' ', trim($match[1]));
            }
        }

        return '';
    };
    $cleanModelName = function (?string $name) use ($extractModel, $marketProductLabel): string {
        $clean = preg_replace('/\s*\[.*?\]\s*/u', ' ', (string) $name);
        $clean = preg_replace('/\b(brand\s*new|baru|new|second|seken|bekas|used|garansi\s*resmi|garansi\s*on|garansi\s*off|official|original)\b/iu', ' ', $clean);
        $clean = preg_replace('/\s*[-:]+\s*/u', ' ', $clean);
        $clean = preg_replace('/\s+/', ' ', trim(strtoupper($clean)));

        return $extractModel($clean) ?: ($clean ?: $marketProductLabel($name));
    };
    $brandFromModel = function (?string $model): string {
        $text = strtoupper(trim((string) $model));
        foreach (['IPHONE', 'SAMSUNG', 'OPPO', 'VIVO', 'XIAOMI', 'REDMI', 'POCO', 'REALME', 'INFINIX', 'TECNO', 'ITEL', 'NOKIA', 'HUAWEI', 'HONOR'] as $token) {
            if (preg_match('/(^|[^A-Z0-9])'.preg_quote($token, '/').'([^A-Z0-9]|$)/u', $text)) {
                return $token;
            }
        }
        $clean = preg_replace('/^(BARU|BEKAS|SECOND|NEW|BNIB|EX DISPLAY)\s*[-:]*\s*/u', '', $text);
        $parts = preg_split('/\s+/', trim($clean));

        return $parts[0] ?? '-';
    };
    $priceBand = function (float $harga): string {
        if ($harga < 5_000_000) return '<5jt';
        if ($harga < 10_000_000) return '5-10jt';
        if ($harga < 15_000_000) return '10-15jt';
        if ($harga < 20_000_000) return '15-20jt';
        return '20jt+';
    };
    $conditionDetail = function (?string $kondisi, ?string $detail): string {
        $condition = strtoupper(trim((string) $kondisi));
        $conditionDetail = strtoupper(trim((string) $detail));
        if ($conditionDetail !== '') return $conditionDetail;
        return match ($condition) {
            'SECOND_RESMI' => 'EX_IBOX',
            'SECOND_BEACUKAI' => 'BEACUKAI',
            'SECOND' => 'SECOND',
            'NEW_OFFICIAL' => 'GARANSI_RESMI',
            'NEW' => 'NEW',
            default => '-',
        };
    };
    $allRows = $botDb->table('external_market_prices')->orderBy('nama')->orderBy('varian')->get()
        ->map(function ($r) use ($cleanModelName, $brandFromModel, $priceBand, $conditionDetail) {
            $harga = (float) ($r->harga ?? 0);
            $model = $cleanModelName($r->nama ?? '');
            $kondisi = strtoupper(trim((string) ($r->kondisi ?? ''))) ?: '-';

            return [
                'model' => $model,
                'brand' => $brandFromModel($model ?: ($r->nama ?? '')),
                'harga' => $harga,
                'sumber' => trim((string) ($r->sumber ?? '-')),
                'varian' => trim((string) ($r->varian ?? '')),
                'kondisi' => $kondisi,
                'kondisi_raw' => $kondisi,
                'kondisi_detail' => $conditionDetail($kondisi, $r->kondisi_detail ?? ''),
                'raw_name' => preg_replace('/\s+/', ' ', trim(strtoupper((string) ($r->nama ?? '')))),
                'raw_condition' => mb_substr((string) ($r->raw_condition ?? ''), 0, 120),
                'price_band' => $priceBand($harga),
                'captured_at' => $r->captured_at ?? null,
            ];
        })
        ->filter(fn ($row) => $row['harga'] > 0 && $row['model'] !== '')
        ->values();
    $baseFilterRows = $allRows;
    if ($requestedSource !== '') {
        $baseFilterRows = $baseFilterRows->filter(fn ($row) => strtolower($row['sumber']) === strtolower($requestedSource))->values();
    }
    if ($requestedPriceBand !== '') {
        $baseFilterRows = $baseFilterRows->filter(fn ($row) => $row['price_band'] === $requestedPriceBand)->values();
    }
    $filterOptions = [
        'brands' => $baseFilterRows->pluck('brand')->filter(fn ($v) => trim((string) $v) !== '' && $v !== '-')->unique()->sort()->take(100)->values(),
        'sources' => $allRows->pluck('sumber')->filter(fn ($v) => trim((string) $v) !== '' && $v !== '-')->unique()->sort()->take(20)->values(),
        'kondisi_details' => $baseFilterRows->pluck('kondisi_detail')->filter(fn ($v) => trim((string) $v) !== '' && $v !== '-')->unique()->sort()->take(20)->values(),
        'price_bands' => ['<5jt', '5-10jt', '10-15jt', '15-20jt', '20jt+'],
    ];
    $filteredRows = $requestedBrand !== ''
        ? $baseFilterRows->filter(fn ($row) => strtoupper($row['brand']) === $requestedBrand)->values()
        : $baseFilterRows;
    $sources = $filteredRows->pluck('sumber')->unique()->sort()->values();
    $brands = $filteredRows->pluck('brand')->filter(fn ($v) => trim((string) $v) !== '' && $v !== '-')->unique()->sort()->values();
    $bySource = $filteredRows->groupBy('sumber')->map(fn ($g) => $g->count());
    $median = function ($rows): float {
        $values = $rows->pluck('harga')->sort()->values();
        $count = $values->count();
        if ($count === 0) return 0;
        $mid = intdiv($count, 2);
        return $count % 2 === 0 ? (((float) $values[$mid - 1] + (float) $values[$mid]) / 2) : (float) $values[$mid];
    };
    $topModels = $filteredRows->groupBy('model')
        ->map(fn ($g, $model) => ['label' => $model, 'nilai' => $g->count(), 'value' => $g->count(), 'median_price' => (int) round($median($g))])
        ->sortBy([['nilai', 'desc'], ['median_price', 'desc']])
        ->take(10)
        ->values();
    $topBrands = $filteredRows->groupBy('brand')
        ->filter(fn ($g, $brand) => trim((string) $brand) !== '')
        ->map(fn ($g, $brand) => ['label' => $brand, 'nilai' => $g->count(), 'value' => $g->count(), 'median_price' => (int) round($median($g))])
        ->sortBy([['nilai', 'desc'], ['median_price', 'desc']])
        ->take(10)
        ->values();
    $priceBands = $filteredRows->groupBy('price_band')
        ->map(fn ($g, $band) => ['label' => $band, 'nilai' => $g->count(), 'value' => $g->count()])
        ->sortBy(fn ($row) => array_search($row['label'], ['<5jt', '5-10jt', '10-15jt', '15-20jt', '20jt+'], true))
        ->values();
    $newEntries = $filteredRows->sortBy([
            ['harga', 'desc'],
            ['model', 'asc'],
        ])
        ->unique(fn ($row) => implode('|', [$row['model'], $row['sumber'], $row['varian'], $row['kondisi']]))
        ->map(fn ($row) => [
            'nama' => $row['model'],
            'model' => $row['model'],
            'produk' => $marketProductLabel($row['model'], $row['varian'], $row['kondisi']),
            'brand' => $row['brand'],
            'sumber' => $row['sumber'],
            'harga' => (int) round($row['harga']),
            'kondisi' => $row['kondisi'],
            'kondisi_raw' => $row['kondisi_raw'],
            'kondisi_detail' => $row['kondisi_detail'],
            'varian' => $row['varian'],
            'updated_at' => $row['captured_at'],
        ])
        ->values();
    $sourceRows = $bySource->map(fn ($count, $source) => [
        'source' => $source,
        'count' => $count,
        'ok' => $count > 0,
        'cache' => null,
        'error' => null,
    ])->values();
    $summary = [
        'data_scope' => 'external_only',
        'internal_sources_used' => [],
        'listing_count' => $filteredRows->count(),
        'source_count' => $sources->count(),
        'brand_count' => $brands->count(),
        'model_count' => $filteredRows->pluck('model')->unique()->count(),
        'active_filters' => [
            'brand' => $requestedBrand ?: null,
            'source' => $requestedSource ?: null,
            'price_band' => $requestedPriceBand ?: null,
        ],
    ];
    $externalReport = [
        'summary' => $summary,
        'top_models' => $topModels,
        'top_brands' => $topBrands,
        'price_bands' => $priceBands,
        'new_entries' => $newEntries,
        'sources' => $sourceRows,
        'filters' => $filterOptions,
    ];
    return response()->json([
        'rows'      => $newEntries,
        'sources'   => $sources,
        'brands'    => $brands,
        'by_source' => $bySource,
        'source_rows' => $sourceRows,
        'summary' => $summary,
        'top_models' => $topModels,
        'top_brands' => $topBrands,
        'price_bands' => $priceBands,
        'new_entries' => $newEntries,
        'filters' => $filterOptions,
        'external_market_report' => $externalReport,
        'total'     => $filteredRows->count(),
    ]);
});

Route::get('/api/market/eksternal-changes', function () use ($marketProductLabel) {
    $botDb     = DB::connection('sqlite_bot');
    $source    = request('source', '');
    $direction = request('direction', 'all');
    $limit     = max(1, min(500, (int) request('limit', 100)));
    $ackRaw    = request('acknowledged', 'all');
    $query     = $botDb->table('external_market_price_changes')->orderByDesc('detected_at');
    if ($ackRaw !== '' && $ackRaw !== 'all') {
        $query->where('acknowledged', (int) ($ackRaw === '1'));
    }
    if ($source !== '') {
        $sourceMap  = ['goodponsel' => 'goodponsel', 'goodponselbali' => 'goodponsel', 'devstore' => 'devstore', 'rumahgadget' => 'rumahgadgetbali', 'rumahgadgetbali' => 'rumahgadgetbali'];
        $sourceKey = $sourceMap[strtolower(preg_replace('/[^a-z0-9]+/', '', (string) $source))] ?? strtolower(preg_replace('/[^a-z0-9]+/', '', (string) $source));
        $query->where('source_key', $sourceKey);
    }
    if ($direction === 'naik')  $query->where('selisih', '>', 0);
    if ($direction === 'turun') $query->where('selisih', '<', 0);
    $fetchLimit = max($limit, min($limit * 20, 5000));
    $seen = [];
    $rows = $query->limit($fetchLimit)->get()->map(function ($r) use ($marketProductLabel, &$seen) {
        $dedupeKey = implode('|', [
            strtoupper(trim((string) ($r->nama ?? ''))),
            strtoupper(trim((string) ($r->varian ?? ''))),
            strtolower(trim((string) ($r->kondisi ?? ''))),
            strtolower(trim((string) ($r->sumber ?? ''))),
        ]);
        if (isset($seen[$dedupeKey])) {
            return null;
        }
        $seen[$dedupeKey] = true;

        return [
            'id'              => (int) ($r->_id ?? 0),
            'source_key'      => $r->source_key ?? '',
            'nama'            => $marketProductLabel($r->nama ?? '', $r->varian ?? '', $r->kondisi ?? ''),
            'produk'          => $marketProductLabel($r->nama ?? '', $r->varian ?? '', $r->kondisi ?? ''),
            'varian'          => $r->varian ?? '',
            'kondisi'         => $r->kondisi ?? '',
            'kondisi_detail'  => $r->kondisi_detail ?? '',
            'sumber'          => $r->sumber ?? '',
            'harga_lama'      => (int) ($r->harga_lama ?? 0),
            'harga_baru'      => (int) ($r->harga_baru ?? 0),
            'selisih'         => (int) ($r->selisih ?? 0),
            'selisih_pct'     => (float) ($r->selisih_pct ?? 0),
            'detected_at'     => $r->detected_at ?? null,
            'acknowledged'    => (int) ($r->acknowledged ?? 0),
        ];
    })->filter()->take($limit)->values();
    return response()->json([
        'rows'  => $rows->values(),
        'total' => $rows->count(),
    ]);
});

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

Route::get('/api/pricelist-products', function () use ($pricelistProductPayload) {
    $query = DB::table('pricelist_products')
        ->orderBy('source_sheet')
        ->orderBy('urut')
        ->orderBy('nama_produk');

    return response()->json(['data' => $query->get()->map(fn ($row) => $pricelistProductPayload($row))->values()]);
});

Route::post('/api/pricelist-products/sync', function (PricelistSheetImporter $importer) use ($logCrudActivity) {
    $beforeCount = DB::table('pricelist_products')->count();
    $summary = $importer->import();
    $afterCount = DB::table('pricelist_products')->count();

    $logCrudActivity('pricelist_products', 'sync', 'google-sheet-pricelist', null, [
        'count' => $beforeCount,
    ], [
        'count' => $afterCount,
        'summary' => $summary,
    ]);

    return response()->json($summary);
});

Route::put('/api/pricelist-products/{sourceId}', function (string $sourceId) use ($logCrudActivity) {
    $payload = request()->validate([
        'is_active' => ['nullable', 'boolean'],
        'harga_nasional' => ['nullable', 'integer'],
        'special_price' => ['nullable', 'integer'],
    ]);
    $before = DB::table('pricelist_products')->where('source_id', $sourceId)->first();
    abort_unless($before !== null, 404);

    $updates = ['updated_at' => now()];
    foreach (['is_active', 'harga_nasional', 'special_price'] as $field) {
        if (array_key_exists($field, $payload)) {
            $updates[$field] = $payload[$field];
        }
    }

    DB::table('pricelist_products')->where('source_id', $sourceId)->update($updates);
    $stored = DB::table('pricelist_products')->where('source_id', $sourceId)->first();
    $logCrudActivity('pricelist_products', 'update', $sourceId, is_numeric($stored->id ?? null) ? (int) $stored->id : null, (array) $before, (array) $stored);

    return response()->json(['status' => 'success', 'data' => $stored]);
});

Route::get('/api/catalog-templates', function () use ($catalogTemplatePayload) {
    return response()->json(['data' => DB::table('catalog_templates')->orderBy('format')->orderBy('name')->get()->map(fn ($row) => $catalogTemplatePayload($row))->values()]);
});

Route::post('/api/catalog-templates', function () use ($catalogTemplatePayload, $logCrudActivity, $makeSourceId) {
    $payload = request()->validate([
        'name' => ['required', 'string', 'max:120'],
        'format' => ['required', Rule::in(['story', 'feed', 'a4'])],
        'layout_config' => ['nullable', 'array'],
    ]);
    $width = $payload['format'] === 'a4' ? 1240 : 1080;
    $height = $payload['format'] === 'a4' ? 1754 : ($payload['format'] === 'feed' ? 1350 : 1920);
    $row = [
        'source_id' => $makeSourceId('CT', null),
        'name' => $payload['name'],
        'format' => $payload['format'],
        'canvas_width' => $width,
        'canvas_height' => $height,
        'layout_config' => json_encode($payload['layout_config'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ];
    DB::table('catalog_templates')->insert($row);
    $stored = DB::table('catalog_templates')->where('source_id', $row['source_id'])->first();
    $logCrudActivity('catalog_templates', 'create', $row['source_id'], is_numeric($stored->id ?? null) ? (int) $stored->id : null, null, (array) $stored);

    return response()->json(['status' => 'success', 'data' => $catalogTemplatePayload($stored)], 201);
});

Route::put('/api/catalog-templates/{sourceId}', function (string $sourceId) use ($catalogTemplatePayload, $logCrudActivity) {
    $payload = request()->validate([
        'name' => ['required', 'string', 'max:120'],
        'format' => ['required', Rule::in(['story', 'feed', 'a4'])],
        'layout_config' => ['nullable', 'array'],
        'is_active' => ['nullable', 'boolean'],
    ]);
    $before = DB::table('catalog_templates')->where('source_id', $sourceId)->first();
    abort_unless($before !== null, 404);
    $width = $payload['format'] === 'a4' ? 1240 : 1080;
    $height = $payload['format'] === 'a4' ? 1754 : ($payload['format'] === 'feed' ? 1350 : 1920);
    DB::table('catalog_templates')->where('source_id', $sourceId)->update([
        'name' => $payload['name'],
        'format' => $payload['format'],
        'canvas_width' => $width,
        'canvas_height' => $height,
        'layout_config' => json_encode($payload['layout_config'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        'is_active' => (bool) ($payload['is_active'] ?? true),
        'updated_at' => now(),
    ]);
    $stored = DB::table('catalog_templates')->where('source_id', $sourceId)->first();
    $logCrudActivity('catalog_templates', 'update', $sourceId, is_numeric($stored->id ?? null) ? (int) $stored->id : null, (array) $before, (array) $stored);

    return response()->json(['status' => 'success', 'data' => $catalogTemplatePayload($stored)]);
});

Route::delete('/api/catalog-templates/{sourceId}', $genericDelete('catalog_templates'));

Route::post('/api/catalog-templates/{sourceId}/background', function (string $sourceId, Request $request) use ($catalogTemplatePayload, $logCrudActivity) {
    $payload = $request->validate([
        'background' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
    ]);
    $before = DB::table('catalog_templates')->where('source_id', $sourceId)->first();
    abort_unless($before !== null, 404);
    $extension = strtolower((string) $payload['background']->getClientOriginalExtension());
    $filename = 'catalog-'.$sourceId.'-'.Str::lower(Str::random(12)).'.'.$extension;
    $directory = storage_path('app/public/catalog-templates');

    if (! File::isDirectory($directory)) {
        File::ensureDirectoryExists($directory);
    }

    $payload['background']->move($directory, $filename);

    if (filled($before->background_path)) {
        $oldPath = $directory.DIRECTORY_SEPARATOR.$before->background_path;
        if (File::exists($oldPath)) {
            File::delete($oldPath);
        }
    }

    DB::table('catalog_templates')->where('source_id', $sourceId)->update([
        'background_path' => $filename,
        'updated_at' => now(),
    ]);
    $stored = DB::table('catalog_templates')->where('source_id', $sourceId)->first();
    $logCrudActivity('catalog_templates', 'upload_background', $sourceId, is_numeric($stored->id ?? null) ? (int) $stored->id : null, (array) $before, (array) $stored);

    return response()->json(['status' => 'success', 'data' => $catalogTemplatePayload($stored)]);
});

Route::get('/api/catalog-templates/background/{filename}', function (string $filename) {
    abort_unless(preg_match('/^[A-Za-z0-9._-]+$/', $filename) === 1, 404);
    $path = storage_path('app/public/catalog-templates/'.$filename);
    abort_unless(File::exists($path), 404);

    return response()->file($path, ['Cache-Control' => 'public, max-age=86400']);
});

// Apple Catalog

$appleProductPayload = function ($row): array {
    $kondisi = [];
    if (filled($row->harga_kondisi)) {
        $decoded = json_decode($row->harga_kondisi, true);
        if (is_array($decoded)) {
            $kondisi = $decoded;
        }
    }
    return [
        'ID'             => $row->source_id,
        'source_sheet'   => $row->source_sheet,
        'urut'           => $row->urut,
        'model'          => $row->model,
        'storage'        => $row->storage,
        'harga_nasional' => $row->harga_nasional,
        'special_price'  => $row->special_price,
        'harga_kondisi'  => $kondisi,
        'is_active'      => (bool) $row->is_active,
    ];
};

Route::get('/api/apple-products', function () use ($appleProductPayload) {
    $sheet = request()->query('sheet');
    $query = DB::table('apple_products')->orderBy('source_sheet')->orderBy('urut');
    if ($sheet) {
        $query->where('source_sheet', strtoupper($sheet));
    }
    return response()->json(['data' => $query->get()->map(fn ($r) => $appleProductPayload($r))->values()]);
});

Route::post('/api/apple-products/sync', function (AppleSheetImporter $importer) use ($logCrudActivity) {
    $before = DB::table('apple_products')->count();
    $summary = $importer->import();
    $after = DB::table('apple_products')->count();
    $logCrudActivity('apple_products', 'sync', 'google-sheet-apple', null, ['count' => $before], ['count' => $after, 'summary' => $summary]);
    return response()->json(['status' => $summary['status'], 'data' => $summary]);
});

Route::get('/api/apple-images/{category}', function (string $category) {
    $base = base_path('resources/img/APPLE/'.strtoupper($category));
    if (! is_dir($base)) {
        return response()->json(['data' => []]);
    }
    $models = [];
    foreach (scandir($base) ?: [] as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }
        $modelDir = $base.'/'.$entry;
        if (is_dir($modelDir)) {
            $images = array_values(array_filter(scandir($modelDir) ?: [], fn ($f) => preg_match('/\.(png|jpg|jpeg|webp)$/i', $f)));
            if ($images) {
                sort($images);
                $catEncoded   = rawurlencode(strtoupper($category));
                $modelEncoded = rawurlencode($entry);
                $entryUpper   = strtoupper($entry);
                $prefix       = $entryUpper.' ';
                // sub-model: images inside this dir are product-named (contain digits/GEN after prefix)
                $isSubmodel = false;
                foreach ($images as $imgFile) {
                    $fileUpper = strtoupper(pathinfo($imgFile, PATHINFO_FILENAME));
                    if (str_starts_with($fileUpper, $prefix)) {
                        $suffix = substr($fileUpper, strlen($prefix));
                        if (preg_match('/\d|GEN/', $suffix)) {
                            $isSubmodel = true;
                            break;
                        }
                    }
                }
                if ($isSubmodel) {
                    foreach ($images as $imgFile) {
                        $imgUrl = '/api/apple-image/'.$catEncoded.'/'.$modelEncoded.'/'.rawurlencode($imgFile);
                        $models[] = ['model' => pathinfo($imgFile, PATHINFO_FILENAME), 'images' => [$imgUrl]];
                    }
                } else {
                    $imageUrls = array_map(fn ($f) => '/api/apple-image/'.$catEncoded.'/'.$modelEncoded.'/'.rawurlencode($f), $images);
                    $models[] = ['model' => $entry, 'images' => $imageUrls];
                }
            }
        } elseif (preg_match('/\.(png|jpg|jpeg|webp)$/i', $entry)) {
            $urlPath = rawurlencode(strtoupper($category)).'/'.rawurlencode($entry);
            $models[] = ['model' => pathinfo($entry, PATHINFO_FILENAME), 'images' => ['/api/apple-image/'.$urlPath]];
        }
    }
    return response()->json(['data' => $models]);
});

Route::get('/api/apple-image/{encodedPath}', function (string $encodedPath) {
    $relative = rawurldecode($encodedPath);
    $base = realpath(base_path('resources/img/APPLE'));
    if (! $base) {
        abort(404);
    }
    $resolved = realpath($base.'/'.$relative);
    if (! $resolved || ! str_starts_with($resolved, $base.DIRECTORY_SEPARATOR)) {
        abort(404);
    }
    abort_unless(File::exists($resolved), 404);
    $ext = strtolower(pathinfo($resolved, PATHINFO_EXTENSION));
    $mime = match ($ext) {
        'jpg', 'jpeg' => 'image/jpeg',
        'webp'        => 'image/webp',
        default       => 'image/png',
    };
    return response()->file($resolved, ['Content-Type' => $mime, 'Cache-Control' => 'public, max-age=86400']);
})->where('encodedPath', '.+');

Route::get('/api/android-images/{brand}', function (string $brand) {
    $base = realpath(base_path('resources/img/ANDROID/'.strtoupper($brand)));
    if (! $base || ! is_dir($base)) {
        return response()->json(['data' => []]);
    }

    $models = [];
    $scan = function (string $dir, string $rel = '') use (&$scan, &$models, $brand): void {
        foreach (scandir($dir) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') continue;
            $full = $dir.DIRECTORY_SEPARATOR.$entry;
            $path = $rel === '' ? $entry : $rel.'/'.$entry;
            if (is_dir($full)) {
                $scan($full, $path);
                continue;
            }
            if (! preg_match('/\.(png|jpg|jpeg|webp)$/i', $entry)) continue;
            $model = pathinfo($entry, PATHINFO_FILENAME);
            $urlPath = rawurlencode(strtoupper($brand)).'/'.collect(explode('/', $path))->map(fn ($p) => rawurlencode($p))->implode('/');
            $models[] = ['model' => $model, 'images' => ['/api/android-image/'.$urlPath]];
        }
    };
    $scan($base);

    return response()->json(['data' => $models]);
});

Route::get('/api/android-image/{encodedPath}', function (string $encodedPath) {
    $relative = rawurldecode($encodedPath);
    $base = realpath(base_path('resources/img/ANDROID'));
    if (! $base) abort(404);
    $resolved = realpath($base.'/'.$relative);
    if (! $resolved || ! str_starts_with($resolved, $base.DIRECTORY_SEPARATOR)) abort(404);
    abort_unless(File::exists($resolved), 404);
    $ext = strtolower(pathinfo($resolved, PATHINFO_EXTENSION));
    $mime = match ($ext) {
        'jpg', 'jpeg' => 'image/jpeg',
        'webp' => 'image/webp',
        default => 'image/png',
    };
    return response()->file($resolved, ['Content-Type' => $mime, 'Cache-Control' => 'public, max-age=86400']);
})->where('encodedPath', '.+');

// ── Image Repository ────────────────────────────────────────────────────────
$imgBase = realpath(base_path('resources/img'));

$imgRepoGuard = function () {
    $user = auth()->user();
    abort_unless($user && app(\App\Support\DashboardAuth::class)->canManageSettings($user), 403, 'Forbidden');
};

$imgRepoResolvePath = function (string $rel) use ($imgBase): string {
    if ($imgBase === false) abort(500, 'Image base directory not found');
    $rel = ltrim(str_replace(['..', '//'], ['', '/'], $rel), '/');
    $full = $rel === '' ? $imgBase : $imgBase.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $rel);
    $resolved = realpath($full) ?: $full;
    if (!str_starts_with(realpath($resolved) ?: $resolved, $imgBase)) abort(403, 'Path tidak diizinkan');
    return $resolved;
};

Route::get('/api/img-repo/browse', function () use ($imgRepoGuard, $imgRepoResolvePath) {
    $imgRepoGuard();
    $rel = (string) request()->query('path', '');
    $dir = $imgRepoResolvePath($rel);
    if (!is_dir($dir)) abort(404, 'Bukan direktori');
    $items = [];
    foreach (scandir($dir) as $name) {
        if ($name === '.' || $name === '..') continue;
        $full = $dir.DIRECTORY_SEPARATOR.$name;
        $isDir = is_dir($full);
        $itemRel = $rel === '' ? $name : $rel.'/'.$name;
        $items[] = [
            'name' => $name,
            'path' => $itemRel,
            'type' => $isDir ? 'dir' : 'file',
            'size' => $isDir ? null : filesize($full),
            'ext'  => $isDir ? null : strtolower(pathinfo($name, PATHINFO_EXTENSION)),
        ];
    }
    usort($items, fn($a, $b) => ($a['type'] === $b['type'] ? strnatcasecmp($a['name'], $b['name']) : ($a['type'] === 'dir' ? -1 : 1)));
    return response()->json(['path' => $rel, 'items' => $items]);
});

Route::get('/api/img-repo/serve', function () use ($imgRepoResolvePath) {
    $rel = (string) request()->query('path', '');
    $file = $imgRepoResolvePath($rel);
    abort_unless(File::exists($file) && is_file($file), 404);
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $mime = match ($ext) {
        'jpg', 'jpeg' => 'image/jpeg',
        'webp'        => 'image/webp',
        'gif'         => 'image/gif',
        'svg'         => 'image/svg+xml',
        default       => 'image/png',
    };
    return response()->file($file, ['Content-Type' => $mime, 'Cache-Control' => 'public, max-age=3600']);
});

Route::post('/api/img-repo/mkdir', function () use ($imgRepoGuard, $imgRepoResolvePath) {
    $imgRepoGuard();
    $parent = (string) request()->input('path', '');
    $name   = trim((string) request()->input('name', ''));
    if ($name === '' || preg_match('/[\/\\\\]/', $name)) abort(422, 'Nama folder tidak valid');
    $dir = $imgRepoResolvePath($parent === '' ? $name : $parent.'/'.$name);
    if (is_dir($dir)) abort(422, 'Folder sudah ada');
    mkdir($dir, 0755, true);
    return response()->json(['status' => 'success', 'path' => ($parent === '' ? $name : $parent.'/'.$name)]);
});

Route::post('/api/img-repo/rename', function () use ($imgRepoGuard, $imgRepoResolvePath) {
    $imgRepoGuard();
    $oldPath = (string) request()->input('path', '');
    $newName = trim((string) request()->input('name', ''));
    if ($newName === '' || preg_match('/[\/\\\\]/', $newName)) abort(422, 'Nama tidak valid');
    $src = $imgRepoResolvePath($oldPath);
    abort_unless(file_exists($src), 404, 'Item tidak ditemukan');
    $parentDir  = dirname($src);
    $dst        = $parentDir.DIRECTORY_SEPARATOR.$newName;
    if (file_exists($dst)) abort(422, 'Nama sudah dipakai');
    rename($src, $dst);
    $parentRel = dirname($oldPath);
    $newRel = ($parentRel === '.' || $parentRel === '') ? $newName : $parentRel.'/'.$newName;
    return response()->json(['status' => 'success', 'path' => $newRel]);
});

Route::delete('/api/img-repo/delete', function () use ($imgRepoGuard, $imgRepoResolvePath) {
    $imgRepoGuard();
    $rel = (string) request()->input('path', '');
    $target = $imgRepoResolvePath($rel);
    abort_unless(file_exists($target), 404, 'Item tidak ditemukan');
    if (is_dir($target)) {
        $rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($target, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($rii as $f) { $f->isDir() ? rmdir($f->getRealPath()) : unlink($f->getRealPath()); }
        rmdir($target);
    } else {
        unlink($target);
    }
    return response()->json(['status' => 'success']);
});

Route::post('/api/img-repo/upload', function () use ($imgRepoGuard, $imgRepoResolvePath) {
    $imgRepoGuard();
    $parent = (string) request()->input('path', '');
    $dir = $imgRepoResolvePath($parent);
    abort_unless(is_dir($dir), 404, 'Direktori tidak ditemukan');
    $files = request()->file('files');
    if (!$files) abort(422, 'Tidak ada file');
    if (!is_array($files)) $files = [$files];
    $saved = [];
    foreach ($files as $file) {
        abort_unless($file->isValid(), 422, 'File tidak valid');
        $ext      = strtolower($file->getClientOriginalExtension());
        $allowed  = ['png', 'jpg', 'jpeg', 'webp', 'gif'];
        abort_unless(in_array($ext, $allowed, true), 422, 'Ekstensi tidak diizinkan: '.$ext);
        $name = $file->getClientOriginalName();
        if (file_exists($dir.DIRECTORY_SEPARATOR.$name)) {
            $base = pathinfo($name, PATHINFO_FILENAME);
            $name = $base.'_'.time().'.'.$ext;
        }
        $file->move($dir, $name);
        $rel = $parent === '' ? $name : $parent.'/'.$name;
        $saved[] = $rel;
    }
    return response()->json(['status' => 'success', 'saved' => $saved]);
})->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
// ────────────────────────────────────────────────────────────────────────────

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
        ->map(fn ($r) => ['ID' => $r->id, 'Master_ID' => $r->resolved_master_id, 'Judul' => $r->title, 'Platform' => $r->platform, 'ID_Post' => $r->id_post, 'Tanggal_Publish' => $r->tanggal_publish, 'Views' => $r->views, 'Likes' => $r->likes, 'Comments' => $r->comments, 'Shares' => $r->shares]);

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
        ->map(fn ($r) => $fromDb($r, ['Nama_Produk' => $r->nama_produk, 'KATEGORI' => $r->kategori ?? null, 'BRAND' => $r->brand ?? null, 'SERI' => $r->seri ?? null, 'RAM' => $r->ram ?? null, 'INTERNAL' => $r->internal ?? null, 'SIZE' => $r->size ?? null, 'WARNA' => $r->warna ?? null, 'Harga_Distributor_1' => $r->harga_distributor_1, 'Harga_Distributor_2' => $r->harga_distributor_2, 'Harga_Kompetitor' => $r->harga_kompetitor, 'Margin_Profit' => $r->margin_profit, 'Harga_Rencana_Jual' => $r->harga_rencana_jual, 'Tanggal_Cek' => $r->tanggal_cek, 'Catatan' => $r->catatan]));

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
