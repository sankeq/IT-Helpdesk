# Runbook Backup dan Restore — IKI IT Helpdesk
**PT Industri Kapal Indonesia (Persero)**

Dokumen operasional ini ditujukan bagi Administrator Sistem & Tim Infrastruktur IT PT Industri Kapal Indonesia (Persero) untuk pelaksanaan prosedur pencadangan (backup), retensi, dan pemulihan bencana (disaster recovery / restore) pada sistem aplikasi IKI IT Helpdesk.

---

## 1. Spesifikasi Aset Data Kritis

| Komponen Data | Lokasi / Driver | Frekuensi Backup | Target RPO | Target RTO |
| :--- | :--- | :--- | :--- | :--- |
| **Database Relasional** | MySQL 8.x / MariaDB (`iki_helpdesk`) | Tiap 6 jam (incremental/dump) & harian penuh | < 6 jam | < 30 menit |
| **Lampiran Tiket (Attachments)** | Local Disk (`storage/app/private/ticket-attachments/`) atau S3 Object Storage | Harian (rsync / sync) | < 24 jam | < 60 menit |
| **Konfigurasi & Enkripsi** | File `.env` & `APP_KEY` (Kunci AES-256) | Tiap ada perubahan konfigurasi | 0 (statis) | < 5 menit |

> [!CAUTION]
> **PENTING**: Kunci `APP_KEY` pada file `.env` digunakan untuk mendekripsi token, sesi, dan data terenkripsi. Selalu simpan salinan `.env` di brankas kata sandi (password manager/vault) perusahaan yang terisolasi dari server aplikasi.

---

## 2. Prosedur Pencadangan (Backup)

### A. Backup Database MySQL via `mysqldump`
Jalankan perintah berikut untuk mengekspor database secara aman tanpa mengunci tabel (*zero-downtime*):

```bash
#!/usr/bin/env bash
set -e

BACKUP_DIR="/var/backups/iki-helpdesk/db"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
DB_NAME="iki_helpdesk"
DB_USER="iki_dbuser"
# DB_PASS diambil dari environment atau ~/.my.cnf

mkdir -p "${BACKUP_DIR}"

echo "[$(date)] Memulai backup database ${DB_NAME}..."
mysqldump --single-transaction \
          --quick \
          --skip-lock-tables \
          --routines \
          --triggers \
          -u "${DB_USER}" -p "${DB_NAME}" | gzip -9 > "${BACKUP_DIR}/${DB_NAME}_${TIMESTAMP}.sql.gz"

echo "[$(date)] Backup selesai: ${BACKUP_DIR}/${DB_NAME}_${TIMESTAMP}.sql.gz"
```

### B. Backup Lampiran Tiket (Private Storage)
Gunakan `rsync` untuk menyinkronkan folder lampiran privat ke server cadangan atau storage eksternal:

```bash
#!/usr/bin/env bash
set -e

SOURCE_DIR="/var/www/iki_helpdesk/storage/app/private/ticket-attachments"
BACKUP_STORAGE="/var/backups/iki-helpdesk/attachments"

mkdir -p "${BACKUP_STORAGE}"

echo "[$(date)] Memulai sinkronisasi lampiran tiket..."
rsync -avz --delete "${SOURCE_DIR}/" "${BACKUP_STORAGE}/"
echo "[$(date)] Sinkronisasi lampiran selesai."
```

### C. Otomasi Crontab Server (Disarankan)
Tambahkan entri berikut pada crontab user `root` atau `deploy` di server produksi (`crontab -e`):

```cron
# Backup Database setiap hari pukul 01:00 WITA
0 1 * * * /usr/local/bin/backup-iki-db.sh >> /var/log/iki_backup_db.log 2>&1

# Sinkronisasi file lampiran setiap hari pukul 02:00 WITA
0 2 * * * /usr/local/bin/backup-iki-attachments.sh >> /var/log/iki_backup_storage.log 2>&1

# Rotasi pembersihan backup lama (> 30 hari) setiap hari Minggu pukul 03:00 WITA
0 3 * * 0 find /var/backups/iki-helpdesk/db -name "*.sql.gz" -mtime +30 -delete
```

