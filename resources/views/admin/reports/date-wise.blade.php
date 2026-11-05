@php
    $reportDetails = [
        'income' => ['title' => 'Date-wise income', 'description' => 'Review every date that received income.', 'totalLabel' => 'Income total', 'route' => 'admin.reports.income'],
        'expense' => ['title' => 'Date-wise expense', 'description' => 'Review every date that recorded an expense.', 'totalLabel' => 'Expense total', 'route' => 'admin.reports.expense'],
        'ledger' => ['title' => 'Date-wise ledger', 'description' => 'Compare income, expense, and the net result for each active date.', 'totalLabel' => 'Net balance', 'route' => 'admin.reports.ledger'],
    ][$report];
@endphp

@extends('layouts.admin', ['title' => 'Reports', 'heading' => 'Reports'])

@section('content')
    <div class="page-heading-row">
        <div><span class="panel-kicker">Reports</span><h2 class="page-title">{{ $reportDetails['title'] }}</h2><p class="page-copy">{{ $reportDetails['description'] }}</p></div>
    </div>

    <section class="content-card report-filter-card">
        <form method="GET" action="{{ route($reportDetails['route']) }}" class="report-filters">
            <div class="report-filter-field"><label for="from">From</label><input id="from" name="from" type="date" value="{{ request('from', $from->format('Y-m-d')) }}"></div>
            <span class="date-separator">→</span>
            <div class="report-filter-field"><label for="to">To</label><input id="to" name="to" type="date" value="{{ request('to', $to->format('Y-m-d')) }}"></div>
            <button class="filter-button" type="submit">Apply range</button>
            <a class="clear-filter" href="{{ route($reportDetails['route']) }}">This year</a>
        </form>
        <p class="filter-caption">Showing {{ $from->format('d M Y') }} to {{ $to->format('d M Y') }}</p>
    </section>

    <section class="stats-grid report-stats single-report-stat" aria-label="Report summary">
        <article class="stat-card"><div class="stat-icon {{ $report === 'expense' ? 'expense-icon' : 'income-icon' }}">{{ $report === 'expense' ? '↘' : '↗' }}</div><p>{{ $reportDetails['totalLabel'] }}</p><strong>৳ {{ number_format($total, 2) }}</strong><span class="stat-note">{{ $recordCount }} {{ str('record')->plural($recordCount) }}</span></article>
    </section>

    <section class="content-card report-ledger-card">
        <div class="card-header"><div><span class="panel-kicker">Filtered records</span><h3>{{ $reportDetails['title'] }}</h3></div><span class="card-count">{{ $rows->count() }} {{ str('active date')->plural($rows->count()) }}</span></div>
        @if ($rows->isNotEmpty())
            <div class="table-wrap">
                <table class="data-table report-ledger-table">
                    <thead><tr><th>Date</th>@if ($report !== 'expense')<th>Income</th>@endif @if ($report !== 'income')<th>Expense</th>@endif @if ($report === 'ledger')<th>Daily net balance</th>@endif<th>Records</th></tr></thead>
                    <tbody>
                        @foreach ($rows as $row)
                            <tr>
                                <td><strong>{{ $row['date']->format('d M Y') }}</strong><small>{{ $row['date']->format('l') }}</small></td>
                                @if ($report !== 'expense')<td><strong class="amount positive">+ ৳ {{ number_format($row['income'], 2) }}</strong></td>@endif
                                @if ($report !== 'income')<td><strong class="amount negative">− ৳ {{ number_format($row['expense'], 2) }}</strong></td>@endif
                                @if ($report === 'ledger')<td><strong class="amount {{ $row['income'] - $row['expense'] < 0 ? 'negative' : 'positive' }}">{{ $row['income'] - $row['expense'] < 0 ? '−' : '+' }} ৳ {{ number_format(abs($row['income'] - $row['expense']), 2) }}</strong></td>@endif
                                <td><span class="table-date">{{ $row['transactions'] }} {{ str('record')->plural($row['transactions']) }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state report-empty"><span>◌</span><strong>No activity in this date range</strong><p>Records will appear here once you add {{ $report === 'ledger' ? 'income or expenses' : $report }}.</p></div>
        @endif
    </section>
@endsection
