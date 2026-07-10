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

    public function test_authenticated_user_can_create_dashboard_user_from_api(): void
    {
        $this->postJson('/api/auth/users', [
            'username' => 'supervisor',
            'nama' => 'Supervisor Shift',
            'email' => 'supervisor@example.com',
            'pin' => '987654',
            'pin_confirmation' => '987654',
        ])->assertOk()
            ->assertJsonPath('data.username', 'supervisor')
            ->assertJsonPath('data.nama', 'Supervisor Shift');

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
}
