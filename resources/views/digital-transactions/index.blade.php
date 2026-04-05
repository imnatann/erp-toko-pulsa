@extends('layouts.app')

@section('title', 'Transaksi Digital')

@section('content')
    <section class="hero">
        <div class="panel hero-copy">
            <div class="eyebrow">Ticketing Digital</div>
            <h1>Daftar transaksi digital semi-manual.</h1>
            <p>Filter ini membantu operator dan supervisor fokus ke tiket yang masih perlu diproses, divalidasi, atau direview.</p>
        </div>
        <a class="button button-primary" href="{{ route('digital-transactions.create') }}">Buat tiket baru</a>
    </section>

    <section class="panel stack">
        <form method="get" class="grid-2">
            <label>
                Status
                <select name="status">
                    <option value="">Semua status</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected($currentStatus === $status->value)>
                            {{ str_replace('_', ' ', $status->value) }}
                        </option>
                    @endforeach
                </select>
            </label>

            <div class="actions" style="align-items: end;">
                <button class="button button-primary" type="submit">Filter</button>
                <a class="button" href="{{ route('digital-transactions.index') }}">Reset</a>
            </div>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Layanan</th>
                    <th>Tujuan</th>
                    <th>Status</th>
                    <th>Outlet</th>
                    <th>Pembuat</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transactions as $transaction)
                    <tr>
                        <td><a href="{{ route('digital-transactions.show', $transaction) }}">{{ $transaction->code }}</a></td>
                        <td>{{ $transaction->digitalService?->name ?? '-' }}</td>
                        <td>{{ $transaction->destination_account }}</td>
                        <td><span class="pill {{ in_array($transaction->status->value, ['pending_validasi', 'gagal'], true) ? 'warn' : '' }}">{{ str_replace('_', ' ', $transaction->status->value) }}</span></td>
                        <td>{{ $transaction->outlet?->name ?? '-' }}</td>
                        <td>{{ $transaction->creator?->name ?? '-' }}</td>
                        <td>Rp {{ number_format($transaction->total_amount) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="muted">Belum ada transaksi digital.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{ $transactions->links() }}
    </section>
@endsection
