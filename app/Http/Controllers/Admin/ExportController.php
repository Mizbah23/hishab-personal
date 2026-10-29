<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Voucher;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function clients(Request $request)
    {
        $search = trim((string) $request->query('q'));
        $clients = Client::query()
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            }))->latest()->get();

        return $this->csv('hishab-clients-'.now()->format('Ymd').'.csv',
            ['Name', 'Company', 'Email', 'Phone', 'Address', 'Active', 'Added'],
            $clients->map(fn (Client $client): array => [
                $client->name, $client->company_name, $client->email, $client->phone,
                $client->address, $client->is_active ? 'Yes' : 'No', $client->created_at->toDateString(),
            ]));
    }

    public function incomes(Request $request)
    {
        $search = trim((string) $request->query('q'));
        $incomes = Income::query()->with('client')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhereHas('client', fn ($query) => $query->where('name', 'like', "%{$search}%")->orWhere('company_name', 'like', "%{$search}%"));
            }))->latest('received_on')->latest()->get();

        return $this->csv('hishab-income-'.now()->format('Ymd').'.csv',
            ['Title', 'Amount (BDT)', 'Received On', 'Category', 'Payment Method', 'Client', 'Notes', 'Attachment'],
            $incomes->map(fn (Income $income): array => [
                $income->title, $income->amount, $income->received_on->toDateString(), $income->category,
                $income->payment_method, $income->client?->name, $income->notes, $income->attachment_name,
            ]));
    }

    public function expenses(Request $request)
    {
        $search = trim((string) $request->query('q'));
        $expenses = Expense::query()
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            }))->latest('spent_on')->latest()->get();

        return $this->csv('hishab-expenses-'.now()->format('Ymd').'.csv',
            ['Title', 'Amount (BDT)', 'Spent On', 'Category', 'Payment Method', 'Notes', 'Attachment'],
            $expenses->map(fn (Expense $expense): array => [
                $expense->title, $expense->amount, $expense->spent_on->toDateString(), $expense->category,
                $expense->payment_method, $expense->notes, $expense->attachment_name,
            ]));
    }

    public function vouchers(Request $request)
    {
        $search = trim((string) $request->query('q'));
        $type = $request->query('type');
        $status = $request->query('status');
        $vouchers = Voucher::query()->with('client')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('voucher_number', 'like', "%{$search}%")
                    ->orWhere('narration', 'like', "%{$search}%")
                    ->orWhereHas('client', fn ($query) => $query->where('name', 'like', "%{$search}%")->orWhere('company_name', 'like', "%{$search}%"));
            }))
            ->when(in_array($type, ['income', 'expense', 'general'], true), fn ($query) => $query->where('type', $type))
            ->when(in_array($status, ['draft', 'posted', 'cancelled'], true), fn ($query) => $query->where('status', $status))
            ->latest('voucher_date')->latest()->get();

        return $this->csv('hishab-vouchers-'.now()->format('Ymd').'.csv',
            ['Voucher Number', 'Type', 'Date', 'Amount (BDT)', 'Status', 'Client', 'Narration'],
            $vouchers->map(fn (Voucher $voucher): array => [
                $voucher->voucher_number, $voucher->type, $voucher->voucher_date->toDateString(), $voucher->amount,
                $voucher->status, $voucher->client?->name, $voucher->narration,
            ]));
    }

    private function csv(string $filename, array $headers, iterable $rows)
    {
        return response()->streamDownload(function () use ($headers, $rows): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
