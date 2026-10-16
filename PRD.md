# Product Requirements Document (PRD)
## IKI IT Helpdesk Ticketing System

- Versi: 1.0
- Status: Draft untuk validasi
- Pemilik produk yang diusulkan: Fungsi TI PT IKI
- Pengguna sasaran: 100–500 pengguna
- Deployment: Cloud
- Platform: Web responsif

## 1. Ringkasan produk

IKI IT Helpdesk Ticketing System memusatkan laporan gangguan dan permintaan layanan TI. Karyawan membuat tiket, helpdesk memverifikasi dan menugaskan, agent menangani, sistem mengukur SLA, dan supervisor/manajemen memantau kinerja. Sistem juga menyediakan inventaris aset ringan, knowledge base, approval terbatas, notifikasi, laporan, dan audit trail.

## 2. Tujuan

- Semua laporan melalui kanal resmi direkam sebagai tiket.
- Setiap tiket memiliki nomor unik, pelapor, kategori, prioritas, status, dan riwayat.
- Penanggung jawab dan target SLA dapat dilihat.
- Pelapor dapat memantau tiket sendiri.
- Tim TI dapat mengukur backlog, response time, resolution time, SLA compliance, dan kepuasan.
- Solusi gangguan dapat digunakan kembali melalui knowledge base.
- Tindakan penting dapat diaudit.

## 3. Persona

### Employee / Requester
Membuat dan memantau tiket, memberi komentar, mengunggah bukti, mengonfirmasi resolusi, memberi rating.

### IT Agent
Menangani tiket yang ditugaskan, mengubah status sesuai workflow, berkomunikasi, menambahkan diagnosis dan solusi.

### IT Supervisor
Mengawasi antrean, menugaskan/mengalihkan agent, mengoreksi prioritas dengan alasan, memantau SLA dan backlog.

### IT Administrator
Mengelola master data, akun, role/permission, SLA, kategori, konfigurasi, dan audit sesuai kewenangan.

### Management / Viewer
Melihat dashboard agregat dan laporan sesuai hak akses; tidak otomatis dapat membaca komentar internal atau detail insiden sensitif.

### Approver
Menyetujui/menolak permintaan tertentu berdasarkan kebijakan, dengan alasan dan riwayat keputusan.

## 4. Ruang lingkup MVP

### Wajib (P0)
- Login/logout dan pengelolaan akun.
- Role-based access control.
- Master pegawai, departemen, lokasi, kategori.
- Buat tiket dan nomor tiket unik.
- Assignment dan workflow status.
- Komentar publik dan catatan internal.
- Lampiran privat dengan validasi.
- Prioritas berbasis impact + urgency.
- SLA response/resolution dan deteksi breach.
- Notifikasi in-app dan email untuk event penting.
- Dashboard dan laporan dasar.
- Ticket history dan audit log.
- Pengaturan timezone dan kalender kerja.

### Direkomendasikan untuk MVP jika kapasitas cukup (P1)
- Inventaris aset TI ringan dan relasi tiket-aset.
- Knowledge base dasar.
- Ekspor CSV.
- Approval sederhana untuk request yang memerlukan persetujuan.

### Di luar MVP
- Problem Management penuh.
- Change Management penuh.
- AI auto-triage/AI resolution.
- Integrasi dua arah ERP/EAM/e-Office.
- Chatbot dan kanal WhatsApp.
- CMDB enterprise.
- Mobile native app.

## 5. Kategori layanan awal

Kategori dapat dikelola dari admin, bukan hard-coded:
1. Hardware & End User Support.
2. Network & Connectivity.
3. Server, Cloud & Infrastructure.
4. Business Applications (ERP, HRIS, SPMS, EAM, e-Office).
5. Account, Access & Cybersecurity.
6. IT Service Request.

Kategori insiden keamanan perlu pembatasan akses dan eskalasi khusus. Jangan simpan password, token, secret, atau kode autentikasi pada deskripsi tiket.

## 6. Kebutuhan fungsional

