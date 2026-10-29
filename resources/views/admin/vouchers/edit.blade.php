@extends('layouts.admin', ['title' => 'Edit voucher', 'heading' => 'Edit voucher'])

@section('content')
    <div class="page-heading-row"><div><span class="panel-kicker">Documents</span><h2 class="page-title">Update voucher</h2><p class="page-copy">Keep this voucher's details accurate.</p></div><a class="secondary-button" href="{{ route('admin.vouchers.index') }}">← Back to vouchers</a></div>
    <section class="content-card form-card"><form method="POST" action="{{ route('admin.vouchers.update', $voucher) }}" class="entity-form">@csrf @method('PUT') @include('admin.vouchers._form')<div class="form-actions"><a class="secondary-button" href="{{ route('admin.vouchers.index') }}">Cancel</a><button class="primary-button compact-button" type="submit">Update voucher <span>→</span></button></div></form></section>
@endsection
