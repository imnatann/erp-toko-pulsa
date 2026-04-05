@extends('layouts.app')

@section('title', 'POS Barang')

@section('content')
    <section class="hero">
        <div class="panel hero-copy">
            <div class="eyebrow">POS Barang Fisik</div>
            <h1>Checkout cepat untuk aksesoris dan barang fisik.</h1>
            <p>POS ini hanya memproses barang fisik. Stok akan berkurang otomatis dan kas final diposting saat checkout berhasil.</p>
        </div>
        <div class="panel">
            <div class="eyebrow">Sesi Kas</div>
            <p><strong>{{ $activeSession ? 'Aktif' : 'Belum dibuka' }}</strong></p>
            <a class="button" href="{{ route('cash-sessions.index') }}">Kelola sesi kas</a>
        </div>
    </section>

    <section class="section-grid">
        <div class="panel">
            <h2>Form checkout</h2>

            @if (! $activeSession)
                <p class="muted">Buka sesi kas dulu sebelum checkout.</p>
            @else
                <form method="post" action="{{ route('pos.store') }}" class="stack">
                    @csrf

                    <div class="grid-2">
                        <label>
                            Metode pembayaran
                            <select name="payment_method" required>
                                <option value="cash">Tunai</option>
                                <option value="qris">QRIS</option>
                                <option value="transfer">Transfer</option>
                            </select>
                        </label>

                        <label>
                            Nominal dibayar pelanggan
                            <input type="number" name="paid_amount" min="0" required>
                        </label>
                    </div>

                    <div class="stack">
                        @foreach ($products as $index => $product)
                            <div class="panel">
                                <div class="grid-3">
                                    <div>
                                        <strong>{{ $product->name }}</strong>
                                        <div class="muted">{{ $product->sku }}</div>
                                    </div>
                                    <div>
                                        <div>Harga jual: Rp {{ number_format($product->selling_price_amount) }}</div>
                                        <div class="muted">Stok: {{ $product->on_hand_qty }}</div>
                                    </div>
                                    <label>
                                        Qty
                                        <input type="number" min="0" name="items[{{ $index }}][qty]" value="0">
                                        <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $product->id }}">
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="actions">
                        <button class="button button-primary" type="submit">Checkout barang</button>
                    </div>
                </form>
            @endif
        </div>

        <div class="panel">
            <h2>Transaksi terakhir</h2>
            <ul class="list-reset">
                @forelse ($recentSales as $sale)
                    <li>
                        <strong>{{ $sale->code }}</strong>
                        <div class="muted">{{ $sale->sold_at?->format('d M Y H:i') }} - Rp {{ number_format($sale->total_amount) }}</div>
                        <div>
                            @foreach ($sale->items as $item)
                                <span class="pill">{{ $item->product?->name }} x{{ $item->qty }}</span>
                            @endforeach
                        </div>
                    </li>
                @empty
                    <li class="muted">Belum ada transaksi POS.</li>
                @endforelse
            </ul>
        </div>
    </section>
@endsection
