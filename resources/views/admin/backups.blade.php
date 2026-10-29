@extends('layouts.admin', ['title' => 'Exports & backups', 'heading' => 'Exports & backups'])

@section('content')
    <div class="page-heading-row">
        <div><span class="panel-kicker">Keep your records safe</span><h2 class="page-title">Exports & backups</h2><p class="page-copy">Download your account data whenever you need a portable copy.</p></div>
    </div>

    <section class="backup-grid">
        <article class="content-card backup-card backup-primary"><div class="backup-icon">⇩</div><span class="panel-kicker">Full data backup</span><h3>Download account backup</h3><p>Export users, clients, income, expenses, vouchers, and activity history as a JSON file. Passwords are never included.</p><a class="primary-button compact-button" href="{{ route('admin.backups.download') }}">Download backup <span>→</span></a></article>
        <article class="content-card backup-card"><div class="backup-icon csv-backup-icon">▤</div><span class="panel-kicker">Spreadsheet exports</span><h3>Export from each module</h3><p>Use the Export CSV button on Clients, Income, Expenses, or Vouchers to download a spreadsheet-friendly file.</p><div class="backup-links"><a href="{{ route('admin.clients.index') }}">Clients</a><a href="{{ route('admin.incomes.index') }}">Income</a><a href="{{ route('admin.expenses.index') }}">Expenses</a><a href="{{ route('admin.vouchers.index') }}">Vouchers</a></div></article>
    </section>

    <section class="setup-card backup-note"><div class="setup-number">TIP</div><div><span class="panel-kicker">Good practice</span><h3>Keep a regular copy of your data</h3><p>Store downloaded backups somewhere secure. The backup contains account and financial history, so treat it like a sensitive business document.</p></div></section>
@endsection
