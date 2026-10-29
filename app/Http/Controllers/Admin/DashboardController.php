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

        $income = (float) (clone $incomeQuery)->sum('amount');
        $expense = (float) (clone $expenseQuery)->sum('amount');

        return view('admin.dashboard', [
            'income' => $income,
            'expense' => $expense,
            'balance' => $income - $expense,
            'clientCount' => Client::where('is_active', true)->count(),
            'voucherCount' => $voucherQuery->count(),
            'incomeCount' => $incomeQuery->count(),
            'expenseCount' => $expenseQuery->count(),
            'period' => $period,
            'periodLabel' => $periodLabel,
            'recentIncomes' => $incomeQuery->with('client')->latest('received_on')->latest()->limit(5)->get(),
            'recentExpenses' => $expenseQuery->latest('spent_on')->latest()->limit(5)->get(),
        ]);
    }

    private function period(string $period): array
    {
        $today = now();

        return match ($period) {
            'month' => [$today->copy()->startOfMonth(), $today->copy()->endOfDay(), 'This month'],
            'quarter' => [$today->copy()->startOfQuarter(), $today->copy()->endOfDay(), 'This quarter'],
            'all' => [null, null, 'All time'],
            default => [$today->copy()->startOfYear(), $today->copy()->endOfDay(), 'This year'],
        };
    }
}
