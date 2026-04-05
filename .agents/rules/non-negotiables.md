# Non Negotiables

Aturan ini harus dianggap wajib saat AI bekerja di project ini.

## Domain dan pencatatan

- Jangan anggap transaksi digital sebagai omzet final sebelum status `berhasil`.
- Jangan posting ledger final saat transaksi masih `draft`, `diproses`, atau `pending_validasi`.
- Setiap perubahan status penting harus menyimpan pelaku, waktu, dan catatan.
- Fee admin harus tetap bisa ditelusuri terpisah dari nominal utama.

## Role dan approval

- Jangan campur hak input, validasi, dan approval tanpa alasan kuat.
- Workflow yang berisiko harus mempertimbangkan `supervisor` atau `owner` approval.
- Semua perubahan sensitif harus bisa diaudit siapa pelakunya.

## Arsitektur

- Jangan taruh seluruh logika bisnis di resource/form/table Filament.
- Jangan menambah tabel atau field tanpa alasan bisnis yang jelas.
- Jangan buat status atau transition baru tanpa memeriksa dampaknya ke laporan, kas, dan audit.

## UI admin

- Fokus pada kecepatan operasional, keterbacaan, dan kejelasan status.
- Jangan mendesain UI admin seperti landing page promosi.
- Desktop adalah baseline, tablet wajib aman, mobile minimal tetap usable.

## Cara mengambil keputusan

- Jika requirement belum jelas, rapikan scope dulu.
- Jika ada beberapa opsi, pilih yang paling sederhana tetapi tetap aman untuk audit dan ekspansi MVP.
- Jika bertentangan antara kemudahan implementasi dan akurasi operasional, pilih akurasi operasional.
