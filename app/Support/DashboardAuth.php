<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DashboardAuth
{
    public static function sessionIdleTimeoutMinutes(): int
    {
        return (int) env('DASHBOARD_SESSION_IDLE_TIMEOUT_MINUTES', 60);
    }

    public const ROLE_SUPER_ADMIN = 'super_admin';

    public const ROLE_ADMIN = 'admin';

    public const ROLE_KASIR = 'kasir';

    public const ROLE_OPERASIONAL = 'operasional';

    public const ROLE_BRAND_AMBASADOR = 'brand_ambasador';

    public const ROLE_TALENT = 'talent';

    protected ?string $sessionFailureReason = null;

    protected function allowsConfiguredAdminBootstrap(): bool
    {
        return in_array((string) config('app.env'), ['local', 'testing'], true);
    }

    public function assignableRoles(): array
    {
        return [
            self::ROLE_SUPER_ADMIN,
            self::ROLE_ADMIN,
            self::ROLE_KASIR,
            self::ROLE_OPERASIONAL,
            self::ROLE_BRAND_AMBASADOR,
            self::ROLE_TALENT,
        ];
    }

    public function canManageUsers(?User $user): bool
    {
        return $user instanceof User && $user->role === self::ROLE_SUPER_ADMIN;
    }

    public function canAccessSensitiveLogs(?User $user): bool
    {
        return $user instanceof User && in_array($user->role, [
            self::ROLE_SUPER_ADMIN,
            self::ROLE_ADMIN,
        ], true);
    }

    public function canManageSettings(?User $user): bool
    {
        return $this->canAccessSensitiveLogs($user);
    }

    public function canManageRawSheets(?User $user): bool
    {
        return $this->canAccessSensitiveLogs($user);
    }

    public function canImportAnalytics(?User $user): bool
    {
        return $this->canAccessSensitiveLogs($user);
    }

    public function canManageMasterPlanData(?User $user): bool
    {
        return $this->canAccessSensitiveLogs($user);
    }

    public function canManageLpjkData(?User $user): bool
    {
        return $this->canAccessSensitiveLogs($user);
    }

    public function roleLabel(?string $role): string
    {
        return match ((string) $role) {
            self::ROLE_SUPER_ADMIN => 'Super Admin',
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_KASIR => 'Kasir',
            self::ROLE_OPERASIONAL => 'Operasional',
            self::ROLE_BRAND_AMBASADOR => 'Brand Ambasador',
            self::ROLE_TALENT => 'Talent',
            default => 'Operasional',
        };
    }

    public function bootstrapConfiguredAdminSession(): ?User
    {
        if (! $this->allowsConfiguredAdminBootstrap()) {
            return null;
        }

        if (Auth::check()) {
            $user = Auth::user();

            return $user instanceof User ? $user : null;
        }

        $user = $this->ensureConfiguredAdminUser();

        if (! $user instanceof User) {
            return null;
        }

        Auth::login($user);
        request()->session()->regenerate();
        $this->touchPresence($user, 'login');

        return $user->refresh();
    }

    public function ensureConfiguredAdminUser(): ?User
    {
        if (! $this->allowsConfiguredAdminBootstrap()) {
            return null;
        }

        $username = trim((string) env('TEST_ADMIN_USERNAME', ''));
        $pin = (string) env('TEST_ADMIN_PIN', '');

        if ($username === '' || $pin === '') {
            return null;
        }

        $email = sprintf('%s@dashboard.local', Str::slug($username, '.'));
        $user = User::query()->where('username', $username)->first();

        if ($user === null) {
            try {
                return User::query()->create([
                    'username' => $username,
                    'name' => $username,
                    'email' => $email,
                    'role' => self::ROLE_SUPER_ADMIN,
                    'email_verified_at' => now(),
                    'password' => Hash::make($pin),
                    'remember_token' => Str::random(10),
                ]);
            } catch (UniqueConstraintViolationException) {
                $user = User::query()->where('username', $username)->first();
            }
        }

        $updates = [];

        if (! Hash::check($pin, $user->password)) {
            $updates['password'] = Hash::make($pin);
        }

        if (blank($user->name)) {
            $updates['name'] = $username;
        }

        if (blank($user->email)) {
            $updates['email'] = $email;
        }

        if (blank($user->role)) {
            $updates['role'] = self::ROLE_SUPER_ADMIN;
        }

        if ($updates !== []) {
            $user->forceFill($updates)->save();
            $user->refresh();
        }

        return $user;
    }

    public function attemptLogin(string $username, string $pin): ?User
    {
        $this->ensureConfiguredAdminUser();

        $credentials = [
            'username' => trim($username),
            'password' => $pin,
        ];

        if (! Auth::attempt($credentials)) {
            return null;
        }

        request()->session()->regenerate();

        $user = Auth::user();

        if ($user instanceof User) {
            $this->touchPresence($user, 'login');
        }

        return $user instanceof User ? $user : null;
    }

    public function logout(): void
    {
        $user = Auth::user();

        if ($user instanceof User) {
            $this->markOffline($user, 'logout');
        }

        Auth::guard('web')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }

    public function ensureActiveSession(Request $request): bool
    {
        $this->sessionFailureReason = null;
        $user = Auth::user();

        if (! $user instanceof User) {
            return false;
        }

        $now = now();
        $lastActivityTimestamp = $request->session()->get('dashboard_last_activity_at');
        $sessionExpiresAt = $user->session_expires_at;

        if (is_numeric($lastActivityTimestamp)) {
            $lastActivity = now()->setTimestamp((int) $lastActivityTimestamp);
            if ($lastActivity->diffInSeconds($now, false) > self::sessionIdleTimeoutMinutes() * 60) {
                $this->expireSession($request, $user);

                return false;
            }
        } elseif ($sessionExpiresAt && $sessionExpiresAt->lte($now)) {
            $this->expireSession($request, $user);

            return false;
        }

        $this->touchPresence($user);

        return true;
    }

    public function touchPresence(User $user, string $event = 'heartbeat'): User
    {
        $now = now();
        $expiresAt = $now->copy()->addMinutes(self::sessionIdleTimeoutMinutes());

        request()->session()->put('dashboard_last_activity_at', $now->timestamp);

        $user->forceFill([
            'is_online' => true,
            'last_seen_at' => $now,
            'session_expires_at' => $expiresAt,
        ])->save();

        if ($event === 'login') {
            $this->logSessionEvent($user, $event, [
                'session_expires_at' => $expiresAt->toIso8601String(),
            ]);
        }

        return $user->refresh();
    }

    public function markOffline(User $user, string $event): User
    {
        $user->forceFill([
            'is_online' => false,
            'session_expires_at' => null,
            'last_seen_at' => now(),
        ])->save();

        request()->session()->forget('dashboard_last_activity_at');
        $this->logSessionEvent($user, $event);

        return $user->refresh();
    }

    protected function expireSession(Request $request, User $user): void
    {
        $this->sessionFailureReason = 'expired';
        $this->markOffline($user, 'timeout');
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    public function sessionFailurePayload(): array
    {
        if ($this->sessionFailureReason === 'expired') {
            return [
                'message' => sprintf(
                    'Sesi login berakhir karena tidak ada aktivitas selama %d menit.',
                    self::sessionIdleTimeoutMinutes()
                ),
                'expired' => true,
            ];
        }

        return [
            'message' => 'Unauthenticated.',
        ];
    }

    public function pruneExpiredPresence(): void
    {
        User::query()
            ->where('is_online', true)
            ->whereNotNull('session_expires_at')
            ->where('session_expires_at', '<=', now())
            ->get()
            ->each(fn (User $user) => $this->markOffline($user, 'timeout'));
    }

    protected function logSessionEvent(User $user, string $event, ?array $payload = null): void
    {
        if (! Schema::hasTable('activity_logs')) {
            return;
        }

        DB::table('activity_logs')->insert([
            'user_id' => $user->getKey(),
            'actor_label' => $user->username ?: $user->email ?: $user->name,
            'table_name' => 'auth_sessions',
            'action' => $event,
            'record_key' => (string) ($user->username ?: $user->email ?: $user->getKey()),
            'record_id' => $user->getKey(),
            'before_payload' => null,
            'after_payload' => $payload === null ? null : json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'created_at' => now(),
        ]);
    }

    public function createUser(string $username, string $pin, ?string $name = null, ?string $email = null, ?string $role = null): User
    {
        $normalizedUsername = trim($username);
        $normalizedName = trim((string) ($name ?: $normalizedUsername));
        $normalizedEmail = trim((string) ($email ?: sprintf('%s@dashboard.local', Str::slug($normalizedUsername, '.'))));
        $normalizedRole = in_array((string) $role, $this->assignableRoles(), true)
            ? (string) $role
            : self::ROLE_OPERASIONAL;

        return User::query()->create([
            'username' => $normalizedUsername,
            'name' => $normalizedName,
            'email' => $normalizedEmail,
            'role' => $normalizedRole,
            'email_verified_at' => now(),
            'password' => Hash::make($pin),
        ]);
    }

    public function updateUser(User $user, string $username, ?string $pin = null, ?string $name = null, ?string $email = null, ?string $role = null): User
    {
        $normalizedUsername = trim($username);
        $normalizedName = trim((string) ($name ?: $normalizedUsername));
        $normalizedEmail = trim((string) ($email ?: sprintf('%s@dashboard.local', Str::slug($normalizedUsername, '.'))));
        $normalizedRole = in_array((string) $role, $this->assignableRoles(), true)
            ? (string) $role
            : $user->role;

        $updates = [
            'username' => $normalizedUsername,
            'name' => $normalizedName,
            'email' => $normalizedEmail,
            'role' => $normalizedRole,
            'email_verified_at' => now(),
        ];

        if ($pin !== null && $pin !== '') {
            $updates['password'] = Hash::make($pin);
        }

        $user->forceFill($updates)->save();

        return $user->refresh();
    }

    public function deleteUser(User $user): void
    {
        $user->delete();
    }

    public function listUsers(): array
    {
        $this->pruneExpiredPresence();

        return User::query()
            ->orderBy('username')
            ->get()
            ->map(fn (User $user) => $this->userPayload($user))
            ->all();
    }

    public function updateProfileName(User $user, string $name): User
    {
        $user->forceFill([
            'name' => trim($name),
        ])->save();

        return $user->refresh();
    }

    public function changePin(User $user, string $oldPin, string $newPin): bool
    {
        if (! Hash::check($oldPin, $user->password)) {
            return false;
        }

        $user->forceFill([
            'password' => Hash::make($newPin),
        ])->save();

        return true;
    }

    public function avatarUrl(?User $user): ?string
    {
        if (! $user instanceof User || blank($user->avatar)) {
            return null;
        }

        $storagePath = storage_path('app/public/avatars/'.$user->avatar);

        if (! file_exists($storagePath)) {
            return null;
        }

        return '/api/auth/avatar/'.rawurlencode($user->avatar);
    }

    public function updateUserAvatar(User $user, string $filename): User
    {
        $user->forceFill([
            'avatar' => $filename,
        ])->save();

        return $user->refresh();
    }

    public function userPayload(User $user): array
    {
        $username = (string) ($user->username ?: $user->email ?: $user->name ?: 'user');
        $name = (string) ($user->name ?: $username);

        return [
            'ID' => $user->getKey(),
            'username' => $username,
            'nama' => $name,
            'email' => (string) $user->email,
            'role' => $this->roleLabel($user->role),
            'role_key' => (string) $user->role,
            'avatar_url' => $this->avatarUrl($user),
            'is_online' => (bool) $user->is_online,
            'last_seen_at' => $this->serializeDateTime($user->last_seen_at),
            'session_expires_at' => $this->serializeDateTime($user->session_expires_at),
            'outlet_id' => 'LOCAL-WEB',
        ];
    }

    protected function serializeDateTime(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return $value instanceof \DateTimeInterface
            ? Carbon::instance($value)->toIso8601String()
            : Carbon::parse($value)->toIso8601String();
    }
}
