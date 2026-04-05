# Skill: Workflow State Machine

## Tujuan

Menjaga perubahan status transaksi tidak liar dan side effect selalu konsisten.

## Prinsip

- status hanya boleh berubah lewat transition yang diizinkan
- setiap transition harus tahu actor, waktu, catatan, dan efek samping
- posting ke ledger hanya boleh pada transition yang benar

## Contoh transition inti

- `draft -> diproses`
- `diproses -> pending_validasi`
- `pending_validasi -> berhasil`
- `pending_validasi -> gagal`
- `draft -> dibatalkan`
- `gagal -> refund_manual` bila ada koreksi uang

## Setiap transition cek

- role yang boleh
- catatan wajib atau tidak
- apakah perlu nomor referensi
- apakah perlu update kas/ledger/report
