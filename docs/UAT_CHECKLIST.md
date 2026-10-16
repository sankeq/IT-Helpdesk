# User Acceptance Testing (UAT) Checklist — IKI IT Helpdesk
**PT Industri Kapal Indonesia (Persero)**

Dokumen ini merupakan panduan verifikasi penerimaan pengguna (*User Acceptance Testing*) sistem operasional **IKI IT Helpdesk**, mencakup skenario pengujian bisnis (Skenario A hingga E) sesuai dokumen spesifikasi [TEST_PLAN.md](file:///d:/IT%20IKI/AI%20Project/IKI_IT_Helpdesk/TEST_PLAN.md).

---

## 1. Informasi Lingkungan UAT

- **Versi Aplikasi**: v1.0.0-RC1
- **Target URL**: `https://helpdesk.iki.co.id` (atau Staging Environment)
- **Koordinator UAT**: Tim Pengembang AI & Administrator TI PT IKI
- **Peserta UAT**: Perwakilan Karyawan Divisi Produksi/Keuangan, Tim Teknisi IT, Supervisor IT, Approver Manajer, dan Super Administrator.

---

## 2. Matriks Pengujian Skenario Bisnis

### Skenario A: Siklus Penanganan Gangguan Jaringan (Network Issue di Galangan Makassar)
*Tujuan: Memverifikasi aliran tiket insiden standar dari pelaporan, penugasan otomatis, respon pertama, penyelesaian teknis, hingga penutupan tiket.*

| No | Langkah Pengujian | Peran Pengguna | Hasil yang Diharapkan | Status (Lulus/Gagal) | Catatan |
| :--- | :--- | :--- | :--- | :--- | :--- |
| A.1 | Login ke portal helpdesk dan buka menu "Buat Tiket Baru" | Karyawan (`employee@iki.co.id`) | Formulir pengaduan terbuka dengan pilihan kategori lengkap | [ ] | |
| A.2 | Isi subjek "Kabel LAN Putus di Dermaga Slipway 2", pilih Kategori: Network, Dampak: High, Urgensi: High | Karyawan | Matriks prioritas otomatis terhitung sebagai **P1 (Kritis)** | [ ] | |
| A.3 | Kirim tiket pengaduan | Karyawan | Tiket tersimpan dengan nomor urut unik format `IKI-IT-YYYY-XXXXXX` | [ ] | |
| A.4 | Buka daftar antrean tiket dan tugaskan (*Assign*) ke teknisi | IT Supervisor (`supervisor@iki.co.id`) | Status berpindah ke `Assigned`, notifikasi masuk ke Teknisi | [ ] | |
| A.5 | Teknisi membuka tiket dan mengirim respon pertama: "Teknisi sedang menuju lokasi dermaga" | Teknisi IT (`agent@iki.co.id`) | Target SLA First Response berhenti, status tiket berpindah ke `In Progress` | [ ] | |
| A.6 | Teknisi mengganti kabel LAN lalu mengubah status ke `Resolved` dengan ringkasan solusi perbaikan | Teknisi IT | Target SLA Resolution tercatat, form wajib mengisi resolution notes | [ ] | |
| A.7 | Karyawan pelapor mengonfirmasi hasil perbaikan dan memberikan rating CSAT bintang 5 | Karyawan | Status tiket berpindah ke `Closed`, rating CSAT dan ulasan tersimpan | [ ] | |

---

### Skenario B: Permintaan Layanan & Alur Persetujuan (Request Akses ERP / VPN)
*Tujuan: Menguji penegakan tata kelola otorisasi di mana permintaan akses wajib disetujui atasan sebelum dieksekusi teknisi.*

| No | Langkah Pengujian | Peran Pengguna | Hasil yang Diharapkan | Status (Lulus/Gagal) | Catatan |
| :--- | :--- | :--- | :--- | :--- | :--- |
| B.1 | Karyawan mengajukan permohonan "Akses Modul Pengadaan ERP Galangan" (Kategori: Account & Access) | Karyawan (`employee@iki.co.id`) | Tiket otomatis memicu status `Waiting Approval`, tab persetujuan terbentuk | [ ] | |
| B.2 | Manajer membuka menu **Approvals Hub** | Approver (`approver@iki.co.id`) | Menampilkan daftar tiket yang menunggu persetujuan manajerial | [ ] | |
| B.3 | Manajer meninjau justification dan mengklik tombol "Setujui" (*Approve*) dengan catatan | Approver | Rekam jejak approval tercatat (Approved by Manager), status tiket berubah ke `Open` / `Assigned` | [ ] | |
| B.4 | Teknisi IT memproses pembuatan akun ERP lalu menandai tiket `Resolved` | Teknisi IT (`agent@iki.co.id`) | Pengguna menerima notifikasi bahwa hak akses telah aktif | [ ] | |
| B.5 | Uji Negatif: Teknisi mencoba menyelesaikan tiket sebelum disetujui | Teknisi IT | Sistem menolak perubahan status dengan pesan validasi tegas | [ ] | |

---

### Skenario C: Tiket Menunggu Vendor Pihak Ketiga & Jeda SLA (*SLA Pause*)
*Tujuan: Memverifikasi timer SLA dapat dijeda secara sah saat ketergantungan berada di pihak eksternal/rekanan galangan.*

| No | Langkah Pengujian | Peran Pengguna | Hasil yang Diharapkan | Status (Lulus/Gagal) | Catatan |
| :--- | :--- | :--- | :--- | :--- | :--- |
| C.1 | Teknisi menangani kerusakan mainboard server galangan dan membutuhkan sparepart dari vendor luar | Teknisi IT (`agent@iki.co.id`) | Tiket berada dalam status `In Progress` | [ ] | |
| C.2 | Ubah status tiket ke `Pending` dengan alasan wajib: `Waiting for Vendor (Pengiriman sparepart Dell)` | Teknisi IT | Timer SLA resolusi masuk ke status **Paused**; waktu jeda dicatat sistem | [ ] | |
| C.3 | Sparepart tiba 2 hari kemudian; teknisi memasang komponen dan mengembalikan status ke `In Progress` | Teknisi IT | Timer SLA kembali aktif (*Resumed*) dengan perpanjangan waktu jatuh tempo yang proporsional | [ ] | |
| C.4 | Uji Negatif: Teknisi mencoba mengubah ke `Pending` tanpa mengisi alasan tertulis | Teknisi IT | Form menolak submit; pesan kesalahan "Alasan penundaan wajib diisi" muncul | [ ] | |

---

### Skenario D: Resolusi Gangguan Berulang Melalui Knowledge Base & Registri Aset
*Tujuan: Memverifikasi pemanfaatan basis pengetahuan untuk self-service karyawan serta keterkaitan tiket dengan registri aset IT.*

| No | Langkah Pengujian | Peran Pengguna | Hasil yang Diharapkan | Status (Lulus/Gagal) | Catatan |
| :--- | :--- | :--- | :--- | :--- | :--- |
| D.1 | Karyawan mencari artikel "Cara Setting VPN Jarak Jauh Galangan" pada modul Knowledge Base | Karyawan (`employee@iki.co.id`) | Artikel panduan ditemukan, karyawan dapat membaca artikel terpublikasi | [ ] | |
| D.2 | Karyawan mengklik tombol "Membantu" (*Helpful Vote*) di bagian bawah artikel | Karyawan | Angka counter `helpful_count` bertambah 1 secara interaktif | [ ] | |
| D.3 | Karyawan membuat tiket perbaikan printer dan memilih dropdown "Asset Terkait": `PRN-001 (Epson L3110 Dermaga)` | Karyawan | Tiket tersimpan dengan relasi aset terverifikasi di database | [ ] | |
| D.4 | Teknisi membuka riwayat aset `PRN-001` melalui menu Asset Registry | Teknisi IT (`agent@iki.co.id`) | Menampilkan log riwayat kerusakan printer berulang pada dermaga tersebut | [ ] | |

---

### Skenario E: Penanganan Insiden Sensitif & Isolasi Catatan Internal (Security Hardening)
*Tujuan: Memastikan kerahasiaan data, proteksi file lampiran privat, dan isolasi catatan internal teknisi dari pelapor.*

| No | Langkah Pengujian | Peran Pengguna | Hasil yang Diharapkan | Status (Lulus/Gagal) | Catatan |
| :--- | :--- | :--- | :--- | :--- | :--- |
| E.1 | Teknisi IT menambahkan catatan investigasi sensitif dengan visibilitas `Internal Note` | Teknisi IT (`agent@iki.co.id`) | Catatan berlabel badge kuning `Internal` tampil di timeline teknisi | [ ] | |
| E.2 | Karyawan pelapor membuka detail tiket yang sama | Karyawan (`employee@iki.co.id`) | Catatan internal sama sekali **tidak terlihat** oleh karyawan pelapor | [ ] | |
| E.3 | Karyawan lain yang tidak memiliki tiket mencoba mengakses URL tiket melalui manipulasi ID (IDOR) | Karyawan Lain (`karyawan.b@iki.co.id`) | Sistem menolak akses dengan respons HTTP 403 Forbidden | [ ] | |
| E.4 | Percobaan upload file berbahaya dengan ekstensi terlarang (`.php`, `.exe`, `.sh`) | Pengguna Sembarang | Sistem menolak upload dengan pesan kesalahan "Tipe file tidak diizinkan" | [ ] | |
| E.5 | Percobaan login salah sebanyak 5 kali berturut-turut pada akun yang sama | Penyerang / Brute-force | Sistem mengunci form login dengan pesan: "Terlalu banyak percobaan login gagal" (Rate Limited) | [ ] | |

---

## 3. Lembar Pengesahan UAT (Sign-Off)

Dengan menandatangani dokumen ini, para pihak menyatakan bahwa sistem **IKI IT Helpdesk v1.0.0** telah diuji sesuai kriteria penerimaan pengguna dan dinyatakan siap untuk tahap rilis operasional produksi (*Production Release*).

| Peran Stakeholder | Nama Pejabat / Perwakilan | Tanggal Verifikasi | Keputusan (Diterima / Ditolak) | Tanda Tangan |
| :--- | :--- | :--- | :--- | :--- |
| **Kepala / GM Divisi TI** | | | [ ] Diterima &nbsp; [ ] Ditolak | _____________________ |
| **Supervisor IT Helpdesk** | | | [ ] Diterima &nbsp; [ ] Ditolak | _____________________ |
| **Perwakilan Karyawan Galangan** | | | [ ] Diterima &nbsp; [ ] Ditolak | _____________________ |
| **Administrator Infrastruktur TI** | | | [ ] Diterima &nbsp; [ ] Ditolak | _____________________ |
