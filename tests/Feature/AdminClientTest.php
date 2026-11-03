<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminClientTest extends TestCase
{
    public function test_client_renewal_details_are_saved(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->post(route('admin.clients.store'), [
            'name' => 'Renewal client',
            'url' => 'https://example.com',
            'renewal_date' => '2027-01-15',
            'renewal_amount' => '1500.50',
        ])->assertRedirect(route('admin.clients.index'));
        $client = Client::where('name', 'Renewal client')->firstOrFail();
        $this->assertSame('https://example.com', $client->url);
        $this->assertSame('2027-01-15', $client->renewal_date->format('Y-m-d'));
        $this->assertSame('1500.50', $client->renewal_amount);
    }

    public function test_pending_migration_blocks_work_until_admin_updates(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        DB::table('migrations')->where('migration', '2026_11_03_130439_add_renewal_fields_to_clients_table')->delete();
        Schema::table('clients', function (Blueprint $table): void {
            $table->dropColumn(['url', 'renewal_date', 'renewal_amount']);
        });
        $this->actingAs($admin)->get(route('admin.clients.create'))->assertRedirect(route('admin.database-update'));
        $this->get(route('admin.database-update'))->assertSee('Database update required');
        $this->post(route('admin.database-update.store'))->assertRedirect(route('admin.dashboard'));
        $this->get(route('admin.clients.create'))->assertOk();
    }

    public function test_non_admin_cannot_run_database_updates(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer']);
        $this->actingAs($viewer)->post(route('admin.database-update.store'))->assertForbidden();
    }

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
