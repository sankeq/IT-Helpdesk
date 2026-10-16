@extends('layouts.app')

@section('title', 'Daftar Tiket')

@section('content')
<div class="space-y-6">

    <!-- Header & Action Row -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Daftar Tiket Helpdesk</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-brand-50 text-brand-700 border border-brand-200">
                    {{ $tickets->total() }} Tiket
                </span>
            </div>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Kelola, telusuri, dan perbarui seluruh tiket layanan TI PT Industri Kapal Indonesia (Persero)
            </p>
        </div>
        <div class="flex items-center space-x-3 shrink-0">
            <a href="{{ route('tickets.create') }}"
               class="inline-flex items-center px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold text-xs sm:text-sm shadow-md shadow-emerald-900/20 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-400">
                <svg class="w-4 h-4 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                Buat Tiket Baru
            </a>
        </div>
    </div>

    <!-- Filter & Search Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 space-y-4">
        <form method="GET" action="{{ route('tickets.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                <!-- Search Input with Magnifier SVG -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Cari Kata Kunci</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input type="text"
                               name="search"
                               value="{{ $filters['search'] ?? '' }}"
                               placeholder="No. tiket, subjek, deskripsi..."
                               class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-colors placeholder:text-slate-400">
                    </div>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Status Tiket</label>
                    <select name="status" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white cursor-pointer transition-colors">
                        <option value="">Semua Status</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->value }}" {{ ($filters['status'] ?? '') === $status->value ? 'selected' : '' }}>
                                {{ $status->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Priority Filter -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Prioritas</label>
                    <select name="priority" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white cursor-pointer transition-colors">
                        <option value="">Semua Prioritas</option>
                        @foreach($priorities as $priority)
                            <option value="{{ $priority->value }}" {{ ($filters['priority'] ?? '') === $priority->value ? 'selected' : '' }}>
                                {{ $priority->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Category Filter -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kategori Masalah</label>
                    <select name="category_id" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white cursor-pointer transition-colors">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ ($filters['category_id'] ?? '') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Date From -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Dari Tanggal</label>
                    <input type="date"
                           name="date_from"
                           value="{{ $filters['date_from'] ?? '' }}"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white cursor-pointer transition-colors">
                </div>

                <!-- Date To -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Sampai Tanggal</label>
                    <input type="date"
                           name="date_to"
                           value="{{ $filters['date_to'] ?? '' }}"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white cursor-pointer transition-colors">
                </div>

                <!-- Sort Field -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Urutan</label>
                    <div class="grid grid-cols-2 gap-2">
                        <select name="sort" class="w-full px-2 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white cursor-pointer transition-colors">
                            <option value="created_at" {{ ($filters['sort'] ?? '') === 'created_at' ? 'selected' : '' }}>Waktu</option>
                            <option value="priority" {{ ($filters['sort'] ?? '') === 'priority' ? 'selected' : '' }}>Prioritas</option>
                            <option value="status" {{ ($filters['sort'] ?? '') === 'status' ? 'selected' : '' }}>Status</option>
                            <option value="ticket_number" {{ ($filters['sort'] ?? '') === 'ticket_number' ? 'selected' : '' }}>No Tiket</option>
                        </select>
                        <select name="direction" class="w-full px-2 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white cursor-pointer transition-colors">
                            <option value="desc" {{ ($filters['direction'] ?? '') === 'desc' ? 'selected' : '' }}>Terbaru</option>
                            <option value="asc" {{ ($filters['direction'] ?? '') === 'asc' ? 'selected' : '' }}>Terlama</option>
                        </select>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-end space-x-2">
                    <button type="submit"
                            class="flex-1 py-2 px-3 rounded-xl bg-brand-700 hover:bg-brand-800 text-white font-bold text-xs shadow-xs hover:shadow transition-all duration-200 cursor-pointer focus:outline-none focus:ring-2 focus:ring-brand-500 inline-flex items-center justify-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        Terapkan
                    </button>
                    <a href="{{ route('tickets.index') }}"
                       class="py-2 px-3 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold text-xs transition-colors duration-200 cursor-pointer">
                        Reset
                    </a>
                </div>

            </div>

            <!-- Active Filters Pills -->
            @php
                $activeFilterCount = count(array_filter([
                    $filters['search'] ?? null,
                    $filters['status'] ?? null,
                    $filters['priority'] ?? null,
                    $filters['category_id'] ?? null,
                    $filters['date_from'] ?? null,
                    $filters['date_to'] ?? null,
                ]));
            @endphp
            @if($activeFilterCount > 0)
                <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-slate-100 text-xs">
                    <span class="text-slate-400 font-medium">Filter Aktif:</span>
                    @if(!empty($filters['search']))
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 font-medium">
                            Pencarian: "{{ $filters['search'] }}"
                        </span>
                    @endif
                    @if(!empty($filters['status']))
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 font-medium border border-blue-200">
                            Status: {{ ucfirst($filters['status']) }}
                        </span>
                    @endif
                    @if(!empty($filters['priority']))
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 font-medium border border-amber-200">
                            Prioritas: {{ $filters['priority'] }}
                        </span>
                    @endif
                    <a href="{{ route('tickets.index') }}" class="text-brand-600 hover:underline text-[11px] font-bold ml-1">
                        Hapus Semua Filter
                    </a>
                </div>
            @endif
        </form>
    </div>

    <!-- Tickets Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50/90 text-[11px] uppercase tracking-wider text-slate-500 border-b border-slate-100 font-semibold">
                    <tr>
                        <th class="py-3.5 px-4 sm:px-6">No. Tiket</th>
                        <th class="py-3.5 px-4">Subjek Masalah</th>
                        <th class="py-3.5 px-4">Kategori</th>
                        <th class="py-3.5 px-4">Pelapor</th>
                        <th class="py-3.5 px-4">Teknisi</th>
                        <th class="py-3.5 px-4 text-center">Prioritas</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-center">SLA</th>
                        <th class="py-3.5 px-4">Waktu Buat</th>
                        <th class="py-3.5 px-4 sm:px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($tickets as $ticket)
                        <tr class="hover:bg-slate-50/70 transition-colors duration-150">
                            <!-- No. Tiket -->
                            <td class="py-3.5 px-4 sm:px-6 whitespace-nowrap">
                                <a href="{{ route('tickets.show', $ticket) }}"
                                   class="font-mono text-xs font-bold text-brand-700 hover:text-brand-900 bg-brand-50/70 hover:bg-brand-100/70 px-2 py-1 rounded-md border border-brand-200/60 inline-flex items-center gap-1 transition-colors cursor-pointer">
                                    {{ $ticket->ticket_number }}
                                </a>
                            </td>

                            <!-- Subjek Masalah -->
                            <td class="py-3.5 px-4 max-w-xs sm:max-w-md">
                                <a href="{{ route('tickets.show', $ticket) }}"
                                   class="font-semibold text-slate-800 hover:text-brand-700 line-clamp-1 cursor-pointer transition-colors">
                                    {{ $ticket->subject }}
                                </a>
                            </td>

                            <!-- Kategori -->
                            <td class="py-3.5 px-4 whitespace-nowrap text-xs text-slate-600">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-slate-100 font-medium text-slate-700 text-[11px]">
                                    {{ $ticket->category->name }}
                                </span>
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
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                        <span class="text-slate-700 font-medium">{{ $ticket->assignee->name }}</span>
                                    </div>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-400 italic">
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

                            <!-- SLA -->
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                @php $slaBadge = $ticket->slaStatusBadge(); @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $slaBadge['bg'] }} {{ $slaBadge['text'] }} border {{ $slaBadge['border'] }}">
                                    {{ $slaBadge['label'] }}
                                </span>
                                @if($ticket->resolution_due_at && !$ticket->resolved_at && !$ticket->is_sla_paused)
                                    <div class="text-[10px] font-mono text-slate-400 mt-0.5">
                                        {{ $ticket->resolution_due_at->timezone('Asia/Makassar')->format('d/m H:i') }} WITA
                                    </div>
                                @endif
                            </td>

                            <!-- Waktu Buat -->
                            <td class="py-3.5 px-4 whitespace-nowrap text-xs text-slate-500 font-mono"
                                title="{{ $ticket->created_at->timezone('Asia/Makassar')->format('d/m/Y H:i:s') }} WITA">
                                {{ $ticket->created_at->format('d/m/Y H:i') }}
                            </td>

                            <!-- Aksi -->
                            <td class="py-3.5 px-4 sm:px-6 text-right whitespace-nowrap">
                                <a href="{{ route('tickets.show', $ticket) }}"
                                   class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-brand-50 hover:text-brand-700 transition-colors duration-150 cursor-pointer group">
                                    Lihat Detail
                                    <svg class="w-3.5 h-3.5 ml-1 text-slate-400 group-hover:text-brand-600 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-16 text-center text-slate-400">
                                <div class="w-14 h-14 mx-auto mb-3 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                </div>
                                <div class="text-base font-bold text-slate-700">Tidak ada tiket yang cocok</div>
                                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                                    Coba ubah atau reset kriteria filter pencarian di atas, atau buat tiket bantuan IT yang baru.
                                </p>
                                <a href="{{ route('tickets.create') }}"
                                   class="inline-flex items-center mt-4 px-4 py-2 rounded-xl bg-brand-700 text-white text-xs font-semibold hover:bg-brand-800 cursor-pointer shadow-xs transition-colors">
                                    + Buat Tiket Sekarang
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($tickets->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $tickets->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
