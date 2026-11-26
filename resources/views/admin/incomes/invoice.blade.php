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
        @media print { body { background:white; } main { margin:0; padding:0; } nav { display:none; } tr { break-inside:avoid; } }
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
        <div><strong>{{ config('app.name') }}</strong><p>Paid {{ $income->received_on->format('d M Y') }}</p></div>
    </header>
    <section aria-label="Client details">
        <h2>Bill to</h2>
        <p>{{ $client->name }}</p>
        @if ($client->email)<p>{{ $client->email }}</p>@endif
        @if ($client->address)<p>{{ $client->address }}</p>@endif
    </section>
    <table>
        <thead><tr><th scope="col">Description</th><th scope="col">Amount (BDT)</th></tr></thead>
        <tbody><tr><td>{{ $income->title }}</td><td>{{ number_format((float) $income->amount, 2) }}</td></tr></tbody>
        <tfoot><tr><th scope="row">Total paid</th><td><strong>BDT {{ number_format((float) $income->amount, 2) }}</strong></td></tr></tfoot>
    </table>
</main>
</body>
</html>
