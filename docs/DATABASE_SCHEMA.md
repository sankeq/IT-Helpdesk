# Database Schema Blueprint

Dokumen ini adalah blueprint implementasi. Nama kolom/tipe akhir disesuaikan dengan versi Laravel dan database yang dipilih pada Sprint 0.

## 1. Konvensi

- PK: BIGINT auto-increment atau ULID; pilih satu pola konsisten.
- Timestamps disimpan dalam UTC.
- FK memakai constraint database.
- Gunakan enum PHP backed enums atau string tervalidasi; hindari database enum jika perlu fleksibilitas migrasi tinggi.
- Soft deletes hanya untuk master data yang perlu dinonaktifkan; jangan soft-delete tiket/histori sebagai cara rutin menghapus.
- PII dan data sensitif tidak boleh dicatat dalam log bebas.
- Index harus ditentukan berdasarkan query nyata; hindari index berlebihan.

## 2. Tabel dan kolom inti

### users
- id PK
- employee_number nullable string, unique jika tersedia
- name string
- email string unique
- password string hash atau external identity reference
- department_id FK nullable
- location_id FK nullable
- is_active boolean default true
- last_login_at timestamp nullable
- email_verified_at timestamp nullable
- created_at, updated_at
- deleted_at nullable hanya bila kebijakan mengizinkan

### departments
- id PK
- code string unique
- name string
- is_active boolean
- created_at, updated_at

### locations
- id PK
- code string unique
- name string
- address nullable
- timezone string default `Asia/Makassar`
- is_active boolean
- created_at, updated_at

### roles
- id PK
- name string unique
- display_name string
- description nullable
- created_at, updated_at

### permissions
- id PK
- name string unique
- description nullable

### role_user
- user_id FK
- role_id FK
- unique(user_id, role_id)

### permission_role
- role_id FK
- permission_id FK
- unique(role_id, permission_id)

### ticket_categories
- id PK
- parent_id FK nullable to ticket_categories
- code string unique
- name string
- description nullable
- requires_approval boolean default false
- is_security_sensitive boolean default false
- is_active boolean default true
- sort_order integer default 0
- created_at, updated_at

### ticket_groups
- id PK
- code string unique
- name string
- description nullable
- is_active boolean
- created_at, updated_at

### ticket_group_user
- ticket_group_id FK
- user_id FK
- unique(ticket_group_id, user_id)

### sla_policies
- id PK
- name string
- priority string
- first_response_minutes integer
- resolution_minutes integer
- business_calendar_id FK nullable
- effective_from timestamp
- effective_until timestamp nullable
- is_active boolean
- created_by FK users
- created_at, updated_at

### business_calendars
- id PK
- name string
- timezone string
- weekly_schedule JSON or normalized schedule tables
- holiday_calendar/source metadata
- is_active boolean
- created_at, updated_at

Untuk kebutuhan audit yang ketat, pertimbangkan tabel `business_calendar_rules` dan `business_calendar_holidays` terpisah daripada JSON.

### tickets
- id PK
- ticket_number string unique
- requester_id FK users
- assignee_id FK users nullable
- ticket_group_id FK nullable
- category_id FK ticket_categories
- location_id FK locations nullable
- asset_id FK assets nullable, atau gunakan pivot jika banyak aset per tiket
- subject string (maks 150)
- description text
- impact string
- urgency string
- priority string
- status string
- source string default `web`
- pending_reason string nullable
- pending_note text nullable
- resolution_summary text nullable
- root_cause text nullable
- sla_policy_id FK nullable
- sla_policy_snapshot JSON nullable
- first_response_due_at timestamp nullable
- resolution_due_at timestamp nullable
- first_responded_at timestamp nullable
- resolved_at timestamp nullable
- closed_at timestamp nullable
- created_at, updated_at
- Index yang disarankan: (status, priority, created_at), (assignee_id, status), (requester_id, created_at), (category_id, created_at), (department filter via requester relation; denormalisasi hanya jika dibutuhkan)

### ticket_comments
- id PK
- ticket_id FK
- user_id FK
- body text
- visibility string: `public` / `internal`
- created_at, updated_at
- Index(ticket_id, created_at)

