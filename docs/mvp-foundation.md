# MVP Foundation

## Tujuan sprint fondasi

- menyiapkan ERP web internal untuk operasional toko pulsa, aksesoris, dan layanan semi-keuangan
- menjaga pemisahan transaksi barang fisik vs transaksi digital semi-manual
- memastikan role, audit trail, kas, laporan, dan UI admin jadi bagian fondasi, bukan tambahan belakangan

## Scope MVP fase 1

### Modul wajib

1. auth dan role dasar
2. master data inti
3. POS barang fisik
4. inventory dasar
5. ticketing transaksi digital
6. manual validation console
7. kas sederhana dan cash session
8. laporan harian operasional
9. audit log dasar

### Modul non-MVP sekarang

- integrasi partner digital penuh
- PWA
- mobile app
- akuntansi penuh
- notifikasi pelanggan

## Aktor dan tanggung jawab

| Aktor | Tanggung jawab inti | Catatan pembatasan |
| --- | --- | --- |
| `owner` | melihat semua laporan, approval sensitif, atur outlet | hindari input transaksi harian bila tidak perlu |
| `admin` | kelola master data, koreksi operasional terbatas | perubahan sensitif wajib audit |
| `kasir` | input penjualan barang, buat tiket digital, kelola cash session | tidak boleh finalisasi validasi digital |
| `operator` | proses tiket digital, beri status hasil | tidak boleh ubah nominal seenaknya |
| `supervisor` | approval nominal besar, refund manual, override tertentu | semua approval wajib tercatat |

## Batasan domain inti

- transaksi barang fisik final saat checkout berhasil
- transaksi digital hanya final saat status `berhasil`
- status `draft`, `diproses`, dan `pending_validasi` belum boleh posting ledger final
- `gagal` tidak masuk omzet final
- `refund_manual` harus menelusuri pelaku, waktu, catatan, dan approval
- fee admin harus disimpan terpisah dari nominal utama

## User flow inti

### POS barang fisik

1. kasir buka sesi kas
2. kasir pilih barang
3. sistem cek stok tersedia
4. kasir selesaikan pembayaran
5. sistem buat `sales`, `sale_items`, `stock_movements`, dan jurnal kas sederhana

### Tiket transaksi digital

1. kasir buat tiket digital
2. status awal `draft`
3. operator ubah ke `diproses`
4. operator ubah ke `pending_validasi` saat menunggu hasil lapangan
5. operator finalisasi ke `berhasil` atau `gagal`
6. sistem posting kas/ledger/laporan hanya saat `berhasil`

### Tarik tunai

1. tiket dibuat seperti transaksi digital lain
2. finalisasi `berhasil` mengurangi kas fisik outlet
3. fee tetap dicatat terpisah

## Dampak lintas modul yang wajib dicek setiap feature

| Area | Dampak wajib |
| --- | --- |
| role dan approval | siapa boleh input, proses, validasi, override |
| kas dan ledger | kapan kas berubah, apakah final atau hanya pending |
| laporan | hanya transaksi final yang masuk omzet |
| audit | perubahan status, nominal, approval, refund |
| responsive admin UI | aksi penting tetap nyaman di desktop dan tablet |

## Struktur implementasi Laravel yang dituju

### Domain layer

- `app/Models`
- `app/Enums`
- `app/Actions`
- `app/Services`
- `app/Policies`

### Admin layer

- `app/Filament/Resources` untuk CRUD master data
- `app/Filament/Pages` untuk workflow non-CRUD seperti validation console
- `app/Filament/Widgets` untuk dashboard operasional

### Database layer

- migration modular per domain
- enum/status lewat PHP enum + validasi transisi di action class
- index pada outlet, tanggal, status, actor, dan kode transaksi

## Backlog implementasi terdekat

### Fondasi aplikasi

1. scaffold Laravel 12
2. konfigurasi PostgreSQL default
3. pasang Filament, auth panel, dan permission layer
4. buat namespace domain untuk transaksi digital, inventory, dan cash

### Domain inti

1. definisikan enum role dan enum status transaksi digital
2. buat migration master data dan organisasi
3. buat migration transaksi digital + status logs + notes + attachments
4. buat migration penjualan barang + stok + kas + audit

### Workflow inti

1. action `CreateDigitalTransaction`
2. action `StartDigitalTransactionProcessing`
3. action `MarkDigitalTransactionPendingValidation`
4. action `MarkDigitalTransactionSucceeded`
5. action `MarkDigitalTransactionFailed`
6. action `RefundDigitalTransactionManually`

## Acceptance awal fase fondasi

- requirement sudah memisahkan barang fisik dan transaksi digital
- tidak ada posting final sebelum status `berhasil`
- semua perubahan status penting menyimpan actor, waktu, dan catatan
- approval rules untuk kasus sensitif sudah didefinisikan
- struktur codebase siap dipisah antara UI dan logika bisnis
