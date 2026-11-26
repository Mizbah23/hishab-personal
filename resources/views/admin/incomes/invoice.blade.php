<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $invoiceNumber }}</title>
    <style>
        * { box-sizing:border-box; }
        body { margin:0; background:#f4f7f5; color:#17211f; font:16px/1.5 system-ui,sans-serif; }
        main { max-width:780px; margin:24px auto; padding:clamp(20px,6vw,48px); background:white; overflow-wrap:anywhere; }
        nav, header { display:flex; flex-wrap:wrap; justify-content:space-between; gap:16px; }
        nav, header, section { margin-bottom:28px; }
        h1, h2, p { margin:0 0 6px; }
        h2 { font-size:15px; }
        table { width:100%; border-collapse:collapse; table-layout:fixed; }
        th, td { padding:12px 6px; text-align:left; border-bottom:1px solid #dde7e1; }
        th:last-child, td:last-child { width:40%; text-align:right; }
        button { padding:10px 14px; color:white; background:#2d6a58; border:0; border-radius:6px; cursor:pointer; font:inherit; }
        .invoice-issuer { margin-left:auto; text-align:right; }
        .invoice-qr { display:block; margin:8px 0 0 auto; }
        @page { margin:0; }
        @media print { body { background:white; } main { margin:0; padding:14mm; } nav { display:none; } header, tr { break-inside:avoid; } }
    </style>
</head>
<body>
<main>
    <nav aria-label="Invoice actions">
        <a href="{{ route('admin.incomes.index') }}">Back to income</a>
        <button type="button" onclick="window.print()">Print / Save as PDF</button>
    </nav>
    <header>
        <div><h1>Invoice</h1><p>{{ $invoiceNumber }}</p></div>
        <div class="invoice-issuer">
            <strong>Uddog Soft Inc.</strong>
            <p>{{ $income->due_amount > 0 ? 'Sale recorded' : 'Paid' }} {{ $income->received_on->format('d M Y') }}</p>
            <img class="invoice-qr" src="{{ asset('images/uddog-soft-invoice-qr.png') }}" width="104" height="104" alt="QR code for uddog-soft.com">
        </div>
    </header>
    <section aria-label="Client details">
        <h2>Bill to</h2>
        <p>{{ $client->name }}</p>
        @if ($client->email)<p>{{ $client->email }}</p>@endif
        @if ($client->address)<p>{{ $client->address }}</p>@endif
    </section>
    <table>
        <thead><tr><th scope="col">Description</th><th scope="col">Amount (BDT)</th></tr></thead>
        <tbody><tr><td>{{ $income->title }}</td><td>{{ number_format((float) $income->amount + (float) $income->due_amount, 2) }}</td></tr></tbody>
        <tfoot>
            @if ($income->due_amount > 0)
                <tr><th scope="row">Sale total</th><td>BDT {{ number_format((float) $income->amount + (float) $income->due_amount, 2) }}</td></tr>
            @endif
            <tr><th scope="row">Total paid</th><td><strong>BDT {{ number_format((float) $income->amount, 2) }}</strong></td></tr>
            @if ($income->due_amount > 0)
                <tr><th scope="row">Balance due</th><td><strong>BDT {{ number_format((float) $income->due_amount, 2) }}</strong></td></tr>
            @endif
        </tfoot>
    </table>
</main>
</body>
</html>
