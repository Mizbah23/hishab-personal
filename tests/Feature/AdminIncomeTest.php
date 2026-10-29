<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Income;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminIncomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_record_income_for_a_client(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $client = Client::create(['name' => 'Northstar Labs', 'is_active' => true]);

        $this->actingAs($admin)->post(route('admin.incomes.store'), [
            'title' => 'Monthly retainer',
            'amount' => '125000.50',
            'received_on' => '2026-09-01',
            'category' => 'Retainer',
            'payment_method' => 'Bank transfer',
            'client_id' => $client->id,
        ])->assertRedirect(route('admin.incomes.index'));

        $this->assertDatabaseHas('incomes', [
            'title' => 'Monthly retainer',
            'amount' => 125000.50,
            'client_id' => $client->id,
            'created_by' => $admin->id,
        ]);
    }

    public function test_income_requires_a_positive_amount_and_valid_date(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.incomes.store'), [
            'title' => 'Invalid income',
            'amount' => '0',
            'received_on' => 'not-a-date',
        ])->assertSessionHasErrors(['amount', 'received_on']);
    }

    public function test_admin_can_update_and_remove_income(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $income = Income::create([
            'title' => 'Old income',
            'amount' => 100,
            'received_on' => '2026-08-01',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)->put(route('admin.incomes.update', $income), [
            'title' => 'Updated income',
            'amount' => 250,
            'received_on' => '2026-08-15',
        ])->assertRedirect(route('admin.incomes.index'));

        $this->assertDatabaseHas('incomes', ['id' => $income->id, 'title' => 'Updated income', 'amount' => 250]);

        $this->actingAs($admin)->delete(route('admin.incomes.destroy', $income))
            ->assertRedirect(route('admin.incomes.index'));

        $this->assertDatabaseMissing('incomes', ['id' => $income->id]);
    }
}
