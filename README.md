# IKI IT Helpdesk Ticketing System

Sistem Manajemen Layanan TI (*IT Service Desk*) dan Inventaris Aset Terpadu yang dirancang khusus untuk operasional galangan kapal **PT Industri Kapal Indonesia (Persero)**.

---

## ⚓ Gambaran Sistem

Aplikasi ini memusatkan seluruh pelaporan insiden, permintaan layanan, pemeliharaan aset, dan approval TI ke dalam satu platform operasional terpadu berbasis web. Didesain dengan estetika maritim modern (*Dark Maritime Dashboard*), sistem ini mendukung jam operasional galangan kapal Makassar, Bitung, dan Jakarta (WITA/WIB) serta kepatuhan SLA yang ketat.

---

## ✨ Fitur Utama

- **Siklus Hidup Tiket Lengkap**: Alur status terstandarisasi (`Open` ➔ `Assigned` ➔ `In Progress` ➔ `Pending` ➔ `Resolved` ➔ `Closed` / `Reopened`).
- **Penentuan Prioritas Otomatis**: Matriks otomatis berbasis *Impact* × *Urgency* (P1 Critical, P2 High, P3 Medium, P4 Low).
- **Perhitungan SLA Cerdas (WITA)**: Memperhitungkan kalender jam kerja maritim PT IKI (07:30 – 16:30 WITA), hari libur nasional, jeda SLA saat pending vendor/user, dan deteksi peringatan breach otomatis.
- **Manajemen Persetujuan (Approval Workflow)**: Mekanisme persetujuan otomatis bertingkat untuk kategori izin akses dan pengadaan perangkat keras.
- **Inventaris Aset TI (IT Asset Registry)**: Pelacakan perangkat keras, perangkat lunak, dan jaringan terintegrasi langsung dengan riwayat insiden tiket.
- **Knowledge Base (Self-Service)**: Artikel panduan pemecahan masalah mandiri untuk mempercepat resolusi kendala berulang.
- **Laporan & KPI Eksekutif**: Metrik kepatuhan SLA, waktu resolusi rata-rata, CSAT, beban kerja teknisi, serta ekspor CSV yang aman dari injeksi formula (OWASP CWE-1236).
- **Audit Trail & Riwayat Lengkap**: Setiap perubahan status, komentar teknisi, pergantian prioritas, dan lampiran berkas tercatat dengan waktu presisi.

---

## 🛠️ Tech Stack & Persyaratan Sistem

- **Backend:** Laravel 13 (PHP 8.5+)
- **Frontend:** Blade Templates, Tailwind CSS (Design System Maritim Terintegrasi)
- **Database:** SQLite (Default / Pengembangan) atau MySQL / PostgreSQL (Produksi)
- **Tooling:** Vite, Composer, Laravel Pint, PHPUnit

---

## 🚀 Panduan Instalasi & Menjalankan Lokal

### 1. Clone Repositori

```bash
git clone https://github.com/sankeq/IT-Helpdesk.git
cd IT-Helpdesk
```

### 2. Pasang Dependensi

```bash
composer install
npm install
```

### 3. Konfigurasi Environment

Salin berkas konfigurasi `.env.example`:

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Migrasi & Seeder Database

Jalankan migrasi database beserta data master & akun simulasi:

```bash
php artisan migrate:fresh --seed
```

### 5. Kompilasi Aset Frontend

```bash
npm run build
```

### 6. Jalankan Server Pengembangan

```bash
php artisan serve
```

Akses aplikasi melalui browser di: `http://127.0.0.1:8000`

---

## 👥 Akun Simulasi & Hak Akses (Hasil Seeder)

Semua akun pengujian menggunakan kata sandi default: `password`

| Role | Email Login | Hak Akses |
| :--- | :--- | :--- |
| **Super Admin** | `admin@iki.co.id` | Akses penuh seluruh modul dan konfigurasi master data |
| **IT Supervisor** | `supervisor.it@iki.co.id` | Pengawasan antrean, delegasi teknisi, laporan & approval |
| **IT Agent (Teknisi)** | `agent.budi@iki.co.id` | Pengerjaan tiket, catatan internal, dan manajemen artikel |
| **Karyawan (Requester)** | `staff.andi@iki.co.id` | Pembuatan tiket, pelacakan status, dan rating kepuasan (CSAT) |

---

## 🧪 Pengujian Otomatis (Automated Tests)

Aplikasi dilengkapi pengujian komprehensif (Unit & Feature Tests):

```bash
php artisan test --compact
```

*Status: 79 tests passed, 367 assertions (100% pass).*

---

## 📖 Dokumentasi Teknis

Dokumentasi rancangan sistem, skema basis data, spesifikasi API, dan panduan pengujian tersimpan lengkap di dalam folder [`docs/`](docs/README.md):

- [Product Requirements Document (PRD)](docs/PRD.md)
- [Database Schema & ERD](docs/DATABASE_SCHEMA.md)
- [Workflow Specification](docs/WORKFLOW_SPEC.md)
- [Role & Permission Matrix](docs/ROLE_PERMISSION_MATRIX.md)
- [Deployment & Runbook](docs/DEPLOYMENT_GUIDE.md)
- [Index Dokumentasi Lengkap](docs/README.md)

---

## 📄 Lisensi

Dikembangkan untuk keperluan operasional internal **PT Industri Kapal Indonesia (Persero)**.
