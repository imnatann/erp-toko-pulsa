# Skill: Database Schema Design

## Tujuan

Menjaga desain tabel konsisten, aman, dan siap untuk reporting serta audit.

## Prinsip

- pisahkan transaksi utama dan log status
- uang simpan konsisten sebagai integer smallest unit atau decimal terstandar
- foreign key jelas
- index untuk status, tanggal, outlet, operator, dan kode transaksi
- jangan simpan data turunan berlebihan tanpa alasan

## Wajib untuk transaksi digital

- tabel transaksi utama
- tabel status logs
- tabel notes/attachments bila perlu
- field created_by dan validated_by bila relevan

## Wajib untuk audit

- timestamps lengkap
- soft delete hanya bila memang perlu
- referensi perubahan status mudah ditelusuri
