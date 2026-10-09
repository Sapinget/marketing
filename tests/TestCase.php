<?php

namespace Tests;

use App\Models\User;
use App\Support\DashboardAuth;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    protected bool $authenticateDashboard = true;

    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();

        if ($this->authenticateDashboard && Schema::hasTable('users')) {
            $this->actingAsDashboardUser();
        }
    }

    protected function actingAsDashboardUser(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $now = now();

        $this->actingAs($user);
        $this->withSession([
            'dashboard_last_activity_at' => $now->timestamp,
            'dashboard_active_session_id' => 'test-dashboard-session-'.$user->getKey(),
        ]);
        $user->forceFill([
            'is_online' => true,
            'last_seen_at' => $now,
            'session_expires_at' => $now->copy()->addMinutes(DashboardAuth::sessionIdleTimeoutMinutes()),
            'active_session_id' => 'test-dashboard-session-'.$user->getKey(),
        ])->save();

        return $user;
    }
}
