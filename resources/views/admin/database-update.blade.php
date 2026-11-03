@extends('layouts.admin', ['title' => 'Database update'])
@section('content')
<div style="position:fixed;inset:0;z-index:1000;background:rgba(23,33,31,.65);display:grid;place-items:center;padding:20px">
    <section class="content-card" role="dialog" aria-modal="true" aria-labelledby="update-title" style="max-width:480px;min-height:0;padding:30px">
        <h2 id="update-title">Database update required</h2>
        <p>Please update the database before continuing your work.</p>
        @error('update')<p class="field-error">{{ $message }}</p>@enderror
        @if(auth()->user()->isAdmin())
            <p>Please ensure a current database backup is available before updating.</p>
            <form method="POST" action="{{ route('admin.database-update.store') }}" onsubmit="this.querySelector('button').disabled=true;this.querySelector('button').textContent='Updating…'">
                @csrf
                <button class="primary-button" type="submit">Update database</button>
            </form>
        @else
            <p>An administrator must complete this update.</p>
        @endif
    </section>
</div>
@endsection
