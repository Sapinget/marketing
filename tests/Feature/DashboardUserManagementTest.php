<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DashboardUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_artisan_command_creates_dashboard_user_that_can_login(): void
    {
        $this->artisan('marketing:user-create', [
            'username' => 'operator',
            'pin' => '4321',
            '--name' => 'Operator Toko',
            '--email' => 'operator@example.com',
        ])->assertExitCode(0);

        $this->assertDatabaseHas('users', [
            'username' => 'operator',
            'name' => 'Operator Toko',
            'email' => 'operator@example.com',
        ]);

        auth()->logout();
        $this->flushSession();

        $this->postJson('/api/auth/login', [
            'username' => 'operator',
            'pin' => '4321',
        ])->assertOk()
            ->assertJsonPath('user.username', 'operator');
    }

    public function test_authenticated_user_can_update_profile_name(): void
    {
        $user = User::factory()->create([
            'username' => 'kasir',
            'name' => 'Kasir Lama',
        ]);

        $this->actingAs($user);

        $this->putJson('/api/auth/profile', [
            'nama' => 'Kasir Baru',
        ])->assertOk()
            ->assertJsonPath('user.nama', 'Kasir Baru');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Kasir Baru',
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'table_name' => 'users',
            'action' => 'update',
            'record_key' => 'kasir',
            'user_id' => $user->id,
        ]);
    }

    public function test_authenticated_user_can_change_pin_and_login_with_new_pin(): void
    {
        $user = User::factory()->create([
            'username' => 'teknisi',
            'password' => Hash::make('111111'),
        ]);

        $this->actingAs($user);

        $this->putJson('/api/auth/pin', [
            'old_pin' => '111111',
            'new_pin' => '222222',
            'new_pin_confirmation' => '222222',
        ])->assertOk();

        auth()->logout();
        $this->flushSession();

        $this->postJson('/api/auth/login', [
            'username' => 'teknisi',
            'pin' => '222222',
        ])->assertOk()
            ->assertJsonPath('user.username', 'teknisi');

        $this->assertDatabaseHas('activity_logs', [
            'table_name' => 'users',
            'action' => 'update',
            'record_key' => 'teknisi',
        ]);
    }

    public function test_authenticated_user_can_list_dashboard_users(): void
    {
        User::factory()->create([
            'username' => 'kasir',
            'name' => 'Kasir',
            'email' => 'kasir@example.com',
        ]);

        $this->getJson('/api/auth/users')
            ->assertOk()
            ->assertJsonFragment([
                'username' => 'kasir',
                'nama' => 'Kasir',
                'email' => 'kasir@example.com',
            ]);
    }

    public function test_non_super_admin_cannot_list_dashboard_users(): void
    {
        $this->actingAsDashboardUser([
            'role' => 'operasional',
        ]);

        $this->getJson('/api/auth/users')
            ->assertForbidden()
            ->assertSee('Forbidden');
    }

    public function test_authenticated_user_can_create_dashboard_user_from_api(): void
    {
        $this->postJson('/api/auth/users', [
            'username' => 'supervisor',
            'nama' => 'Supervisor Shift',
            'email' => 'supervisor@example.com',
            'role' => 'kasir',
            'pin' => '987654',
            'pin_confirmation' => '987654',
        ])->assertOk()
            ->assertJsonPath('data.username', 'supervisor')
            ->assertJsonPath('data.nama', 'Supervisor Shift')
            ->assertJsonPath('data.role', 'Kasir')
            ->assertJsonPath('data.role_key', 'kasir');

        auth()->logout();
        $this->flushSession();

        $this->postJson('/api/auth/login', [
            'username' => 'supervisor',
            'pin' => '987654',
        ])->assertOk()
            ->assertJsonPath('user.username', 'supervisor');

        $this->assertDatabaseHas('activity_logs', [
            'table_name' => 'users',
            'action' => 'create',
            'record_key' => 'supervisor',
        ]);
    }

    public function test_authenticated_user_can_create_brand_ambasador_and_talent_roles(): void
    {
        foreach ([
            ['username' => 'brand-ambasador', 'role' => 'brand_ambasador', 'label' => 'Brand Ambasador'],
            ['username' => 'talent-role', 'role' => 'talent', 'label' => 'Talent'],
        ] as $index => $case) {
            $this->postJson('/api/auth/users', [
                'username' => $case['username'],
                'nama' => $case['label'],
                'email' => "{$case['username']}@example.com",
                'role' => $case['role'],
                'pin' => '987654',
                'pin_confirmation' => '987654',
            ])->assertOk()
                ->assertJsonPath('data.username', $case['username'])
                ->assertJsonPath('data.role', $case['label'])
                ->assertJsonPath('data.role_key', $case['role']);

            $this->assertDatabaseHas('users', [
                'username' => $case['username'],
                'role' => $case['role'],
            ]);
        }
    }

    public function test_non_super_admin_cannot_create_dashboard_user_from_api(): void
    {
        $this->actingAsDashboardUser([
            'role' => 'kasir',
        ]);

        $this->postJson('/api/auth/users', [
            'username' => 'supervisor',
            'nama' => 'Supervisor Shift',
            'email' => 'supervisor@example.com',
            'pin' => '987654',
            'pin_confirmation' => '987654',
        ])->assertForbidden()
            ->assertSee('Forbidden');
    }

    public function test_authenticated_user_can_update_dashboard_user_from_api(): void
    {
        $user = User::factory()->create([
            'username' => 'operator',
            'name' => 'Operator Lama',
            'email' => 'operator-lama@example.com',
            'password' => Hash::make('111111'),
        ]);

        $this->putJson("/api/auth/users/{$user->id}", [
            'username' => 'operator-baru',
            'nama' => 'Operator Baru',
            'email' => 'operator-baru@example.com',
            'role' => 'admin',
            'pin' => '222222',
            'pin_confirmation' => '222222',
        ])->assertOk()
            ->assertJsonPath('data.username', 'operator-baru')
            ->assertJsonPath('data.nama', 'Operator Baru')
            ->assertJsonPath('data.email', 'operator-baru@example.com')
            ->assertJsonPath('data.role', 'Admin')
            ->assertJsonPath('data.role_key', 'admin');

        auth()->logout();
        $this->flushSession();

        $this->postJson('/api/auth/login', [
            'username' => 'operator-baru',
            'pin' => '222222',
        ])->assertOk()
            ->assertJsonPath('user.username', 'operator-baru');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'username' => 'operator-baru',
            'name' => 'Operator Baru',
            'email' => 'operator-baru@example.com',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'table_name' => 'users',
            'action' => 'update',
            'record_key' => 'operator-baru',
            'record_id' => $user->id,
        ]);
    }

    public function test_non_super_admin_cannot_update_dashboard_user_from_api(): void
    {
        $this->actingAsDashboardUser([
            'role' => 'operasional',
        ]);

        $user = User::factory()->create([
            'username' => 'operator',
        ]);

        $this->putJson("/api/auth/users/{$user->id}", [
            'username' => 'operator-baru',
            'nama' => 'Operator Baru',
            'email' => 'operator-baru@example.com',
        ])->assertForbidden()
            ->assertSee('Forbidden');
    }

    public function test_authenticated_user_can_delete_dashboard_user_from_api(): void
    {
        $user = User::factory()->create([
            'username' => 'gudang',
            'name' => 'Admin Gudang',
            'email' => 'gudang@example.com',
        ]);

        $this->deleteJson("/api/auth/users/{$user->id}")
            ->assertOk()
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'table_name' => 'users',
            'action' => 'delete',
            'record_key' => 'gudang',
            'record_id' => $user->id,
        ]);
    }

    public function test_non_super_admin_cannot_delete_dashboard_user_from_api(): void
    {
        $this->actingAsDashboardUser([
            'role' => 'operasional',
        ]);

        $user = User::factory()->create([
            'username' => 'gudang',
        ]);

        $this->deleteJson("/api/auth/users/{$user->id}")
            ->assertForbidden()
            ->assertSee('Forbidden');
    }

    public function test_non_admin_cannot_access_activity_logs_endpoint(): void
    {
        $this->actingAsDashboardUser([
            'role' => 'operasional',
        ]);

        $this->getJson('/api/activity-logs')
            ->assertForbidden()
            ->assertSee('Forbidden');
    }

    public function test_admin_can_access_activity_logs_endpoint(): void
    {
        $this->actingAsDashboardUser([
            'role' => 'admin',
        ]);

        $this->getJson('/api/activity-logs')
            ->assertOk()
            ->assertJsonPath('data', []);
    }

    public function test_non_admin_cannot_manage_settings_or_raw_sheets(): void
    {
        $this->actingAsDashboardUser([
            'role' => 'kasir',
        ]);

        $this->getJson('/api/settings')
            ->assertForbidden()
            ->assertSee('Forbidden');

        $this->putJson('/api/settings', [
            'data' => [
                'Status' => ['DRAFT'],
            ],
        ])->assertForbidden()
            ->assertSee('Forbidden');

        $this->getJson('/api/raw-sheets/Nama_Stock')
            ->assertForbidden()
            ->assertSee('Forbidden');
    }

    public function test_admin_can_manage_settings_and_raw_sheets(): void
    {
        $this->actingAsDashboardUser([
            'role' => 'admin',
        ]);

        $this->getJson('/api/settings')
            ->assertOk();

        $this->putJson('/api/settings', [
            'data' => [
                'Status' => ['DRAFT', 'DONE'],
            ],
        ])->assertOk()
            ->assertJsonPath('data.Status.0', 'DRAFT');

        $this->getJson('/api/raw-sheets/Nama_Stock')
            ->assertOk();
    }
}
