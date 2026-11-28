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
        <article class="stat-card balance-card">
            <div class="stat-icon balance-icon">=</div>
            <p>Net balance</p>
            <strong>৳ {{ number_format($balance, 2) }}</strong>
            <span class="stat-note">Income minus expenses</span>
        </article>
        <article class="stat-card">
            <div class="stat-icon expense-icon">↘</div>
            <p>Total expenses</p>
            <strong>৳ {{ number_format($expense, 2) }}</strong>
            <span class="stat-note">All recorded expenses</span>
        </article>
        <article class="stat-card">
            <div class="stat-icon income-icon">↗</div>
            <p>Total income</p>
            <strong>৳ {{ number_format($income, 2) }}</strong>
            <span class="stat-note">All recorded income</span>
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
        $targetStep = max(1, $comparisonMonths->max(fn ($month) => max($month['income'], $month['expense'])) / 5);
        $magnitude = 10 ** floor(log10($targetStep));
        $lineStep = ceil($targetStep / $magnitude) * $magnitude;
        $linePeak = $lineStep * 5;
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
        <article class="content-card annual-income-card">
            <div class="card-header"><h3>Income vs Expense for Last 6 Months</h3></div>
            <div class="income-line-scroll" tabindex="0" role="region" aria-label="Six-month income and expense chart">
                <svg class="income-line" viewBox="0 0 640 260" role="img" aria-label="Monthly income and expenses in BDT">
                    <text x="100" y="22" class="income-axis-label">Amount (BDT)</text>
                    @for ($tick = 0; $tick <= 5; $tick++)
                        <line x1="95" y1="{{ 225 - 36 * $tick }}" x2="608" y2="{{ 225 - 36 * $tick }}" class="income-gridline" />
                        <text x="90" y="{{ 225 - 36 * $tick }}" text-anchor="end" dominant-baseline="middle" class="income-axis-label">৳{{ number_format($tick * $lineStep) }}</text>
                    @endfor
                    @foreach (['income' => '#0eaa65', 'expense' => '#e86c28'] as $series => $color)
                        @php
                            $points = $comparisonMonths->map(fn ($month, $index) => [
                                'x' => 100 + $index * 100,
                                'y' => 225 - $month[$series] / $linePeak * 180,
                            ]);
                            $path = 'M '.$points[0]['x'].' '.$points[0]['y'];
                            for ($index = 1; $index < $points->count(); $index++) {
                                $previous = $points[$index - 1];
                                $point = $points[$index];
                                $middle = ($previous['x'] + $point['x']) / 2;
                                $path .= " C {$middle} {$previous['y']} {$middle} {$point['y']} {$point['x']} {$point['y']}";
                            }
                        @endphp
                        <path d="{{ $path }} L600 225 H100 Z" fill="{{ $color }}" opacity=".06" />
                        <path d="{{ $path }}" fill="none" stroke="{{ $color }}" stroke-width="3" />
                        @foreach ($points as $index => $point)
                            <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="3" fill="{{ $color }}"><title>{{ $comparisonMonths[$index]['label'] }} {{ ucfirst($series) }}: ৳{{ number_format($comparisonMonths[$index][$series], 2) }}</title></circle>
                        @endforeach
                    @endforeach
                    @foreach ($comparisonMonths as $month)
                        <text x="{{ 100 + $loop->index * 100 }}" y="248" text-anchor="middle" class="income-axis-label">{{ $month['label'] }}</text>
                    @endforeach
                </svg>
            </div>
            <div class="chart-legend comparison-legend"><i class="legend-income"></i> Income <i class="legend-expense"></i> Expense</div>
            <details>
                <summary>View exact monthly amounts</summary>
                <div class="income-month-values">
                    @foreach ($comparisonMonths as $month)
                        <span>{{ $month['label'] }} · Income ৳{{ number_format($month['income'], 2) }} · Expense ৳{{ number_format($month['expense'], 2) }}</span>
                    @endforeach
                </div>
            </details>
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
