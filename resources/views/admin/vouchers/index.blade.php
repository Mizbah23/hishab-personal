@extends('layouts.admin', ['title' => 'Vouchers', 'heading' => 'Vouchers'])

@section('content')
    <div class="page-heading-row">
        <div><span class="panel-kicker">Documents</span><h2 class="page-title">Accounting vouchers</h2><p class="page-copy">Keep a traceable record of the documents behind your transactions.</p></div>
        <div class="page-actions">@if (auth()->user()->hasPermission('exports.view'))<a class="secondary-button" href="{{ route('admin.exports.vouchers', request()->query()) }}">Export CSV</a>@endif @if (auth()->user()->hasPermission('vouchers.manage'))<a class="primary-button compact-button" href="{{ route('admin.vouchers.create') }}">Add voucher <span>＋</span></a>@endif</div>
    </div>

    <section class="content-card table-card">
        <form method="GET" action="{{ route('admin.vouchers.index') }}" class="voucher-filters">
            <div class="search-form"><span>⌕</span><input type="search" name="q" value="{{ $search }}" placeholder="Search number, narration, or client"></div>
            <select name="type"><option value="">All types</option>@foreach (['income' => 'Income', 'expense' => 'Expense', 'general' => 'General'] as $value => $label)<option value="{{ $value }}" {{ $type === $value ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select>
            <select name="status"><option value="">All statuses</option>@foreach (['draft' => 'Draft', 'posted' => 'Posted', 'cancelled' => 'Cancelled'] as $value => $label)<option value="{{ $value }}" {{ $status === $value ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select>
            <button class="filter-button" type="submit">Filter</button>
            @if ($search || $type || $status)<a class="clear-filter" href="{{ route('admin.vouchers.index') }}">Clear</a>@endif
        </form>
        @if ($vouchers->isEmpty())
            <div class="empty-state large-empty"><span>▤</span><strong>{{ $search || $type || $status ? 'No vouchers found' : 'No vouchers created yet' }}</strong><p>{{ $search || $type || $status ? 'Try changing your filters.' : 'Start by creating your first voucher.' }}</p></div>
        @else
            <div class="voucher-total"><span class="card-count">{{ $vouchers->total() }} total records</span></div>
            <div class="table-wrap"><table class="data-table"><thead><tr><th>Voucher</th><th>Type</th><th>Client</th><th>Date</th><th>Amount</th><th>Status</th>@if (auth()->user()->hasPermission('vouchers.manage'))<th></th>@endif</tr></thead><tbody>
                @foreach ($vouchers as $voucher)
                    <tr><td><div class="table-person"><span class="table-avatar voucher-table-avatar">▤</span><span><strong>{{ $voucher->voucher_number }}</strong><small>{{ $voucher->narration ?? 'No narration' }}</small></span></div></td><td><span class="type-badge {{ $voucher->type }}-type">{{ str($voucher->type)->headline() }}</span></td><td><span class="table-contact">{{ $voucher->client?->name ?? 'No client linked' }}<small>{{ $voucher->client?->company_name ?? '—' }}</small></span></td><td><span class="table-date">{{ $voucher->voucher_date->format('d M Y') }}</span></td><td><strong class="amount {{ $voucher->type === 'expense' ? 'negative' : 'positive' }}">{{ $voucher->type === 'expense' ? '−' : '+' }} ৳ {{ number_format((float) $voucher->amount, 2) }}</strong></td><td><span class="status-badge {{ $voucher->status }}-badge">{{ str($voucher->status)->headline() }}</span></td>@if (auth()->user()->hasPermission('vouchers.manage'))<td><div class="row-actions"><a href="{{ route('admin.vouchers.edit', $voucher) }}">Edit</a><form method="POST" action="{{ route('admin.vouchers.destroy', $voucher) }}" onsubmit="return confirm('Remove this voucher?')">@csrf @method('DELETE')<button type="submit">Remove</button></form></div></td>@endif</tr>
                @endforeach
            </tbody></table></div>
            @if ($vouchers->hasPages())<div class="pagination-wrap">{{ $vouchers->links() }}</div>@endif
        @endif
    </section>
@endsection
