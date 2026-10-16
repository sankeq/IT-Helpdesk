# API Specification — Baseline

MVP menggunakan Laravel web routes dan form submissions untuk UI Blade. API JSON versioned dapat dipakai untuk integrasi dan pengembangan berikutnya. Endpoint di bawah adalah rancangan, bukan implementasi yang sudah berjalan.

Base path: `/api/v1`  
Authentication: session untuk web; Sanctum/token hanya jika ada use case integrasi yang disetujui.  
Format: JSON untuk API.  
Authorization: permission dan policy per resource.

## 1. Endpoint

| Method | Endpoint | Permission | Tujuan |
|---|---|---|---|
| GET | `/me` | authenticated | Profil pengguna aktif |
| GET | `/tickets` | ticket visibility | Daftar tiket dengan filter/pagination |
| POST | `/tickets` | `tickets.create` | Membuat tiket |
| GET | `/tickets/{ticket}` | policy view | Detail tiket |
| PATCH | `/tickets/{ticket}` | policy update | Update field yang diizinkan |
| POST | `/tickets/{ticket}/assign` | `tickets.assign` | Assign/reassign |
| POST | `/tickets/{ticket}/transition` | policy transition | Ubah status |
| POST | `/tickets/{ticket}/comments` | policy comment | Tambah komentar |
| POST | `/tickets/{ticket}/attachments` | policy upload | Upload file |
| GET | `/tickets/{ticket}/attachments/{attachment}` | policy download | Download terotorisasi |
| GET | `/categories` | authenticated | Kategori aktif |
| GET | `/assets` | `assets.view` | Cari aset |
| GET | `/reports/tickets` | `reports.view` | Ringkasan tiket |
| GET | `/reports/sla` | `sla.view`/`reports.view` | Ringkasan SLA |
| GET | `/knowledge` | `knowledge.view` | Artikel terbit |
| POST | `/approvals/{approval}/decision` | `approvals.decide` | Putuskan approval |

## 2. Contoh request create ticket

```json
{
  "subject": "Gangguan koneksi jaringan",
  "description": "Koneksi terputus di area kerja.",
  "category_id": 2,
  "impact": "medium",
  "urgency": "high",
  "location_id": 1,
  "asset_ids": []
}
```

`requester_id`, `status`, `priority`, `ticket_number`, `assignee_id`, SLA target, dan timestamp harus ditentukan server-side. Jangan percaya nilai dari client.

## 3. Contoh response

```json
{
  "data": {
    "ticket_number": "IKI-IT-2026-000001",
    "subject": "Gangguan koneksi jaringan",
    "status": "open",
    "priority": "P2",
    "created_at": "2026-10-16T05:00:00Z"
  }
}
```

## 4. Pagination dan filter

- Pagination server-side; default 25, maksimum 100.
- Filter yang diizinkan: status, priority, category, assignee, group, department, location, created_from, created_to.
- Validasi sort column dari allowlist; jangan memasukkan input user langsung ke SQL `ORDER BY`.
- Terapkan query scope sesuai hak akses pengguna.
- Gunakan index sesuai pola query yang terukur.

## 5. Error response

Gunakan status HTTP standar:
- 401 unauthenticated
- 403 forbidden
- 404 not found atau tidak dapat diakses sesuai kebijakan anti-enumeration
- 422 validation error
- 429 rate limited
- 500 generic error tanpa stack trace ke pengguna

Contoh:
```json
{
  "message": "Data yang dikirim tidak valid.",
  "errors": {
    "subject": ["Judul wajib diisi."]
  }
}
```

## 6. Idempotency dan integrasi

- Endpoint create/approval yang dipanggil sistem eksternal dapat membutuhkan idempotency key.
- Integrasi wajib memakai kredensial service account dengan permission minimal.
- Jangan menerima webhook tanpa autentikasi, signature, timestamp validation, dan replay protection yang sesuai.
- Log correlation ID, bukan token/secret.
