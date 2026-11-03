<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Income;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __invoke(Request $request): View
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $from = Carbon::parse($filters['from'] ?? now()->startOfYear()->toDateString())->startOfDay();
        $to = Carbon::parse($filters['to'] ?? now()->toDateString())->endOfDay();
        $incomes = Income::query()
            ->whereDate('received_on', '>=', $from->toDateString())
            ->whereDate('received_on', '<=', $to->toDateString())
            ->get(['amount', 'received_on', 'category']);
        $expenses = Expense::query()
            ->whereDate('spent_on', '>=', $from->toDateString())
            ->whereDate('spent_on', '<=', $to->toDateString())
            ->get(['amount', 'spent_on', 'category']);

        $incomeByMonth = $incomes->groupBy(fn (Income $income): string => $income->received_on->format('Y-m'));
        $expenseByMonth = $expenses->groupBy(fn (Expense $expense): string => $expense->spent_on->format('Y-m'));
        $monthly = collect(CarbonPeriod::create($from->copy()->startOfMonth(), '1 month', $to->copy()->startOfMonth()))
            ->map(function (Carbon $month) use ($incomeByMonth, $expenseByMonth): array {
                $key = $month->format('Y-m');

                return [
                    'key' => $key,
                    'label' => $month->format('M Y'),
                    'income' => (float) $incomeByMonth->get($key, collect())->sum('amount'),
                    'expense' => (float) $expenseByMonth->get($key, collect())->sum('amount'),
                ];
            })->values();

        $incomeTotal = (float) $incomes->sum('amount');
        $expenseTotal = (float) $expenses->sum('amount');
        $categoryIncome = $incomes->groupBy(fn (Income $income): string => $income->category ?: 'Uncategorised')
            ->map(fn ($items): float => (float) $items->sum('amount'))->sortDesc()->take(6);
        $categoryExpense = $expenses->groupBy(fn (Expense $expense): string => $expense->category ?: 'Uncategorised')
            ->map(fn ($items): float => (float) $items->sum('amount'))->sortDesc()->take(6);
        $incomeByDate = $incomes->groupBy(fn (Income $income): string => $income->received_on->toDateString());
        $expenseByDate = $expenses->groupBy(fn (Expense $expense): string => $expense->spent_on->toDateString());
        $daily = $incomeByDate->keys()
            ->merge($expenseByDate->keys())
            ->unique()
            ->sortDesc()
            ->values()
            ->map(function (string $date) use ($incomeByDate, $expenseByDate): array {
                $income = (float) $incomeByDate->get($date, collect())->sum('amount');
                $expense = (float) $expenseByDate->get($date, collect())->sum('amount');

                return [
                    'date' => Carbon::parse($date),
                    'income' => $income,
                    'expense' => $expense,
                    'net' => $income - $expense,
                    'transactions' => $incomeByDate->get($date, collect())->count() + $expenseByDate->get($date, collect())->count(),
                ];
            });
        $peakAmount = max(1, (float) $monthly->max(fn (array $month): float => max($month['income'], $month['expense'])));

        return view('admin.reports', [
            'from' => $from,
            'to' => $to,
            'monthly' => $monthly,
            'incomeTotal' => $incomeTotal,
            'expenseTotal' => $expenseTotal,
            'balance' => $incomeTotal - $expenseTotal,
            'incomeCount' => $incomes->count(),
            'expenseCount' => $expenses->count(),
            'daily' => $daily,
            'categoryIncome' => $categoryIncome,
            'categoryExpense' => $categoryExpense,
            'peakAmount' => $peakAmount,
        ]);
    }
}
