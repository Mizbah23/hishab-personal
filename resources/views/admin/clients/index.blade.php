@extends('layouts.admin', ['title' => 'Clients', 'heading' => 'Clients'])

@section('content')
    <div class="page-heading-row">
        <div>
            <span class="panel-kicker">Relationships</span>
            <h2 class="page-title">Your clients</h2>
            <p class="page-copy">Keep the people and companies behind your work organised.</p>
        </div>
        <div class="page-actions">@if (auth()->user()->hasPermission('exports.view'))<a class="secondary-button" href="{{ route('admin.exports.clients', request()->query()) }}">Export CSV</a>@endif @if (auth()->user()->hasPermission('clients.manage'))<a class="primary-button compact-button" href="{{ route('admin.clients.create') }}">Add client <span>＋</span></a>@endif</div>
    </div>

    <section class="content-card table-card">
        <div class="table-toolbar">
            <form method="GET" action="{{ route('admin.clients.index') }}" class="search-form">
                <span>⌕</span>
                <input type="search" name="q" value="{{ $search }}" placeholder="Search by name, company, or email">
            </form>
            <span class="card-count">{{ $clients->total() }} total clients</span>
        </div>

        @if ($clients->isEmpty())
            <div class="empty-state large-empty"><span>◎</span><strong>{{ $search ? 'No clients found' : 'No clients added yet' }}</strong><p>{{ $search ? 'Try a different search term.' : 'Start by adding your first client.' }}</p></div>
        @else
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>Client</th><th>Contact</th><th>Status</th><th>Added</th>@if (auth()->user()->hasPermission('clients.manage'))<th></th>@endif</tr></thead>
                    <tbody>
                        @foreach ($clients as $client)
                            <tr>
                                <td><div class="table-person"><span class="table-avatar">{{ strtoupper(substr($client->name, 0, 1)) }}</span><span><strong>{{ $client->name }}</strong><small>{{ $client->company_name ?? 'Individual client' }}</small></span></div></td>
                                <td><span class="table-contact">{{ $client->email ?? '—' }}<small>{{ $client->phone ?? 'No phone added' }}</small></span></td>
                                <td><span class="status-badge {{ $client->is_active ? 'active-badge' : 'inactive-badge' }}">{{ $client->is_active ? 'Active' : 'Inactive' }}</span></td>
                                <td><span class="table-date">{{ $client->created_at->format('d M Y') }}</span></td>
                                @if (auth()->user()->hasPermission('clients.manage'))<td><div class="row-actions"><a href="{{ route('admin.clients.edit', $client) }}">Edit</a><form method="POST" action="{{ route('admin.clients.destroy', $client) }}" onsubmit="return confirm('Remove this client?')">@csrf @method('DELETE')<button type="submit">Remove</button></form></div></td>@endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($clients->hasPages())
                <div class="pagination-wrap">{{ $clients->links() }}</div>
            @endif
        @endif
    </section>
@endsection
