@extends('layouts.admin', ['title' => 'Edit expense', 'heading' => 'Edit expense'])

@section('content')
    <div class="page-heading-row"><div><span class="panel-kicker">Money out</span><h2 class="page-title">Update expense</h2><p class="page-copy">Keep this expense record accurate.</p></div><a class="secondary-button" href="{{ route('admin.expenses.index') }}">← Back to expenses</a></div>
    <section class="content-card form-card"><form method="POST" action="{{ route('admin.expenses.update', $expense) }}" enctype="multipart/form-data" class="entity-form">@csrf @method('PUT') @include('admin.expenses._form')<div class="form-actions"><a class="secondary-button" href="{{ route('admin.expenses.index') }}">Cancel</a><button class="primary-button compact-button" type="submit">Update expense <span>→</span></button></div></form></section>
@endsection
