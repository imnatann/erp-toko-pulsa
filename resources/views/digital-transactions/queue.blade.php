@extends('layouts.app')

@section('title', 'Queue Pending')

@section('content')
    <section class="hero">
        <div class="panel hero-copy">
            <div class="eyebrow">Queue Pending</div>
            <h1>Fokus operator untuk tiket yang belum selesai.</h1>
            <p>Gunakan filter SLA untuk mengejar tiket lama, lalu jalankan aksi cepat tanpa harus bolak-balik ke halaman detail.</p>
        </div>
        <a class="button button-primary" href="{{ route('digital-transactions.index') }}">Buka semua tiket</a>
    </section>

    <section class="grid-3" style="margin-bottom: 20px;">
        <div class="panel">
            <h2>{{ $queueCounts['all'] }}</h2>
            <p class="muted">Semua queue aktif</p>
        </div>
        <div class="panel">
            <h2>{{ $queueCounts['10'] }}</h2>
            <p class="muted">Pending >= 10 menit</p>
        </div>
        <div class="panel">
            <h2>{{ $queueCounts['30'] }}</h2>
            <p class="muted">Pending >= 30 menit</p>
        </div>
        <div class="panel">
            <h2>{{ $queueCounts['60'] }}</h2>
            <p class="muted">Pending >= 60 menit</p>
        </div>
    </section>

    <section class="panel stack">
        <form method="get" class="grid-3">
            <label>
                SLA
                <select name="sla">
                    <option value="">Semua</option>
                    <option value="10" @selected($sla === '10')>>= 10 menit</option>
                    <option value="30" @selected($sla === '30')>>= 30 menit</option>
                    <option value="60" @selected($sla === '60')>>= 60 menit</option>
                </select>
            </label>

            <label>
                Status
                <select name="status">
                    <option value="">Semua queue</option>
                    <option value="diproses" @selected($status === 'diproses')>diproses</option>
                    <option value="pending_validasi" @selected($status === 'pending_validasi')>pending_validasi</option>
                </select>
            </label>

            @if ($outlets->isNotEmpty())
                <label>
                    Outlet
                    <select name="outlet_id">
                        <option value="0">Semua outlet</option>
                        @foreach ($outlets as $outlet)
                            <option value="{{ $outlet->id }}" @selected($outletId === $outlet->id)>{{ $outlet->name }}</option>
                        @endforeach
                    </select>
                </label>
            @endif

            <div class="actions">
                <button class="button button-primary" type="submit">Terapkan filter</button>
                <a class="button" href="{{ route('digital-transactions.queue') }}">Reset</a>
            </div>
        </form>

        <div class="stack">
            @forelse ($transactions as $transaction)
                @php
                    $ageMinutes = (int) $transaction->submitted_at->diffInMinutes(now());
                    $pillClass = $ageMinutes >= 30 ? 'danger' : ($ageMinutes >= 10 ? 'warn' : '');
                @endphp
                <div class="panel">
                    <div class="section-grid">
                        <div>
                            <div class="actions" style="justify-content: space-between; margin-top: 0;">
                                <div>
                                    <strong><a href="{{ route('digital-transactions.show', $transaction) }}">{{ $transaction->code }}</a></strong>
                                    <div class="muted">{{ $transaction->digitalService?->name ?? '-' }} - {{ $transaction->outlet?->name ?? '-' }}</div>
                                </div>
                                <div>
                                    <span class="pill {{ $transaction->status->value === 'pending_validasi' ? 'warn' : '' }}">{{ str_replace('_', ' ', $transaction->status->value) }}</span>
                                    <span class="pill {{ $pillClass }}">{{ $ageMinutes }} menit</span>
                                </div>
                            </div>

                            <div class="grid-3" style="margin-top: 14px;">
                                <div>
                                    <div class="eyebrow">Tujuan</div>
                                    <div>{{ $transaction->destination_account }}</div>
                                </div>
                                <div>
                                    <div class="eyebrow">Pembuat</div>
                                    <div>{{ $transaction->creator?->name ?? '-' }}</div>
                                </div>
                                <div>
                                    <div class="eyebrow">Total</div>
                                    <div>Rp {{ number_format($transaction->total_amount) }}</div>
                                </div>
                            </div>

                            <div class="grid-3" style="margin-top: 14px;">
                                <div>
                                    <div class="eyebrow">Assignee</div>
                                    <div>{{ $transaction->assignee?->name ?? 'Belum ditugaskan' }}</div>
                                </div>
                                <div>
                                    <div class="eyebrow">SLA</div>
                                    <div>
                                        @if ($ageMinutes >= 60)
                                            <span class="pill danger">Kritis</span>
                                        @elseif ($ageMinutes >= 30)
                                            <span class="pill danger">Escalated</span>
                                        @elseif ($ageMinutes >= 10)
                                            <span class="pill warn">Perlu follow up</span>
                                        @else
                                            <span class="pill">Normal</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3>Assignment</h3>
                            <form method="post" action="{{ route('digital-transactions.assign', $transaction) }}" class="stack" style="margin-bottom: 14px;">
                                @csrf
                                <input type="hidden" name="sla" value="{{ $sla }}">
                                <input type="hidden" name="status" value="{{ $status }}">
                                <input type="hidden" name="outlet_id" value="{{ $outletId }}">

                                <label>
                                    Assign ke operator
                                    <select name="assignee_id" required>
                                        <option value="">Pilih operator</option>
                                        @foreach ($assignees->where('outlet_id', $transaction->outlet_id) as $assignee)
                                            <option value="{{ $assignee->id }}" @selected($transaction->assigned_to === $assignee->id)>
                                                {{ $assignee->name }} - {{ $assignee->role->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </label>

                                <button class="button" type="submit">Simpan assignee</button>
                            </form>

                            <h3>Aksi cepat</h3>
                            <div class="stack">
                                @php($available = $quickTransitions[$transaction->id] ?? [])

                                @forelse ($available as $targetStatus)
                                    <form method="post" action="{{ route('digital-transactions.quick-transition', $transaction) }}" class="stack">
                                        @csrf
                                        <input type="hidden" name="target_status" value="{{ $targetStatus->value }}">
                                        <input type="hidden" name="sla" value="{{ $sla }}">
                                        <input type="hidden" name="status" value="{{ $status }}">
                                        <input type="hidden" name="outlet_id" value="{{ $outletId }}">

                                        @if (in_array($targetStatus->value, ['berhasil', 'gagal'], true))
                                            <label>
                                                Catatan {{ $targetStatus->value }}
                                                <input type="text" name="note" placeholder="Catatan operator" required>
                                            </label>
                                        @else
                                            <input type="hidden" name="note" value="">
                                        @endif

                                        @if ($targetStatus->value === 'berhasil')
                                            <label>
                                                Ref eksternal
                                                <input type="text" name="external_reference" placeholder="Opsional / wajib sesuai proses">
                                            </label>
                                        @endif

                                        <button class="button {{ $targetStatus->value === 'berhasil' ? 'button-primary' : ($targetStatus->value === 'gagal' ? 'button-danger' : '') }}" type="submit">
                                            {{ str_replace('_', ' ', $targetStatus->value) }}
                                        </button>
                                    </form>
                                @empty
                                    <p class="muted">Tidak ada aksi cepat untuk status ini.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="panel muted">Tidak ada tiket yang cocok dengan filter queue saat ini.</div>
            @endforelse
        </div>

        {{ $transactions->links() }}
    </section>
@endsection
