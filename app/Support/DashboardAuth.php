<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DashboardAuth
{
    public function bootstrapConfiguredAdminSession(): ?User
    {
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

        return $user->refresh();
    }

    public function ensureConfiguredAdminUser(): ?User
    {
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

        return $user instanceof User ? $user : null;
    }

    public function logout(): void
    {
        Auth::guard('web')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }

    public function createUser(string $username, string $pin, ?string $name = null, ?string $email = null): User
    {
        $normalizedUsername = trim($username);
        $normalizedName = trim((string) ($name ?: $normalizedUsername));
        $normalizedEmail = trim((string) ($email ?: sprintf('%s@dashboard.local', Str::slug($normalizedUsername, '.'))));

        return User::query()->updateOrCreate(
            ['username' => $normalizedUsername],
            [
                'name' => $normalizedName,
                'email' => $normalizedEmail,
                'email_verified_at' => now(),
                'password' => Hash::make($pin),
            ],
        );
    }

    public function listUsers(): array
    {
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

    public function userPayload(User $user): array
    {
        $username = (string) ($user->username ?: $user->email ?: $user->name ?: 'user');
        $name = (string) ($user->name ?: $username);

        return [
            'ID' => $user->getKey(),
            'username' => $username,
            'nama' => $name,
            'email' => (string) $user->email,
            'role' => 'Super Admin',
            'outlet_id' => 'LOCAL-WEB',
        ];
    }
}
