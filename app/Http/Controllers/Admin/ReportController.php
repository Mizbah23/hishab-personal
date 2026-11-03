<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Income;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function overview(Request $request): View
    {
        [$from, $to] = $this->dateRange($request);
        $incomes = Income::query()
            ->whereBetween('received_on', [$from->toDateString(), $to->toDateString()])
            ->get(['amount', 'received_on', 'category']);
        $expenses = Expense::query()
            ->whereBetween('spent_on', [$from->toDateString(), $to->toDateString()])
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
            'categoryIncome' => $categoryIncome,
            'categoryExpense' => $categoryExpense,
            'peakAmount' => $peakAmount,
        ]);
    }

    public function income(Request $request): View
    {
        [$from, $to] = $this->dateRange($request);
        $incomes = Income::query()
            ->whereBetween('received_on', [$from->toDateString(), $to->toDateString()])
            ->get(['amount', 'received_on']);
        $rows = $incomes->groupBy(fn (Income $income): string => $income->received_on->toDateString())
            ->sortKeysDesc()
            ->map(fn ($items, string $date): array => [
                'date' => Carbon::parse($date),
                'income' => (float) $items->sum('amount'),
                'expense' => 0.0,
                'transactions' => $items->count(),
            ])->values();

        return $this->dateWiseView('income', $from, $to, $rows, (float) $incomes->sum('amount'), $incomes->count());
    }

    public function expense(Request $request): View
    {
        [$from, $to] = $this->dateRange($request);
        $expenses = Expense::query()
            ->whereBetween('spent_on', [$from->toDateString(), $to->toDateString()])
            ->get(['amount', 'spent_on']);
        $rows = $expenses->groupBy(fn (Expense $expense): string => $expense->spent_on->toDateString())
            ->sortKeysDesc()
            ->map(fn ($items, string $date): array => [
                'date' => Carbon::parse($date),
                'income' => 0.0,
                'expense' => (float) $items->sum('amount'),
                'transactions' => $items->count(),
            ])->values();

        return $this->dateWiseView('expense', $from, $to, $rows, (float) $expenses->sum('amount'), $expenses->count());
    }

    public function ledger(Request $request): View
    {
        [$from, $to] = $this->dateRange($request);
        $incomes = Income::query()
            ->whereBetween('received_on', [$from->toDateString(), $to->toDateString()])
            ->get(['amount', 'received_on']);
        $expenses = Expense::query()
            ->whereBetween('spent_on', [$from->toDateString(), $to->toDateString()])
            ->get(['amount', 'spent_on']);
        $incomeByDate = $incomes->groupBy(fn (Income $income): string => $income->received_on->toDateString());
        $expenseByDate = $expenses->groupBy(fn (Expense $expense): string => $expense->spent_on->toDateString());
        $rows = $incomeByDate->keys()
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
                    'transactions' => $incomeByDate->get($date, collect())->count() + $expenseByDate->get($date, collect())->count(),
                ];
            });

        return $this->dateWiseView('ledger', $from, $to, $rows, (float) $incomes->sum('amount') - (float) $expenses->sum('amount'), $incomes->count() + $expenses->count());
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function dateRange(Request $request): array
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        return [
            Carbon::parse($filters['from'] ?? now()->startOfYear()->toDateString())->startOfDay(),
            Carbon::parse($filters['to'] ?? now()->toDateString())->endOfDay(),
        ];
    }

    private function dateWiseView(string $report, Carbon $from, Carbon $to, Collection $rows, float $total, int $recordCount): View
    {
        return view('admin.reports.date-wise', compact('report', 'from', 'to', 'rows', 'total', 'recordCount'));
    }
}
