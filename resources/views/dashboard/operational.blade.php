@extends('layouts.app')

@section('title', 'Dashboard Operasional')

@section('content')
    <section class="hero">
        <div class="panel hero-copy">
            <div class="eyebrow">Dashboard Operasional</div>
            <h1>ERP toko internal untuk transaksi semi-manual dan stok harian.</h1>
            <p>Halaman ini sengaja fokus ke status kerja nyata: tiket pending, kebutuhan validasi, dan produk stok tipis.</p>
        </div>
        <div class="panel">
            <div class="eyebrow">Outlet Aktif</div>
            <h2>{{ $outletCount }}</h2>
            <p class="muted">Outlet terdaftar di sistem</p>
        </div>
    </section>

    <section class="grid-3" style="margin-bottom: 20px;">
        <div class="panel">
            <h2>{{ $openCashSessionsCount }}</h2>
            <p class="muted">Sesi kas terbuka</p>
        </div>
        <div class="panel">
            <h2>{{ $escalatedPendingCount }}</h2>
            <p class="muted">Queue escalated >= 30 menit</p>
        </div>
        @foreach ($statusCounts as $status => $count)
            <div class="panel">
                <h2>{{ $count }}</h2>
                <p class="muted">{{ str_replace('_', ' ', $status) }}</p>
            </div>
        @endforeach
    </section>

    <section class="section-grid">
        <div class="panel">
            <div class="hero" style="margin-bottom: 8px;">
                <div>
                    <h2>Antrian Validasi</h2>
                    <p class="muted">Tiket yang masih butuh proses operator atau validasi akhir.</p>
                </div>
                <a class="button button-primary" href="{{ route('digital-transactions.queue') }}">Buka Queue</a>
            </div>

            @if ($pendingTransactions->isEmpty())
                <p class="muted">Belum ada transaksi yang sedang diproses atau menunggu validasi.</p>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Layanan</th>
                            <th>Tujuan</th>
                            <th>Status</th>
                            <th>Outlet</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pendingTransactions as $transaction)
                            <tr>
                                <td><a href="{{ route('digital-transactions.show', $transaction) }}">{{ $transaction->code }}</a></td>
                                <td>{{ $transaction->digitalService?->name ?? '-' }}</td>
                                    <td>{{ $transaction->destination_account }}</td>
                                    <td>
                                        <span class="pill {{ $transaction->status->value === 'pending_validasi' ? 'warn' : '' }}">
                                            {{ str_replace('_', ' ', $transaction->status->value) }}
                                        </span>
                                        @if ($transaction->submitted_at->diffInMinutes(now()) >= 30)
                                            <span class="pill danger">Escalated</span>
                                        @endif
                                    </td>
                                    <td>{{ $transaction->outlet?->name ?? '-' }}</td>
                                </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="panel">
            <h2>Stok Kritis</h2>
            <p class="muted">Produk dengan stok di bawah atau sama dengan batas minimum.</p>
            <div class="actions" style="margin-top: 0; margin-bottom: 12px;">
                <a class="button" href="{{ route('pos.index') }}">Buka POS</a>
                <a class="button" href="{{ route('cash-sessions.index') }}">Sesi kas</a>
            </div>

            @if ($lowStockProducts->isEmpty())
                <p class="muted">Belum ada produk yang terdeteksi di bawah batas minimum.</p>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Produk</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lowStockProducts as $product)
                            <tr>
                                <td>{{ $product->sku }}</td>
                                <td>{{ $product->name }}</td>
                                <td><span class="pill danger">Perlu restock</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </section>
@endsection
