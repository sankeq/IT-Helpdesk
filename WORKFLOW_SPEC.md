# Workflow Specification

## 1. Status

- `open`: tiket baru dibuat.
- `assigned`: agent/group telah ditugaskan.
- `in_progress`: penanganan aktif.
- `pending`: menunggu pihak/kondisi lain.
- `resolved`: solusi diberikan.
- `reopened`: requester menyatakan masalah belum selesai.
- `closed`: tiket ditutup.
- `cancelled`: tiket dibatalkan dengan alasan.

## 2. Transisi yang diizinkan

| From | To | Actor | Syarat |
|---|---|---|---|
| open | assigned | Supervisor/authorized agent | Assignee atau group ditetapkan |
| open | cancelled | Requester/supervisor | Alasan wajib |
| assigned | in_progress | Assignee | Agent berwenang |
| assigned | pending | Assignee | Pending reason wajib |
| assigned | cancelled | Supervisor | Alasan wajib |
| in_progress | pending | Assignee | Pending reason wajib |
| in_progress | resolved | Assignee | Resolution summary wajib |
| in_progress | assigned | Supervisor | Reassignment dengan alasan |
| pending | in_progress | Assignee/supervisor | Catatan lanjutan |
| pending | assigned | Supervisor | Reassignment dengan alasan |
| resolved | closed | Requester/authorized agent/system | Sesuai kebijakan penutupan |
| resolved | reopened | Requester/supervisor | Reopen reason wajib |
| closed | reopened | Supervisor/admin saja | Alasan audit wajib dan kebijakan mengizinkan |
| reopened | in_progress | Assignee | Penanganan dilanjutkan |
| reopened | assigned | Supervisor | Reassignment bila diperlukan |

Semua transisi harus diverifikasi di backend melalui domain service/policy. Jangan menerima status arbitrary dari request.

## 3. Pembuatan tiket

1. Validasi user aktif dan payload.
2. Validasi category aktif dan file.
3. Tentukan requester dari session, bukan dari input yang dapat dimanipulasi.
4. Hitung priority dari impact + urgency menggunakan matriks konfigurasi.
5. Ambil snapshot SLA yang sesuai.
6. Buat tiket dalam transaction.
7. Buat history event `ticket_created`.
8. Jadwalkan notifikasi/dispatch event setelah commit.
9. Kembalikan ticket number dan detail aman.

## 4. Penomoran tiket

Format contoh: `IKI-IT-YYYY-000001`.

Persyaratan:
- Unique constraint database.
- Aman pada concurrent requests.
- Jangan menggunakan `COUNT(*) + 1`.
- Pilihan implementasi: sequence/counter table yang di-lock transactionally, atau ULID internal dengan nomor tampilan yang dialokasikan secara aman.
- Tahun mengikuti timezone bisnis yang disepakati.
- Jangan menggunakan nomor tiket sebagai satu-satunya mekanisme authorization.

## 5. Priority matrix

Matriks awal berikut configurable dan harus disetujui pemilik layanan:

| Impact \ Urgency | High | Medium | Low |
|---|---|---|---|
| High | P1 | P2 | P2 |
| Medium | P2 | P3 | P3 |
| Low | P3 | P4 | P4 |

Supervisor dapat override dengan alasan. Catat nilai lama, nilai baru, actor, timestamp, dan reason.

## 6. SLA

Simpan:
- `first_response_due_at`
- `resolution_due_at`
- `first_responded_at`
- `resolved_at`
- `closed_at`
- `sla_policy_version` atau snapshot policy
- event pause/resume/breach dalam `ticket_sla_events`

Definisikan first response sebagai respons substantif oleh petugas, bukan sekadar email otomatis. Business calendar menentukan jam kerja dan hari libur.

Jika status `pending`, sistem hanya pause SLA jika kebijakan resmi untuk alasan pending tersebut mengizinkan. Simpan event dan durasi pause. Jangan menghitung ulang target historis diam-diam ketika policy berubah.

## 7. Eskalasi

Usulan aturan konfigurabel:
- Reminder saat 75% target SLA terpakai.
- Eskalasi ke supervisor saat target response terlewati.
- Eskalasi ke supervisor/manager sesuai prioritas saat resolution SLA terlewati.
- P1 mengirim alert ke kanal on-call yang disetujui, jika ada.
- Hindari notifikasi duplikat dengan idempotency key/event unique constraint.
- Queue retry dengan backoff; kegagalan dicatat.

## 8. Pending reason

Enum awal:
- `waiting_requester`
- `waiting_approval`
- `waiting_vendor`
- `waiting_parts`
- `waiting_schedule`
- `other`

`other` wajib disertai deskripsi. Status pending harus memiliki `pending_reason` dan catatan.

## 9. Resolve/close/reopen

Saat resolve:
- `resolution_summary` wajib.
- Catat `resolved_at`.
- Kirim notifikasi requester.
- Opsional: minta rating setelah close.

Saat close:
- Catat `closed_at`.
- Simpan actor atau system actor.
- Pertahankan histori dan lampiran sesuai retensi.

Saat reopen:
- Alasan wajib.
- Reset/lanjutkan SLA sesuai kebijakan yang telah disepakati; jangan mengasumsikan otomatis.
- Buat history dan notifikasi.

## 10. Event domain yang disarankan

- `TicketCreated`
- `TicketAssigned`
- `TicketPriorityChanged`
- `TicketStatusChanged`
- `TicketCommentAdded`
- `TicketResolved`
- `TicketReopened`
- `TicketClosed`
- `SlaWarningRaised`
- `SlaBreached`
- `ApprovalRequested`
- `ApprovalDecided`

Dispatch notifikasi setelah database transaction commit.
