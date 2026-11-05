@extends('layouts.admin', ['title' => 'Dashboard', 'heading' => 'Good morning'])

@section('content')
    <section class="welcome-panel">
        <div>
            <span class="panel-kicker">Financial overview</span>
            <h2>Keep your numbers moving forward.</h2>
            <p>{{ $periodLabel }} overview across your income and expenses.</p>
        </div>
        <div class="panel-orbit"><span>৳</span></div>
    </section>

    <section class="stats-grid dashboard-stats" aria-label="Account summary">
        <article class="stat-card">
            <div class="stat-icon income-icon">↗</div>
            <p>Total income</p>
            <strong>৳ {{ number_format($income, 2) }}</strong>
            <span class="stat-note">All recorded income</span>
        </article>
        <article class="stat-card">
            <div class="stat-icon expense-icon">↘</div>
            <p>Total expenses</p>
            <strong>৳ {{ number_format($expense, 2) }}</strong>
            <span class="stat-note">All recorded expenses</span>
        </article>
        <article class="stat-card balance-card">
            <div class="stat-icon balance-icon">=</div>
            <p>Net balance</p>
            <strong>৳ {{ number_format($balance, 2) }}</strong>
            <span class="stat-note">Income minus expenses</span>
        </article>
        <article class="stat-card">
            <div class="stat-icon client-icon">◎</div>
            <p>Active clients</p>
            <strong>{{ number_format($clientCount) }}</strong>
            <span class="stat-note">Ready to be managed</span>
        </article>
        <article class="stat-card">
            <div class="stat-icon voucher-table-avatar">▤</div>
            <p>Vouchers in period</p>
            <strong>{{ number_format($voucherCount) }}</strong>
            <span class="stat-note">Documents recorded</span>
        </article>
    </section>

    @php
        $barPeak = max(1, $recentMonths->max(fn ($month) => max($month['income'], $month['expense'], abs($month['net']))));
        $linePeak = max(1, $yearIncome->max('amount'));
    @endphp
    <section class="dashboard-columns financial-charts">
        <article class="content-card">
            <div class="card-header"><h3>Last 3 months</h3><span class="card-count">Current month to date · BDT</span></div>
            <div class="quarter-chart">
                @foreach ($recentMonths as $month)
                    <div class="quarter-month"><strong>{{ $month['label'] }}</strong>
                        @foreach (['income' => 'Income', 'expense' => 'Expense', 'net' => 'Net balance'] as $key => $label)
                            <div class="quarter-series"><span>{{ $label }}</span><div class="quarter-track"><i class="series-{{ $key }} {{ $month[$key] < 0 ? 'series-loss' : '' }}" style="width:{{ abs($month[$key]) / $barPeak * 100 }}%"></i></div><strong>৳ {{ number_format($month[$key], 2) }}</strong></div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </article>
        <article class="content-card">
            <div class="card-header"><h3>{{ $chartYear }} income</h3><span class="card-count">Monthly totals · BDT</span></div>
            <div class="income-line-scroll">
                <svg class="income-line" viewBox="0 0 640 260" role="img" aria-label="Current year monthly income line chart">
                    <line x1="35" y1="215" x2="610" y2="215" stroke="#dce5df" />
                    <polyline fill="none" stroke="#2d8061" stroke-width="3" points="@foreach ($yearIncome as $point){{ 40 + $loop->index * 51 }},{{ 215 - $point['amount'] / $linePeak * 165 }} @endforeach" />
                    @foreach ($yearIncome as $point)
                        <circle cx="{{ 40 + $loop->index * 51 }}" cy="{{ 215 - $point['amount'] / $linePeak * 165 }}" r="4" fill="#2d8061"><title>{{ $point['label'] }}: ৳ {{ number_format($point['amount'], 2) }}</title></circle>
                        <text x="{{ 40 + $loop->index * 51 }}" y="240" text-anchor="middle" font-size="11" fill="#65716b">{{ $point['label'] }}</text>
                    @endforeach
                </svg>
            </div>
            <details><summary>View exact monthly amounts</summary><div class="income-month-values">@foreach ($yearIncome as $point)<span>{{ $point['label'] }}: ৳ {{ number_format($point['amount'], 2) }}</span>@endforeach</div></details>
        </article>
    </section>
    <section class="dashboard-columns">
        <article class="content-card">
            <div class="card-header">
                <div><span class="panel-kicker">Latest activity</span><h3>Recent income</h3></div>
                <span class="card-count">{{ $recentIncomes->count() }} records</span>
            </div>
            @forelse ($recentIncomes as $item)
                <div class="activity-row">
                    <span class="activity-icon income-icon">↗</span>
                    <span class="activity-detail"><strong>{{ $item->title }}</strong><small>{{ $item->client?->name ?? 'No client' }} · {{ $item->received_on->format('d M Y') }}</small></span>
                    <strong class="amount positive">+ ৳ {{ number_format((float) $item->amount, 2) }}</strong>
                </div>
            @empty
                <div class="empty-state"><span>✦</span><strong>No income recorded yet</strong><p>Income entries will appear here once added.</p></div>
            @endforelse
        </article>

        <article class="content-card">
            <div class="card-header">
                <div><span class="panel-kicker">Latest activity</span><h3>Recent expenses</h3></div>
                <span class="card-count">{{ $recentExpenses->count() }} records</span>
            </div>
            @forelse ($recentExpenses as $item)
                <div class="activity-row">
                    <span class="activity-icon expense-icon">↘</span>
                    <span class="activity-detail"><strong>{{ $item->title }}</strong><small>{{ $item->category ?? 'Uncategorised' }} · {{ $item->spent_on->format('d M Y') }}</small></span>
                    <strong class="amount negative">− ৳ {{ number_format((float) $item->amount, 2) }}</strong>
                </div>
            @empty
                <div class="empty-state"><span>✦</span><strong>No expenses recorded yet</strong><p>Expense entries will appear here once added.</p></div>
            @endforelse
        </article>
    </section>



    <section class="setup-card">
        <div class="setup-number">01</div>
        <div><span class="panel-kicker">Next up</span><h3>Build out your account workflow</h3><p>Clients, income, expenses, and vouchers are now represented in the database. The next step is adding their management screens.</p></div>
        <span class="setup-arrow">→</span>
    </section>
@endsection
