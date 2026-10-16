@extends('layouts.app')

@section('title', 'Inventaris Aset TI — IKI IT Helpdesk')

@section('content')
<div class="space-y-6">

    <!-- Top Header Banner with Maritime Depth -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-brand-950 via-brand-900 to-navy-900 p-6 sm:p-8 text-white shadow-xl border border-brand-800/60">
        <!-- Maritime Blueprint Grid Pattern Overlay -->
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_1px_1px,rgba(255,255,255,0.08)_1px,transparent_0)] bg-[size:24px_24px] pointer-events-none opacity-60"></div>
        <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-5">
            <div>
                <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-200 text-xs font-semibold uppercase tracking-wider mb-2.5 backdrop-blur-xs">
                    <svg class="w-3.5 h-3.5 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <span>Manajemen Infrastruktur &amp; Perangkat Galangan</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Inventaris Aset Teknologi Informasi
                </h1>
                <p class="text-blue-100/90 text-xs sm:text-sm mt-1.5 max-w-2xl leading-relaxed">
                    Pusat registrasi resmi perangkat keras galangan, workstation CAD/CAM, infrastruktur jaringan, serta lisensi operasional PT Industri Kapal Indonesia (Persero).
                </p>
            </div>

            @if(auth()->user()->hasPermission('assets.manage'))
                <div class="shrink-0 flex items-center gap-2">
                    <a href="{{ route('assets.create') }}"
                       class="inline-flex items-center px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs sm:text-sm shadow-md hover:shadow-emerald-600/30 transition-all duration-200 cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-400">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                        Daftarkan Aset Baru
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Total Assets -->
        <a href="{{ route('assets.index') }}"
           class="group relative bg-white p-5 rounded-2xl border border-slate-200/90 shadow-xs hover:shadow-md hover:border-brand-300 transition-all cursor-pointer">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500">Total Aset Terdaftar</span>
                <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center group-hover:bg-brand-50 group-hover:text-brand-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
            </div>
            <div class="text-3xl font-extrabold text-slate-800 mt-2 font-mono tracking-tight">{{ $totalCount }}</div>
            <div class="flex items-center justify-between mt-2 pt-2 border-t border-slate-100 text-[11px] text-slate-400">
                <span>Seluruh unit galangan &amp; kantor</span>
                <span class="text-brand-600 font-semibold group-hover:underline">Semua &rarr;</span>
            </div>
        </a>

        <!-- Active Assets -->
        <a href="{{ route('assets.index', ['status' => 'active']) }}"
           class="group relative bg-white p-5 rounded-2xl border border-emerald-200/90 shadow-xs hover:shadow-md hover:border-emerald-400 transition-all cursor-pointer">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-emerald-700">Aset Berstatus Aktif</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:bg-emerald-100 transition-colors">
                    <span class="relative flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                    </span>
                </div>
            </div>
            <div class="text-3xl font-extrabold text-emerald-800 mt-2 font-mono tracking-tight">{{ $activeCount }}</div>
            <div class="flex items-center justify-between mt-2 pt-2 border-t border-emerald-50 text-[11px] text-emerald-600">
                <span>{{ $totalCount > 0 ? round(($activeCount / $totalCount) * 100) : 0 }}% siap pakai &amp; beroperasi</span>
                <span class="font-semibold group-hover:underline">Filter Aktif &rarr;</span>
            </div>
        </a>

        <!-- In Maintenance Assets -->
        <a href="{{ route('assets.index', ['status' => 'maintenance']) }}"
           class="group relative bg-white p-5 rounded-2xl border border-amber-200/90 shadow-xs hover:shadow-md hover:border-amber-400 transition-all cursor-pointer">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-amber-700">Dalam Pemeliharaan / Servis</span>
                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center group-hover:bg-amber-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
            </div>
            <div class="text-3xl font-extrabold text-amber-800 mt-2 font-mono tracking-tight">{{ $maintenanceCount }}</div>
            <div class="flex items-center justify-between mt-2 pt-2 border-t border-amber-50 text-[11px] text-amber-600">
                <span>Penanganan teknisi &amp; perbaikan</span>
                <span class="font-semibold group-hover:underline">Filter Servis &rarr;</span>
            </div>
        </a>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-5">
        <form method="GET" action="{{ route('assets.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
            <div class="lg:col-span-2">
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Cari Tag, Nama, atau Seri</label>
                <div class="relative">
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Contoh: IKI-AST, Dell, SN-..."
                           class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white placeholder-slate-400">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Kategori</label>
                <select name="category" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white">
                    <option value="">Semua Kategori</option>
                    <option value="hardware" {{ request('category') === 'hardware' ? 'selected' : '' }}>Hardware / Komputer</option>
                    <option value="peripheral" {{ request('category') === 'peripheral' ? 'selected' : '' }}>Periferal &amp; Printer</option>
                    <option value="network" {{ request('category') === 'network' ? 'selected' : '' }}>Network / Jaringan</option>
                    <option value="software" {{ request('category') === 'software' ? 'selected' : '' }}>Software / Lisensi</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Status Aset</label>
                <select name="status" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="maintenance" {{ request('status') === 'maintenance' ? 'selected' : '' }}>Dalam Perawatan</option>
                    <option value="retired" {{ request('status') === 'retired' ? 'selected' : '' }}>Purna Pakai</option>
                    <option value="disposed" {{ request('status') === 'disposed' ? 'selected' : '' }}>Dihapuskan</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Lokasi Galangan</label>
                <select name="location_id" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white">
                    <option value="">Semua Lokasi</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>
                            {{ $loc->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit"
                        class="flex-1 inline-flex items-center justify-center px-4 py-2 rounded-xl bg-brand-700 hover:bg-brand-800 text-white font-bold text-xs shadow-xs transition-colors cursor-pointer">
                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    Filter
                </button>
                @if(request()->hasAny(['search', 'category', 'status', 'location_id', 'department_id']))
                    <a href="{{ route('assets.index') }}"
                       class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-xs transition-colors cursor-pointer"
                       title="Hapus Filter">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Assets Data Table -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
        <div class="p-4 sm:px-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div class="flex items-center space-x-2">
                <span class="w-2.5 h-2.5 rounded-full bg-brand-600"></span>
                <h3 class="font-bold text-xs sm:text-sm text-slate-800 uppercase tracking-wider">
                    Daftar Inventaris ({{ $assets->total() }} Perangkat)
                </h3>
            </div>
            <span class="text-[11px] text-slate-400 font-mono">Halaman {{ $assets->currentPage() }} dari {{ $assets->lastPage() }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50/80 text-[11px] uppercase tracking-wider text-slate-500 border-b border-slate-100 font-bold">
                    <tr>
                        <th class="py-3.5 px-4 sm:px-6">Tag Aset</th>
                        <th class="py-3.5 px-4">Nama Perangkat &amp; Model</th>
                        <th class="py-3.5 px-4">Kategori</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4">Lokasi &amp; Departemen</th>
                        <th class="py-3.5 px-4">Pengguna Ditugaskan</th>
                        <th class="py-3.5 px-4 text-center">Riwayat Tiket</th>
                        <th class="py-3.5 px-4 sm:px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($assets as $asset)
                        <tr class="hover:bg-slate-50/75 transition-colors group">
                            <!-- Asset Tag -->
                            <td class="py-3.5 px-4 sm:px-6 font-mono font-bold text-brand-700 whitespace-nowrap">
                                <a href="{{ route('assets.show', $asset) }}" class="inline-flex items-center space-x-1.5 hover:text-brand-900 group-hover:underline">
                                    <svg class="w-3.5 h-3.5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                    </svg>
                                    <span>{{ $asset->asset_tag }}</span>
                                </a>
                            </td>

                            <!-- Name & Model -->
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900">{{ $asset->name }}</div>
                                <div class="text-[11px] text-slate-400 font-mono mt-0.5">{{ $asset->model ?? '-' }}</div>
                            </td>

                            <!-- Category Badge -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider
                                    {{ $asset->category === 'hardware' ? 'bg-blue-50 text-blue-700 border border-blue-200' : '' }}
                                    {{ $asset->category === 'network' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : '' }}
                                    {{ $asset->category === 'peripheral' ? 'bg-amber-50 text-amber-700 border border-amber-200' : '' }}
                                    {{ $asset->category === 'software' ? 'bg-purple-50 text-purple-700 border border-purple-200' : '' }}">
                                    {{ $asset->category }}
                                </span>
                            </td>

                            <!-- Status Pill -->
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold
                                    {{ $asset->status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : '' }}
                                    {{ $asset->status === 'maintenance' ? 'bg-amber-50 text-amber-700 border border-amber-200' : '' }}
                                    {{ $asset->status === 'retired' ? 'bg-slate-100 text-slate-600 border border-slate-200' : '' }}
                                    {{ $asset->status === 'disposed' ? 'bg-rose-50 text-rose-700 border border-rose-200' : '' }}">
                                    @if($asset->status === 'active')
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                    @elseif($asset->status === 'maintenance')
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5"></span>
                                    @endif
                                    {{ ucfirst($asset->status) }}
                                </span>
                            </td>

                            <!-- Location & Dept -->
                            <td class="py-3.5 px-4">
                                <div class="font-medium text-slate-800">{{ $asset->location?->name ?? 'Semua Lokasi' }}</div>
                                <div class="text-[11px] text-slate-400">{{ $asset->department?->name ?? '-' }}</div>
                            </td>

                            <!-- Assigned User -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($asset->assignedTo)
                                    <div class="flex items-center space-x-2">
                                        <div class="w-6 h-6 rounded-full bg-slate-200 text-slate-700 font-bold text-[10px] flex items-center justify-center shrink-0">
                                            {{ strtoupper(substr($asset->assignedTo->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="font-semibold text-slate-800">{{ $asset->assignedTo->name }}</div>
                                            <div class="text-[10px] text-slate-400 font-mono">{{ $asset->assignedTo->employee_number ?? '-' }}</div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-slate-400 italic text-[11px]">Belum dialokasikan</span>
                                @endif
                            </td>

                            <!-- Ticket History Count -->
                            <td class="py-3.5 px-4 text-center font-mono">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold {{ $asset->tickets_count > 0 ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $asset->tickets_count }} tiket
                                </span>
                            </td>

                            <!-- Action Buttons -->
                            <td class="py-3.5 px-4 sm:px-6 text-right whitespace-nowrap space-x-1">
                                <a href="{{ route('assets.show', $asset) }}"
                                   class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-brand-50 hover:text-brand-700 transition-colors">
                                    Detail
                                </a>
                                @if(auth()->user()->hasPermission('assets.manage'))
                                    <a href="{{ route('assets.edit', $asset) }}"
                                       class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors">
                                        Edit
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-16 text-center text-slate-400">
                                <div class="max-w-sm mx-auto space-y-3">
                                    <div class="w-12 h-12 mx-auto rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <div class="font-bold text-slate-700 text-sm">Tidak Ada Aset TI Ditemukan</div>
                                    <p class="text-xs text-slate-400">Tidak ada data aset yang cocok dengan filter atau kata kunci saat ini.</p>
                                    @if(request()->hasAny(['search', 'category', 'status', 'location_id']))
                                        <a href="{{ route('assets.index') }}" class="inline-block text-xs font-bold text-brand-600 hover:underline">Reset Semua Filter</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($assets->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $assets->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
