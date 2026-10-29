@extends('layouts.admin', ['title' => 'Activity history', 'heading' => 'Activity history'])

@section('content')
    <div class="page-heading-row">
        <div><span class="panel-kicker">Accountability</span><h2 class="page-title">What changed, and when.</h2><p class="page-copy">A record of financial and client activity across the workspace.</p></div>
    </div>

    <section class="content-card activity-filter-card">
        <form method="GET" action="{{ route('admin.activity.index') }}" class="activity-filters">
            <div class="search-form"><span>⌕</span><input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search activity"></div>
            <select name="module"><option value="">All modules</option>@foreach ($modules as $value => $label)<option value="{{ $value }}" {{ ($filters['module'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select>
            <select name="action"><option value="">All actions</option>@foreach (['created' => 'Created', 'updated' => 'Updated', 'deleted' => 'Deleted'] as $value => $label)<option value="{{ $value }}" {{ ($filters['action'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select>
            <input name="from" type="date" value="{{ $filters['from'] ?? '' }}" aria-label="From date">
            <input name="to" type="date" value="{{ $filters['to'] ?? '' }}" aria-label="To date">
            <button class="filter-button" type="submit">Filter</button>
            @if (count($filters))<a class="clear-filter" href="{{ route('admin.activity.index') }}">Clear</a>@endif
        </form>
    </section>

    <section class="content-card table-card activity-table-card">
        @if ($logs->isEmpty())
            <div class="empty-state large-empty"><span>◷</span><strong>No activity found</strong><p>New changes will appear here automatically.</p></div>
        @else
            <div class="table-toolbar"><span class="panel-kicker">Recent changes</span><span class="card-count">{{ $logs->total() }} total events</span></div>
            <div class="table-wrap"><table class="data-table"><thead><tr><th>Activity</th><th>Module</th><th>Changed by</th><th>When</th><th>Details</th></tr></thead><tbody>
                @foreach ($logs as $log)
                    <tr><td><span class="activity-action {{ $log->action }}-action">{{ str($log->action)->headline() }}</span><strong class="activity-description">{{ $log->description }}</strong></td><td><span class="type-badge activity-module">{{ str(class_basename($log->auditable_type))->headline() }}</span></td><td><span class="table-contact">{{ $log->user?->name ?? 'System' }}<small>{{ $log->user?->roleLabel() ?? 'Automated event' }}</small></span></td><td><span class="table-date">{{ $log->created_at->format('d M Y, h:i A') }}</span></td><td>@if ($log->changes)<span class="change-count">{{ count($log->changes) }} {{ str('field')->plural(count($log->changes)) }}</span>@else<span class="table-date">No field details</span>@endif</td></tr>
                @endforeach
            </tbody></table></div>
            @if ($logs->hasPages())<div class="pagination-wrap">{{ $logs->links() }}</div>@endif
        @endif
    </section>
@endsection
