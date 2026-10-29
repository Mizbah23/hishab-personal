<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'action' => ['nullable', 'in:created,updated,deleted'],
            'module' => ['nullable', 'in:client,income,expense,voucher'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $types = [
            'client' => Client::class,
            'income' => Income::class,
            'expense' => Expense::class,
            'voucher' => Voucher::class,
        ];

        $logs = ActivityLog::query()
            ->with('user')
            ->when($filters['q'] ?? null, fn ($query, $search) => $query->where('description', 'like', "%{$search}%"))
            ->when($filters['action'] ?? null, fn ($query, $action) => $query->where('action', $action))
            ->when($filters['module'] ?? null, fn ($query, $module) => $query->where('auditable_type', $types[$module]))
            ->when($filters['from'] ?? null, fn ($query, $from) => $query->whereDate('created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($query, $to) => $query->whereDate('created_at', '<=', $to))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.activity.index', [
            'logs' => $logs,
            'filters' => $filters,
            'modules' => [
                'client' => 'Clients',
                'income' => 'Income',
                'expense' => 'Expenses',
                'voucher' => 'Vouchers',
            ],
        ]);
    }
}
