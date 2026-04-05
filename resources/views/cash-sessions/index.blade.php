@extends('layouts.app')

@section('title', 'Sesi Kas')

@section('content')
    <section class="hero">
        <div class="panel hero-copy">
            <div class="eyebrow">Cash Session</div>
            <h1>Sesi buka dan tutup kas outlet.</h1>
            <p>Kasir wajib membuka sesi sebelum checkout barang fisik. Penutupan sesi membantu rekap kas harian dan audit operasional.</p>
        </div>
    </section>

    <section class="section-grid">
        <div class="panel">
            <h2>Sesi aktif outlet</h2>
            @if ($activeSession)
                <p><strong>{{ $activeSession->outlet?->name ?? 'Outlet' }}</strong></p>
                <p class="muted">Dibuka {{ $activeSession->opened_at?->format('d M Y H:i') }} oleh {{ $activeSession->opener?->name }}</p>
                <p>Saldo awal: Rp {{ number_format($activeSession->opening_balance_amount) }}</p>

                <form method="post" action="{{ route('cash-sessions.close', $activeSession) }}" class="stack">
                    @csrf
                    <label>
                        Saldo penutupan
                        <input type="number" name="closing_balance_amount" min="0" required>
                    </label>
                    <label>
                        Catatan penutupan
                        <textarea name="closing_note"></textarea>
                    </label>
                    <div class="actions">
                        <button class="button button-warning" type="submit">Tutup sesi kas</button>
                    </div>
                </form>
            @else
                <p class="muted">Belum ada sesi aktif di outlet ini.</p>
                <form method="post" action="{{ route('cash-sessions.store') }}" class="stack">
                    @csrf
                    <label>
                        Saldo awal
                        <input type="number" name="opening_balance_amount" min="0" required>
                    </label>
                    <div class="actions">
                        <button class="button button-primary" type="submit">Buka sesi kas</button>
                    </div>
                </form>
            @endif
        </div>

        <div class="panel">
            <h2>Riwayat sesi</h2>
            <table>
                <thead>
                    <tr>
                        <th>Outlet</th>
                        <th>Status</th>
                        <th>Dibuka</th>
                        <th>Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sessions as $session)
                        <tr>
                            <td>{{ $session->outlet?->name ?? '-' }}</td>
                            <td><span class="pill {{ $session->status->value === 'open' ? 'warn' : 'success' }}">{{ $session->status->value }}</span></td>
                            <td>{{ $session->opened_at?->format('d M Y H:i') }}</td>
                            <td>Rp {{ number_format($session->opening_balance_amount) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="muted">Belum ada riwayat sesi kas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $sessions->links() }}
        </div>
    </section>
@endsection
