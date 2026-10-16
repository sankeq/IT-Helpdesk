# MVP Backlog & Sprint Plan

## 1. Prioritas

- P0: wajib sebelum pilot/go-live.
- P1: penting untuk operasi awal; dapat diluncurkan sesudah MVP jika disepakati.
- P2: fase lanjutan.

## 2. Epics dan user stories

### EP-01 Authentication & RBAC — P0
- US-001: User aktif dapat login/logout.
- US-002: Admin dapat menetapkan role/permission.
- US-003: Backend menolak akses tanpa permission.
- US-004: Akun nonaktif tidak dapat login dan session dicabut sesuai mekanisme.

Acceptance:
- Feature test untuk login, akun nonaktif, policy, dan akses lintas tiket.
- Tidak ada route terlindungi tanpa middleware/policy yang tepat.

### EP-02 Organization — P0
- US-005: Admin mengelola user, department, location.
- US-006: Admin dapat impor CSV dengan validasi dan preview.

Acceptance:
- Duplikasi email/NIP ditolak atau dilaporkan.
- Impor menghasilkan ringkasan sukses/gagal.
- Akun tidak dihapus jika sudah terhubung dengan transaksi.

### EP-03 Ticket lifecycle — P0
- US-007: Employee membuat tiket.
- US-008: Sistem membuat ticket number unik.
- US-009: Employee melihat tiket sendiri.
- US-010: Agent melihat queue yang diizinkan.
- US-011: Supervisor assign/reassign.
- US-012: Agent transition status sesuai workflow.
- US-013: Agent menambah public comment/internal note.
- US-014: User mengunggah lampiran privat.
- US-015: Sistem merekam ticket history.

Acceptance:
- Concurrent create tidak menghasilkan ticket number duplikat.
- IDOR tests memastikan user tidak dapat membaca tiket orang lain.
- Internal notes tidak terlihat pada requester view/email.
- Invalid status transitions ditolak.

### EP-04 Priority & SLA — P0
- US-016: Priority dihitung dari impact/urgency matrix.
- US-017: SLA target disnapshot saat tiket dibuat.
- US-018: Sistem menandai warning/breach.
- US-019: Pending pause mengikuti policy yang disahkan.
- US-020: Perubahan priority/SLA tercatat.

Acceptance:
- Unit tests untuk jam kerja, akhir pekan, hari libur, pause/resume.
- Perubahan policy tidak mengubah target historis diam-diam.
- Supervisor override wajib memberi alasan.

### EP-05 Notifications — P0
- US-021: Requester mendapat email/in-app pada event yang disepakati.
- US-022: Agent diberi tahu assignment.
- US-023: Supervisor diberi tahu SLA breach.
- US-024: Queue retry dan failed jobs dapat dipantau.

Acceptance:
- Notification dispatch setelah transaction commit.
- Deduplication/idempotency mencegah email berulang untuk event sama.
- Email tidak mengandung internal note atau data sensitif.

### EP-06 Dashboard & Reports — P0
- US-025: Employee melihat ringkasan tiket sendiri.
- US-026: Agent melihat workload.
- US-027: Supervisor melihat backlog/SLA.
- US-028: Laporan difilter periode, status, priority, category, department, location.
- US-029: Ekspor CSV (P1 jika waktu terbatas).

Acceptance:
- Angka dashboard konsisten dengan query sumber.
- Permission export diterapkan.
- Filter divalidasi dan pagination diterapkan.

### EP-07 Asset Registry — P1
- US-030: Admin mencatat aset TI.
- US-031: Tiket dihubungkan ke satu/lebih aset.
- US-032: Riwayat tiket aset dapat dilihat oleh role berwenang.

### EP-08 Knowledge Base — P1
- US-033: Agent membuat artikel draft.
- US-034: Editor berwenang menerbitkan artikel.
- US-035: Employee mencari artikel published saja.

### EP-09 Approval — P1
- US-036: Kategori tertentu dapat meminta approval.
- US-037: Approver menyetujui/menolak dengan alasan.
- US-038: Keputusan tercatat dan memicu notifikasi.

### EP-10 Integrations — P2
- US-039: HRIS user sync setelah API/ownership dikonfirmasi.
- US-040: ERP/EAM asset sync setelah kontrak integrasi disepakati.

### EP-11 Advanced ITSM — P2
- US-041: Problem records untuk gangguan berulang.
- US-042: Change records dengan approval.
- US-043: AI rekomendasi knowledge article dengan human review.

## 3. Sprint plan baseline

- Sprint 0 (1 minggu): requirement validation, repo, CI, environment, ERD, design system.
- Sprint 1 (1 minggu): auth, users, departments, locations, RBAC.
- Sprint 2 (1 minggu): categories, ticket create/list/detail, ticket number.
- Sprint 3 (1 minggu): assignment, workflow, comments, attachments, history.
- Sprint 4 (1 minggu): SLA calculator, business calendar, queue, notifications.
- Sprint 5 (1 minggu): dashboards, reports, security tests, observability.
- Sprint 6 (1 minggu): UAT, bugfix, training, pilot/go-live readiness.

Timeline adalah estimasi tim kecil; bukan komitmen final.

## 4. Release gates

### Gate A — Internal demo
- Main ticket lifecycle berfungsi.
- RBAC dasar teruji.

### Gate B — UAT
- Semua P0 acceptance criteria lulus.
- User representative menyetujui alur.
- SLA dan status sudah disahkan.

### Gate C — Production
- Security review.
- Backup/restore test.
- Monitoring dan alert.
- Domain/TLS/secret management.
- SOP support dan incident response.
- Persetujuan pemilik sistem.
