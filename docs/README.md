# 📚 Dokumentasi Teknis IKI IT Helpdesk

Folder ini memuat seluruh spesifikasi teknis, rancangan arsitektur, panduan operasional, dan perencanaan pengujian untuk **IKI IT Helpdesk Ticketing System**.

---

## 🗂️ Daftar Dokumen

### 1. Produk & Ruang Lingkup
- [PRD.md](PRD.md) — *Product Requirements Document* (kebutuhan produk, persona, dan cakupan MVP).
- [MVP_BACKLOG.md](MVP_BACKLOG.md) — Rincian backlog fitur dan prioritas implementasi MVP.
- [DECISIONS_AND_OPEN_QUESTIONS.md](DECISIONS_AND_OPEN_QUESTIONS.md) — Log keputusan arsitektural dan penanganan hal non-blocking.

### 2. Arsitektur & Basis Data
- [DATABASE_SCHEMA.md](DATABASE_SCHEMA.md) — Spesifikasi struktur tabel, relasi, tipe data, dan indeks.
- [ERD.md](ERD.md) — Diagram relasi antar entitas database (*Entity Relationship Diagram*).
- [WORKFLOW_SPEC.md](WORKFLOW_SPEC.md) — Definisi state transition siklus hidup tiket dan alur approval.
- [ROLE_PERMISSION_MATRIX.md](ROLE_PERMISSION_MATRIX.md) — Matriks hak akses per-peran (*Super Admin, Supervisor, Agent, Employee*).

### 3. API & Desain UI/UX
- [API_SPEC.md](API_SPEC.md) — Spesifikasi endpoint internal, format request/response, dan status HTTP.
- [UI_UX_SPEC.md](UI_UX_SPEC.md) — Panduan desain visual, tokens tema maritim, dan hierarki komponen.

### 4. Pengujian & Penerapan (QA & DevOps)
- [TEST_PLAN.md](TEST_PLAN.md) — Rencana strategi pengujian (Unit, Feature, Matrix Otorisasi, dan SLA).
- [UAT_CHECKLIST.md](UAT_CHECKLIST.md) — Daftar periksa verifikasi penerimaan pengguna (*User Acceptance Testing*).
- [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md) — Panduan penerapan lingkungan produksi & konfigurasi runtime.
- [BACKUP_RESTORE_RUNBOOK.md](BACKUP_RESTORE_RUNBOOK.md) — Prosedur pencadangan dan pemulihan data operasional.
- [RELEASE_NOTES.md](RELEASE_NOTES.md) — Catatan rilis versi 1.0.

### 5. Konfigurasi Agen & Pembantu
- [VIBE_CODING_PROMPT.md](VIBE_CODING_PROMPT.md) — Prompt master spesifikasi sistem untuk agen AI.
- [CLAUDE.md](CLAUDE.md) — Panduan operasional instruksi CLI agen.
