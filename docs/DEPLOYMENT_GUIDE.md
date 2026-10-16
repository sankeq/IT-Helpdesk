# Deployment Guide — Cloud

## 1. Arsitektur minimum

- Laravel application (production).
- Managed MySQL/PostgreSQL.
- Private object storage untuk attachment.
- Queue worker.
- Scheduler/cron.
- SMTP/provider email.
- Centralized logs dan error monitoring.
- Automated database backups.

MVP dapat dimulai dengan satu application instance jika risiko dan target availability disetujui. Untuk kebutuhan lebih tinggi, pisahkan worker dan tambahkan redundancy.

## 2. Environment

Pisahkan environment:
- local
- test/CI
- staging
- production

Jangan berbagi database production dengan staging. Jangan salin data pribadi nyata ke development tanpa dasar dan kontrol yang disetujui.

## 3. Environment variables (contoh nama)

```env
APP_NAME="IKI IT Helpdesk"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://helpdesk.example.internal
APP_TIMEZONE=UTC

LOG_CHANNEL=stack
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

QUEUE_CONNECTION=redis
CACHE_STORE=redis
SESSION_DRIVER=database

MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME="IKI IT Helpdesk"

FILESYSTEM_DISK=private
```

Nama variabel dapat berbeda sesuai versi Laravel. Jangan commit `.env`; gunakan secret manager/secret variables provider. `APP_DEBUG=false` wajib di production.

## 4. Deployment checklist

1. Siapkan domain/subdomain yang disetujui.
2. Aktifkan TLS.
3. Buat database managed dengan least-privilege user.
4. Siapkan private object storage.
5. Siapkan queue worker dan scheduler.
6. Konfigurasi secret melalui secret manager.
7. Jalankan dependency/security checks.
8. Jalankan migration melalui proses deployment yang disetujui.
9. Jalankan tests.
10. Jalankan `php artisan config:cache`, `route:cache`, `view:cache` sesuai dukungan versi/framework.
11. Pastikan storage tidak membuka file private ke publik.
12. Aktifkan monitoring, error tracking, dan alert.
13. Uji email dan queue.
14. Uji backup dan restore.
15. Jalankan smoke test.
16. Catat release version dan rollback procedure.

## 5. Backup dan recovery

- Backup database otomatis; atur retensi sesuai kebijakan.
- Aktifkan point-in-time recovery bila tersedia dan dibutuhkan.
- Backup metadata/file storage atau gunakan durability/versioning provider.
- Lindungi backup dari akses akun aplikasi yang sama bila memungkinkan.
- Lakukan restore drill berkala.
- Catat hasil restore test, durasi, dan gap.
- RPO/RTO harus disetujui berdasarkan kebutuhan bisnis.

## 6. Security checklist

- HTTPS only.
- `APP_DEBUG=false`.
- Secure cookies, session rotation, CSRF protection.
- Login throttling dan password policy/SSO sesuai kebijakan.
- Least privilege untuk database, storage, queue, dan cloud IAM.
- Private file storage; download melalui authorization.
- MIME/type/size validation dan randomized storage keys.
- Patch framework/dependencies.
- Audit log tidak menyimpan password, token, session cookie, atau secret.
- Admin MFA jika tersedia/diwajibkan.
- Uji authorization/IDOR dan upload.
- Review akses dan akun berkala.

## 7. Monitoring

Monitor:
- HTTP 5xx rate.
- Latency p95.
- DB connections/slow queries.
- Queue depth dan failed jobs.
- Scheduler heartbeat.
- Disk/object storage usage.
- Email delivery failure.
- Backup success/failure.
- Authentication anomalies.
- SLA escalation job health.

## 8. Rollback

- Simpan release artifact sebelumnya.
- Hindari migration destructive tanpa expand/contract plan.
- Backup sebelum migration berisiko.
- Siapkan langkah rollback aplikasi dan mitigasi migration.
- Jangan mengandalkan rollback kode untuk membatalkan perubahan data yang irreversible.
