@extends('layouts.admin', ['title' => 'Income', 'heading' => 'Income'])

@section('content')
    <div class="page-heading-row">
        <div><span class="panel-kicker">Money in</span><h2 class="page-title">Income records</h2><p class="page-copy">Track payments and revenue across your work.</p></div>
        <div class="page-actions">@if (auth()->user()->hasPermission('exports.view'))<a class="secondary-button" href="{{ route('admin.exports.incomes', request()->query()) }}">Export CSV</a>@endif @if (auth()->user()->hasPermission('incomes.manage'))<a class="primary-button compact-button" href="{{ route('admin.incomes.create') }}">Record income <span>＋</span></a>@endif</div>
    </div>

    <section class="content-card table-card">
        <div class="table-toolbar">
            <form method="GET" action="{{ route('admin.incomes.index') }}" class="search-form"><span>⌕</span><input type="search" name="q" value="{{ $search }}" placeholder="Search title, category, or client"></form>
            <span class="card-count">{{ $incomes->total() }} total records</span>
        </div>
        @if ($incomes->isEmpty())
            <div class="empty-state large-empty"><span>↗</span><strong>{{ $search ? 'No income found' : 'No income recorded yet' }}</strong><p>{{ $search ? 'Try a different search term.' : 'Start by recording your first income.' }}</p></div>
        @else
            <div class="table-wrap"><table class="data-table"><thead><tr><th>Income</th><th>Client</th><th>Received</th><th>Amount</th><th>File</th>@if (auth()->user()->hasPermission('incomes.manage'))<th></th>@endif</tr></thead><tbody>
                @foreach ($incomes as $income)
                    <tr>
                        <td><div class="table-person"><span class="table-avatar income-table-avatar">↗</span><span><strong>{{ $income->title }}</strong><small>{{ $income->category ?? 'Uncategorised' }}{{ $income->payment_method ? ' · '.$income->payment_method : '' }}</small></span></div></td>
                        <td><span class="table-contact">{{ $income->client?->name ?? 'No client linked' }}<small>{{ $income->client?->company_name ?? '—' }}</small></span></td>
                        <td><span class="table-date">{{ $income->received_on->format('d M Y') }}</span></td>
                        <td><strong class="amount positive">+ ৳ {{ number_format((float) $income->amount, 2) }}</strong></td>
                        <td>@if ($income->attachment_path)<a class="attachment-link" href="{{ route('admin.incomes.attachment', $income) }}">📎 View file</a>@else<span class="table-date">—</span>@endif</td>
                        @if (auth()->user()->hasPermission('incomes.manage'))<td><div class="row-actions"><a href="{{ route('admin.incomes.edit', $income) }}">Edit</a><form method="POST" action="{{ route('admin.incomes.destroy', $income) }}" onsubmit="return confirm('Remove this income record?')">@csrf @method('DELETE')<button type="submit">Remove</button></form></div></td>@endif
                    </tr>
                @endforeach
            </tbody></table></div>
            @if ($incomes->hasPages())<div class="pagination-wrap">{{ $incomes->links() }}</div>@endif
        @endif
    </section>
@endsection
