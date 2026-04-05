@extends('layouts.app')

@section('title', 'Detail Transaksi Digital')

@section('content')
    <section class="hero">
        <div class="panel hero-copy">
            <div class="eyebrow">Detail Tiket</div>
            <h1>{{ $transaction->code }}</h1>
            <p>Status saat ini: <span class="pill {{ in_array($transaction->status->value, ['pending_validasi', 'gagal'], true) ? 'warn' : '' }}">{{ str_replace('_', ' ', $transaction->status->value) }}</span></p>
        </div>
        <a class="button" href="{{ route('digital-transactions.index') }}">Kembali ke daftar</a>
    </section>

    <section class="section-grid" style="margin-bottom: 16px;">
        <div class="panel">
            <h2>Ringkasan tiket</h2>
            <table>
                <tbody>
                    <tr><th>Layanan</th><td>{{ $transaction->digitalService?->name ?? '-' }}</td></tr>
                    <tr><th>Kategori</th><td>{{ $transaction->digitalService?->serviceCategory?->name ?? '-' }}</td></tr>
                    <tr><th>Outlet</th><td>{{ $transaction->outlet?->name ?? '-' }}</td></tr>
                    <tr><th>Tujuan</th><td>{{ $transaction->destination_account }}</td></tr>
                    <tr><th>Nama tujuan</th><td>{{ $transaction->destination_name ?: '-' }}</td></tr>
                    <tr><th>Nominal</th><td>Rp {{ number_format($transaction->nominal_amount) }}</td></tr>
                    <tr><th>Fee</th><td>Rp {{ number_format($transaction->fee_amount) }}</td></tr>
                    <tr><th>Total</th><td>Rp {{ number_format($transaction->total_amount) }}</td></tr>
                    <tr><th>Butuh approval supervisor</th><td>{{ $transaction->requires_supervisor_approval ? 'Ya' : 'Tidak' }}</td></tr>
                    <tr><th>Pembuat</th><td>{{ $transaction->creator?->name ?? '-' }}</td></tr>
                    <tr><th>Diproses oleh</th><td>{{ $transaction->processor?->name ?? '-' }}</td></tr>
                    <tr><th>Divalidasi oleh</th><td>{{ $transaction->validator?->name ?? '-' }}</td></tr>
                    <tr><th>Ref eksternal</th><td>{{ $transaction->external_reference ?: '-' }}</td></tr>
                    <tr><th>Catatan input</th><td>{{ $transaction->operator_note ?: '-' }}</td></tr>
                    <tr><th>Catatan validasi</th><td>{{ $transaction->validation_note ?: '-' }}</td></tr>
                </tbody>
            </table>
        </div>

        <div class="panel">
            <h2>Aksi status</h2>
            @if (empty($availableTransitions))
                <p class="muted">Tidak ada transisi lanjutan untuk status ini.</p>
            @else
                <form method="post" action="{{ route('digital-transactions.transition', $transaction) }}" class="stack">
                    @csrf

                    <label>
                        Status tujuan
                        <select name="target_status" required>
                            <option value="">Pilih status</option>
                            @foreach ($availableTransitions as $status)
                                <option value="{{ $status->value }}">{{ str_replace('_', ' ', $status->value) }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label>
                        Pelaksana aksi
                        <input type="text" value="{{ auth()->user()->name }} - {{ auth()->user()->role->label() }}" disabled>
                    </label>

                    <label>
                        Catatan aksi
                        <textarea name="note"></textarea>
                    </label>

                    <label>
                        Referensi eksternal
                        <input type="text" name="external_reference">
                    </label>

                    <div class="actions">
                        <button class="button button-primary" type="submit">Jalankan transisi</button>
                    </div>
                </form>
            @endif
        </div>
    </section>

    <section class="panel">
        <h2>Riwayat status</h2>
        @if ($transaction->statusLogs->isEmpty())
            <p class="muted">Belum ada riwayat status.</p>
        @else
            <ul class="list-reset">
                @foreach ($transaction->statusLogs->sortByDesc('acted_at') as $log)
                    <li>
                        <strong>{{ $log->to_status->value }}</strong>
                        <span class="muted">dari {{ $log->from_status?->value ?? 'awal' }} oleh {{ $log->actor?->name ?? '-' }} pada {{ optional($log->acted_at)->format('d M Y H:i') }}</span>
                        @if ($log->note)
                            <div>{{ $log->note }}</div>
                        @endif
                        @if ($log->external_reference)
                            <div class="muted">Ref: {{ $log->external_reference }}</div>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
@endsection
