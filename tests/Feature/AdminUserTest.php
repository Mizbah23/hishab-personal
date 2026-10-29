<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_user_with_a_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Account Team',
            'email' => 'accounts@example.com',
            'role' => 'accountant',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'is_active' => '1',
        ])->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'accounts@example.com',
            'role' => 'accountant',
            'is_active' => 1,
        ]);
    }

    public function test_inactive_users_cannot_sign_in(): void
    {
        $user = User::factory()->create([
            'email' => 'inactive@example.com',
            'password' => 'secret123',
            'role' => 'viewer',
            'is_active' => false,
        ]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'secret123',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_admin_cannot_remove_own_admin_access_or_delete_themselves(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->put(route('admin.users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => 'viewer',
            'is_active' => '1',
        ])->assertSessionHasErrors('role');

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'role' => 'admin', 'is_active' => 1]);

        $this->actingAs($admin)->delete(route('admin.users.destroy', $admin))
            ->assertSessionHasErrors('user');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }
}
