<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Income;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_open_the_admin_dashboard(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_sign_in_and_open_the_dashboard(): void
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => 'admin',
        ]);

        $this->post(route('login.store'), [
            'email' => 'admin@example.com',
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Financial overview')
            ->assertSee('Total income');
    }

    public function test_dashboard_period_filter_limits_financial_summary(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Income::create(['title' => 'Current income', 'amount' => 100, 'received_on' => '2026-08-31']);
        Income::create(['title' => 'Older income', 'amount' => 900, 'received_on' => '2025-12-31']);
        Expense::create(['title' => 'Current expense', 'amount' => 25, 'spent_on' => '2026-08-31']);

        $this->actingAs($admin)->get(route('admin.dashboard', ['period' => 'year']))
            ->assertOk()
            ->assertSee('100.00')
            ->assertSee('25.00')
            ->assertSee('75.00')
            ->assertDontSee('Older income');
    }
}
