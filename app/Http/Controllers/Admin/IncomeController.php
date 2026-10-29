<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Income;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class IncomeController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));

        $incomes = Income::query()
            ->with('client')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%")
                        ->orWhereHas('client', function ($query) use ($search): void {
                            $query->where('name', 'like', "%{$search}%")
                                ->orWhere('company_name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('received_on')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.incomes.index', compact('incomes', 'search'));
    }

    public function create(): View
    {
        return view('admin.incomes.create', ['clients' => $this->clients()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request) + ['created_by' => $request->user()->id];
        $data += $this->attachmentData($request);

        Income::create($data);

        return redirect()->route('admin.incomes.index')->with('status', 'Income recorded successfully.');
    }

    public function edit(Income $income): View
    {
        return view('admin.incomes.edit', [
            'income' => $income,
            'clients' => $this->clients(),
        ]);
    }

    public function update(Request $request, Income $income): RedirectResponse
    {
        $data = $this->validated($request);
        $attachment = $this->attachmentData($request);
        $oldPath = $income->attachment_path;

        $income->update($data + $attachment);

        if ($attachment && $oldPath) {
            Storage::disk('local')->delete($oldPath);
        }

        return redirect()->route('admin.incomes.index')->with('status', 'Income updated successfully.');
    }

    public function destroy(Income $income): RedirectResponse
    {
        if ($income->attachment_path) {
            Storage::disk('local')->delete($income->attachment_path);
        }

        $income->delete();

        return redirect()->route('admin.incomes.index')->with('status', 'Income removed successfully.');
    }

    public function downloadAttachment(Income $income)
    {
        abort_unless($income->attachment_path && Storage::disk('local')->exists($income->attachment_path), 404);

        return Storage::disk('local')->download($income->attachment_path, $income->attachment_name);
    }

    private function clients()
    {
        return Client::query()->where('is_active', true)->orderBy('name')->get();
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'received_on' => ['required', 'date'],
            'category' => ['nullable', 'string', 'max:100'],
            'payment_method' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
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
            'attachment_path' => $file->store('attachments/incomes', 'local'),
            'attachment_name' => $file->getClientOriginalName(),
        ];
    }
}
