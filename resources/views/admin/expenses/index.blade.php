@extends('layouts.admin', ['title' => 'Expenses', 'heading' => 'Expenses'])

@section('content')
    <div class="page-heading-row">
        <div><span class="panel-kicker">Money out</span><h2 class="page-title">Expense records</h2><p class="page-copy">Keep a clear view of the costs behind your work.</p></div>
        <div class="page-actions">@if (auth()->user()->hasPermission('exports.view'))<a class="secondary-button" href="{{ route('admin.exports.expenses', request()->query()) }}">Export CSV</a>@endif @if (auth()->user()->hasPermission('expenses.manage'))<a class="primary-button compact-button" href="{{ route('admin.expenses.create') }}">Record expense <span>＋</span></a>@endif</div>
    </div>

    <section class="content-card table-card">
        <div class="table-toolbar"><form method="GET" action="{{ route('admin.expenses.index') }}" class="search-form"><span>⌕</span><input type="search" name="q" value="{{ $search }}" placeholder="Search title or category"></form><span class="card-count">{{ $expenses->total() }} total records</span></div>
        @if ($expenses->isEmpty())
            <div class="empty-state large-empty"><span>↘</span><strong>{{ $search ? 'No expenses found' : 'No expenses recorded yet' }}</strong><p>{{ $search ? 'Try a different search term.' : 'Start by recording your first expense.' }}</p></div>
        @else
            <div class="table-wrap"><table class="data-table"><thead><tr><th>Expense</th><th>Category</th><th>Spent</th><th>Amount</th><th>File</th>@if (auth()->user()->hasPermission('expenses.manage'))<th></th>@endif</tr></thead><tbody>
                @foreach ($expenses as $expense)
                    <tr><td><div class="table-person"><span class="table-avatar expense-table-avatar">↘</span><span><strong>{{ $expense->title }}</strong><small>{{ $expense->payment_method ?? 'No payment method' }}</small></span></div></td><td><span class="table-date">{{ $expense->category ?? 'Uncategorised' }}</span></td><td><span class="table-date">{{ $expense->spent_on->format('d M Y') }}</span></td><td><strong class="amount negative">− ৳ {{ number_format((float) $expense->amount, 2) }}</strong></td><td>@if ($expense->attachment_path)<a class="attachment-link" href="{{ route('admin.expenses.attachment', $expense) }}">📎 View file</a>@else<span class="table-date">—</span>@endif</td>@if (auth()->user()->hasPermission('expenses.manage'))<td><div class="row-actions"><a href="{{ route('admin.expenses.edit', $expense) }}">Edit</a><form method="POST" action="{{ route('admin.expenses.destroy', $expense) }}" onsubmit="return confirm('Remove this expense record?')">@csrf @method('DELETE')<button type="submit">Remove</button></form></div></td>@endif</tr>
                @endforeach
            </tbody></table></div>
            @if ($expenses->hasPages())<div class="pagination-wrap">{{ $expenses->links() }}</div>@endif
        @endif
    </section>
@endsection
