# Catatan Rilis (Release Notes) — IKI IT Helpdesk
**Versi**: v1.0.0 (General Availability)  
**Tanggal Rilis**: 16 Oktober 2026  
**Organisasi**: PT Industri Kapal Indonesia (Persero) / IKI Shipyard  
**Lingkungan**: Production Candidate  

---

## Ringkasan Eksekutif

Sistem **IKI IT Helpdesk v1.0.0** resmi dirilis sebagai platform manajemen tiket dan layanan teknologi informasi terpadu untuk mendukung operasional galangan kapal PT Industri Kapal Indonesia (Persero), mencakup Kantor Pusat Makassar, Galangan Kapal Makassar, Kantor Perwakilan Jakarta, dan Unit Galangan Bitung.

Sistem ini dibangun dengan arsitektur tangguh berbasis Laravel 11/PHP 8.2+, basis data relasional MySQL, otentikasi berbasis peran ketat (RBAC), kepatuhan terhadap standar keamanan OWASP Top 10, serta otomatisasi SLA berbasis kalender kerja Waktu Indonesia Tengah (WITA).

---

## Sorotan Fitur Utama Berdasarkan Fase Pengembangan

### 1. Fondasi & Manajemen Hak Akses (Fase 0 & 1)
- **Role-Based Access Control (RBAC)**: Pembagian peran terperinci mencakup Super Admin, IT Admin, IT Supervisor, IT Agent / Teknisi, Karyawan, Manajemen Eksekutif, dan Approver.
- **Master Data Perusahaan**: Integrasi divisi (IT, SDM, Keuangan, Produksi & Galangan, QC, Pengadaan, GA) dan unit lokasi kerja (Makassar HQ, Makassar Yard, Jakarta, Bitung).
- **Otentikasi Aman**: Perlindungan akun tidak aktif, sesi regenerasi, hashing sandi kuat (Bcrypt), dan pencegahan *user enumeration*.

### 2. Mesin Siklus Tiket & Riwayat Audit (Fase 2)
- **Penomoran Otomatis**: Format terstandarisasi `IKI-IT-YYYY-XXXXXX` yang aman dari *race condition*.
- **Matriks Prioritas ITIL**: Perhitungan otomatis tingkat prioritas (P1 Kritis, P2 Tinggi, P3 Sedang, P4 Rendah) dari kombinasi dampak dan urgensi.
- **Finite State Machine**: Transisi status terkontrol (`Open` → `Assigned` → `In Progress` → `Pending` → `Resolved` → `Closed` / `Cancelled`).
- **Audit Trail Komprehensif**: Pencatatan riwayat perubahan atribut, status, dan aktor secara *immutable*.
- **Isolasi Catatan Internal**: Catatan investigasi teknisi (*internal notes*) terisolasi dari pelapor.
- **Evaluasi Layanan (CSAT)**: Rating kepuasan 1-5 bintang beserta testimoni dari karyawan pelapor saat tiket diselesaikan.

### 3. Kalender Kerja Galangan & Mesin SLA (Fase 3)
- **Business Calendar WITA**: Perhitungan jam kerja galangan (08:00 - 17:00 WITA) dengan pengecualian akhir pekan dan hari libur nasional.
- **Target SLA Respons & Resolusi**: Batas waktu respon pertama dan penyelesaian dihitung dinamis berdasarkan matriks prioritas.
- **Jeda SLA (*SLA Pause*)**: Otomatisasi jeda waktu saat tiket berstatus `Pending` karena menunggu konfirmasi vendor atau pengadaan sparepart.
- **Pusat Notifikasi & Eskalasi**: Notifikasi dalam aplikasi (*in-app notification center*) dengan badge belum dibaca dan perintah otomatis `php artisan tickets:check-sla` untuk deteksi eskalasi tiket lewat batas.

