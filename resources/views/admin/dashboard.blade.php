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

    <section class="content-card quick-actions-card">
        <div><span class="panel-kicker">Shortcuts</span><h3>Keep the books current.</h3></div>
        <div class="quick-actions">
            @if (auth()->user()->hasPermission('incomes.manage'))<a class="quick-action income-quick" href="{{ route('admin.incomes.create') }}"><span>↗</span> Record income</a>@endif
            @if (auth()->user()->hasPermission('expenses.manage'))<a class="quick-action expense-quick" href="{{ route('admin.expenses.create') }}"><span>↘</span> Record expense</a>@endif
            @if (auth()->user()->hasPermission('vouchers.manage'))<a class="quick-action voucher-quick" href="{{ route('admin.vouchers.create') }}"><span>▤</span> Add voucher</a>@endif
            @if (auth()->user()->hasPermission('clients.manage'))<a class="quick-action client-quick" href="{{ route('admin.clients.create') }}"><span>◎</span> Add client</a>@endif
            @if (! auth()->user()->hasPermission('incomes.manage') && ! auth()->user()->hasPermission('expenses.manage'))<span class="read-only-note">Your Viewer role has read-only access to the workspace.</span>@endif
        </div>
    </section>

    <section class="setup-card">
        <div class="setup-number">01</div>
        <div><span class="panel-kicker">Next up</span><h3>Build out your account workflow</h3><p>Clients, income, expenses, and vouchers are now represented in the database. The next step is adding their management screens.</p></div>
        <span class="setup-arrow">→</span>
    </section>
@endsection
