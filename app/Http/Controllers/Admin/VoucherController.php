<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Voucher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VoucherController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));
        $type = $request->query('type');
        $status = $request->query('status');

        $vouchers = Voucher::query()
            ->with('client')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('voucher_number', 'like', "%{$search}%")
                        ->orWhere('narration', 'like', "%{$search}%")
                        ->orWhereHas('client', function ($query) use ($search): void {
                            $query->where('name', 'like', "%{$search}%")
                                ->orWhere('company_name', 'like', "%{$search}%");
                        });
                });
            })
            ->when(in_array($type, ['income', 'expense', 'general'], true), fn ($query) => $query->where('type', $type))
            ->when(in_array($status, ['draft', 'posted', 'cancelled'], true), fn ($query) => $query->where('status', $status))
            ->latest('voucher_date')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.vouchers.index', compact('vouchers', 'search', 'type', 'status'));
    }

    public function create(): View
    {
        return view('admin.vouchers.create', [
            'clients' => $this->clients(),
            'voucherNumber' => Voucher::nextVoucherNumber(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request) + ['created_by' => $request->user()->id];
        $data += $this->attachmentData($request);
        $data['voucher_number'] = Voucher::nextVoucherNumber();

        Voucher::create($data);

        return redirect()->route('admin.vouchers.index')->with('status', 'Voucher created successfully.');
    }

    public function edit(Voucher $voucher): View
    {
        return view('admin.vouchers.edit', [
            'voucher' => $voucher,
            'clients' => $this->clients(),
        ]);
    }

    public function update(Request $request, Voucher $voucher): RedirectResponse
    {
        $data = $this->validated($request);
        $attachment = $this->attachmentData($request);
        $oldPath = $voucher->attachment_path;

        $voucher->update($data + $attachment);

        if ($attachment !== [] && $oldPath) {
            Storage::disk('local')->delete($oldPath);
        }

        return redirect()->route('admin.vouchers.index')->with('status', 'Voucher updated successfully.');
    }

    public function destroy(Voucher $voucher): RedirectResponse
    {
        if ($voucher->attachment_path) {
            Storage::disk('local')->delete($voucher->attachment_path);
        }

        $voucher->delete();

        return redirect()->route('admin.vouchers.index')->with('status', 'Voucher removed successfully.');
    }

    public function downloadAttachment(Voucher $voucher)
    {
        abort_unless($voucher->attachment_path && Storage::disk('local')->exists($voucher->attachment_path), 404);

        return Storage::disk('local')->download($voucher->attachment_path, $voucher->attachment_name);
    }

    private function clients()
    {
        return Client::query()->where('is_active', true)->orderBy('name')->get();
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'type' => ['required', Rule::in(['income', 'expense', 'general'])],
            'voucher_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'status' => ['required', Rule::in(['draft', 'posted', 'cancelled'])],
            'narration' => ['nullable', 'string', 'max:255'],
            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'attachment' => ['nullable', 'file', 'max:2048', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx'],
        ]);
    }

    private function attachmentData(Request $request): array
    {
        if (! $request->hasFile('attachment')) {
            return [];
        }

        $file = $request->file('attachment');

        return [
            'attachment_path' => $file->store('attachments/vouchers', 'local'),
            'attachment_name' => $file->getClientOriginalName(),
        ];
    }
}
