# Planning ERP Web Toko Pulsa + Aksesoris + Layanan Keuangan Semi-Manual

## 1. Ringkasan

Dokumen ini berisi planning project ERP web untuk toko yang menjual:

- Pulsa semua operator
- Paket data / kuota elektrik
- Voucher kuota
- Produk fisik seperti casing HP, charger, speaker bluetooth, kabel data, headset, dan aksesoris lain
- Top up e-wallet seperti DANA dan GoPay
- Transfer uang
- Tarik tunai

Karakter utama bisnis ini adalah:

- transaksi digital tidak dianggap berhasil otomatis
- pelaku usaha memproses dan mengecek hasil secara manual di channel lain
- setelah dipastikan berhasil, baru transaksi dicatat final di sistem

Jadi sistem yang dirancang bukan full auto settlement, melainkan ERP semi-manual yang fokus pada operasional, validasi manual, pencatatan final, kas, stok, dan laporan.

## 2. Hasil Riset Internet

Riset awal dilakukan dengan referensi berikut:

1. Bank Indonesia - Sistem Pembayaran  
   `https://www.bi.go.id/id/fungsi-utama/sistem-pembayaran/default.aspx`
2. Bank Indonesia - Perizinan Penyelenggara Jasa Sistem Pembayaran  
   `https://www.bi.go.id/id/fungsi-utama/sistem-pembayaran/perizinan/default.aspx`
3. Bank Indonesia - APU PPT PPPSPM  
   `https://www.bi.go.id/id/fungsi-utama/sistem-pembayaran/anti-pencucian-uang-dan-pencegahan-pendanaan-terrorisme/default.aspx`
4. Bank Indonesia - SNAP  
   `https://www.bi.go.id/id/layanan/Standar/SNAP/default.aspx`
5. Odoo POS retail reference  
   `https://www.odoo.com/app/point-of-sale-shop`
6. Digiflazz marketplace reference  
   `https://digiflazz.com/`
7. Digiflazz docs landing page  
   `https://id.digiflazz.com/docs`

### Intisari riset

- Sistem pembayaran, transfer dana, remitansi, dan layanan keuangan di Indonesia termasuk area yang diatur ketat oleh Bank Indonesia.
- Jika bisnis ingin menangani transfer, top up e-wallet, atau tarik tunai, pendekatan paling aman adalah sebagai agen/merchant/partner operasional, bukan sebagai penyelenggara keuangan mandiri tanpa izin.
- BI menekankan pentingnya audit trail, manajemen risiko, KYC/APU-PPT, dan pencatatan yang jelas untuk layanan keuangan.
- Untuk toko skala UMKM/konter, model semi-manual sangat realistis: operator mengecek hasil transaksi di aplikasi/provider/channel lain, lalu mengonfirmasi hasilnya di sistem.
- Struktur ERP retail modern umumnya perlu menyatukan POS, inventory, laporan, kas, dan kontrol operasional.

## 3. Tujuan Sistem

Sistem ini bertujuan untuk:

- memusatkan penjualan barang fisik dan layanan digital dalam satu dashboard
- memisahkan transaksi yang baru dibuat dengan transaksi yang sudah benar-benar berhasil
- menjaga pencatatan kas dan fee admin tetap rapi
- mengontrol stok barang fisik
- memudahkan owner melihat omzet, transaksi pending, transaksi gagal, dan performa operator
- menyiapkan fondasi multi-outlet jika toko berkembang

## 4. Konsep Inti Sistem

Sistem dibagi menjadi 3 area utama:

### A. Penjualan barang fisik

Contoh:

- casing HP
- charger/casan
- speaker bluetooth
- kabel data
- headset

Karakteristik:

- transaksi langsung di POS
- stok berkurang otomatis
- masuk laporan penjualan saat checkout

### B. Transaksi digital semi-manual

Contoh:

- isi pulsa
- kuota / voucher data
- top up DANA / GoPay
- transfer uang
- tarik tunai

Karakteristik:

- transaksi dibuat sebagai tiket/antrian
- operator memproses di channel eksternal/manual
- hasil diverifikasi manual
- pencatatan final hanya dilakukan saat status berhasil

