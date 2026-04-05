# Skill: Laravel Filament Architecture

## Tujuan

Membangun ERP web yang rapi dengan Laravel + Filament tanpa mencampur logika bisnis ke UI.

## Prinsip

- Filament untuk admin panel, form, table, widget, page
- logika bisnis taruh di service/action class
- validasi domain penting jangan hanya di form
- enum/status pakai object atau enum yang jelas

## Gunakan Resource saat

- CRUD master data
- tabel dan form standar

## Gunakan Page/Custom Action saat

- ada workflow khusus seperti validasi transaksi
- ada dashboard operasional
- ada approval flow

## Struktur yang disarankan

- `app/Models`
- `app/Enums`
- `app/Services`
- `app/Actions`
- `app/Filament/Resources`
- `app/Filament/Pages`
- `app/Filament/Widgets`
- `app/Policies`

## Checklist implementasi

- model relation jelas
- policy ada
- form dan table searchable/sortable seperlunya
- action berbahaya butuh confirm
- activity/audit terpicu saat perubahan penting
