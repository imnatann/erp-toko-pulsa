# Implementation Status

## Yang sudah disiapkan

- scaffold `Laravel 12` sudah masuk ke root project
- konfigurasi dasar ERP ditambahkan di `config/erp.php`
- enum domain awal untuk role, status transaksi digital, dan tipe layanan sudah dibuat
- model inti untuk outlet, user, service, transaksi digital, kas, ledger, dan audit sudah tersedia
- action workflow transaksi digital sudah dibuat untuk create ticket dan transition status
- migration fondasi MVP sudah dibuat untuk organisasi, master data, transaksi digital, POS, inventory, kas, dan audit
- seeder dasar operasional sudah dibuat untuk outlet utama, user role inti, layanan digital contoh, channel manual, dan produk awal
- policy transaksi digital sudah dibuat dan didaftarkan lewat gate untuk proses, validasi, cancel, dan refund manual
- halaman dashboard operasional sederhana sudah menggantikan welcome page di `/` agar status pending dan stok kritis bisa langsung terlihat
- modul web transaksi digital dasar sudah tersedia tanpa Filament: daftar tiket, form create, halaman detail, dan aksi transisi status
- auth session internal dasar sudah aktif: login, logout, proteksi route `auth`, dan actor workflow sekarang memakai user yang sedang login
- modul POS barang fisik dasar sudah tersedia: buka/tutup sesi kas, checkout barang, pengurangan stok, posting kas masuk, dan audit sale completion
- queue pending transaksi digital sekarang punya halaman fokus tersendiri dengan filter SLA, filter outlet, dan aksi cepat dari daftar queue
- queue sekarang juga mendukung assignment operator/supervisor per tiket dan indikator escalation langsung di dashboard maupun halaman queue
- test feature workflow status digital sudah lewat

## Catatan penting

- target database project tetap `PostgreSQL`, tetapi test saat ini memakai `sqlite :memory:` agar mudah diverifikasi lokal
- instalasi `Filament` belum dilanjutkan karena environment PHP CLI saat ini belum punya extension `ext-intl`, yang dibutuhkan dependency Filament terbaru
- role permission sementara dimodelkan dengan kolom `users.role` agar fondasi domain tetap bisa berjalan sambil menunggu package permission final
- panel admin Filament masih belum aktif; dashboard dan workflow saat ini masih berbasis route + blade sederhana
- session auth sudah ada, tetapi belum ada reset password, guard lanjutan, throttle login, atau halaman manajemen akun
- POS saat ini masih versi operasional dasar: belum ada scan barcode, void, retur, diskon, multi-payment, atau draft cart persisten
- queue pending sudah punya assignment dasar, tetapi belum ada reminder otomatis, bulk action, load balancing, atau escalation approval rule

## Next step yang disarankan

1. aktifkan extension PHP `intl`
2. install `filament/filament`
3. pasang package permission yang dipilih untuk RBAC final
4. buat panel admin awal: dashboard, outlet, user, service category, digital service, manual channel
5. buat page workflow untuk pending queue dan detail validasi
6. sambungkan posting kas/ledger ke flow POS barang fisik
7. tambah policy, seeder, dan test role/reporting
8. ganti role kolom sederhana ke RBAC package final saat Filament sudah bisa dipasang
9. tambah hardening auth: throttle login, policy lebih granular, dan audit login/logout bila diperlukan
10. tambah fitur POS lanjut: void/retur, diskon, barcode, dan rekap shift kasir
11. tambah workflow queue lanjut: reminder otomatis, bulk action, load balancing assignment, dan approval exception