### C. Kontrol operasional dan keuangan

Fungsi:

- pencatatan kas
- pencatatan fee admin
- audit log
- laporan harian
- monitoring pending transaction

## 5. Prinsip Bisnis dan Kepatuhan

- Sistem ini diposisikan sebagai alat operasional internal dan pencatatan bisnis.
- Untuk layanan keuangan seperti transfer, e-wallet, dan tarik tunai, implementasi bisnis disarankan memakai partner atau channel yang sah/berizin.
- Sistem wajib menyimpan jejak transaksi yang jelas:
  - siapa yang input
  - siapa yang validasi
  - kapan status berubah
  - nominal
  - nomor tujuan
  - catatan operator
  - referensi/manual proof jika ada
- Untuk nominal tertentu, sebaiknya ada approval supervisor/owner.

## 6. Status Transaksi Digital

Status yang direkomendasikan:

- `draft` = transaksi baru dibuat
- `diproses` = operator sedang memproses manual
- `pending_validasi` = masih menunggu kepastian hasil
- `berhasil` = sudah divalidasi manual berhasil
- `gagal` = sudah divalidasi manual gagal
- `dibatalkan` = dibatalkan sebelum final
- `refund_manual` = uang dikembalikan manual jika ada kasus khusus

## 7. Flow Operasional Utama

### 7.1 Isi pulsa / paket data / voucher kuota

1. Kasir membuat tiket transaksi.
2. Input nomor tujuan, produk, harga jual, dan catatan bila perlu.
3. Sistem memberi nomor transaksi internal.
4. Operator memproses manual di aplikasi/provider/channel lain.
5. Operator mengecek hasil.
6. Jika berhasil, operator klik `berhasil` dan isi catatan atau referensi.
7. Sistem baru mencatat transaksi final ke laporan dan ledger.
8. Jika gagal, operator klik `gagal` dan isi alasan.

### 7.2 Top up e-wallet

1. Kasir input nomor akun/HP tujuan.
2. Input nominal dan biaya admin.
3. Sistem membuat tiket.
4. Operator memproses manual.
5. Operator validasi hasil.
6. Jika berhasil, fee dan pencatatan final diposting.

### 7.3 Transfer uang

1. Kasir input nama penerima, bank/tujuan, nomor rekening, nominal, dan fee admin.
2. Sistem membuat tiket.
3. Operator memproses transfer manual.
4. Setelah dicek sukses, operator isi nomor referensi bila ada.
5. Sistem mencatat transaksi final.

### 7.4 Tarik tunai

1. Kasir input nominal tarik tunai dan fee.
2. Sistem buat tiket.
3. Operator memproses dan memverifikasi.
4. Jika berhasil, kas fisik outlet berkurang dan fee dicatat.
5. Jika gagal, tidak ada posting final.

### 7.5 Penjualan barang fisik

1. Kasir scan/pilih barang.
2. Sistem cek stok.
3. Pembayaran dilakukan.
4. Sistem mencatat penjualan final.
5. Stok berkurang otomatis.

## 8. Modul ERP yang Dibutuhkan

### 8.1 Auth dan Role

Role yang direkomendasikan:

- `owner`
- `admin`
- `kasir`
- `operator`
- `supervisor`
- `staff gudang` opsional

Hak akses dibedakan agar input, validasi, koreksi, dan approval tidak bercampur.

### 8.2 Master Data

Data master yang perlu ada:

- outlet
- user
- pelanggan
- kategori layanan
- layanan digital
- kategori barang
- barang fisik
- supplier
- metode pembayaran
- channel/proses manual
- fee admin default
- harga jual default

### 8.3 POS Barang Fisik

Fitur:

- pilih/scan barang
- hitung total otomatis
- pembayaran tunai/non-tunai
- cetak struk
- riwayat transaksi
- retur sederhana opsional

### 8.4 Ticketing Transaksi Digital

Fitur:

- buat transaksi digital baru
- nomor transaksi otomatis
- input nomor tujuan / rekening / akun / nominal
- pilih jenis layanan
- simpan fee admin
- simpan catatan awal
- status awal `draft` atau `pending_validasi`

### 8.5 Manual Validation Console