### 4. Dasbor Analitik & Pelaporan (Fase 4)
- **Dasbor Multi-Peran**: Tampilan metrik kinerja spesifik untuk teknisi, supervisor, dan manajer eksekutif.
- **Metrik Kepatuhan SLA**: Visualisasi persentase SLA yang terpenuhi, tiket aktif, waktu respon rata-rata, dan distribusi kategori.
- **Modul Ekspor Laporan**: Ekspor data tiket ke CSV dengan filter tanggal, prioritas, status, divisi, dan lokasi.
- **Proteksi Formula Injection**: Pembersihan karakter berbahaya (`=`, `+`, `-`, `@`, tab) untuk mencegah serangan CSV/Excel macro injection.

### 5. Registri Aset IT, Basis Pengetahuan & Hub Persetujuan (Fase 5)
- **IT Asset Registry**: Inventaris perangkat keras (PC, Laptop, Server, Printer) dengan nomor serial, status garansi, spesifikasi, dan relasi langsung ke tiket kendala.
- **Knowledge Base Mandiri**: Modul artikel panduan dan FAQ terindeks pencarian cepat untuk memfasilitasi *self-service* karyawan serta fitur voting artikel membantu (*helpful vote*).
- **Approvals Hub**: Pusat otorisasi terpusat untuk permintaan akses atau perubahan konfigurasi penting oleh pejabat berwenang sebelum ditangani tim teknis.

### 6. Pengerasan Keamanan & Kesiapan Operasional (Fase 6)
- **Login Rate Limiting**: Pembatasan percobaan login (maksimal 5 kali salah per menit per akun/IP) untuk mencegah serangan *brute force*.
- **Validasi File Lampiran Ketat**: Penolakan berkas berbahaya (`.php`, `.exe`, `.sh`, `.bat`) serta pembatasan ukuran berkas maksimal 10 MB.
- **Mitigasi IDOR & Penyimpanan Privat**: Berkas lampiran disimpan di private disk dan diverifikasi hak aksesnya sebelum diunduh.
- **Endpoint Diagnostik (`GET /health`)**: Pemeriksaan kesehatan subsistem database, cache engine, dan private disk secara berkala.
- **Cakupan Pengujian 100%**: 80 pengujian otomatis unit & fitur dengan 368 asersi berhasil (*zero failure*).
- **Dokumentasi Runbook**: Prosedur pencadangan dan pemulihan bencana tertuang dalam `BACKUP_RESTORE_RUNBOOK.md` dan `UAT_CHECKLIST.md`.

---

## Petunjuk Instalasi & Penerapan Produksi

1. **Clone & Persiapan Lingkungan**:
   ```bash
   git clone <repository_url> /var/www/iki_helpdesk
   cd /var/www/iki_helpdesk
   composer install --no-dev --optimize-autoloader
   npm ci && npm run build
   ```

2. **Konfigurasi Environment**:
   Salin `.env.example` ke `.env` dan atur parameter produksi:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://helpdesk.iki.co.id
   APP_TIMEZONE=Asia/Makassar
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_DATABASE=iki_helpdesk
   DB_USERNAME=iki_user
   DB_PASSWORD=<secret_secure_password>
   FILESYSTEM_DISK=local
   ```

3. **Migrasi Database & Seeding Awal**:
   ```bash
   php artisan key:generate
   php artisan migrate --force
   php artisan db:seed --force
   ```

4. **Optimasi & Cache Produksi**:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

5. **Verifikasi Status Kesehatan**:
   ```bash
   curl -i http://localhost:8000/health
   # Respons wajib menampilkan HTTP 200 OK dengan "status": "healthy"
   ```

---

## Tim Pengembang & Dukungan Teknis

- **Pemilik Sistem**: Divisi Teknologi Informasi PT Industri Kapal Indonesia (Persero)
- **Kontak Dukungan**: `it.support@iki.co.id`
- **Lisensi**: Hak Cipta © 2026 PT Industri Kapal Indonesia (Persero). Seluruh hak cipta dilindungi undang-undang.
