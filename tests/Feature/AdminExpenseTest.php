<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminExpenseTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_record_an_expense(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.expenses.store'), [
            'title' => 'Cloud hosting',
            'amount' => '8500.75',
            'spent_on' => '2026-09-01',
            'category' => 'Hosting',
            'payment_method' => 'Card',
        ])->assertRedirect(route('admin.expenses.index'));

        $this->assertDatabaseHas('expenses', [
            'title' => 'Cloud hosting',
            'amount' => 8500.75,
            'category' => 'Hosting',
            'created_by' => $admin->id,
        ]);
    }

    public function test_expense_requires_a_positive_amount_and_valid_date(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.expenses.store'), [
            'title' => 'Invalid expense',
            'amount' => '-5',
            'spent_on' => 'not-a-date',
        ])->assertSessionHasErrors(['amount', 'spent_on']);
    }

    public function test_admin_can_update_and_remove_an_expense(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $expense = Expense::create([
            'title' => 'Old expense',
            'amount' => 100,
            'spent_on' => '2026-08-01',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)->put(route('admin.expenses.update', $expense), [
            'title' => 'Updated expense',
            'amount' => 250,
            'spent_on' => '2026-08-15',
        ])->assertRedirect(route('admin.expenses.index'));

        $this->assertDatabaseHas('expenses', ['id' => $expense->id, 'title' => 'Updated expense', 'amount' => 250]);

        $this->actingAs($admin)->delete(route('admin.expenses.destroy', $expense))
            ->assertRedirect(route('admin.expenses.index'));

        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
    }
}
