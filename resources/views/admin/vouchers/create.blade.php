@extends('layouts.admin', ['title' => 'Add voucher', 'heading' => 'Add voucher'])

@section('content')
    <div class="page-heading-row"><div><span class="panel-kicker">Documents</span><h2 class="page-title">Create a voucher</h2><p class="page-copy">Record the document details for an income, expense, or general entry.</p></div><a class="secondary-button" href="{{ route('admin.vouchers.index') }}">← Back to vouchers</a></div>
    <section class="content-card form-card"><form method="POST" action="{{ route('admin.vouchers.store') }}" class="entity-form">@csrf @include('admin.vouchers._form')<div class="form-actions"><a class="secondary-button" href="{{ route('admin.vouchers.index') }}">Cancel</a><button class="primary-button compact-button" type="submit">Save voucher <span>→</span></button></div></form></section>
@endsection