---

## 3. Kebijakan Retensi Backup

1. **Harian**: Disimpan selama 14 hari kalender di server lokal (`/var/backups`).
2. **Mingguan**: Snapshot setiap hari Minggu disimpan selama 8 minggu di offsite storage.
3. **Bulanan**: Snapshot akhir bulan disimpan selama 12 bulan untuk kepatuhan audit BUMN / ISO 27001.

---

## 4. Prosedur Pemulihan (Restore) Lengkap

Ikuti 7 langkah standar berikut saat terjadi kegagalan server, kerusakan data, atau migrasi infrastruktur:

### Langkah 1: Aktifkan Mode Pemeliharaan (*Maintenance Mode*)
Cegah penulisan data baru oleh pengguna galangan selama proses restorasi berlangsung:
```bash
cd /var/www/iki_helpdesk
php artisan down --secret="iki-restore-drill-2026" --render="errors::503"
```
*(Administrator tetap dapat mengakses sistem melalui URL bypass: `https://helpdesk.iki.co.id/iki-restore-drill-2026`)*

### Langkah 2: Pulihkan Skema & Data Database
Ekstrak dan muat file cadangan SQL terkompresi ke database MySQL:
```bash
BACKUP_FILE="/var/backups/iki-helpdesk/db/iki_helpdesk_20261016_010000.sql.gz"

echo "Memulihkan database dari ${BACKUP_FILE}..."
gunzip < "${BACKUP_FILE}" | mysql -u root -p iki_helpdesk
```

### Langkah 3: Pulihkan File Lampiran Tiket
Kembalikan folder lampiran ke direktori private storage aplikasi:
```bash
rsync -avz /var/backups/iki-helpdesk/attachments/ /var/www/iki_helpdesk/storage/app/private/ticket-attachments/
chown -R www-data:www-data /var/www/iki_helpdesk/storage/app/private/ticket-attachments
chmod -R 750 /var/www/iki_helpdesk/storage/app/private/ticket-attachments
```

### Langkah 4: Jalankan Migrasi Database Tambahan (Bila Diperlukan)
Pastikan struktur tabel sinkron dengan versi kode yang sedang aktif:
```bash
php artisan migrate --force
```

### Langkah 5: Bersihkan & Hangatkan Cache Aplikasi (*Warmup*)
```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Langkah 6: Verifikasi Diagnostik Endpoint Health Check
Jalankan health probe lokal untuk memastikan koneksi database, disk storage, dan cache berstatus `healthy` (HTTP 200):
```bash
curl -i http://localhost:8000/health
```
Respons wajib menampilkan `"status": "healthy"` dengan seluruh subsistem bernilai `"status": "up"`.

### Langkah 7: Nonaktifkan Mode Pemeliharaan
Buka kembali akses sistem untuk seluruh karyawan PT Industri Kapal Indonesia:
```bash
php artisan up
```

---

## 5. Jadwal Uji Coba Pemulihan (*Disaster Recovery Drill*)

- **Frekuensi Drill**: Minimal 1 kali per kuartal (3 bulan).
- **Prosedur Uji**: Tim IT wajib melakukan simulasi restore cadangan ke server staging / sandbox terpisah tanpa mengganggu produksi.
- **Daftar Periksa Uji Restore**:
  - [ ] Jumlah baris tabel tiket, pengguna, dan riwayat cocok dengan metadata cadangan.
  - [ ] File lampiran acak dapat diunduh dan dibuka tanpa *corrupted data*.
  - [ ] SLA recalculation dan timeline tiket tampil utuh.
  - [ ] Catat durasi waktu restore aktual terhadap target RTO (< 30 menit).
