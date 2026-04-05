<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Penjualan - {{ $sale->code }}</title>
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
    <p>{{ $sale->outlet->name }}</p>
    <p>{{ $sale->outlet->address ?? 'Alamat Outlet' }}</p>
    
    <div class="divider"></div>
    <p class="text-left">No: {{ $sale->code }}</p>
    <p class="text-left">Tgl: {{ $sale->sold_at->format('d/m/Y H:i') }}</p>
    <p class="text-left">Kasir: {{ $sale->cashier->name }}</p>
    @if($sale->customer)
    <p class="text-left">Pelanggan: {{ $sale->customer->name }}</p>
    @endif
    <div class="divider"></div>

    <table>
        @foreach($sale->items as $item)
        <tr>
            <td colspan="3" class="text-left">{{ $item->product->name }}</td>
        </tr>
        <tr>
            <td class="text-left">{{ $item->qty }} x {{ number_format($item->unit_price_amount, 0, ',', '.') }}</td>
            <td class="text-right">@if($item->discount_amount > 0) (Diskon: {{ number_format($item->discount_amount, 0, ',', '.') }}) @endif</td>
            <td class="text-right">{{ number_format($item->line_total_amount, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </table>

    <div class="divider"></div>

    <table>
        <tr>
            <td class="text-left bold">Subtotal</td>
            <td class="text-right bold">{{ number_format($sale->subtotal_amount, 0, ',', '.') }}</td>
        </tr>
        @if($sale->discount_amount > 0)
        <tr>
            <td class="text-left">Diskon Total</td>
            <td class="text-right">-{{ number_format($sale->discount_amount, 0, ',', '.') }}</td>
        </tr>
        @endif
        <tr>
            <td class="text-left bold">Total</td>
            <td class="text-right bold">{{ number_format($sale->total_amount, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="text-left">Tunai / Dibayar</td>
            <td class="text-right">{{ number_format($sale->paid_amount, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="text-left">Kembali</td>
            <td class="text-right">{{ number_format($sale->change_amount, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="divider"></div>
    <p>Terima Kasih</p>
    <p>Barang yang sudah dibeli tidak dapat ditukar/dikembalikan.</p>
</body>
</html>
