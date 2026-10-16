# Role & Permission Matrix

## 1. Role awal

- `employee`
- `it_agent`
- `it_supervisor`
- `it_admin`
- `management_viewer`
- `approver`

Gunakan permission spesifik; jangan hanya memeriksa nama role di controller.

## 2. Permission naming

Contoh:
- `tickets.create`
- `tickets.view_own`
- `tickets.view_assigned`
- `tickets.view_all`
- `tickets.assign`
- `tickets.update`
- `tickets.change_priority`
- `tickets.resolve`
- `tickets.close`
- `tickets.reopen`
- `tickets.cancel`
- `tickets.comment_public`
- `tickets.comment_internal`
- `tickets.attachments.download`
- `sla.view`
- `sla.manage`
- `assets.view`
- `assets.manage`
- `knowledge.view`
- `knowledge.manage`
- `approvals.view_assigned`
- `approvals.decide`
- `reports.view`
- `reports.export`
- `users.manage`
- `roles.manage`
- `audit.view`
- `settings.manage`

## 3. Matriks ringkas

| Permission | Employee | Agent | Supervisor | IT Admin | Management | Approver |
|---|---:|---:|---:|---:|---:|---:|
| tickets.create | Ya | Ya | Ya | Ya | Opsional | Ya |
| tickets.view_own | Ya | Ya | Ya | Ya | Tidak otomatis | Ya |
| tickets.view_assigned | Tidak | Ya | Ya | Jika diberi | Tidak | Tidak |
| tickets.view_all | Tidak | Tidak default | Ya | Operasional | Tidak; gunakan report | Tidak |
| tickets.assign | Tidak | Opsional | Ya | Ya bila berwenang | Tidak | Tidak |
| tickets.change_priority | Tidak | Terbatas | Ya | Ya | Tidak | Tidak |
| tickets.resolve | Tidak | Ya | Ya | Hanya bila ditugaskan/berwenang | Tidak | Tidak |
| tickets.comment_internal | Tidak | Ya | Ya | Sesuai kebutuhan | Tidak | Tidak |
| sla.manage | Tidak | Tidak | Usulan saja | Ya | Tidak | Tidak |
| assets.manage | Tidak | Terbatas | Ya | Ya | Tidak | Tidak |
| knowledge.manage | Tidak | Ya | Ya | Ya | Tidak | Tidak |
| approvals.decide | Tidak default | Tidak default | Jika ditunjuk | Tidak otomatis | Jika ditunjuk | Ya |
| reports.view | Tiket sendiri | Sesuai assignment | Ya | Ya | Ya, agregat | Sesuai kebutuhan |
| reports.export | Tidak | Terbatas | Ya | Ya | Jika disetujui | Tidak |
| users.manage | Tidak | Tidak | Tidak | Ya | Tidak | Tidak |
| roles.manage | Tidak | Tidak | Tidak | Terbatas | Tidak | Tidak |
| audit.view | Tidak | Tidak | Sesuai kebijakan | Ya | Tidak otomatis | Tidak |

## 4. Aturan otorisasi

- Gunakan Laravel Policies/Gates atau package RBAC yang kompatibel.
- Periksa otorisasi pada setiap request dan file download.
- Jangan mengandalkan ID yang sulit ditebak sebagai kontrol akses.
- Management dashboard menampilkan agregat; detail sensitif perlu permission khusus.
- Kategori keamanan dapat membatasi visibility ke group khusus.
- Administrator tidak otomatis memperoleh kewenangan bisnis approval.
- Catatan internal tidak pernah dikirim ke requester.
- Akses audit log dibatasi dan dicatat.
- Penonaktifan akun segera mencabut akses, tetapi tidak mengubah author history.
