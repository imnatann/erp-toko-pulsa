# Skill: API Integration Readiness

## Tujuan

Menyiapkan pondasi bila nanti transaksi semi-manual ingin sebagian dihubungkan ke provider/API.

## Prinsip

- semua integrasi lewat adapter/service layer
- log request/response penting
- siapkan idempotency dan timeout handling
- webhook harus tervalidasi

## Untuk sekarang

- desain data jangan menutup kemungkinan ada external reference
- pisahkan internal status dan external status bila perlu
