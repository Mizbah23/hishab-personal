@extends('layouts.admin', ['title' => 'Record expense', 'heading' => 'Record expense'])

@section('content')
    <div class="page-heading-row"><div><span class="panel-kicker">Money out</span><h2 class="page-title">Record expense</h2><p class="page-copy">Add a cost to keep your account balance accurate.</p></div><a class="secondary-button" href="{{ route('admin.expenses.index') }}">← Back to expenses</a></div>
    <section class="content-card form-card"><form method="POST" action="{{ route('admin.expenses.store') }}" enctype="multipart/form-data" class="entity-form">@csrf @include('admin.expenses._form')<div class="form-actions"><a class="secondary-button" href="{{ route('admin.expenses.index') }}">Cancel</a><button class="primary-button compact-button" type="submit">Save expense <span>→</span></button></div></form></section>
@endsection
