@extends('layouts.admin', ['title' => 'Add client', 'heading' => 'Add client'])

@section('content')
    <div class="page-heading-row">
        <div><span class="panel-kicker">Client directory</span><h2 class="page-title">Add a client</h2><p class="page-copy">Save the contact details you need for future income records.</p></div>
        <a class="secondary-button" href="{{ route('admin.clients.index') }}">← Back to clients</a>
    </div>
    <section class="content-card form-card">
        <form method="POST" action="{{ route('admin.clients.store') }}" class="entity-form">
            @csrf
            @include('admin.clients._form')
            <div class="form-actions"><a class="secondary-button" href="{{ route('admin.clients.index') }}">Cancel</a><button class="primary-button compact-button" type="submit">Save client <span>→</span></button></div>
        </form>
    </section>
@endsection
