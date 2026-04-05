# Skill: Security and Audit

## Tujuan

Menjadikan keamanan dan audit trail bagian default dari implementasi, bukan tambahan belakangan.

## Dasar riset

- OWASP ASVS cocok sebagai baseline verifikasi aplikasi web
- WCAG tetap penting agar keamanan tidak merusak usability

## Fokus keamanan proyek ini

- RBAC yang ketat
- validasi server-side
- perlindungan dari perubahan status ilegal
- audit trail untuk transaksi finansial
- secret/config di environment
- log keamanan seperlunya

## Wajib diaudit

- login sensitif
- perubahan role
- perubahan status transaksi
- refund/manual correction
- edit nominal penting
