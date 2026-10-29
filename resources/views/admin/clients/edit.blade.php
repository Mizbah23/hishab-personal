@extends('layouts.admin', ['title' => 'Edit client', 'heading' => 'Edit client'])

@section('content')
    <div class="page-heading-row">
        <div><span class="panel-kicker">Client directory</span><h2 class="page-title">Update {{ $client->name }}</h2><p class="page-copy">Keep this client's information current.</p></div>
        <a class="secondary-button" href="{{ route('admin.clients.index') }}">← Back to clients</a>
    </div>
    <section class="content-card form-card">
        <form method="POST" action="{{ route('admin.clients.update', $client) }}" class="entity-form">
            @csrf @method('PUT')
            @include('admin.clients._form')
            <div class="form-actions"><a class="secondary-button" href="{{ route('admin.clients.index') }}">Cancel</a><button class="primary-button compact-button" type="submit">Update client <span>→</span></button></div>
        </form>
    </section>
@endsection
