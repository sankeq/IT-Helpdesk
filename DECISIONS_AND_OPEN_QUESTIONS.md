# Decisions & Open Questions

## 1. Keputusan baseline (dapat diubah melalui persetujuan)

- Arsitektur: Laravel modular monolith.
- UI: Blade SSR + Tailwind CSS.
- Target: 100–500 pengguna.
- Deployment: cloud.
- Zona waktu bisnis: Asia/Makassar.
- Ticket lifecycle: Open, Assigned, In Progress, Pending, Resolved, Reopened, Closed, Cancelled.
- Attachment: private storage.
- Access control: RBAC + resource policies.
- SLA: configurable, bukan hard-coded.
- Integrasi: tidak diasumsikan tersedia sampai API dan akses dikonfirmasi.

## 2. Keputusan yang harus dikonfirmasi sebelum produksi

### Platform dan operasi
1. Provider cloud dan region yang disetujui?
2. Apakah domain menggunakan subdomain internal atau dapat diakses dari internet?
3. Apakah perlu VPN/IP allowlist?
4. Siapa pemilik operasional dan support aplikasi?
5. Target availability, RPO, RTO yang disetujui?

### Identitas
6. Login lokal, SSO, Active Directory/LDAP, atau IdP lain?
7. Sumber data pegawai resmi dan pemilik master data?
8. Apakah NIP tersedia dan unik untuk seluruh akun yang relevan?

### Proses layanan
9. Jam layanan dan kalender hari libur?
10. Definisi response pertama yang sah?
11. Apakah SLA pause saat pending; untuk alasan apa?
12. Aturan auto-close dan reopen?
13. Apakah P1 memiliki on-call 24/7?
14. Daftar kategori dan subkategori resmi?
15. Apakah request perangkat/akses memerlukan approval, dan siapa approver berdasarkan matriks kewenangan?

### Data dan integrasi
16. Database pilihan final: MySQL atau PostgreSQL?
17. Email relay/provider dan sender resmi?
18. API HRIS, ERP, EAM, e-Office tersedia dan terdokumentasi?
19. Aturan retensi tiket, attachment, dan audit log?
20. Apakah tiket keamanan memiliki klasifikasi dan visibility khusus?

### Pelaporan
21. KPI resmi, denominator, kalender kerja, dan format laporan bulanan?
22. Apakah ekspor CSV diizinkan untuk semua supervisor atau hanya role tertentu?

## 3. Asumsi non-blocking

- Mulai dengan login lokal jika belum ada SSO; rancang agar dapat diganti/integrasikan.
- Gunakan data sintetis untuk development.
- Gunakan email dan in-app notification.
- Integrasi eksternal ditunda sampai API disahkan.
- SLA angka awal hanya dipakai pada test/staging sampai disetujui.
- Approval dimulai dengan satu approver per request; multi-level approval dapat ditambahkan bila diperlukan.

## 4. Risiko go-live

Jangan go-live sebelum:
- SLA dan jam kerja disahkan.
- Role/permission diuji.
- File storage privat diverifikasi.
- Backup dan restore diuji.
- UAT disetujui.
- Domain/TLS/monitoring/incident response tersedia.
- Pemilik aplikasi dan prosedur support ditetapkan.
