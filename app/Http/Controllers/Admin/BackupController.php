<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Expense;
use App\Models\Income;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\View\View;

class BackupController extends Controller
{
    public function index(): View
    {
        return view('admin.backups');
    }

    public function download()
    {
        $payload = [
            'application' => config('app.name'),
            'generated_at' => now()->toIso8601String(),
            'users' => User::query()->get(['id', 'name', 'email', 'role', 'is_active', 'created_at', 'updated_at'])->toArray(),
            'clients' => Client::withTrashed()->get()->toArray(),
            'incomes' => Income::all()->toArray(),
            'expenses' => Expense::all()->toArray(),
            'vouchers' => Voucher::all()->toArray(),
            'activity_logs' => ActivityLog::query()->get()->toArray(),
        ];

        return response()->streamDownload(function () use ($payload): void {
            echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, 'hishab-data-backup-'.now()->format('Ymd-His').'.json', ['Content-Type' => 'application/json']);
    }
}
