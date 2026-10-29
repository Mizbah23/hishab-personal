@extends('layouts.admin', ['title' => 'Edit income', 'heading' => 'Edit income'])

@section('content')
    <div class="page-heading-row"><div><span class="panel-kicker">Money in</span><h2 class="page-title">Update income</h2><p class="page-copy">Keep this income record accurate.</p></div><a class="secondary-button" href="{{ route('admin.incomes.index') }}">← Back to income</a></div>
    <section class="content-card form-card"><form method="POST" action="{{ route('admin.incomes.update', $income) }}" enctype="multipart/form-data" class="entity-form">@csrf @method('PUT') @include('admin.incomes._form')<div class="form-actions"><a class="secondary-button" href="{{ route('admin.incomes.index') }}">Cancel</a><button class="primary-button compact-button" type="submit">Update income <span>→</span></button></div></form></section>
@endsection
