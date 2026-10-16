@extends('layouts.app')

@section('title', 'Laporan & Analitik Layanan TI — IKI IT Helpdesk')

@section('content')
<div class="space-y-6">

    <!-- Top Header Banner with Maritime Depth -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-brand-950 via-brand-900 to-navy-900 p-6 sm:p-8 text-white shadow-xl border border-brand-800/60">
        <!-- Blueprint Grid Pattern Overlay -->
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_1px_1px,rgba(255,255,255,0.08)_1px,transparent_0)] bg-[size:24px_24px] pointer-events-none opacity-60"></div>
        <div class="absolute -right-8 -bottom-8 w-64 h-64 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-5">
            <div>
                <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-200 text-xs font-semibold uppercase tracking-wider mb-2.5 backdrop-blur-xs">
                    <svg class="w-3.5 h-3.5 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span>Pusat Analitik &amp; Laporan Operasional TI</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Laporan Kinerja Layanan Helpdesk
                </h1>
                <p class="text-blue-100/90 text-xs sm:text-sm mt-1.5 max-w-3xl leading-relaxed">
                    Evaluasi komprehensif volume tiket, pemenuhan Service Level Agreement (SLA), kepuasan pengguna (CSAT), dan distribusi beban kerja teknisi PT Industri Kapal Indonesia (Persero).
                </p>
            </div>

            @if(auth()->user()->hasPermission('reports.export'))
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('reports.export', request()->all()) }}"
                       class="inline-flex items-center px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs sm:text-sm shadow-md hover:shadow-emerald-600/30 transition-all duration-200 cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-400">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Ekspor Data CSV
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Multi-Dimensional Filter Card -->
    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-xs p-5 sm:p-6">
        <form method="GET" action="{{ route('reports.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                <!-- Date Range: From -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Dari Tanggal (WITA)</label>
                    <input type="date"
                           name="date_from"
                           value="{{ $filters['date_from'] ?? '' }}"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white cursor-pointer transition-colors">
                </div>

                <!-- Date Range: To -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Sampai Tanggal (WITA)</label>
                    <input type="date"
                           name="date_to"
                           value="{{ $filters['date_to'] ?? '' }}"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white cursor-pointer transition-colors">
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Status Tiket</label>
                    <select name="status" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white cursor-pointer transition-colors">
                        <option value="">Semua Status</option>
                        @foreach($statuses as $st)
                            <option value="{{ $st->value }}" {{ ($filters['status'] ?? '') === $st->value ? 'selected' : '' }}>
                                {{ $st->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Priority Filter -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Tingkat Prioritas</label>
                    <select name="priority" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white cursor-pointer transition-colors">
                        <option value="">Semua Prioritas</option>
                        @foreach($priorities as $pr)
                            <option value="{{ $pr->value }}" {{ ($filters['priority'] ?? '') === $pr->value ? 'selected' : '' }}>
                                {{ $pr->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Category Filter -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Kategori Masalah</label>
                    <select name="category_id" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white cursor-pointer transition-colors">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ ($filters['category_id'] ?? '') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Department Filter -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Departemen Pelapor</label>
                    <select name="department_id" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white cursor-pointer transition-colors">
                        <option value="">Semua Departemen</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ ($filters['department_id'] ?? '') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Location Filter -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Unit / Lokasi Galangan</label>
                    <select name="location_id" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white cursor-pointer transition-colors">
                        <option value="">Semua Lokasi</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ ($filters['location_id'] ?? '') == $loc->id ? 'selected' : '' }}>
                                {{ $loc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Assignee Filter -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Teknisi Bertanggung Jawab</label>
                    <select name="assignee_id" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white cursor-pointer transition-colors">
                        <option value="">Semua Teknisi</option>
                        @foreach($agents as $ag)
                            <option value="{{ $ag->id }}" {{ ($filters['assignee_id'] ?? '') == $ag->id ? 'selected' : '' }}>
                                {{ $ag->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>

            <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-100">
                <a href="{{ route('reports.index') }}"
                   class="px-4 py-2 text-xs font-semibold rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors cursor-pointer">
                    Reset Filter
                </a>
                <button type="submit"
                        class="px-5 py-2 text-xs font-bold rounded-xl bg-brand-700 text-white hover:bg-brand-800 shadow-xs hover:shadow-brand-700/30 transition-all cursor-pointer flex items-center">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    <!-- KPI Metric Cards Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">

        <!-- Volume Total -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-xs">
            <span class="text-xs font-semibold text-slate-500">Volume Tiket</span>
            <div class="text-2xl font-extrabold text-slate-800 mt-1.5 font-mono tracking-tight">{{ $kpis['total'] }}</div>
            <div class="text-[10px] text-slate-400 mt-1">Total tiket terdata</div>
        </div>

        <!-- Resolution Rate -->
        <div class="bg-white p-4 rounded-2xl border border-emerald-200/90 shadow-xs">
            <span class="text-xs font-semibold text-emerald-700">Tingkat Penyelesaian</span>
            <div class="text-2xl font-extrabold text-emerald-800 mt-1.5 font-mono tracking-tight">{{ $kpis['resolution_rate'] }}%</div>
            <div class="text-[10px] text-emerald-600 mt-1">Selesai / Ditutup</div>
        </div>

        <!-- SLA Resolution Compliance -->
        <div class="bg-white p-4 rounded-2xl border border-blue-200/90 shadow-xs">
            <span class="text-xs font-semibold text-blue-700">Kepatuhan SLA Resolusi</span>
            <div class="text-2xl font-extrabold text-blue-800 mt-1.5 font-mono tracking-tight">{{ $kpis['sla_compliance_rate'] }}%</div>
            <div class="text-[10px] text-blue-600 mt-1">Target waktu terpenuhi</div>
        </div>

        <!-- SLA First Response Compliance -->
        <div class="bg-white p-4 rounded-2xl border border-indigo-200/90 shadow-xs">
            <span class="text-xs font-semibold text-indigo-700">Kepatuhan Respon</span>
            <div class="text-2xl font-extrabold text-indigo-800 mt-1.5 font-mono tracking-tight">{{ $kpis['first_response_compliance_rate'] }}%</div>
            <div class="text-[10px] text-indigo-600 mt-1">Tepat waktu tanggap</div>
        </div>

        <!-- CSAT Average -->
        <div class="bg-white p-4 rounded-2xl border border-amber-200/90 shadow-xs">
            <span class="text-xs font-semibold text-amber-700">Skor CSAT Pengguna</span>
            <div class="text-2xl font-extrabold text-amber-800 mt-1.5 font-mono tracking-tight flex items-center gap-1">
                {{ $kpis['avg_csat'] ? $kpis['avg_csat'].' / 5' : 'N/A' }}
            </div>
            <div class="text-[10px] text-amber-600 mt-1">{{ $kpis['total_ratings'] }} ulasan masuk</div>
        </div>

        <!-- Avg Resolution Duration -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-xs">
            <span class="text-xs font-semibold text-slate-500">Rata-rata Resolusi</span>
            <div class="text-2xl font-extrabold text-slate-800 mt-1.5 font-mono tracking-tight">{{ $kpis['avg_resolution_hours'] }} jam</div>
            <div class="text-[10px] text-slate-400 mt-1">Reopen: {{ $kpis['reopen_rate'] }}%</div>
        </div>

    </div>

    <!-- Analytical Breakdown Charts / Panels -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Top Issue Categories -->
        <div class="bg-white rounded-3xl border border-slate-200/90 shadow-xs p-5 sm:p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <div class="w-6 h-6 rounded-lg bg-brand-50 text-brand-700 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                    Kategori Masalah Paling Sering
                </h3>
                <span class="text-xs text-slate-400 font-mono">{{ $byCategory->count() }} kategori</span>
            </div>
            <div class="space-y-3.5">
                @forelse($byCategory->take(6) as $item)
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="font-bold text-slate-800">{{ $item['name'] }}</span>
                            <span class="font-mono text-slate-500 font-semibold">{{ $item['count'] }} tiket ({{ $item['percentage'] }}%)</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                            <div class="bg-brand-600 h-2.5 rounded-full transition-all duration-300" style="width: {{ $item['percentage'] }}%"></div>
                        </div>
                    </div>
                @empty
                    <div class="text-xs text-slate-400 italic py-6 text-center">Belum ada data tiket untuk kategori ini.</div>
                @endforelse
            </div>
        </div>

        <!-- Priority & Location Distribution -->
        <div class="bg-white rounded-3xl border border-slate-200/90 shadow-xs p-5 sm:p-6 space-y-5">
            <div>
                <h3 class="text-sm font-bold text-slate-900 mb-3 flex items-center gap-2">
                    <div class="w-6 h-6 rounded-lg bg-rose-50 text-rose-700 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    Distribusi Tingkat Prioritas
                </h3>
                <div class="grid grid-cols-4 gap-2 text-center text-xs">
                    <div class="p-2.5 rounded-2xl bg-rose-50 border border-rose-200">
                        <div class="text-rose-700 font-bold text-[11px]">P1 Critical</div>
                        <div class="text-xl font-mono font-extrabold text-rose-800 mt-1">{{ $byPriority['P1'] ?? 0 }}</div>
                    </div>
                    <div class="p-2.5 rounded-2xl bg-orange-50 border border-orange-200">
                        <div class="text-orange-700 font-bold text-[11px]">P2 High</div>
                        <div class="text-xl font-mono font-extrabold text-orange-800 mt-1">{{ $byPriority['P2'] ?? 0 }}</div>
                    </div>
                    <div class="p-2.5 rounded-2xl bg-sky-50 border border-sky-200">
                        <div class="text-sky-700 font-bold text-[11px]">P3 Medium</div>
                        <div class="text-xl font-mono font-extrabold text-sky-800 mt-1">{{ $byPriority['P3'] ?? 0 }}</div>
                    </div>
                    <div class="p-2.5 rounded-2xl bg-emerald-50 border border-emerald-200">
                        <div class="text-emerald-700 font-bold text-[11px]">P4 Low</div>
                        <div class="text-xl font-mono font-extrabold text-emerald-800 mt-1">{{ $byPriority['P4'] ?? 0 }}</div>
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-100 pt-4">
                <h3 class="text-sm font-bold text-slate-900 mb-3 flex items-center gap-2">
                    <div class="w-6 h-6 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    Distribusi Lokasi Kerja Galangan
                </h3>
                <div class="space-y-2 text-xs">
                    @forelse($byLocation as $loc)
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="font-bold text-slate-800">{{ $loc['name'] }}</span>
                            <span class="font-mono text-slate-600 font-semibold">{{ $loc['count'] }} tiket ({{ $loc['percentage'] }}%)</span>
                        </div>
                    @empty
                        <div class="text-xs text-slate-400 italic text-center py-2">Belum ada lokasi tercatat.</div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

    <!-- Workload & Performance per Agent -->
    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-xs overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div>
                <h3 class="text-base font-extrabold text-slate-900">Beban Kerja &amp; Kinerja Teknisi IT</h3>
                <p class="text-xs text-slate-500 mt-0.5">Pemantauan volume penugasan, tingkat penyelesaian, kepatuhan batas waktu, dan rating kepuasan CSAT</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50/80 text-[11px] uppercase tracking-wider text-slate-500 border-b border-slate-100 font-bold">
                    <tr>
                        <th class="py-3.5 px-4 sm:px-6">Nama Teknisi</th>
                        <th class="py-3.5 px-4 text-center">Total Ditugaskan</th>
                        <th class="py-3.5 px-4 text-center">Selesai Ditangani</th>
                        <th class="py-3.5 px-4 text-center">Pelanggaran SLA</th>
                        <th class="py-3.5 px-4 text-center">Rata-rata CSAT</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($agentWorkloads as $workload)
                        <tr class="hover:bg-slate-50/75 transition-colors">
                            <td class="py-3.5 px-4 sm:px-6">
                                <div class="font-bold text-slate-900">{{ $workload['name'] }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">{{ $workload['email'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-800">
                                {{ $workload['total_assigned'] }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono font-bold text-emerald-700">
                                {{ $workload['resolved_count'] }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono font-bold {{ $workload['breached_count'] > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                {{ $workload['breached_count'] }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono font-bold text-amber-700">
                                {{ $workload['csat'] ? '★ '.$workload['csat'].' / 5' : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400">Tidak ada teknisi aktif terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Filtered Tickets Data Table -->
    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-xs overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div>
                <h3 class="text-base font-extrabold text-slate-900">Rincian Data Tiket Terfilter</h3>
                <p class="text-xs text-slate-500 mt-0.5">Menampilkan {{ $tickets->total() }} tiket berdasarkan kriteria yang dipilih</p>
            </div>
            <span class="text-xs text-slate-400 font-mono">Halaman {{ $tickets->currentPage() }} dari {{ $tickets->lastPage() }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50/80 text-[11px] uppercase tracking-wider text-slate-500 border-b border-slate-100 font-bold">
                    <tr>
                        <th class="py-3.5 px-4 sm:px-6">No. Tiket</th>
                        <th class="py-3.5 px-4">Subjek</th>
                        <th class="py-3.5 px-4">Pelapor &amp; Dept</th>
                        <th class="py-3.5 px-4">Teknisi</th>
                        <th class="py-3.5 px-4 text-center">Prioritas</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-center">SLA Status</th>
                        <th class="py-3.5 px-4 text-center">CSAT</th>
                        <th class="py-3.5 px-4">Waktu Dibuat</th>
                        <th class="py-3.5 px-4 sm:px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($tickets as $t)
                        <tr class="hover:bg-slate-50/75 transition-colors group">
                            <td class="py-3.5 px-4 sm:px-6 font-mono font-bold text-brand-700 whitespace-nowrap">
                                <a href="{{ route('tickets.show', $t) }}" class="inline-flex items-center space-x-1 hover:text-brand-900 group-hover:underline">
                                    <svg class="w-3.5 h-3.5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                                    </svg>
                                    <span>{{ $t->ticket_number }}</span>
                                </a>
                            </td>
                            <td class="py-3.5 px-4 max-w-xs">
                                <a href="{{ route('tickets.show', $t) }}" class="font-bold text-slate-800 hover:text-brand-700 line-clamp-1">
                                    {{ $t->subject }}
                                </a>
                                <div class="text-[11px] text-slate-400 mt-0.5">{{ $t->category?->name }}</div>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="font-bold text-slate-800">{{ $t->requester?->name }}</div>
                                <div class="text-[11px] text-slate-400">{{ $t->requester?->department?->name ?? '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($t->assignee)
                                    <span class="inline-flex items-center text-slate-700 font-medium">
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 mr-1.5"></span>
                                        {{ $t->assignee->name }}
                                    </span>
                                @else
                                    <span class="text-slate-400 italic">Belum ditugaskan</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold priority-badge-{{ $t->priority->value }}">
                                    {{ $t->priority->label() }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold status-badge-{{ $t->status->value }}">
                                    {{ $t->status->label() }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                @php $slaBadge = $t->slaStatusBadge(); @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $slaBadge['bg'] }} {{ $slaBadge['text'] }} border {{ $slaBadge['border'] }}">
                                    {{ $slaBadge['label'] }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                @if($t->rating)
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        ★ {{ $t->rating }}
                                    </span>
                                @else
                                    <span class="text-slate-400 font-mono">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap font-mono text-[11px] text-slate-500">
                                {{ $t->created_at->timezone('Asia/Makassar')->format('d/m/Y H:i') }}
                            </td>
                            <td class="py-3.5 px-4 sm:px-6 text-right whitespace-nowrap">
                                <a href="{{ route('tickets.show', $t) }}"
                                   class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-brand-50 hover:text-brand-700 transition-colors">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-12 text-center text-slate-400">
                                Tidak ada data tiket yang cocok dengan kriteria filter saat ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tickets->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $tickets->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