### ticket_histories
- id PK
- ticket_id FK
- actor_id FK users nullable untuk system actor
- event_type string
- old_value JSON nullable
- new_value JSON nullable
- reason text nullable
- correlation_id string nullable
- created_at
- Index(ticket_id, created_at), Index(event_type, created_at)

### ticket_attachments
- id PK
- ticket_id FK
- uploaded_by FK users
- storage_disk string
- storage_key string unique
- original_filename string
- safe_filename string nullable
- mime_type string
- file_size bigint
- checksum string nullable
- visibility string: `public` / `internal`
- created_at
- Jangan menyimpan file di public web root. Download selalu melalui controller/policy.

### ticket_sla_events
- id PK
- ticket_id FK
- event_type string: warning, pause, resume, response_breach, resolution_breach, target_recalculated
- occurred_at timestamp
- duration_seconds bigint nullable
- metadata JSON nullable
- created_at
- Unique idempotency key atau deduplication strategy untuk alert.

### assets
- id PK
- asset_tag string unique
- asset_type string
- brand nullable
- model nullable
- serial_number nullable
- status string
- assigned_user_id FK users nullable
- department_id FK departments nullable
- location_id FK locations nullable
- warranty_until date nullable
- acquired_at date nullable
- notes text nullable
- created_at, updated_at

### ticket_assets
- ticket_id FK
- asset_id FK
- relationship_type string default `affected`
- unique(ticket_id, asset_id, relationship_type)

### approvals
- id PK
- ticket_id FK
- requested_by FK users
- approver_id FK users
- status string: pending, approved, rejected, cancelled
- decision_reason text nullable
- requested_at timestamp
- decided_at timestamp nullable
- created_at, updated_at
- Index(approver_id, status), Index(ticket_id, status)

### knowledge_articles
- id PK
- title string
- slug string unique
- body longtext
- category_id FK nullable
- status string: draft, published, archived
- author_id FK users
- published_at timestamp nullable
- created_at, updated_at

### notifications
- id PK (Laravel database notifications UUID atau pola yang dipilih)
- type string
- notifiable_type, notifiable_id (jika memakai polymorphic notifications)
- data JSON
- read_at timestamp nullable
- created_at, updated_at

### audit_logs
- id PK
- actor_id FK users nullable
- action string
- entity_type string
- entity_id string
- request_id/correlation_id string nullable
- ip_address string nullable sesuai kebijakan privasi
- user_agent string nullable
- metadata JSON nullable, disaring dari secrets
- created_at
- Tabel ini tidak dapat diubah melalui UI normal. Terapkan kontrol akses dan retensi.

## 3. Constraint penting

- `ticket_number` unique.
- Email unik dan employee_number unik jika digunakan.
- Foreign key tidak boleh menghapus history transaksi secara cascade tanpa keputusan eksplisit.
- Kategori/SLA yang tidak aktif tidak digunakan untuk tiket baru, tetapi tetap dapat dirujuk tiket lama.
- Nilai impact, urgency, priority, status, source, visibility, dan approval status harus tervalidasi.
- Validasi server-side tetap diperlukan walau ada constraint database.

## 4. Nomor tiket dan concurrency

Jangan membuat nomor tiket dengan `COUNT(*) + 1`. Pilihan:
1. Counter table dengan lock transaksi dan reset tahun yang aman; atau
2. Sequence database jika database mendukung dan pendekatan sesuai; atau
3. ULID sebagai identifier internal plus display number yang dialokasikan secara transaction-safe.

Pastikan format tahun memakai timezone bisnis yang ditetapkan.

## 5. Migration sequence yang disarankan

1. departments, locations
2. users dan autentikasi
3. roles, permissions, pivot
4. ticket_categories, ticket_groups, group membership
5. business_calendars, sla_policies
6. assets
7. tickets
8. comments, histories, attachments, SLA events
9. approvals
10. knowledge_articles
11. notifications dan audit_logs

## 6. Kebijakan penghapusan

- User nonaktifkan, bukan hapus, jika pernah terlibat tiket.
- Tiket/history tidak dihapus rutin.
- File dihapus sesuai retensi yang disahkan dan dicatat.
- Penghapusan data mengikuti kebijakan arsip, privasi, dan legal perusahaan.
