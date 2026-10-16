<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Asset;
use App\Models\BusinessCalendar;
use App\Models\Department;
use App\Models\Location;
use App\Models\Permission;
use App\Models\Role;
use App\Models\SlaPolicy;
use App\Models\TicketCategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Departments ───
        $departments = [
            ['code' => 'IT', 'name' => 'Divisi Teknologi Informasi'],
            ['code' => 'HRD', 'name' => 'Divisi SDM'],
            ['code' => 'FIN', 'name' => 'Divisi Keuangan'],
            ['code' => 'PROD', 'name' => 'Divisi Produksi & Galangan'],
            ['code' => 'QC', 'name' => 'Divisi Quality Control'],
            ['code' => 'PROC', 'name' => 'Divisi Pengadaan & Logistik'],
            ['code' => 'GA', 'name' => 'Divisi General Affairs'],
        ];
        foreach ($departments as $dept) {
            Department::firstOrCreate(['code' => $dept['code']], $dept);
        }

        // ─── Locations ───
        $locations = [
            ['code' => 'MKS-HQ', 'name' => 'Kantor Pusat Makassar', 'timezone' => 'Asia/Makassar'],
            ['code' => 'MKS-GAL', 'name' => 'Galangan Kapal Makassar', 'timezone' => 'Asia/Makassar'],
            ['code' => 'JKT', 'name' => 'Kantor Perwakilan Jakarta', 'timezone' => 'Asia/Jakarta'],
            ['code' => 'BIT', 'name' => 'Unit Galangan Bitung', 'timezone' => 'Asia/Makassar'],
        ];
        foreach ($locations as $loc) {
            Location::firstOrCreate(['code' => $loc['code']], $loc);
        }

        // ─── Permissions ───
        $permissionNames = [
            'tickets.create', 'tickets.view_own', 'tickets.view_assigned',
            'tickets.view_all', 'tickets.assign', 'tickets.update',
            'tickets.change_priority', 'tickets.resolve', 'tickets.close',
            'tickets.reopen', 'tickets.cancel',
            'tickets.comment_public', 'tickets.comment_internal',
            'tickets.attachments.download',
            'users.manage', 'roles.manage', 'settings.manage', 'audit.view',
            'reports.view', 'reports.export',
            'approvals.view_assigned', 'approvals.decide',
            'assets.view', 'assets.manage',
            'knowledge.view', 'knowledge.manage',
        ];
        $permissions = [];
        foreach ($permissionNames as $name) {
            $permissions[$name] = Permission::firstOrCreate(['name' => $name]);
        }

        // ─── Roles with permissions ───
        $roleDefinitions = [
            'super_admin' => [
                'display_name' => 'Super Administrator',
                'permissions' => $permissionNames,
            ],
            'employee' => [
                'display_name' => 'Karyawan',
                'permissions' => [
                    'tickets.create', 'tickets.view_own',
                    'tickets.comment_public', 'tickets.attachments.download',
                    'tickets.close', 'tickets.cancel', 'tickets.reopen',
                    'knowledge.view',
                ],
            ],
            'it_agent' => [
                'display_name' => 'IT Agent / Teknisi',
                'permissions' => [
                    'tickets.create', 'tickets.view_own', 'tickets.view_assigned',
                    'tickets.update', 'tickets.resolve',
                    'tickets.comment_public', 'tickets.comment_internal',
                    'tickets.attachments.download', 'tickets.close',
                    'assets.view',
                    'knowledge.view', 'knowledge.manage',
                ],
            ],
            'it_supervisor' => [
                'display_name' => 'IT Supervisor',
                'permissions' => [
                    'tickets.create', 'tickets.view_own', 'tickets.view_assigned',
                    'tickets.view_all', 'tickets.assign', 'tickets.update',
                    'tickets.change_priority', 'tickets.resolve', 'tickets.close',
                    'tickets.reopen', 'tickets.cancel',
                    'tickets.comment_public', 'tickets.comment_internal',
                    'tickets.attachments.download',
                    'reports.view', 'reports.export',
                    'approvals.view_assigned', 'approvals.decide',
                    'assets.view', 'assets.manage',
                    'knowledge.view', 'knowledge.manage',
                ],
            ],
            'it_admin' => [
                'display_name' => 'IT Administrator',
                'permissions' => [
                    'tickets.create', 'tickets.view_own', 'tickets.view_all',
                    'tickets.assign', 'tickets.update', 'tickets.change_priority',
                    'tickets.resolve', 'tickets.close', 'tickets.reopen', 'tickets.cancel',
                    'tickets.comment_public', 'tickets.comment_internal',
                    'tickets.attachments.download',
                    'users.manage', 'roles.manage', 'settings.manage', 'audit.view',
                    'reports.view', 'reports.export',
                    'approvals.view_assigned', 'approvals.decide',
                    'assets.view', 'assets.manage',
                    'knowledge.view', 'knowledge.manage',
                ],
            ],
            'management_viewer' => [
                'display_name' => 'Manajemen Eksekutif',
                'permissions' => [
                    'reports.view',
                    'knowledge.view',
                ],
            ],
            'approver' => [
                'display_name' => 'Approver',
                'permissions' => [
                    'tickets.create', 'tickets.view_own',
                    'tickets.comment_public', 'tickets.attachments.download',
                    'approvals.view_assigned', 'approvals.decide',
                    'knowledge.view',
                ],
            ],
        ];

        foreach ($roleDefinitions as $name => $def) {
            $role = Role::firstOrCreate(
                ['name' => $name],
                ['display_name' => $def['display_name']],
            );
            $permissionIds = [];
            foreach ($def['permissions'] as $permName) {
                if (isset($permissions[$permName])) {
                    $permissionIds[] = $permissions[$permName]->id;
                }
            }
            $role->permissions()->syncWithoutDetaching($permissionIds);
        }

        // ─── Ticket Categories & Subcategories (from PRD Section 5) ───
        $categoryTree = [
            [
                'code' => 'HW',
                'name' => 'Hardware & End User Support',
                'sort_order' => 1,
                'children' => [
                    ['code' => 'HW-PC', 'name' => 'PC / Komputer Desktop', 'sort_order' => 1],
                    ['code' => 'HW-LAP', 'name' => 'Laptop / Notebook', 'sort_order' => 2],
                    ['code' => 'HW-PRN', 'name' => 'Printer / Scanner', 'sort_order' => 3],
                    ['code' => 'HW-PER', 'name' => 'Periferal & Aksesori', 'sort_order' => 4],
                ],
            ],
            [
                'code' => 'NET',
                'name' => 'Network & Connectivity',
                'sort_order' => 2,
                'children' => [
                    ['code' => 'NET-LAN', 'name' => 'Kabel LAN Galangan & Kantor', 'sort_order' => 1],
                    ['code' => 'NET-WIFI', 'name' => 'Koneksi Wi-Fi', 'sort_order' => 2],
                    ['code' => 'NET-VPN', 'name' => 'VPN & Akses Jarak Jauh', 'sort_order' => 3],
                    ['code' => 'NET-INT', 'name' => 'Koneksi Internet Utama', 'sort_order' => 4],
                ],
            ],
            [
                'code' => 'SRV',
                'name' => 'Server, Cloud & Infrastructure',
                'sort_order' => 3,
                'children' => [
                    ['code' => 'SRV-FILE', 'name' => 'File Sharing / Storage Server', 'sort_order' => 1],
                    ['code' => 'SRV-VM', 'name' => 'Virtual Machine / Backup', 'sort_order' => 2],
                ],
            ],
            [
                'code' => 'APP',
                'name' => 'Business Applications',
                'sort_order' => 4,
                'children' => [
                    ['code' => 'APP-ERP', 'name' => 'Sistem ERP Korporat', 'sort_order' => 1],
                    ['code' => 'APP-HRIS', 'name' => 'Sistem Informasi SDM (HRIS)', 'sort_order' => 2],
                    ['code' => 'APP-MAIL', 'name' => 'Email Perusahaan', 'sort_order' => 3],
                ],
            ],
            [
                'code' => 'SEC',
                'name' => 'Account, Access & Cybersecurity',
                'sort_order' => 5,
                'is_security_sensitive' => true,
                'children' => [
                    ['code' => 'SEC-PWD', 'name' => 'Reset Kata Sandi Akun', 'sort_order' => 1],
                    ['code' => 'SEC-MAL', 'name' => 'Peringatan Virus / Malware', 'sort_order' => 2],
                ],
            ],
            [
                'code' => 'REQ',
                'name' => 'IT Service Request',
                'sort_order' => 6,
                'requires_approval' => true,
                'children' => [
                    ['code' => 'REQ-DEV', 'name' => 'Pengadaan Perangkat Baru', 'sort_order' => 1],
                    ['code' => 'REQ-ACC', 'name' => 'Permohonan Hak Akses Sistem', 'sort_order' => 2],
                ],
            ],
        ];

        foreach ($categoryTree as $catData) {
            $children = $catData['children'] ?? [];
            unset($catData['children']);

            $parent = TicketCategory::firstOrCreate(['code' => $catData['code']], $catData);

            foreach ($children as $childData) {
                $childData['parent_id'] = $parent->id;
                if (! isset($childData['requires_approval']) && $parent->requires_approval) {
                    $childData['requires_approval'] = true;
                }
                if (! isset($childData['is_security_sensitive']) && $parent->is_security_sensitive) {
                    $childData['is_security_sensitive'] = true;
                }
                TicketCategory::updateOrCreate(['code' => $childData['code']], $childData);
            }
        }

        // ─── Demo Users ───
        $itDept = Department::where('code', 'IT')->first();
        $finDept = Department::where('code', 'FIN')->first();
        $hqLoc = Location::where('code', 'MKS-HQ')->first();

        // 1. Super Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@iki.co.id'],
            [
                'name' => 'Administrator IT',
                'employee_number' => 'IKI-001',
                'password' => Hash::make('password123'),
                'department_id' => $itDept->id,
                'location_id' => $hqLoc->id,
                'is_active' => true,
            ],
        );
        $admin->roles()->syncWithoutDetaching([
            Role::where('name', 'super_admin')->first()->id,
            Role::where('name', 'it_admin')->first()->id,
        ]);

        // 2. Supervisor IT
        $supervisor = User::firstOrCreate(
            ['email' => 'supervisor@iki.co.id'],
            [
                'name' => 'Supervisor IT',
                'employee_number' => 'IKI-002',
                'password' => Hash::make('password123'),
                'department_id' => $itDept->id,
                'location_id' => $hqLoc->id,
                'is_active' => true,
            ],
        );
        $supervisor->roles()->syncWithoutDetaching([Role::where('name', 'it_supervisor')->first()->id]);

        // 3. IT Agent / Teknisi
        $agent = User::firstOrCreate(
            ['email' => 'agent@iki.co.id'],
            [
                'name' => 'Teknisi IT',
                'employee_number' => 'IKI-003',
                'password' => Hash::make('password123'),
                'department_id' => $itDept->id,
                'location_id' => $hqLoc->id,
                'is_active' => true,
            ],
        );
        $agent->roles()->syncWithoutDetaching([Role::where('name', 'it_agent')->first()->id]);

        // 4. Employee (employee@iki.co.id and karyawan@iki.co.id)
        $employee = User::firstOrCreate(
            ['email' => 'employee@iki.co.id'],
            [
                'name' => 'Ahmad Karyawan',
                'employee_number' => 'IKI-100',
                'password' => Hash::make('password123'),
                'department_id' => $finDept->id,
                'location_id' => $hqLoc->id,
                'is_active' => true,
            ],
        );
        $employee->roles()->syncWithoutDetaching([Role::where('name', 'employee')->first()->id]);

        $karyawan = User::firstOrCreate(
            ['email' => 'karyawan@iki.co.id'],
            [
                'name' => 'Bambang Karyawan',
                'employee_number' => 'IKI-101',
                'password' => Hash::make('password123'),
                'department_id' => $finDept->id,
                'location_id' => $hqLoc->id,
                'is_active' => true,
            ],
        );
        $karyawan->roles()->syncWithoutDetaching([Role::where('name', 'employee')->first()->id]);

        // 5. Approver
        $approver = User::firstOrCreate(
            ['email' => 'approver@iki.co.id'],
            [
                'name' => 'Budi Manajer (Approver)',
                'employee_number' => 'IKI-050',
                'password' => Hash::make('password123'),
                'department_id' => $finDept->id,
                'location_id' => $hqLoc->id,
                'is_active' => true,
            ],
        );
        $approver->roles()->syncWithoutDetaching([Role::where('name', 'approver')->first()->id]);

        // ─── Business Calendar & SLA Policies (PRD Section 6) ───
        $calendar = BusinessCalendar::firstOrCreate(
            ['name' => 'Jam Kerja PT IKI (Makassar WITA)'],
            [
                'timezone' => 'Asia/Makassar',
                'work_start_time' => '08:00',
                'work_end_time' => '17:00',
                'work_days' => [1, 2, 3, 4, 5], // Senin - Jumat
                'holidays' => ['2026-01-01', '2026-08-17', '2026-12-25'],
                'is_active' => true,
            ]
        );

        $slaPolicies = [
            [
                'name' => 'SLA P1 - Critical (24/7 Tanggap Darurat)',
                'priority' => 'P1',
                'first_response_minutes' => 15,
                'resolution_minutes' => 240, // 4 hours
                'business_calendar_id' => null, // 24/7 calendar
                'pause_on_pending_reasons' => ['waiting_vendor', 'waiting_parts', 'waiting_approval'],
                'is_active' => true,
            ],
            [
                'name' => 'SLA P2 - High (Prioritas Tinggi)',
                'priority' => 'P2',
                'first_response_minutes' => 30,
                'resolution_minutes' => 480, // 8 jam kerja (1 hari kerja)
                'business_calendar_id' => $calendar->id,
                'pause_on_pending_reasons' => ['waiting_vendor', 'waiting_parts', 'waiting_approval'],
                'is_active' => true,
            ],
            [
                'name' => 'SLA P3 - Medium (Standar Operasional)',
                'priority' => 'P3',
                'first_response_minutes' => 240, // 4 jam kerja
                'resolution_minutes' => 960, // 16 jam kerja (2 hari kerja)
                'business_calendar_id' => $calendar->id,
                'pause_on_pending_reasons' => ['waiting_vendor', 'waiting_parts', 'waiting_approval'],
                'is_active' => true,
            ],
            [
                'name' => 'SLA P4 - Low (Permintaan Rendah / Rutin)',
                'priority' => 'P4',
                'first_response_minutes' => 480, // 8 jam kerja
                'resolution_minutes' => 2400, // 40 jam kerja (5 hari kerja)
                'business_calendar_id' => $calendar->id,
                'pause_on_pending_reasons' => ['waiting_vendor', 'waiting_parts', 'waiting_approval'],
                'is_active' => true,
            ],
        ];

        foreach ($slaPolicies as $policy) {
            SlaPolicy::updateOrCreate(
                ['priority' => $policy['priority']],
                $policy
            );
        }

        // ─── IT Asset Registry (Sample Assets) ───
        $galLoc = Location::where('code', 'MKS-GAL')->first() ?? $hqLoc;
        $prodDept = Department::where('code', 'PROD')->first() ?? $finDept;

        $sampleAssets = [
            [
                'asset_tag' => 'IKI-AST-2026-001',
                'name' => 'Workstation CAD/CAM Graving Dock',
                'category' => 'hardware',
                'model' => 'Dell Precision 3660 Tower',
                'serial_number' => 'SN-DELL-GD-0912',
                'status' => 'active',
                'location_id' => $galLoc->id,
                'department_id' => $prodDept->id,
                'assigned_to_user_id' => $employee->id,
                'purchase_date' => '2025-01-15',
                'warranty_expiry' => '2028-01-15',
                'notes' => 'Komputer pemrosesan blueprint kapal galangan utama.',
            ],
            [
                'asset_tag' => 'IKI-AST-2026-002',
                'name' => 'Laptop ThinkPad Biro Pengadaan',
                'category' => 'hardware',
                'model' => 'Lenovo ThinkPad E14 Gen 4',
                'serial_number' => 'SN-LNV-PROC-4412',
                'status' => 'active',
                'location_id' => $hqLoc->id,
                'department_id' => $finDept->id,
                'assigned_to_user_id' => $karyawan->id,
                'purchase_date' => '2025-06-10',
                'warranty_expiry' => '2027-06-10',
                'notes' => 'Laptop operasional pengadaan material baja galangan.',
            ],
            [
                'asset_tag' => 'IKI-AST-2026-003',
                'name' => 'Printer HP LaserJet Slipway Bengkel',
                'category' => 'peripheral',
                'model' => 'HP LaserJet Pro M404dn',
                'serial_number' => 'SN-HP-SLP-7721',
                'status' => 'active',
                'location_id' => $galLoc->id,
                'department_id' => $prodDept->id,
                'assigned_to_user_id' => null,
                'purchase_date' => '2024-11-20',
                'warranty_expiry' => '2026-11-20',
                'notes' => 'Printer cetak surat jalan dan inspeksi lambung kapal.',
            ],
            [
                'asset_tag' => 'IKI-AST-2026-004',
                'name' => 'Cisco Core Switch 48-Port Ruang Server',
                'category' => 'network',
                'model' => 'Cisco Catalyst 3850-48T',
                'serial_number' => 'SN-CSCO-SRV-9901',
                'status' => 'active',
                'location_id' => $hqLoc->id,
                'department_id' => $itDept->id,
                'assigned_to_user_id' => null,
                'purchase_date' => '2023-08-14',
                'warranty_expiry' => '2026-08-14',
                'notes' => 'Switch tulang punggung (backbone) jaringan kantor pusat dan galangan.',
            ],
        ];

        foreach ($sampleAssets as $ast) {
            Asset::updateOrCreate(
                ['asset_tag' => $ast['asset_tag']],
                $ast
            );
        }

        // ─── Knowledge Base Articles (Sample Guides) ───
        $netCategory = TicketCategory::where('code', 'NET-WIFI')->first() ?? TicketCategory::first();
        $appCategory = TicketCategory::where('code', 'REQ-ACC')->first() ?? TicketCategory::first();
        $hwCategory = TicketCategory::where('code', 'HW-PRN')->first() ?? TicketCategory::first();

        $sampleArticles = [
            [
                'title' => 'Panduan Koneksi Wi-Fi Internal PT IKI di Galangan & Kantor',
                'slug' => 'panduan-koneksi-wifi-galangan-pt-iki',
                'category_id' => $netCategory->id,
                'author_id' => $agent->id,
                'content' => "## Cara Menghubungkan Perangkat ke Jaringan Wi-Fi IKI-Enterprise\n\n1. Pastikan fitur Wi-Fi pada perangkat laptop atau ponsel Anda telah aktif.\n2. Pilih SSID bernama **IKI-Galangan-Secure** atau **IKI-Corporate**.\n3. Masukkan identitas akun pegawai Anda menggunakan format: NIP atau Email Resmi.\n4. Apabila muncul verifikasi sertifikat keselamatan, pilih **Trust Certificate** (PT IKI CA).\n5. Jika koneksi gagal, periksa apakah Anda berada dalam jangkauan Access Point dermaga atau hubungi Helpdesk TI pada ekstensi 102.",
                'status' => 'published',
                'views_count' => 45,
                'helpful_count' => 18,
                'published_at' => Carbon::now('UTC')->subDays(10),
            ],
            [
                'title' => 'Tata Cara dan Alur Pengajuan Akses Modul ERP Galangan',
                'slug' => 'tata-cara-alur-pengajuan-akses-modul-erp-galangan',
                'category_id' => $appCategory->id,
                'author_id' => $supervisor->id,
                'content' => "## Prosedur Hak Akses Sistem ERP Korporat\n\nUntuk menjaga kepatuhan dan keamanan data keuangan serta proyek kapal PT IKI, permohonan hak akses modul sistem informasi mengikuti prosedur sebagai berikut:\n\n1. Pemohon membuat tiket dengan kategori **Permintaan Akses & Akun**.\n2. Tuliskan modul yang dibutuhkan (misal: General Ledger, Proyek Galangan, Inventory).\n3. Tiket akan masuk secara otomatis ke antrean persetujuan (*Pending Approval*) Manajer Pemohon.\n4. Setelah disetujui Manajer, teknisi IT akan membuatkan user ID dalam waktu maksimal 4 jam kerja.",
                'status' => 'published',
                'views_count' => 62,
                'helpful_count' => 25,
                'published_at' => Carbon::now('UTC')->subDays(7),
            ],
            [
                'title' => 'Penanganan Cepat Kertas Macet (Paper Jam) Printer Operasional',
                'slug' => 'penanganan-cepat-kertas-macet-paper-jam-printer',
                'category_id' => $hwCategory->id,
                'author_id' => $agent->id,
                'content' => "## Langkah Mandiri Menangani Kertas Tersangkut\n\n1. Matikan tombol daya printer terlebih dahulu untuk keselamatan.\n2. Buka penutup belakang dan penutup baki kertas utama secara perlahan.\n3. Tarik lembaran kertas yang tersangkut searah dengan jalur keluarnya kertas (jangan ditarik berlawanan arah agar roller tidak rusak).\n4. Periksa apakah terdapat serpihan kertas kecil di area roller fuser.\n5. Tutup kembali penutup baki dan nyalakan daya. Jika lampu indikator tetap merah, segera buat tiket bantuan IT.",
                'status' => 'published',
                'views_count' => 28,
                'helpful_count' => 12,
                'published_at' => Carbon::now('UTC')->subDays(3),
            ],
            [
                'title' => 'Draft Standarisasi Backup File Gambar Teknik Kapal (CAD)',
                'slug' => 'draft-standarisasi-backup-cad-galangan',
                'category_id' => $hwCategory->id,
                'author_id' => $agent->id,
                'content' => "## Draf Standar Operasional Backup Gambar CAD\n\nArtikel ini masih dalam peninjauan internal tim IT Infrastructure mengenai jadwal sinkronisasi otomatis file DWG/DXF dari workstation perancang ke server NAS terpusat.",
                'status' => 'draft',
                'views_count' => 3,
                'helpful_count' => 0,
                'published_at' => null,
            ],
        ];

        foreach ($sampleArticles as $art) {
            Article::updateOrCreate(
                ['slug' => $art['slug']],
                $art
            );
        }
    }
}
