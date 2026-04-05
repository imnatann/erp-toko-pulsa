# Project State

## Status sekarang

Project ini masih berada di fase perencanaan dan fondasi instruksi AI.

Yang sudah ada:

- `README.md` berisi planning bisnis dan scope ERP toko pulsa
- `.agents/skills/*` berisi skill tematik untuk requirement, domain, arsitektur, workflow, keamanan, laporan, dan QA

Yang belum diasumsikan sudah ada:

- scaffold aplikasi Laravel
- resource Filament
- schema database final
- flow implementasi MVP yang benar-benar berjalan

## Implikasi ke cara kerja AI

- Untuk task saat ini, AI harus siap bekerja di level planning, schema, arsitektur, dan template implementasi.
- Jangan mengasumsikan file app sudah tersedia kalau belum benar-benar ada.
- Saat membuat keputusan teknis, pakai arah stack target project, bukan stack acak.

## Arah implementasi

- bangun web ERP internal dulu
- utamakan operasional toko nyata dibanding otomatisasi berlebihan
- jadikan transaksi digital sebagai workflow ticketing + validasi manual
- pastikan fondasi role, kas, audit, laporan, dan inventory dipikirkan dari awal

## Definition of done untuk task di fase ini

Task dianggap baik jika:

- nyambung ke kebutuhan bisnis di `README.md`
- konsisten dengan aturan domain semi-manual
- bisa jadi dasar implementasi Laravel + Filament
- tidak merusak kemungkinan multi-outlet dan audit trail di fase berikutnya