Ini modul paling penting untuk model bisnis ini.

Fitur:

- daftar semua transaksi pending
- filter per layanan
- filter per outlet
- filter per operator
- tombol `Tandai Berhasil`
- tombol `Tandai Gagal`
- tombol `Kembalikan ke Pending`
- catatan validasi wajib
- nomor referensi opsional
- upload bukti opsional
- history perubahan status

### 8.6 Kas dan Ledger

Fitur:

- kas masuk
- kas keluar
- pencatatan fee admin
- saldo kas harian
- shift kasir buka/tutup
- rekap transaksi final yang memengaruhi kas

### 8.7 Inventory

Fitur:

- stok awal
- stok masuk
- stok keluar
- penyesuaian stok
- stock opname
- minimum stock alert

### 8.8 Purchasing

Fitur:

- pembelian barang ke supplier
- penerimaan barang
- update harga beli
- riwayat supplier

### 8.9 Reporting dan Dashboard

Dashboard owner dan admin minimal menampilkan:

- omzet hari ini
- jumlah transaksi berhasil
- jumlah transaksi gagal
- jumlah transaksi pending
- fee admin per layanan
- penjualan barang fisik
- stok menipis
- performa per operator
- performa per kasir

### 8.10 Audit Log

Wajib untuk semua transaksi penting:

- pembuatan transaksi
- perubahan status
- perubahan nominal
- pembatalan
- validasi akhir
- koreksi manual

## 9. Struktur Data Inti

Berikut tabel inti yang direkomendasikan:

### Tabel user dan organisasi

- `users`
- `roles`
- `outlets`

### Tabel master bisnis

- `customers`
- `suppliers`
- `service_categories`
- `digital_services`
- `product_categories`
- `products`
- `payment_methods`
- `manual_channels`

### Tabel transaksi barang fisik

- `sales`
- `sale_items`
- `stock_movements`
- `stocks`
- `purchases`
- `purchase_items`

### Tabel transaksi digital

- `digital_transactions`
- `digital_transaction_status_logs`
- `manual_validation_notes`
- `transaction_attachments`

### Tabel keuangan

- `cash_transactions`
- `expenses`
- `cash_sessions`
- `ledger_entries`

### Tabel audit

- `audit_logs`

## 10. Data yang Harus Disimpan pada Transaksi Digital

Setiap transaksi digital minimal menyimpan:

- kode transaksi internal
- tanggal dan jam
- outlet
- user pembuat
- operator validator
- jenis layanan
- nomor tujuan / rekening / akun
- nama tujuan jika relevan
- nominal pokok
- fee admin
- total bayar pelanggan
- status transaksi
- catatan input
- catatan validasi
- nomor referensi eksternal jika ada
- bukti lampiran jika ada

## 11. Aturan Pencatatan Keuangan

Model sederhana yang direkomendasikan untuk MVP:

- saat tiket digital dibuat, belum masuk omzet final
- saat status menjadi `berhasil`, baru masuk omzet/pendapatan/fee
- saat status `gagal`, tidak masuk omzet final
- saat status `pending`, hanya muncul di antrian operasional
- untuk barang fisik, penjualan langsung dicatat saat checkout

Alasan memakai model ini:

- paling mudah dipahami pelaku usaha
- mengurangi salah catat
- cocok untuk proses manual

## 12. Laporan yang Wajib Ada

### Laporan operasional

- transaksi pending hari ini
- transaksi pending lebih dari 10 menit
- transaksi pending lebih dari 30 menit
- transaksi gagal hari ini
- transaksi berhasil hari ini

### Laporan keuangan sederhana

- omzet harian
- omzet per jenis layanan
- fee admin per jenis layanan
- kas masuk/keluar
- rekap shift kasir

### Laporan barang fisik

- penjualan barang per hari
- stok saat ini
- stok menipis
- riwayat pembelian barang

### Laporan performa

- transaksi per operator
- jumlah gagal per operator
- kecepatan penyelesaian transaksi
- penjualan per kasir

## 13. Halaman yang Perlu Dibuat

Minimal halaman berikut:

- login
- dashboard owner/admin
- dashboard operator
- master layanan digital
- master barang fisik
- master supplier
- POS barang fisik
- form transaksi digital
- daftar antrian pending
- detail validasi transaksi
- halaman kas masuk/keluar
- halaman stock opname
- halaman laporan
- halaman audit trail

## 14. Prioritas MVP

### Fase 1 - wajib dulu

- login dan role
- master data dasar
- POS barang fisik
- inventory dasar
- form transaksi digital
- antrian transaksi pending
- validasi manual berhasil/gagal
- kas sederhana
- laporan harian
- audit log dasar

### Fase 2 - setelah sistem stabil

- multi-outlet
- upload bukti transaksi
- reminder transaksi pending
- approval supervisor untuk nominal besar
- retur/void yang lebih rapi
- laporan performa operator

### Fase 3 - pengembangan lanjut

- akuntansi lebih lengkap
- WhatsApp notifikasi pelanggan
- dashboard mobile yang lebih nyaman
- integrasi partner sebagian jika nanti dibutuhkan
- analitik margin dan tren produk

## 15. Wireframe Fitur Inti

### Dashboard Owner

Menampilkan:

- total omzet hari ini
- total fee hari ini
- jumlah pending
- jumlah gagal
- jumlah berhasil
- stok kritis
- grafik 7 hari terakhir

### Dashboard Operator

Menampilkan:

- transaksi yang belum diproses
- transaksi pending validasi
- transaksi prioritas tinggi
- tombol cepat `Berhasil` / `Gagal`

### Form Transaksi Digital

Field utama:

- jenis layanan
- nomor tujuan / rekening
- nama tujuan opsional
- nominal
- fee admin
- total bayar
- catatan
- tombol simpan

### Detail Validasi

Field utama:

- data transaksi lengkap
- status saat ini
- catatan operator
- nomor referensi
- upload bukti
- tombol `Tandai Berhasil`
- tombol `Tandai Gagal`

## 16. Rekomendasi Stack Teknis

Stack yang direkomendasikan untuk proyek ini:

- Backend: `Laravel`
- Admin panel ERP: `Filament`
- Database: `PostgreSQL`
- Frontend internal: `Blade` + `Livewire`
- Penyimpanan file: local storage atau object storage
- Queue/cache: `Redis` opsional untuk fase lanjut

Alasan:

- cepat dibangun untuk sistem admin-heavy
- cocok untuk workflow, CRUD, role, approval, dan audit log
- mudah dikelola untuk project ERP internal

## 17. Risiko Utama dan Mitigasi

### Risiko

- transaksi pending lupa divalidasi
- operator salah menandai berhasil
- selisih kas pada layanan tarik tunai
- transaksi digital tercatat ganda
- owner kesulitan memantau transaksi bermasalah

### Mitigasi

- dashboard pending transaction
- mandatory note saat validasi
- audit log lengkap
- reminder pending lama
- pembatasan hak akses
- approval untuk nominal besar
- laporan exception harian

## 18. Rencana Sprint Implementasi

### Sprint 1

- setup project
- auth dan role
- master data dasar
- dashboard awal

### Sprint 2

- POS barang fisik
- produk dan stok
- pembelian barang sederhana

### Sprint 3

- form transaksi digital
- status transaksi
- antrian pending
- validasi manual

### Sprint 4

- kas masuk/keluar
- shift kasir
- laporan harian
- audit log

### Sprint 5

- hardening
- multi-outlet dasar
- laporan performa
- polish UI/UX

## 19. Kesimpulan

Planning terbaik untuk kebutuhan ini adalah membangun:

- ERP web semi-manual
- POS barang fisik
- ticketing transaksi digital
- console validasi manual
- inventory sederhana
- kas dan laporan operasional

Pendekatan ini paling cocok dengan cara kerja toko nyata, lebih aman secara operasional, dan tidak memaksa otomatisasi penuh pada transaksi yang di lapangan memang masih dicek manual.

## 20. Next Step

Setelah planning ini, langkah logis berikutnya adalah:

1. membuat struktur project aplikasi
2. menyusun database schema detail
3. membuat wireframe UI halaman inti
4. mulai scaffold backend Laravel + Filament
5. implementasi modul MVP fase 1