- FR-001: Pengguna aktif dapat login; akun nonaktif ditolak.
- FR-002: Sistem menerapkan permission di backend pada setiap resource/action.
- FR-003: Pelapor dapat membuat tiket dengan judul, deskripsi, kategori, impact, urgency, lokasi jika relevan, dan lampiran opsional.
- FR-004: Sistem membuat nomor tiket unik, misalnya `IKI-IT-2026-000001`.
- FR-005: Pelapor hanya dapat melihat tiket miliknya kecuali diberi permission eksplisit.
- FR-006: Agent dapat melihat tiket sesuai assignment/group/permission.
- FR-007: Supervisor dapat menugaskan atau mengalihkan agent.
- FR-008: Workflow hanya menerima transisi status yang diizinkan.
- FR-009: Komentar dapat ditandai `public` atau `internal`; komentar internal tidak terlihat oleh requester.
- FR-010: Setiap perubahan status, prioritas, assignee, SLA, dan approval tercatat.
- FR-011: SLA dihitung berdasarkan kebijakan dan kalender yang berlaku.
- FR-012: Sistem menandai tiket mendekati/melewati SLA dan mengirim eskalasi.
- FR-013: Notifikasi kegagalan dikirim ulang secara terbatas melalui queue.
- FR-014: Dashboard mendukung filter periode, status, priority, category, department, location, assignee.
- FR-015: Sistem menyimpan solusi saat tiket di-resolve.
- FR-016: Pelapor dapat mengonfirmasi resolusi atau reopen dengan alasan.
- FR-017: Admin dapat mengelola kategori, lokasi, departemen, SLA, role, dan user.
- FR-018: File disimpan privat dan hanya dapat diunduh setelah otorisasi.
- FR-019: Sistem dapat mengaitkan tiket dengan aset.
- FR-020: Sistem dapat menerbitkan artikel knowledge base dengan status draft/published.
- FR-021: Approval mencatat approver, keputusan, waktu, dan alasan.
- FR-022: Sistem menyediakan ekspor laporan sesuai permission.
- FR-023: Sistem tidak menghapus hard-delete histori transaksi melalui UI normal.
- FR-024: Perubahan master SLA tidak mengubah target tiket historis secara diam-diam.

## 7. Kebutuhan nonfungsional

- NFR-001 Security: HTTPS, validasi input, CSRF protection, output escaping, throttling login, secure session, least privilege.
- NFR-002 Privacy: akses berbasis kebutuhan; data pegawai hanya untuk tujuan layanan.
- NFR-003 Availability: target desain awal 99,5% bulanan, tergantung penyedia/arsitektur dan harus disetujui.
- NFR-004 Recovery: sasaran awal RPO 24 jam dan RTO 8 jam; wajib diverifikasi melalui restore test.
- NFR-005 Performance: untuk beban normal, halaman daftar/dashboard umum ditargetkan p95 <= 2,5 detik pada staging yang representatif; hasil perlu divalidasi melalui load test.
- NFR-006 Scalability: 100–500 akun; dukung queue worker dan horizontal scale bila dibutuhkan.
- NFR-007 Auditability: perubahan penting mencatat actor, action, entity, timestamp, dan metadata yang aman.
- NFR-008 Maintainability: modular monolith, automated tests, migration, code review.
- NFR-009 Accessibility: form memiliki label, pesan error jelas, navigasi keyboard dasar.
- NFR-010 Localization: Bahasa Indonesia; tampilan waktu `Asia/Makassar`; penyimpanan timestamp konsisten UTC.
- NFR-011 Backup: backup terjadwal, retensi sesuai kebijakan, dan uji pemulihan.
- NFR-012 Observability: error tracking, application logs, queue failure alerts, health checks.

## 8. Prioritas dan SLA awal

Nilai ini untuk simulasi, bukan SLA resmi:
- P1 Critical: response 15 menit, resolution 4 jam.
- P2 High: response 30 menit, resolution 8 jam kerja.
- P3 Medium: response 4 jam kerja, resolution 2 hari kerja.
- P4 Low: response 1 hari kerja, resolution 5 hari kerja.

Sebelum produksi, pemilik proses harus menyetujui jam layanan, kalender kerja, libur, definisi first response, pause policy saat Pending, eskalasi, dan pengecualian.

## 9. KPI

- Ticket volume.
- Tickets resolved/closed.
- Backlog.
- First response time.
- Resolution time.
- SLA compliance.
- Reopen rate.
- CSAT.
- Recurring issue categories.
- Workload per agent.

Definisi denominator dan business-hours calculation harus konsisten. Dashboard agregat tidak boleh mengekspos data sensitif yang tidak dibutuhkan.

## 10. Asumsi dan dependensi

- Perusahaan menyediakan daftar pegawai/unit/lokasi yang boleh digunakan.
- Tersedia domain dan cloud environment yang disetujui.
- Tersedia layanan email.
- Pemilik TI menyetujui SLA, kategori, prioritas, role, dan jam kerja.
- Integrasi HRIS/ERP/EAM/e-Office menunggu API dan otorisasi resmi.
- Satu database dipilih saat Sprint 0.
- Tidak ada credential produksi di repository atau prompt AI.

## 11. Risiko

- Perubahan scope saat coding.
- Data pegawai tidak bersih.
- SLA belum disepakati.
- Integrasi eksternal belum memiliki API.
- Lampiran/file sensitif terekspos.
- User bypass aplikasi melalui kanal informal.
- Backup belum diuji.
- Hak akses terlalu luas.

Mitigasi: freeze scope MVP, data cleansing, UAT, security tests, private storage, komunikasi prosedur, restore drill, dan review permission.
