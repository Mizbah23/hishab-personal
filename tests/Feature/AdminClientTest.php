<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminClientTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_client(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.clients.store'), [
            'name' => 'Nadia Rahman',
            'company_name' => 'Northstar Labs',
            'email' => 'nadia@northstar.test',
            'phone' => '+8801700000000',
            'is_active' => '1',
        ])->assertRedirect(route('admin.clients.index'));

        $this->assertDatabaseHas('clients', [
            'name' => 'Nadia Rahman',
            'company_name' => 'Northstar Labs',
            'email' => 'nadia@northstar.test',
        ]);
    }

    public function test_admin_can_update_and_remove_a_client(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $client = Client::create(['name' => 'Old Name', 'is_active' => true]);

        $this->actingAs($admin)->put(route('admin.clients.update', $client), [
            'name' => 'Updated Name',
            'is_active' => '1',
        ])->assertRedirect(route('admin.clients.index'));

        $this->assertDatabaseHas('clients', ['id' => $client->id, 'name' => 'Updated Name']);

        $this->actingAs($admin)->delete(route('admin.clients.destroy', $client))
            ->assertRedirect(route('admin.clients.index'));

        $this->assertSoftDeleted('clients', ['id' => $client->id]);
    }

    public function test_client_creation_requires_a_name(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.clients.store'), [
            'email' => 'missing-name@example.com',
        ])->assertSessionHasErrors('name');
    }
}
