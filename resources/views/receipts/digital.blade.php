<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Transaksi - {{ $transaction->code }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            width: 300px;
            margin: 0 auto;
            padding: 10px;
        }
        h2, h3, h4, p {
            margin: 0;
            padding: 0;
            text-align: center;
        }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .divider { border-bottom: 1px dashed #000; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 2px 0; }
        .bold { font-weight: bold; }
    </style>
</head>
<body onload="window.print()">
    <h2>ERP KONTER</h2>
    <p>{{ $transaction->outlet->name }}</p>
    <p>{{ $transaction->outlet->address ?? 'Alamat Outlet' }}</p>
    
    <div class="divider"></div>
    <p class="text-left">No: {{ $transaction->code }}</p>
    <p class="text-left">Tgl: {{ $transaction->validated_at ? $transaction->validated_at->format('d/m/Y H:i') : $transaction->created_at->format('d/m/Y H:i') }}</p>
    <p class="text-left">Status: {{ $transaction->status->getLabel() }}</p>
    @if($transaction->customer)
    <p class="text-left">Pelanggan: {{ $transaction->customer->name }}</p>
    @endif
    <div class="divider"></div>

    <table>
        <tr>
            <td colspan="2" class="text-left">{{ $transaction->digitalService->name }}</td>
        </tr>
        <tr>
            <td class="text-left">No Tujuan:</td>
            <td class="text-right">{{ $transaction->destination_account }}</td>
        </tr>
        @if($transaction->destination_name)
        <tr>
            <td class="text-left">Nama Tujuan:</td>
            <td class="text-right">{{ $transaction->destination_name }}</td>
        </tr>
        @endif
        <tr>
            <td class="text-left">Provider:</td>
            <td class="text-right">{{ $transaction->digitalService->provider ?? '-' }}</td>
        </tr>
        @if($transaction->external_reference)
        <tr>
            <td class="text-left">Ref/SN:</td>
            <td class="text-right">{{ $transaction->external_reference }}</td>
        </tr>
        @endif
    </table>

    <div class="divider"></div>

    <table>
        <tr>
            <td class="text-left bold">Nominal</td>
            <td class="text-right bold">{{ number_format($transaction->nominal_amount, 0, ',', '.') }}</td>
        </tr>
        @if($transaction->fee_amount > 0)
        <tr>
            <td class="text-left">Admin/Fee</td>
            <td class="text-right">{{ number_format($transaction->fee_amount, 0, ',', '.') }}</td>
        </tr>
        @endif
        <tr>
            <td class="text-left bold">Total</td>
            <td class="text-right bold">{{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="divider"></div>
    <p>Terima Kasih</p>
    <p>Simpan struk ini sebagai bukti pembayaran yang sah.</p>
</body>
</html>
