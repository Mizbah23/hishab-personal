<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));

        $expenses = Expense::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%");
                });
            })
            ->latest('spent_on')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.expenses.index', compact('expenses', 'search'));
    }

    public function create(): View
    {
        return view('admin.expenses.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request) + ['created_by' => $request->user()->id];
        $data += $this->attachmentData($request);

        Expense::create($data);

        return redirect()->route('admin.expenses.index')->with('status', 'Expense recorded successfully.');
    }

    public function edit(Expense $expense): View
    {
        return view('admin.expenses.edit', compact('expense'));
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $data = $this->validated($request);
        $attachment = $this->attachmentData($request);
        $oldPath = $expense->attachment_path;

        $expense->update($data + $attachment);

        if ($attachment && $oldPath) {
            Storage::disk('local')->delete($oldPath);
        }

        return redirect()->route('admin.expenses.index')->with('status', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        if ($expense->attachment_path) {
            Storage::disk('local')->delete($expense->attachment_path);
        }

        $expense->delete();

        return redirect()->route('admin.expenses.index')->with('status', 'Expense removed successfully.');
    }

    public function downloadAttachment(Expense $expense)
    {
        abort_unless($expense->attachment_path && Storage::disk('local')->exists($expense->attachment_path), 404);

        return Storage::disk('local')->download($expense->attachment_path, $expense->attachment_name);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'spent_on' => ['required', 'date'],
            'category' => ['nullable', 'string', 'max:100'],
            'payment_method' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'attachment' => ['nullable', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx'],
        ]);
    }

    private function attachmentData(Request $request): array
    {
        if (! $request->hasFile('attachment')) {
            return [];
        }

        $file = $request->file('attachment');

        return [
            'attachment_path' => $file->store('attachments/expenses', 'local'),
            'attachment_name' => $file->getClientOriginalName(),
        ];
    }
}
