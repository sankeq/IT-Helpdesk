# Test Plan

## 1. Test layers

- Unit: priority matrix, SLA calculations, state transitions, ticket numbering.
- Feature: web routes, forms, policies, notifications, reports.
- Integration: database, queue, mail, private storage.
- Security: IDOR, privilege escalation, upload validation, CSRF/XSS, rate limits.
- Performance: listing, dashboard, search, export.
- UAT: representative employees, agents, supervisor, admin, approver.

## 2. Critical test cases

| ID | Skenario | Expected result |
|---|---|---|
| T-001 | Login dengan akun aktif | Berhasil |
| T-002 | Login akun nonaktif | Ditolak |
| T-003 | Requester membuka tiket orang lain | Ditolak/404 sesuai kebijakan |
| T-004 | Requester mengubah assignee/status lewat request palsu | Ditolak |
| T-005 | Agent menulis internal note | Hanya role berwenang melihat |
| T-006 | Nomor tiket dibuat secara concurrent | Seluruh nomor unik |
| T-007 | Status transition tidak valid | Ditolak dan tidak mengubah DB |
| T-008 | Resolve tanpa resolution summary | Validasi gagal |
| T-009 | Pending tanpa reason | Validasi gagal |
| T-010 | Upload file terlarang/terlalu besar | Ditolak |
| T-011 | Download attachment tanpa permission | Ditolak |
| T-012 | SLA melewati jam kerja | Perhitungan sesuai business calendar |
| T-013 | SLA policy berubah | Tiket lama tidak berubah diam-diam |
| T-014 | Queue/email provider gagal | Retry terkendali dan failure tercatat |
| T-015 | Supervisor override priority tanpa alasan | Ditolak |
| T-016 | User dinonaktifkan | Tidak dapat login; histori tetap ada |
| T-017 | Filter report dimanipulasi | Validasi dan authorization tetap berlaku |
| T-018 | Ekspor report oleh user tanpa permission | Ditolak |
| T-019 | Restore backup | Data dapat dipulihkan sesuai runbook |
| T-020 | Error production | Tidak ada stack trace/secret kepada user |

## 3. UAT scenarios

### Scenario A: Gangguan jaringan
Employee membuat tiket, sistem memberi nomor, supervisor assign, agent menangani, menambah komentar, resolve, requester mengonfirmasi, tiket closed.

### Scenario B: Permintaan akses
Employee mengajukan akses aplikasi, tiket masuk kategori access, approver menerima permintaan, keputusan tercatat, agent memproses sesuai persetujuan.

### Scenario C: Tiket menunggu vendor
Agent menandai pending dengan alasan vendor; SLA pause hanya jika policy menyatakan demikian; ketika vendor merespons, tiket kembali in progress dan timer mengikuti aturan.

### Scenario D: Gangguan berulang
Agent mencari knowledge base, mengaitkan solusi yang relevan, menyelesaikan tiket, dan menandai potensi problem berulang.

### Scenario E: Insiden sensitif
Tiket kategori security hanya terlihat oleh role/group yang berwenang; komentar internal dan attachment tidak terekspos kepada requester yang tidak berhak.

## 4. Exit criteria
- Semua test P0 lulus.
- Tidak ada kerentanan kritis/tinggi yang belum diterima secara formal.
- UAT ditandatangani pemilik proses.
- Backup/restore test sukses.
- Monitoring dan support owner tersedia.
- Known issues dicatat dan disetujui.
