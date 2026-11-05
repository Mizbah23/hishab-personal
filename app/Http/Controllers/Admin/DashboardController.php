<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $period = $request->validate(['period' => ['nullable', 'in:all,month,quarter,year']])['period'] ?? 'year';
        [$from, $to, $periodLabel] = $this->period($period);

        $incomeQuery = Income::query();
        $expenseQuery = Expense::query();
        $voucherQuery = Voucher::query();

        if ($from && $to) {
            $incomeQuery->whereBetween('received_on', [$from->toDateString(), $to->toDateString()]);
            $expenseQuery->whereBetween('spent_on', [$from->toDateString(), $to->toDateString()]);
            $voucherQuery->whereBetween('voucher_date', [$from->toDateString(), $to->toDateString()]);
        }

        $incomeEntries = Voucher::financialEntries('income', $from?->toDateString(), $to?->toDateString());
        $expenseEntries = Voucher::financialEntries('expense', $from?->toDateString(), $to?->toDateString());
        $income = (float) $incomeEntries->sum('amount');
        $expense = (float) $expenseEntries->sum('amount');
        $today = now()->toImmutable();
        $chartFrom = $today->startOfYear()->min($today->startOfMonth()->subMonths(2));
        $chartIncome = Voucher::financialEntries('income', $chartFrom->toDateString(), $today->toDateString())->groupBy(fn ($entry) => $entry->received_on->format('Y-m'));
        $chartExpense = Voucher::financialEntries('expense', $chartFrom->toDateString(), $today->toDateString())->groupBy(fn ($entry) => $entry->spent_on->format('Y-m'));
        $recentMonths = collect(range(2, 0))->map(function (int $offset) use ($today, $chartIncome, $chartExpense): array {
            $month = $today->startOfMonth()->subMonths($offset);
            $income = (float) $chartIncome->get($month->format('Y-m'), collect())->sum('amount');
            $expense = (float) $chartExpense->get($month->format('Y-m'), collect())->sum('amount');

            return ['label' => $month->format('M Y'), 'income' => $income, 'expense' => $expense, 'net' => $income - $expense];
        });
        $yearIncome = collect(range(1, 12))->map(fn (int $month): array => ['label' => $today->month($month)->format('M'), 'amount' => (float) $chartIncome->get($today->month($month)->format('Y-m'), collect())->sum('amount')]);

        return view('admin.dashboard', [
            'recentMonths' => $recentMonths,
            'yearIncome' => $yearIncome,
            'chartYear' => $today->year,
            'income' => $income,
            'expense' => $expense,
            'balance' => $income - $expense,
            'clientCount' => Client::where('is_active', true)->count(),
            'voucherCount' => $voucherQuery->count(),
            'incomeCount' => $incomeEntries->count(),
            'expenseCount' => $expenseEntries->count(),
            'period' => $period,
            'periodLabel' => $periodLabel,
            'recentIncomes' => $incomeQuery->with('client')->latest('received_on')->latest()->limit(5)->get(),
            'recentExpenses' => $expenseQuery->latest('spent_on')->latest()->limit(5)->get(),
        ]);
    }

    private function period(string $period): array
    {
        $today = now()->toImmutable();

        return match ($period) {
            'month' => [$today->copy()->startOfMonth(), $today->copy()->endOfDay(), 'This month'],
            'quarter' => [$today->copy()->startOfQuarter(), $today->copy()->endOfDay(), 'This quarter'],
            'all' => [null, null, 'All time'],
            default => [$today->copy()->startOfYear(), $today->copy()->endOfDay(), 'This year'],
        };
    }
}
