@extends('layouts.admin', ['title' => 'Record income', 'heading' => 'Record income'])

@section('content')
    <div class="page-heading-row"><div><span class="panel-kicker">Money in</span><h2 class="page-title">Record income</h2><p class="page-copy">Add a payment or revenue entry to your accounts.</p></div><a class="secondary-button" href="{{ route('admin.incomes.index') }}">← Back to income</a></div>
    <section class="content-card form-card"><form method="POST" action="{{ route('admin.incomes.store') }}" enctype="multipart/form-data" class="entity-form">@csrf @include('admin.incomes._form')<div class="form-actions"><a class="secondary-button" href="{{ route('admin.incomes.index') }}">Cancel</a><button class="primary-button compact-button" type="submit">Save income <span>→</span></button></div></form></section>
@endsection
