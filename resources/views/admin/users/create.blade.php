@extends('layouts.admin', ['title' => 'Add user', 'heading' => 'Add user'])

@section('content')
    <div class="page-heading-row"><div><span class="panel-kicker">Access control</span><h2 class="page-title">Add a user</h2><p class="page-copy">Create an account and choose its starting role.</p></div><a class="secondary-button" href="{{ route('admin.users.index') }}">← Back to users</a></div>
    <section class="content-card form-card"><form method="POST" action="{{ route('admin.users.store') }}" class="entity-form">@csrf @include('admin.users._form')<div class="form-actions"><a class="secondary-button" href="{{ route('admin.users.index') }}">Cancel</a><button class="primary-button compact-button" type="submit">Create user <span>→</span></button></div></form></section>
@endsection
