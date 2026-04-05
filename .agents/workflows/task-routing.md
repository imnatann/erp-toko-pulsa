# Task Routing

Gunakan panduan ini agar AI memilih konteks `.agents` yang tepat sebelum bekerja.

## Routing per jenis task

### 1. Request masih umum atau dari sisi owner

Baca dulu:

- `.agents/context/project-state.md`
- `.agents/context/domain-summary.md`
- `.agents/skills/product-requirements/SKILL.md`

Output yang diharapkan:

- tujuan bisnis
- scope MVP atau non-MVP
- acceptance criteria
- dampak ke modul lain

### 2. Task domain transaksi, kas, validasi, inventory, outlet

Baca dulu:

- `.agents/context/domain-summary.md`
- `.agents/rules/non-negotiables.md`
- `.agents/skills/domain-erp-konter/SKILL.md`
- skill domain lain yang relevan

### 3. Task schema database atau relasi data

Baca dulu:

- `.agents/context/tech-stack.md`
- `.agents/skills/database-schema-design/SKILL.md`
- `.agents/skills/workflow-state-machine/SKILL.md`
- `.agents/skills/domain-erp-konter/SKILL.md`

### 4. Task Laravel, Filament, action, policy, page, widget

Baca dulu:

- `.agents/context/tech-stack.md`
- `.agents/skills/laravel-filament-architecture/SKILL.md`
- `.agents/rules/non-negotiables.md`

### 5. Task dashboard, admin UX, responsive, tablet flow

Baca dulu:

- `.agents/skills/admin-ui-responsive/SKILL.md`
- `.agents/context/domain-summary.md`
- `.agents/rules/non-negotiables.md`

### 6. Task audit, security, approval, sensitive workflow

Baca dulu:

- `.agents/skills/security-and-audit/SKILL.md`
- `.agents/rules/non-negotiables.md`

### 7. Task laporan dan analitik

Baca dulu:

- `.agents/skills/reporting-and-analytics/SKILL.md`
- `.agents/context/domain-summary.md`

### 8. Task testing atau quality gate

Baca dulu:

- `.agents/skills/qa-and-test-strategy/SKILL.md`
- skill domain/arsitektur yang disentuh feature-nya

## Ritual singkat sebelum eksekusi

Sebelum mulai kerja, AI sebaiknya memastikan:

1. task masuk kategori apa
2. file `.agents` mana yang relevan
3. aturan domain apa yang paling berisiko dilanggar
4. output yang dibutuhkan user: planning, schema, code, atau review
