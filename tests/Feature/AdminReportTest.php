<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Income;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_a_filtered_report(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Income::create(['title' => 'January income', 'amount' => 10000, 'received_on' => '2026-01-15', 'category' => 'Retainer']);
        Income::create(['title' => 'February income', 'amount' => 8000, 'received_on' => '2026-02-15', 'category' => 'Project']);
        Expense::create(['title' => 'January expense', 'amount' => 2500, 'spent_on' => '2026-01-20', 'category' => 'Hosting']);

        $this->actingAs($admin)->get(route('admin.reports', [
            'from' => '2026-01-01',
            'to' => '2026-01-31',
        ]))->assertOk()
            ->assertSee('10,000.00')
            ->assertSee('2,500.00')
            ->assertSee('7,500.00')
            ->assertSee('Hosting')
            ->assertSee('Retainer');
    }

    public function test_report_rejects_an_invalid_date_range(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.reports', [
            'from' => '2026-02-01',
            'to' => '2026-01-01',
        ]))->assertSessionHasErrors('to');
    }

    public function test_monthly_report_has_one_group_per_month_including_empty_months(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Income::create(['title' => 'January payment', 'amount' => 20000, 'received_on' => '2026-01-15']);

        $this->actingAs($admin)->get(route('admin.reports', [
            'from' => '2026-01-15',
            'to' => '2026-03-20',
        ]))->assertViewHas('monthly', function ($months): bool {
            return $months->pluck('key')->all() === ['2026-01', '2026-02', '2026-03']
                && $months[0]['income'] === 20000.0
                && $months[1]['income'] === 0.0;
        });
    }
}
