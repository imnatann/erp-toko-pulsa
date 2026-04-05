# Skill: Domain ERP Konter

## Tujuan

Menjaga AI tetap konsisten dengan cara kerja toko pulsa, aksesoris, dan layanan keuangan semi-manual.

## Konteks domain

- barang fisik: casing, charger, speaker, headset, kabel, dll
- layanan digital: pulsa, kuota, voucher, top up e-wallet, transfer, tarik tunai
- transaksi digital divalidasi manual sebelum dicatat final

## Aturan inti

- `pending` bukan omzet final
- `berhasil` baru boleh posting ke ledger/laporan
- `gagal` tidak boleh masuk omzet final
- tarik tunai memengaruhi kas fisik outlet
- fee admin harus tercatat terpisah dari nominal pokok bila memungkinkan

## Hal yang harus selalu dicek

- siapa pembuat transaksi
- siapa validator
- kapan status berubah
- apakah transaksi memengaruhi kas
- apakah transaksi butuh approval supervisor

## Status default yang dipakai

- `draft`
- `diproses`
- `pending_validasi`
- `berhasil`
- `gagal`
- `dibatalkan`
- `refund_manual`
