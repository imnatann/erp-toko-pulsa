# Tech Stack Direction

## Stack target

- backend: `Laravel`
- admin/internal UI: `Filament`
- frontend internal tambahan: `Blade` + `Livewire`
- database: `PostgreSQL`
- cache/queue lanjutan: `Redis` opsional
- storage file: local storage atau object storage

## Prinsip teknis

- utamakan struktur yang mudah dirawat untuk ERP internal
- pisahkan logika bisnis dari layer form, table, atau widget
- desain schema dan workflow harus siap audit trail
- fitur baru harus mudah ditest dan dipecah per modul

## Pola default yang diinginkan

- model untuk representasi data inti
- service/action untuk workflow bisnis
- enum atau object status untuk state transaksi
- policy untuk kontrol akses
- page/action khusus untuk workflow non-CRUD

## Hindari

- logika validasi penting hanya hidup di UI form
- status transaksi disimpan sebagai string liar tanpa aturan transisi
- pencatatan kas dan omzet dicampur tanpa event bisnis yang jelas
- desain UI marketing-style untuk area admin operasional
