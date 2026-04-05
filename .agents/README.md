# AI Operating Manual

Folder `.agents` adalah source of truth utama untuk AI yang bekerja di project ini.

Sebelum analisis, coding, refactor, atau bikin planning baru, AI wajib membaca file yang relevan di folder ini dan memprioritaskan instruksi di sini dibanding asumsi default.

## Start here

Urutan baca minimum:

1. `.agents/README.md`
2. `.agents/context/project-state.md`
3. `.agents/rules/non-negotiables.md`
4. `.agents/workflows/task-routing.md`
5. skill yang relevan di `.agents/skills/*/SKILL.md`

Kalau task menyentuh domain transaksi, kas, inventory, role, laporan, atau UI admin, AI tidak boleh skip langkah ini.

## Cara AI harus bekerja

- Mulai dari konteks bisnis, bukan dari implementasi teknis.
- Cocokkan task ke skill yang paling relevan sebelum memutuskan desain solusi.
- Jangan membuat pola baru kalau sudah ada arahan di `.agents`.
- Kalau ada konflik, ikuti instruksi yang paling spesifik.
- Kalau belum ada aturan spesifik, ikuti arah stack dan prinsip di folder ini.

## Fokus project saat ini

- prioritas utama: ERP web internal
- scope utama: admin panel, POS, inventory, kas, laporan, audit
- transaksi digital: semi-manual, final hanya setelah validasi operator
- target device awal: desktop dulu, tablet harus tetap nyaman, mobile jangan rusak
- stack target: Laravel + Filament + PostgreSQL

## Struktur folder `.agents`

- `.agents/context` = kondisi project, arah stack, ringkasan domain
- `.agents/rules` = aturan yang tidak boleh dilanggar
- `.agents/workflows` = cara memilih skill dan mengerjakan task
- `.agents/prompts` = prompt siap pakai untuk memulai sesi AI
- `.agents/skills` = skill spesifik per area kerja

## Urutan prioritas skill

### Fase web MVP

1. `product-requirements`
2. `domain-erp-konter`
3. `laravel-filament-architecture`
4. `database-schema-design`
5. `workflow-state-machine`
6. `admin-ui-responsive`
7. `security-and-audit`
8. `reporting-and-analytics`
9. `qa-and-test-strategy`

### Fase setelah web stabil

10. `pwa-readiness`
11. `api-integration-readiness`
12. `mobile-adaptation`

## Aturan global singkat

- Jangan catat transaksi digital sebagai final sebelum validasi manual berhasil.
- Setiap perubahan status penting harus punya audit trail.
- Semua fitur baru harus dicek dampaknya ke role, kas, laporan, dan responsive behavior.
- Web admin tidak boleh terasa seperti landing page; fokus ke kejelasan operasional.
- Jika task masih kabur, rapikan requirement dulu sebelum lompat ke implementasi.
