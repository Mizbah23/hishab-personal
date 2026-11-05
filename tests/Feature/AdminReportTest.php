<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Income;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_posted_vouchers_count_once_in_dashboard_and_reports(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->travelTo('2026-11-05 12:00:00');
        $voucher = Voucher::create(['voucher_number' => 'LINKED', 'type' => 'income', 'status' => 'posted', 'amount' => 100, 'voucher_date' => '2026-11-05']);
        Income::create(['title' => 'Linked', 'amount' => 100, 'received_on' => '2026-11-05', 'voucher_id' => $voucher->id]);
        foreach (['posted' => 200, 'draft' => 900, 'cancelled' => 800] as $status => $amount) {
            Voucher::create(['voucher_number' => $status, 'type' => 'income', 'status' => $status, 'amount' => $amount, 'voucher_date' => '2026-11-05']);
        }
        Voucher::create(['voucher_number' => 'EXP', 'type' => 'expense', 'status' => 'posted', 'amount' => 50, 'voucher_date' => '2026-11-05']);
        Voucher::create(['voucher_number' => 'GENERAL', 'type' => 'general', 'status' => 'posted', 'amount' => 700, 'voucher_date' => '2026-11-05']);

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertViewHas('balance', 250.0);
        $this->get(route('admin.reports'))->assertViewHas('incomeTotal', 300.0)->assertViewHas('expenseTotal', 50.0)->assertViewHas('balance', 250.0);
        $this->get(route('admin.reports.income'))->assertViewHas('total', 300.0);
        $this->get(route('admin.reports.expense'))->assertViewHas('total', 50.0);
        $this->get(route('admin.reports.ledger'))->assertViewHas('total', 250.0);
        $this->get(route('admin.reports', ['from' => '2026-11-06', 'to' => '2026-11-07']))->assertViewHas('balance', 0.0);
    }

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
            ->assertSee('Retainer')
            ->assertDontSee('Date-wise movement');
    }

    public function test_date_wise_income_report_only_shows_income_in_the_selected_range(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Income::create(['title' => 'January income', 'amount' => 10000, 'received_on' => '2026-01-15']);
        Income::create(['title' => 'February income', 'amount' => 8000, 'received_on' => '2026-02-15']);
        Expense::create(['title' => 'January expense', 'amount' => 2500, 'spent_on' => '2026-01-15']);

        $this->actingAs($admin)->get(route('admin.reports.income', [
            'from' => '2026-01-01',
            'to' => '2026-01-31',
        ]))->assertOk()
            ->assertSee('Date-wise income')
            ->assertSee('15 Jan 2026')
            ->assertSee('10,000.00')
            ->assertDontSee('8,000.00')
            ->assertDontSee('2,500.00');
    }

    public function test_date_wise_expense_report_only_shows_expenses_in_the_selected_range(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Expense::create(['title' => 'January expense', 'amount' => 2500, 'spent_on' => '2026-01-15']);
        Expense::create(['title' => 'February expense', 'amount' => 1800, 'spent_on' => '2026-02-15']);
        Income::create(['title' => 'January income', 'amount' => 10000, 'received_on' => '2026-01-15']);

        $this->actingAs($admin)->get(route('admin.reports.expense', [
            'from' => '2026-01-01',
            'to' => '2026-01-31',
        ]))->assertOk()
            ->assertSee('Date-wise expense')
            ->assertSee('15 Jan 2026')
            ->assertSee('2,500.00')
            ->assertDontSee('1,800.00')
            ->assertDontSee('10,000.00');
    }

    public function test_date_wise_ledger_report_shows_income_expense_and_daily_net_balance(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Income::create(['title' => 'January income', 'amount' => 10000, 'received_on' => '2026-01-15']);
        Expense::create(['title' => 'January expense', 'amount' => 2500, 'spent_on' => '2026-01-15']);

        $this->actingAs($admin)->get(route('admin.reports.ledger', [
            'from' => '2026-01-01',
            'to' => '2026-01-31',
        ]))->assertOk()
            ->assertSee('Date-wise ledger')
            ->assertSee('15 Jan 2026')
            ->assertSee('10,000.00')
            ->assertSee('2,500.00')
            ->assertSee('7,500.00');
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
