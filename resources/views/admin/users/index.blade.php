@extends('layouts.admin', ['title' => 'Users & roles', 'heading' => 'Users & roles'])

@section('content')
    <div class="page-heading-row">
        <div><span class="panel-kicker">Access control</span><h2 class="page-title">Users & roles</h2><p class="page-copy">Manage who can access the Hishab workspace.</p></div>
        <a class="primary-button compact-button" href="{{ route('admin.users.create') }}">Add user <span>＋</span></a>
    </div>

    @if ($errors->has('user'))<div class="alert error-alert">{{ $errors->first('user') }}</div>@endif
    <section class="content-card table-card">
        <div class="table-toolbar"><form method="GET" action="{{ route('admin.users.index') }}" class="search-form"><span>⌕</span><input type="search" name="q" value="{{ $search }}" placeholder="Search by name or email"></form><span class="card-count">{{ $users->total() }} total users</span></div>
        @if ($users->isEmpty())
            <div class="empty-state large-empty"><span>♙</span><strong>{{ $search ? 'No users found' : 'No users added yet' }}</strong><p>{{ $search ? 'Try a different search term.' : 'Add another user when you are ready.' }}</p></div>
        @else
            <div class="table-wrap"><table class="data-table"><thead><tr><th>User</th><th>Role</th><th>Status</th><th>Joined</th><th></th></tr></thead><tbody>
                @foreach ($users as $user)
                    <tr><td><div class="table-person"><span class="table-avatar user-table-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</span><span><strong>{{ $user->name }}</strong><small>{{ $user->email }}{{ $user->is(auth()->user()) ? ' · You' : '' }}</small></span></div></td><td><span class="role-badge {{ $user->role }}-role">{{ $roles[$user->role] ?? str($user->role)->headline() }}</span></td><td><span class="status-badge {{ $user->is_active ? 'active-badge' : 'inactive-badge' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span></td><td><span class="table-date">{{ $user->created_at->format('d M Y') }}</span></td><td><div class="row-actions"><a href="{{ route('admin.users.edit', $user) }}">Edit</a>@if (! $user->is(auth()->user()))<form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Remove this user?')">@csrf @method('DELETE')<button type="submit">Remove</button></form>@endif</div></td></tr>
                @endforeach
            </tbody></table></div>
            @if ($users->hasPages())<div class="pagination-wrap">{{ $users->links() }}</div>@endif
        @endif
    </section>
@endsection
