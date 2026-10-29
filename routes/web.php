<?php

use App\Http\Controllers\Admin\ActivityController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\IncomeController;
use App\Http\Controllers\Admin\ProgressController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VoucherController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

Route::post('/logout', [AuthController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::prefix('admin')->name('admin.')->middleware('auth')->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->middleware('permission:dashboard.view')->name('dashboard');
    Route::get('/progress', ProgressController::class)->middleware('permission:progress.view')->name('progress');
    Route::get('/reports', ReportController::class)->middleware('permission:reports.view')->name('reports');
    Route::get('/activity', [ActivityController::class, 'index'])->middleware('permission:audit.view')->name('activity.index');
    Route::get('/backups', [BackupController::class, 'index'])->middleware('permission:backups.manage')->name('backups.index');
    Route::get('/backups/download', [BackupController::class, 'download'])->middleware('permission:backups.manage')->name('backups.download');
    Route::prefix('exports')->middleware('permission:exports.view')->name('exports.')->group(function (): void {
        Route::get('/clients', [ExportController::class, 'clients'])->name('clients');
        Route::get('/incomes', [ExportController::class, 'incomes'])->name('incomes');
        Route::get('/expenses', [ExportController::class, 'expenses'])->name('expenses');
        Route::get('/vouchers', [ExportController::class, 'vouchers'])->name('vouchers');
    });

    Route::resource('users', UserController::class)->except(['show'])->middleware('permission:users.manage');

    Route::resource('clients', ClientController::class)->only(['index'])->middleware('permission:clients.view');
    Route::resource('clients', ClientController::class)->except(['index', 'show'])->middleware('permission:clients.manage');

    Route::resource('incomes', IncomeController::class)->only(['index'])->middleware('permission:incomes.view');
    Route::resource('incomes', IncomeController::class)->except(['index', 'show'])->middleware('permission:incomes.manage');
    Route::get('/incomes/{income}/attachment', [IncomeController::class, 'downloadAttachment'])->middleware('permission:incomes.view')->name('incomes.attachment');

    Route::resource('expenses', ExpenseController::class)->only(['index'])->middleware('permission:expenses.view');
    Route::resource('expenses', ExpenseController::class)->except(['index', 'show'])->middleware('permission:expenses.manage');
    Route::get('/expenses/{expense}/attachment', [ExpenseController::class, 'downloadAttachment'])->middleware('permission:expenses.view')->name('expenses.attachment');

    Route::resource('vouchers', VoucherController::class)->only(['index'])->middleware('permission:vouchers.view');
    Route::resource('vouchers', VoucherController::class)->except(['index', 'show'])->middleware('permission:vouchers.manage');
});
