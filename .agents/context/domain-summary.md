# Domain Summary

## Bisnis yang dilayani

Project ini untuk toko yang menjual:

- barang fisik seperti casing, charger, speaker, kabel data, headset
- layanan digital seperti pulsa, kuota, voucher, top up e-wallet
- layanan semi-keuangan seperti transfer uang dan tarik tunai

## Aturan inti domain

- transaksi barang fisik bisa final saat checkout
- transaksi digital tidak final saat tiket dibuat
- transaksi digital baru final setelah validasi manual berhasil
- fee admin sebaiknya tercatat terpisah dari nominal pokok
- tarik tunai memengaruhi kas fisik outlet

## Aktor utama

- `owner`
- `admin`
- `kasir`
- `operator`
- `supervisor`

## Status transaksi digital default

- `draft`
- `diproses`
- `pending_validasi`
- `berhasil`
- `gagal`
- `dibatalkan`
- `refund_manual`

## Risiko yang harus selalu diingat

- transaksi pending lupa divalidasi
- transaksi berhasil tercatat ganda
- kas fisik dan sistem tidak sinkron
- operator salah memberi status akhir
- approval untuk nominal besar terlewat
