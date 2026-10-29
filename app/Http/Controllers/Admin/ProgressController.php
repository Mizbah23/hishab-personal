<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ProgressController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.progress', [
            'completed' => [
                ['label' => 'Laravel project setup', 'detail' => 'Laravel 13 application with MySQL connection'],
                ['label' => 'Admin authentication', 'detail' => 'Login, logout, session protection, and admin middleware'],
                ['label' => 'Accounting database foundation', 'detail' => 'Clients, income, expenses, vouchers, and user roles'],
                ['label' => 'Admin dashboard', 'detail' => 'Summary cards and recent activity panels'],
                ['label' => 'Client management', 'detail' => 'Create, view, search, edit, and remove clients'],
                ['label' => 'Income management', 'detail' => 'Record, search, edit, and remove income entries'],
                ['label' => 'Expense management', 'detail' => 'Record, search, edit, and remove expense entries'],
                ['label' => 'Income and expense attachments', 'detail' => 'Protected invoice, voucher, and image uploads up to 5 MB'],
                ['label' => 'Voucher management', 'detail' => 'Create, search, filter, edit, and remove vouchers'],
                ['label' => 'Reports and filters', 'detail' => 'Date-range reports with monthly and category summaries'],
                ['label' => 'User management and roles', 'detail' => 'Create, manage, activate, and assign Administrator, Accountant, or Viewer roles'],
                ['label' => 'Role-specific permissions', 'detail' => 'Administrators, Accountants, and Viewers have separate read/manage access'],
                ['label' => 'Audit and activity history', 'detail' => 'Track who created, changed, or removed financial and client records'],
                ['label' => 'Exports and backups', 'detail' => 'CSV exports and secure JSON data backups without passwords'],
                ['label' => 'Dashboard refinements', 'detail' => 'Period-based summaries, voucher totals, recent activity, and quick actions'],
            ],
            'next' => [
                ['label' => 'Accounting categories and recurring settings', 'detail' => 'Make common income and expense entries faster to record consistently'],
            ],
        ]);
    }
}
