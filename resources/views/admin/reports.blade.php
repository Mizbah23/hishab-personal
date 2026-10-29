@extends('layouts.admin', ['title' => 'Reports', 'heading' => 'Reports'])

@section('content')
    <div class="page-heading-row">
        <div><span class="panel-kicker">Insights</span><h2 class="page-title">Reports that keep you informed.</h2><p class="page-copy">Review income, expenses, and balance for any period.</p></div>
    </div>

    <section class="content-card report-filter-card">
        <form method="GET" action="{{ route('admin.reports') }}" class="report-filters">
            <div class="report-filter-field"><label for="from">From</label><input id="from" name="from" type="date" value="{{ request('from', $from->format('Y-m-d')) }}"></div>
            <span class="date-separator">→</span>
            <div class="report-filter-field"><label for="to">To</label><input id="to" name="to" type="date" value="{{ request('to', $to->format('Y-m-d')) }}"></div>
            <button class="filter-button" type="submit">Apply range</button>
            <a class="clear-filter" href="{{ route('admin.reports') }}">This year</a>
        </form>
        <p class="filter-caption">Showing {{ $from->format('d M Y') }} to {{ $to->format('d M Y') }}</p>
    </section>

    <section class="stats-grid report-stats" aria-label="Report summary">
        <article class="stat-card"><div class="stat-icon income-icon">↗</div><p>Income in period</p><strong>৳ {{ number_format($incomeTotal, 2) }}</strong><span class="stat-note">{{ $incomeCount }} {{ str('record')->plural($incomeCount) }}</span></article>
        <article class="stat-card"><div class="stat-icon expense-icon">↘</div><p>Expenses in period</p><strong>৳ {{ number_format($expenseTotal, 2) }}</strong><span class="stat-note">{{ $expenseCount }} {{ str('record')->plural($expenseCount) }}</span></article>
        <article class="stat-card balance-card"><div class="stat-icon balance-icon">=</div><p>Net balance</p><strong>৳ {{ number_format($balance, 2) }}</strong><span class="stat-note">Income minus expenses</span></article>
    </section>

    <section class="report-columns">
        <article class="content-card chart-card">
            <div class="card-header"><div><span class="panel-kicker">Period trend</span><h3>Monthly movement</h3></div><span class="chart-legend"><i class="legend-income"></i> Income <i class="legend-expense"></i> Expense</span></div>
            <p class="monthly-help">Monthly totals in BDT. Bar lengths use the same scale for income and expenses.</p>
            <div class="monthly-chart">
                @foreach ($monthly as $month)
                    <section class="monthly-row" aria-label="{{ $month['label'] }}">
                        <div class="monthly-heading"><strong>{{ $month['label'] }}</strong><span>Net ৳ {{ number_format($month['income'] - $month['expense'], 2) }}</span></div>
                        @foreach (['income' => 'Income', 'expense' => 'Expense'] as $type => $label)
                            <div class="monthly-series">
                                <span>{{ $label }}</span>
                                <div class="monthly-track" aria-hidden="true"><span class="{{ $type }}-bar" style="width:{{ round(($month[$type] / $peakAmount) * 100, 2) }}%"></span></div>
                                <strong>৳ {{ number_format($month[$type], 2) }}</strong>
                            </div>
                        @endforeach
                    </section>
                @endforeach
            </div>
            @if ($incomeCount === 0 && $expenseCount === 0)<p class="monthly-help">No transactions in this period.</p>@endif
        </article>

        <article class="content-card category-card">
            <div class="card-header"><div><span class="panel-kicker">Where it goes</span><h3>Expense categories</h3></div></div>
            @forelse ($categoryExpense as $category => $amount)
                <div class="category-row"><span class="category-name">{{ $category }}</span><span class="category-track"><i style="width:{{ max(4, round(($amount / max(1, $expenseTotal)) * 100)) }}%"></i></span><strong>৳ {{ number_format($amount, 0) }}</strong></div>
            @empty
                <div class="empty-state category-empty"><span>◌</span><strong>No expense categories yet</strong><p>Category totals will appear here.</p></div>
            @endforelse
        </article>
    </section>

    <section class="content-card category-income-card">
        <div class="card-header"><div><span class="panel-kicker">Where it comes from</span><h3>Income categories</h3></div></div>
        <div class="income-category-grid">
            @forelse ($categoryIncome as $category => $amount)
                <div class="income-category"><span class="stat-icon income-icon">↗</span><span><small>{{ $category }}</small><strong>৳ {{ number_format($amount, 2) }}</strong></span></div>
            @empty
                <div class="empty-state category-empty"><span>◌</span><strong>No income categories yet</strong><p>Category totals will appear here.</p></div>
            @endforelse
        </div>
    </section>
@endsection
