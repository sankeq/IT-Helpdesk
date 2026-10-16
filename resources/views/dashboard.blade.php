@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">

    <!-- Top Greeting Banner -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-brand-950 via-slate-900 to-navy-900 p-6 sm:p-8 text-white shadow-xl border border-brand-800/40">
        <!-- Background decorative ambient mesh & shapes -->
        <div class="absolute -right-12 -top-12 h-64 w-64 rounded-full bg-blue-500/15 blur-3xl pointer-events-none"></div>
        <div class="absolute right-1/4 -bottom-16 h-56 w-56 rounded-full bg-emerald-500/10 blur-3xl pointer-events-none"></div>
        <div class="absolute inset-0 bg-[radial-gradient(rgba(255,255,255,0.06)_1px,transparent_1px)] [background-size:20px_20px] pointer-events-none opacity-40"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="space-y-3">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-200 text-xs font-semibold tracking-wide uppercase">
                        <svg class="w-3.5 h-3.5 text-blue-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2v20m0 0a8 8 0 01-8-8H2m10 8a8 8 0 008-8h-2M12 2a3 3 0 100 6 3 3 0 000-6z"/>
                        </svg>
                        PT Industri Kapal Indonesia (Persero)
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/10 border border-white/15 text-blue-100 text-xs font-medium">
                        <svg class="w-3 h-3 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ now()->timezone('Asia/Makassar')->translatedFormat('l, d M Y') }} • WITA</span>
                    </span>
                </div>

                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white flex items-center gap-2">
                        Selamat Datang, {{ auth()->user()->name }}!
                    </h1>
                    <p class="text-blue-100/90 text-xs sm:text-sm mt-1 max-w-2xl leading-relaxed">
                        Pusat Kendali Layanan TI Terpadu. Pantau siklus penanganan tiket, status persetujuan, dan kepatuhan SLA operasional secara real-time.
                    </p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap items-center gap-3 shrink-0">
                <a href="{{ route('tickets.create') }}"
                   class="inline-flex items-center px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold text-xs sm:text-sm shadow-lg shadow-emerald-900/30 hover:shadow-emerald-700/40 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2 focus:ring-offset-brand-950">
                    <svg class="w-4 h-4 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    Buat Tiket Baru
                </a>
                <a href="{{ route('tickets.index') }}"
                   class="inline-flex items-center px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/15 text-white font-semibold text-xs sm:text-sm border border-white/20 backdrop-blur-xs shadow-xs hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 cursor-pointer focus:outline-none focus:ring-2 focus:ring-white/40">
                    <svg class="w-4 h-4 mr-2 text-blue-200 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                    Lihat Semua Tiket
                </a>
            </div>
        </div>
    </div>

    <!-- KPI / Statistics Cards Grid (Ringkasan Status Tiket) -->
    <div>
        <div class="flex items-center justify-between mb-3 px-0.5">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Ringkasan Status Tiket
            </h2>
            <span class="text-[11px] text-slate-400">Total terdata: <strong class="text-slate-700 font-mono">{{ $stats['total'] }}</strong> tiket</span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-7 gap-3 sm:gap-4">

            <!-- Total -->
            <a href="{{ route('tickets.index') }}"
               class="group relative bg-white p-4 rounded-2xl border border-slate-200/90 hover:border-slate-300 shadow-xs hover:shadow-md hover:-translate-y-1 transition-all duration-200 flex flex-col justify-between overflow-hidden cursor-pointer">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 group-hover:text-slate-700 transition-colors">Total Tiket</span>
                    <div class="w-7 h-7 rounded-lg bg-slate-100 group-hover:bg-slate-200 text-slate-600 flex items-center justify-center transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                </div>
                <div class="my-2">
                    <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 font-mono tracking-tight group-hover:text-brand-700 transition-colors">
                        {{ $stats['total'] }}
                    </div>
                </div>
                <div class="text-[11px] text-slate-400 font-medium flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                    Semua tiket terdaftar
                </div>
                <div class="absolute inset-x-0 bottom-0 h-0.5 bg-slate-300 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            </a>

            <!-- Open (Terbuka) -->
            <a href="{{ route('tickets.index', ['status' => 'open']) }}"
               class="group relative bg-white p-4 rounded-2xl border border-blue-200/80 hover:border-blue-400 shadow-xs hover:shadow-md hover:-translate-y-1 transition-all duration-200 flex flex-col justify-between overflow-hidden cursor-pointer">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-blue-700">Terbuka</span>
                    <div class="w-7 h-7 rounded-lg bg-blue-50 group-hover:bg-blue-100 text-blue-600 flex items-center justify-center transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                        </svg>
                    </div>
                </div>
                <div class="my-2">
                    <div class="text-2xl sm:text-3xl font-extrabold text-blue-700 font-mono tracking-tight group-hover:text-blue-800 transition-colors">
                        {{ $stats['open'] }}
                    </div>
                </div>
                <div class="text-[11px] text-blue-600 font-medium flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                    Menunggu respon
                </div>
                <div class="absolute inset-x-0 bottom-0 h-0.5 bg-blue-500 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            </a>

            <!-- Assigned (Ditugaskan) -->
            <a href="{{ route('tickets.index', ['status' => 'assigned']) }}"
               class="group relative bg-white p-4 rounded-2xl border border-indigo-200/80 hover:border-indigo-400 shadow-xs hover:shadow-md hover:-translate-y-1 transition-all duration-200 flex flex-col justify-between overflow-hidden cursor-pointer">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-indigo-700">Ditugaskan</span>
                    <div class="w-7 h-7 rounded-lg bg-indigo-50 group-hover:bg-indigo-100 text-indigo-600 flex items-center justify-center transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                </div>
                <div class="my-2">
                    <div class="text-2xl sm:text-3xl font-extrabold text-indigo-700 font-mono tracking-tight group-hover:text-indigo-800 transition-colors">
                        {{ $stats['assigned'] }}
                    </div>
                </div>
                <div class="text-[11px] text-indigo-600 font-medium flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                    Ada teknisi
                </div>
                <div class="absolute inset-x-0 bottom-0 h-0.5 bg-indigo-500 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            </a>

            <!-- In Progress (Diproses) -->
            <a href="{{ route('tickets.index', ['status' => 'in_progress']) }}"
               class="group relative bg-white p-4 rounded-2xl border border-amber-200/80 hover:border-amber-400 shadow-xs hover:shadow-md hover:-translate-y-1 transition-all duration-200 flex flex-col justify-between overflow-hidden cursor-pointer">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-amber-700">Diproses</span>
                    <div class="w-7 h-7 rounded-lg bg-amber-50 group-hover:bg-amber-100 text-amber-600 flex items-center justify-center transition-colors">
                        <svg class="w-3.5 h-3.5 group-hover:rotate-45 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="my-2">
                    <div class="text-2xl sm:text-3xl font-extrabold text-amber-700 font-mono tracking-tight group-hover:text-amber-800 transition-colors">
                        {{ $stats['in_progress'] }}
                    </div>
                </div>
                <div class="text-[11px] text-amber-600 font-medium flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping"></span>
                    Sedang ditangani
                </div>
                <div class="absolute inset-x-0 bottom-0 h-0.5 bg-amber-500 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            </a>

            <!-- Pending -->
            <a href="{{ route('tickets.index', ['status' => 'pending']) }}"
               class="group relative bg-white p-4 rounded-2xl border border-orange-200/80 hover:border-orange-400 shadow-xs hover:shadow-md hover:-translate-y-1 transition-all duration-200 flex flex-col justify-between overflow-hidden cursor-pointer">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-orange-700">Pending</span>
                    <div class="w-7 h-7 rounded-lg bg-orange-50 group-hover:bg-orange-100 text-orange-600 flex items-center justify-center transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="my-2">
                    <div class="text-2xl sm:text-3xl font-extrabold text-orange-700 font-mono tracking-tight group-hover:text-orange-800 transition-colors">
                        {{ $stats['pending'] }}
                    </div>
                </div>
                <div class="text-[11px] text-orange-600 font-medium flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                    Tertunda sementara
                </div>
                <div class="absolute inset-x-0 bottom-0 h-0.5 bg-orange-500 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            </a>

            <!-- Resolved (Selesai) -->
            <a href="{{ route('tickets.index', ['status' => 'resolved']) }}"
               class="group relative bg-white p-4 rounded-2xl border border-emerald-200/80 hover:border-emerald-400 shadow-xs hover:shadow-md hover:-translate-y-1 transition-all duration-200 flex flex-col justify-between overflow-hidden cursor-pointer">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-emerald-700">Selesai</span>
                    <div class="w-7 h-7 rounded-lg bg-emerald-50 group-hover:bg-emerald-100 text-emerald-600 flex items-center justify-center transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="my-2">
                    <div class="text-2xl sm:text-3xl font-extrabold text-emerald-700 font-mono tracking-tight group-hover:text-emerald-800 transition-colors">
                        {{ $stats['resolved'] }}
                    </div>
                </div>
                <div class="text-[11px] text-emerald-600 font-medium flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    Perlu konfirmasi
                </div>
                <div class="absolute inset-x-0 bottom-0 h-0.5 bg-emerald-500 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            </a>

            <!-- Closed (Ditutup) -->
            <a href="{{ route('tickets.index', ['status' => 'closed']) }}"
               class="group relative bg-white p-4 rounded-2xl border border-slate-200/90 hover:border-slate-300 shadow-xs hover:shadow-md hover:-translate-y-1 transition-all duration-200 flex flex-col justify-between overflow-hidden cursor-pointer">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-600">Ditutup</span>
                    <div class="w-7 h-7 rounded-lg bg-slate-100 group-hover:bg-slate-200 text-slate-500 flex items-center justify-center transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                        </svg>
                    </div>
                </div>
                <div class="my-2">
                    <div class="text-2xl sm:text-3xl font-extrabold text-slate-700 font-mono tracking-tight group-hover:text-slate-900 transition-colors">
                        {{ $stats['closed'] }}
                    </div>
                </div>
                <div class="text-[11px] text-slate-400 font-medium flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                    Tuntas diarsipkan
                </div>
                <div class="absolute inset-x-0 bottom-0 h-0.5 bg-slate-400 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            </a>

        </div>
    </div>

    @if($isSupervisorOrAdmin)
        <!-- Supervisor & Management Performance Overview Banner -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 sm:p-6 space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-brand-600"></span>
                        Ringkasan Operasional & Kepatuhan SLA
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Metrik kualitas respon dan penyelesaian tiket helpdesk terpusat</p>
                </div>
                @if(auth()->user()->hasPermission('reports.view'))
                    <a href="{{ route('reports.index') }}"
                       class="inline-flex items-center text-xs font-bold text-brand-600 hover:text-brand-800 transition-colors group">
                        Buka Laporan & Analitik Lengkap
                        <svg class="w-4 h-4 ml-1 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- SLA Compliance -->
                <div class="p-4 rounded-xl bg-gradient-to-br from-blue-50/70 to-indigo-50/40 border border-blue-100 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-blue-700">Kepatuhan SLA Resolusi</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ ($roleData['slaComplianceRate'] ?? 100) >= 90 ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ ($roleData['slaComplianceRate'] ?? 100) >= 90 ? 'Optimal' : 'Perlu Perhatian' }}
                            </span>
                        </div>
                        <div class="text-2xl sm:text-3xl font-extrabold text-blue-950 font-mono mt-2">
                            {{ $roleData['slaComplianceRate'] ?? 100 }}%
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="w-full bg-blue-200/70 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 h-full rounded-full transition-all duration-500" style="width: {{ min(100, max(5, $roleData['slaComplianceRate'] ?? 100)) }}%"></div>
                        </div>
                        <div class="text-[11px] text-blue-600/90 mt-1.5 font-medium">Tiket diselesaikan sesuai target batas waktu</div>
                    </div>
                </div>

                <!-- CSAT Rating -->
                <div class="p-4 rounded-xl bg-gradient-to-br from-amber-50/70 to-orange-50/40 border border-amber-100 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-amber-700">Kepuasan Pengguna (CSAT)</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                {{ $roleData['totalRatings'] ?? 0 }} Responden
                            </span>
                        </div>
                        <div class="flex items-baseline gap-2 mt-2">
                            <span class="text-2xl sm:text-3xl font-extrabold text-amber-950 font-mono">
                                {{ $roleData['avgCsat'] ? number_format($roleData['avgCsat'], 1) : '-' }}
                            </span>
                            <span class="text-xs font-bold text-amber-700">/ 5.0</span>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="flex items-center gap-1 text-amber-400">
                            @php $csatScore = (float) ($roleData['avgCsat'] ?? 0); @endphp
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="w-4 h-4 {{ $i <= round($csatScore) ? 'text-amber-500 fill-amber-400' : 'text-slate-200 fill-slate-100' }}" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            @endfor
                        </div>
                        <div class="text-[11px] text-amber-700/90 mt-1 font-medium">Berdasarkan survei penyelesaian tiket</div>
                    </div>
                </div>

                <!-- Backlog Volume -->
                <div class="p-4 rounded-xl bg-gradient-to-br from-slate-50 to-slate-100/60 border border-slate-200 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-slate-700">Total Backlog Berjalan</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-700">
                                Open & Progress
                            </span>
                        </div>
                        <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 font-mono mt-2">
                            {{ $roleData['backlogCount'] ?? 0 }}
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="text-[11px] text-slate-500 font-medium flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full {{ ($roleData['backlogCount'] ?? 0) > 10 ? 'bg-amber-500' : 'bg-emerald-500' }}"></span>
                            Tiket aktif memerlukan penanganan teknisi
                        </div>
                    </div>
                </div>
            </div>

            <!-- Agent Workloads Overview (if available) -->
            @if(!empty($roleData['agentWorkloads']) && $roleData['agentWorkloads']->isNotEmpty())
                <div class="pt-2 border-t border-slate-100">
                    <div class="text-xs font-bold text-slate-700 mb-2.5 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Beban Kerja Teknisi IT Aktif
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5">
                        @foreach($roleData['agentWorkloads'] as $agent)
                            <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-200/80">
                                <div class="flex items-center space-x-2.5 min-w-0">
                                    <div class="w-7 h-7 rounded-lg bg-brand-100 text-brand-700 font-bold text-xs flex items-center justify-center shrink-0">
                                        {{ strtoupper(substr($agent->name, 0, 1)) }}
                                    </div>
                                    <div class="truncate">
                                        <div class="text-xs font-semibold text-slate-800 truncate">{{ $agent->name }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $agent->email }}</div>
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-xs font-mono font-bold {{ $agent->active_tickets_count > 3 ? 'bg-amber-100 text-amber-800' : 'bg-slate-200/80 text-slate-700' }}">
                                    {{ $agent->active_tickets_count }} tiket
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif

    @if($isAgent)
        <!-- Agent Workbench & Queue Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Unassigned Queue -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                        Antrean Tiket Terbuka (Belum Ditugaskan)
                    </h3>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 font-mono">
                        {{ $roleData['unassignedCount'] ?? 0 }} antrean
                    </span>
                </div>
                <div class="divide-y divide-slate-100 text-xs">
                    @forelse($roleData['unassignedTickets'] ?? [] as $unTicket)
                        <div class="py-3 flex items-center justify-between gap-3 hover:bg-slate-50/50 rounded-lg px-1 transition-colors">
                            <div class="space-y-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-xs font-bold text-brand-700 bg-brand-50 px-1.5 py-0.5 rounded border border-brand-100">
                                        {{ $unTicket->ticket_number }}
                                    </span>
                                    <span class="text-[11px] font-semibold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full truncate">
                                        {{ $unTicket->category->name }}
                                    </span>
                                </div>
                                <a href="{{ route('tickets.show', $unTicket) }}" class="font-bold text-slate-800 hover:text-brand-700 block line-clamp-1 transition-colors">
                                    {{ $unTicket->subject }}
                                </a>
                                <div class="text-[11px] text-slate-400 flex items-center gap-1.5">
                                    <span>{{ $unTicket->requester->name }}</span>
                                    @if($unTicket->location)
                                        <span>•</span>
                                        <span>{{ $unTicket->location->name }}</span>
                                    @endif
                                    <span>•</span>
                                    <span>{{ $unTicket->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                            <a href="{{ route('tickets.show', $unTicket) }}"
                               class="shrink-0 px-3 py-1.5 rounded-lg text-xs font-semibold bg-brand-50 text-brand-700 hover:bg-brand-600 hover:text-white transition-all duration-150 inline-flex items-center gap-1">
                                Ambil
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    @empty
                        <div class="py-8 text-center text-slate-400 text-xs italic">
                            <svg class="w-8 h-8 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Semua tiket baru telah ditugaskan ke teknisi.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Urgent Attention Queue -->
            <div class="bg-white rounded-2xl border border-rose-200/90 shadow-xs p-5 space-y-3">
                <div class="flex items-center justify-between border-b border-rose-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-pulse"></span>
                        Perhatian SLA Mendesak (Tugas Saya)
                    </h3>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 font-mono">
                        {{ count($roleData['urgentTickets'] ?? []) }} mendesak
                    </span>
                </div>
                <div class="divide-y divide-rose-50 text-xs">
                    @forelse($roleData['urgentTickets'] ?? [] as $urgTicket)
                        <div class="py-3 flex items-center justify-between gap-3 hover:bg-rose-50/40 rounded-lg px-1 transition-colors">
                            <div class="space-y-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-xs font-bold text-rose-700 bg-rose-50 px-1.5 py-0.5 rounded border border-rose-200">
                                        {{ $urgTicket->ticket_number }}
                                    </span>
                                    <span class="text-[11px] font-semibold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-full">
                                        Prioritas {{ $urgTicket->priority->value }}
                                    </span>
                                </div>
                                <a href="{{ route('tickets.show', $urgTicket) }}" class="font-bold text-rose-900 hover:text-rose-700 block line-clamp-1 transition-colors">
                                    {{ $urgTicket->subject }}
                                </a>
                                <div class="text-[11px] text-rose-600/90 flex items-center gap-1 font-medium">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Jatuh Tempo: {{ $urgTicket->resolution_due_at?->timezone('Asia/Makassar')->format('d M H:i') }} WITA
                                </div>
                            </div>
                            <a href="{{ route('tickets.show', $urgTicket) }}"
                               class="shrink-0 px-3 py-1.5 rounded-lg text-xs font-bold bg-rose-600 text-white hover:bg-rose-500 shadow-xs hover:shadow-md transition-all duration-150">
                                Tangani Segera
                            </a>
                        </div>
                    @empty
                        <div class="py-8 text-center text-slate-400 text-xs italic">
                            <svg class="w-8 h-8 mx-auto mb-2 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7"/>
                            </svg>
                            Bagus! Tidak ada tiket yang mendekati batas waktu pelanggaran SLA.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    @endif

    @if($isEmployee && !empty($roleData['pendingRatingTickets']) && $roleData['pendingRatingTickets']->isNotEmpty())
        <!-- Employee CSAT Survey Reminder Banner -->
        <div class="p-5 rounded-2xl bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 text-amber-900 shadow-xs space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2.5">
                    <span class="w-7 h-7 rounded-lg bg-amber-200/80 text-amber-700 flex items-center justify-center text-sm font-bold shadow-xs">★</span>
                    <div>
                        <h3 class="text-sm font-bold text-amber-950">Beri Penilaian Layanan IT IKI</h3>
                        <p class="text-xs text-amber-800">Tiket Anda telah selesai ditangani. Mohon luangkan waktu beberapa detik untuk menilai kepuasan Anda.</p>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
                @foreach($roleData['pendingRatingTickets'] as $pTicket)
                    <a href="{{ route('tickets.show', $pTicket) }}"
                       class="p-3.5 bg-white rounded-xl border border-amber-200 hover:border-amber-400 hover:shadow-xs transition-all block text-xs group">
                        <div class="font-mono font-bold text-brand-700 group-hover:underline">{{ $pTicket->ticket_number }}</div>
                        <div class="font-medium text-slate-800 line-clamp-1 mt-0.5">{{ $pTicket->subject }}</div>
                        <div class="text-amber-600 font-bold mt-2 text-[11px] flex items-center gap-1 group-hover:text-amber-700">
                            Beri Nilai Bintang
                            <span class="group-hover:translate-x-0.5 transition-transform">→</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Recent Tickets Card & Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                    </svg>
                    Tiket Terkini
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Daftar tiket terbaru yang relevan dengan wewenang akun Anda</p>
            </div>

            <!-- Quick Filter Bar & Action -->
            <div class="flex flex-wrap items-center gap-2.5">
                <!-- Search Input Filter -->
                <div class="relative">
                    <input type="text"
                           id="ticketQuickSearch"
                           placeholder="Filter cepat di tabel..."
                           class="w-44 sm:w-56 text-xs pl-8 pr-3 py-1.5 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-colors">
                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>

                <a href="{{ route('tickets.index') }}"
                   class="text-xs font-semibold text-brand-600 hover:text-brand-800 inline-flex items-center cursor-pointer transition-colors px-3 py-1.5 rounded-lg hover:bg-brand-50">
                    Lihat Seluruh Tiket
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600" id="recentTicketsTable">
                <thead class="bg-slate-50/90 text-[11px] uppercase tracking-wider text-slate-500 border-b border-slate-100 font-semibold">
                    <tr>
                        <th class="py-3.5 px-4 sm:px-6">No. Tiket</th>
                        <th class="py-3.5 px-4">Subjek & Kategori</th>
                        <th class="py-3.5 px-4">Pelapor</th>
                        <th class="py-3.5 px-4">Teknisi</th>
                        <th class="py-3.5 px-4 text-center">Prioritas</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4">Waktu Dibuat</th>
                        <th class="py-3.5 px-4 sm:px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentTickets as $ticket)
                        <tr class="hover:bg-slate-50/80 transition-colors duration-150 ticket-row"
                            data-search="{{ strtolower($ticket->ticket_number . ' ' . $ticket->subject . ' ' . $ticket->requester->name . ' ' . ($ticket->assignee?->name ?? '')) }}">
                            <!-- No. Tiket -->
                            <td class="py-3.5 px-4 sm:px-6 whitespace-nowrap">
                                <a href="{{ route('tickets.show', $ticket) }}"
                                   class="font-mono text-xs font-bold text-brand-700 hover:text-brand-900 bg-brand-50/70 hover:bg-brand-100/70 px-2 py-1 rounded-md border border-brand-200/60 inline-flex items-center gap-1 transition-colors cursor-pointer">
                                    {{ $ticket->ticket_number }}
                                </a>
                            </td>

                            <!-- Subjek & Kategori -->
                            <td class="py-3.5 px-4 max-w-xs sm:max-w-md">
                                <a href="{{ route('tickets.show', $ticket) }}"
                                   class="font-semibold text-slate-800 hover:text-brand-700 line-clamp-1 cursor-pointer transition-colors">
                                    {{ $ticket->subject }}
                                </a>
                                <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                                    <span>{{ $ticket->category->name }}</span>
                                </div>
                            </td>

                            <!-- Pelapor -->
                            <td class="py-3.5 px-4 whitespace-nowrap text-xs">
                                <div class="flex items-center space-x-2">
                                    <div class="w-6 h-6 rounded-full bg-slate-100 text-slate-700 font-bold text-[10px] flex items-center justify-center shrink-0 border border-slate-200">
                                        {{ strtoupper(substr($ticket->requester->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-medium text-slate-800">{{ $ticket->requester->name }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $ticket->requester->department->name ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- Teknisi -->
                            <td class="py-3.5 px-4 whitespace-nowrap text-xs">
                                @if($ticket->assignee)
                                    <div class="flex items-center space-x-1.5">
                                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                        <span class="text-slate-700 font-medium">{{ $ticket->assignee->name }}</span>
                                    </div>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-500 italic">
                                        Belum ditugaskan
                                    </span>
                                @endif
                            </td>

                            <!-- Prioritas -->
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold priority-badge-{{ $ticket->priority->value }}">
                                    {{ $ticket->priority->label() }}
                                </span>
                            </td>

                            <!-- Status -->
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold status-badge-{{ $ticket->status->value }}">
                                    {{ $ticket->status->label() }}
                                </span>
                            </td>

                            <!-- Waktu Dibuat -->
                            <td class="py-3.5 px-4 whitespace-nowrap text-xs text-slate-500 font-mono"
                                title="{{ $ticket->created_at->timezone('Asia/Makassar')->format('d F Y H:i:s') }} WITA">
                                {{ $ticket->created_at->diffForHumans() }}
                            </td>

                            <!-- Aksi -->
                            <td class="py-3.5 px-4 sm:px-6 text-right whitespace-nowrap">
                                <a href="{{ route('tickets.show', $ticket) }}"
                                   class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-brand-50 hover:text-brand-700 transition-colors duration-150 cursor-pointer group">
                                    Detail
                                    <svg class="w-3.5 h-3.5 ml-1 text-slate-400 group-hover:text-brand-600 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr id="emptyRecentRow">
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div class="text-sm font-semibold text-slate-700">Belum ada tiket terdaftar.</div>
                                <p class="text-xs text-slate-400 mt-1">Buat tiket baru untuk mengajukan keluhan atau permohonan layanan IT.</p>
                                <a href="{{ route('tickets.create') }}" class="inline-flex items-center mt-3 px-3 py-1.5 rounded-lg text-xs font-semibold bg-brand-50 text-brand-700 hover:bg-brand-100 transition-colors cursor-pointer">
                                    + Buat tiket pertama Anda sekarang
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@push('scripts')
<script>
    // Lightweight Quick Table Search
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('ticketQuickSearch');
        if (!searchInput) return;

        searchInput.addEventListener('input', function (e) {
            const query = e.target.value.toLowerCase().trim();
            const rows = document.querySelectorAll('.ticket-row');

            rows.forEach(row => {
                const searchData = row.getAttribute('data-search') || '';
                if (query === '' || searchData.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
</script>
@endpush
@endsection
