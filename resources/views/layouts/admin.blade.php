<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Admin' }} · {{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/hishab-favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="app-shell">
    <aside class="sidebar">
        <a class="brand" href="{{ route('admin.dashboard') }}">
            <img class="brand-logo" src="{{ asset('images/hishab-logo.png') }}" alt="Hishab logo">
            <span><strong>Hishab</strong><small>Personal accounts</small></span>
        </a>

        <nav class="nav-list" aria-label="Main navigation">
            <span class="nav-label">Workspace</span>
            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                <span class="nav-icon">⌂</span> Dashboard
            </a>
            <a class="nav-link {{ request()->routeIs('admin.progress') ? 'active' : '' }}" href="{{ route('admin.progress') }}">
                <span class="nav-icon">◌</span> Build progress
            </a>
            <a class="nav-link {{ request()->routeIs('admin.reports') ? 'active' : '' }}" href="{{ route('admin.reports') }}">
                <span class="nav-icon">▥</span> Reports
            </a>
            <span class="nav-label">Manage</span>
            @if (auth()->user()->hasPermission('incomes.view'))<a class="nav-link {{ request()->routeIs('admin.incomes.*') ? 'active' : '' }}" href="{{ route('admin.incomes.index') }}"><span class="nav-icon">↗</span> Income</a>@endif
            @if (auth()->user()->hasPermission('expenses.view'))<a class="nav-link {{ request()->routeIs('admin.expenses.*') ? 'active' : '' }}" href="{{ route('admin.expenses.index') }}"><span class="nav-icon">↘</span> Expenses</a>@endif
            @if (auth()->user()->hasPermission('clients.view'))<a class="nav-link {{ request()->routeIs('admin.clients.*') ? 'active' : '' }}" href="{{ route('admin.clients.index') }}"><span class="nav-icon">◎</span> Clients</a>@endif
            @if (auth()->user()->hasPermission('vouchers.view'))<a class="nav-link {{ request()->routeIs('admin.vouchers.*') ? 'active' : '' }}" href="{{ route('admin.vouchers.index') }}"><span class="nav-icon">▤</span> Vouchers</a>@endif
            @if (auth()->user()->hasPermission('users.manage'))<a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}"><span class="nav-icon">♙</span> Users & roles</a>@endif
            @if (auth()->user()->hasPermission('audit.view'))<a class="nav-link {{ request()->routeIs('admin.activity.*') ? 'active' : '' }}" href="{{ route('admin.activity.index') }}"><span class="nav-icon">◷</span> Activity history</a>@endif
            @if (auth()->user()->hasPermission('backups.manage'))<a class="nav-link {{ request()->routeIs('admin.backups.*') ? 'active' : '' }}" href="{{ route('admin.backups.index') }}"><span class="nav-icon">⇩</span> Exports & backups</a>@endif
        </nav>

        <div class="sidebar-bottom">
            <div class="user-chip">
                <span class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                <span><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->roleLabel() }}</small></span>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="logout-button" type="submit">Sign out <span>→</span></button>
            </form>
        </div>
    </aside>

    <main class="main-content">
        @if (session('status'))
            <div class="alert success">{{ session('status') }}</div>
        @endif

        @yield('content')
    </main>
</body>
</html>
