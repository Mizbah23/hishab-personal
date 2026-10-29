@extends('layouts.admin', ['title' => 'Edit user', 'heading' => 'Edit user'])

@section('content')
    <div class="page-heading-row"><div><span class="panel-kicker">Access control</span><h2 class="page-title">Update {{ $user->name }}</h2><p class="page-copy">Change this user's role, status, or account details.</p></div><a class="secondary-button" href="{{ route('admin.users.index') }}">← Back to users</a></div>
    <section class="content-card form-card"><form method="POST" action="{{ route('admin.users.update', $user) }}" class="entity-form">@csrf @method('PUT') @include('admin.users._form')<div class="form-actions"><a class="secondary-button" href="{{ route('admin.users.index') }}">Cancel</a><button class="primary-button compact-button" type="submit">Update user <span>→</span></button></div></form></section>
@endsection
