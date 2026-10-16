# UI/UX Specification

## 1. Prinsip visual

- Profesional, bersih, responsif, mudah dipakai karyawan nonteknis.
- Gunakan identitas visual korporasi PT IKI sesuai brand guideline resmi jika tersedia.
- Default warna: navy/putih dengan aksen biru; final palette perlu disetujui.
- Status dan prioritas tidak boleh dibedakan hanya dengan warna; gunakan label teks/icon.
- Form memiliki label, validasi inline, error summary, loading state, empty state.
- Desktop-first untuk agent, responsif untuk karyawan via ponsel.

## 2. Sitemap

- `/login`
- `/dashboard`
- `/tickets`
- `/tickets/create`
- `/tickets/{ticket}`
- `/my-tickets`
- `/my-assignments`
- `/assets`
- `/assets/{asset}`
- `/knowledge`
- `/knowledge/{article}`
- `/approvals`
- `/reports`
- `/reports/tickets`
- `/reports/sla`
- `/reports/agents`
- `/admin/users`
- `/admin/departments`
- `/admin/locations`
- `/admin/categories`
- `/admin/sla`
- `/admin/roles`
- `/admin/settings`
- `/admin/audit-logs`

## 3. Halaman

### Login
- Email/username sesuai kebijakan identitas.
- Password atau SSO jika disediakan.
- Pesan login generik agar tidak mengungkap akun terdaftar.
- Rate limiting dan proteksi brute force.

### Employee dashboard
- CTA “Buat Tiket”.
- Ringkasan tiket aktif, menunggu respons, selesai.
- Daftar tiket terbaru.
- Artikel bantuan.
- Hanya data yang boleh diakses pengguna.

### Form create ticket
- Subject.
- Category/subcategory.
- Description.
- Impact, urgency.
- Location.
- Asset selector opsional.
- Attachment.
- Tampilkan batas tipe/ukuran file.
- Prioritas dapat ditampilkan sebagai perkiraan, tetapi server tetap menjadi sumber kebenaran.

### Ticket detail
- Nomor dan status.
- Subject, deskripsi, kategori, impact/urgency/priority.
- Pelapor, lokasi, assignee/group.
- SLA timer/target dengan penjelasan status.
- Timeline histori.
- Public comments dan internal notes dipisahkan jelas.
- Attachment dengan permission.
- Tombol aksi berdasarkan role dan transisi yang valid.
- Resolve form mewajibkan resolution summary.

### Agent work queue
- Filter dan pencarian.
- Prioritas, SLA remaining/breach, status, assignee.
- Sort dan pagination server-side.
- Bulk action hanya jika ada permission, audit, dan konfirmasi.

### Supervisor dashboard
- KPI backlog, SLA, tiket per status, per agent, kategori.
- Filter periode, lokasi, unit kerja.
- Drill-down hanya sesuai permission.

### Admin
- CRUD master data dengan status active/inactive.
- Konfirmasi untuk perubahan berdampak besar.
- Perubahan SLA menyimpan effective dates/version.
- Audit trail untuk konfigurasi sensitif.

## 4. Design system minimum

- Button: primary, secondary, danger, ghost.
- Badge: status dan priority.
- Form field: text, textarea, select, date/time, file upload.
- Data table: sorting, filtering, pagination, empty state.
- Modal: confirmation only for consequential action.
- Toast/alert: success, warning, error.
- Timeline: status, assignment, comments, SLA events.
- Loading skeleton dan error fallback.

## 5. UX rules

- Jangan menghilangkan tombol sebagai satu-satunya security control; backend harus menolak request.
- Confirm destructive/consequential actions.
- Pesan error menjelaskan cara memperbaiki tanpa membocorkan detail internal.
- Jangan tampilkan internal notes kepada requester.
- Jangan tampilkan file URL publik.
- Tanggal ditampilkan lokal `Asia/Makassar`, timestamp API diserialisasi konsisten.
- Semua tabel besar memakai pagination.
