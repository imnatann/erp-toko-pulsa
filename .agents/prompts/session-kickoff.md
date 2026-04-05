# Session Kickoff Prompt

Pakai prompt ini kalau ingin AI benar-benar orientasi ke isi `.agents`.

```text
Sebelum mengerjakan apa pun di project ini, baca `.agents/README.md`, `.agents/context/project-state.md`, `.agents/rules/non-negotiables.md`, dan `.agents/workflows/task-routing.md`.

Setelah itu, baca skill `.agents/skills/*/SKILL.md` yang paling relevan dengan task yang saya minta. Anggap folder `.agents` sebagai source of truth utama selama sesi ini. Jika ada konflik dengan asumsi default, prioritaskan instruksi di `.agents`.

Saat memberi solusi, selalu cek dampaknya ke:
- role dan approval
- status transaksi digital
- kas dan ledger
- audit trail
- laporan
- responsive admin UI

Kalau requirement saya masih kabur, rapikan scope dulu sebelum coding.
```

## Versi lebih singkat

```text
Baca `.agents` dulu dan jadikan itu acuan utama. Rujuk file yang relevan sebelum analisis atau coding, lalu jelaskan keputusanmu sesuai aturan project ini.
```
