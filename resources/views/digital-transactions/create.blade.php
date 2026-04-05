@extends('layouts.app')

@section('title', 'Buat Tiket Digital')

@section('content')
    <section class="hero">
        <div class="panel hero-copy">
            <div class="eyebrow">Input Ticket</div>
            <h1>Buat tiket transaksi digital baru.</h1>
            <p>Form ini dipakai kasir untuk mencatat permintaan pelanggan sebelum operator memprosesnya manual.</p>
        </div>
    </section>

    <form method="post" action="{{ route('digital-transactions.store') }}" class="panel stack">
        @csrf

        <div class="grid-2">
            <label>
                Outlet
                <select name="outlet_id" required>
                    <option value="">Pilih outlet</option>
                    @foreach ($outlets as $outlet)
                        <option value="{{ $outlet->id }}" @selected((string) old('outlet_id') === (string) $outlet->id)>{{ $outlet->name }}</option>
                    @endforeach
                </select>
            </label>

            <div class="panel">
                <div class="eyebrow">Pembuat tiket</div>
                <strong>{{ auth()->user()->name }}</strong>
                <div class="muted">{{ auth()->user()->role->label() }}</div>
            </div>
        </div>

        <div class="grid-2">
            <label>
                Layanan digital
                <select name="digital_service_id" required>
                    <option value="">Pilih layanan</option>
                    @foreach ($services as $service)
                        <option value="{{ $service->id }}" @selected((string) old('digital_service_id') === (string) $service->id)>
                            {{ $service->name }} - {{ $service->serviceCategory?->name }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label>
                Channel manual
                <select name="manual_channel_id">
                    <option value="">Belum ditentukan</option>
                    @foreach ($manualChannels as $channel)
                        <option value="{{ $channel->id }}" @selected((string) old('manual_channel_id') === (string) $channel->id)>{{ $channel->name }}</option>
                    @endforeach
                </select>
            </label>
        </div>

        <div class="grid-2">
            <label>
                Nomor tujuan / rekening / akun
                <input type="text" name="destination_account" value="{{ old('destination_account') }}" required>
            </label>

            <label>
                Nama tujuan
                <input type="text" name="destination_name" value="{{ old('destination_name') }}">
            </label>
        </div>

        <div class="grid-2">
            <label>
                Nominal pokok
                <input type="number" min="1" name="nominal_amount" value="{{ old('nominal_amount') }}" required>
            </label>

            <label>
                Fee admin
                <input type="number" min="0" name="fee_amount" value="{{ old('fee_amount') }}">
            </label>
        </div>

        <label>
            Catatan awal
            <textarea name="operator_note">{{ old('operator_note') }}</textarea>
        </label>

        <div class="actions">
            <button class="button button-primary" type="submit">Simpan tiket</button>
            <a class="button" href="{{ route('digital-transactions.index') }}">Kembali</a>
        </div>
    </form>
@endsection
