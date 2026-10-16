@extends('layouts.app')

@section('title', 'Rincian Aset TI — ' . $asset->asset_tag)

@section('content')
<div class="space-y-6">

    <!-- Breadcrumb & Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <nav class="flex items-center space-x-2 text-xs text-slate-500 mb-1.5" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-brand-700 transition-colors">Dashboard</a>
                <span>/</span>
                <a href="{{ route('assets.index') }}" class="hover:text-brand-700 transition-colors">Inventaris Aset TI</a>
                <span>/</span>
                <span class="font-semibold text-slate-800 font-mono">{{ $asset->asset_tag }}</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-3">
                <span>{{ $asset->name }}</span>
                <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-mono font-bold
                    {{ $asset->status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : '' }}
                    {{ $asset->status === 'maintenance' ? 'bg-amber-50 text-amber-700 border border-amber-200' : '' }}
                    {{ $asset->status === 'retired' ? 'bg-slate-100 text-slate-700 border border-slate-200' : '' }}
                    {{ $asset->status === 'disposed' ? 'bg-rose-50 text-rose-700 border border-rose-200' : '' }}">
                    @if($asset->status === 'active')
                        <span class="w-2 h-2 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                    @elseif($asset->status === 'maintenance')
                        <span class="w-2 h-2 rounded-full bg-amber-500 mr-1.5 animate-pulse"></span>
                    @endif
                    {{ ucfirst($asset->status) }}
                </span>
            </h1>
            <p class="text-xs text-slate-500 mt-1 font-mono">
                Tag Inventaris: <span class="font-bold text-slate-700">{{ $asset->asset_tag }}</span> • Serial Manufaktur: <span class="font-bold text-slate-700">{{ $asset->serial_number ?? 'N/A' }}</span>
            </p>
        </div>

        <div class="flex items-center space-x-2">
            <a href="{{ route('assets.index') }}"
               class="inline-flex items-center px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-50 hover:border-slate-300 transition-all shadow-2xs">
                <svg class="w-3.5 h-3.5 mr-1.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke Inventaris
            </a>

            @if(auth()->user()->hasPermission('assets.manage'))
                <a href="{{ route('assets.edit', $asset) }}"
                   class="inline-flex items-center px-4 py-2 rounded-xl bg-brand-700 hover:bg-brand-800 text-white text-xs font-bold transition-all shadow-xs">
                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Ubah Data Aset
                </a>
            @endif
        </div>
    </div>

    <!-- Asset Specifications & History Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left Column: Hardware Card & History Table -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Technical Specifications Card -->
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-6 space-y-5">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-brand-50 text-brand-700 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        Spesifikasi Teknis &amp; Detail Perangkat
                    </h2>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider
                        {{ $asset->category === 'hardware' ? 'bg-blue-50 text-blue-700 border border-blue-200' : '' }}
                        {{ $asset->category === 'network' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : '' }}
                        {{ $asset->category === 'peripheral' ? 'bg-amber-50 text-amber-700 border border-amber-200' : '' }}
                        {{ $asset->category === 'software' ? 'bg-purple-50 text-purple-700 border border-purple-200' : '' }}">
                        {{ $asset->category }}
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div class="p-3 rounded-xl bg-slate-50/70 border border-slate-100">
                        <span class="text-slate-400 block font-medium text-[11px]">Nomor Tag Inventaris</span>
                        <span class="font-mono font-extrabold text-brand-800 text-sm mt-0.5 block">{{ $asset->asset_tag }}</span>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50/70 border border-slate-100">
                        <span class="text-slate-400 block font-medium text-[11px]">Model / Spesifikasi</span>
                        <span class="font-bold text-slate-800 text-sm mt-0.5 block font-mono">{{ $asset->model ?? '-' }}</span>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50/70 border border-slate-100">
                        <span class="text-slate-400 block font-medium text-[11px]">Nomor Seri Manufaktur (S/N)</span>
                        <span class="font-mono font-bold text-slate-800 text-xs mt-0.5 block">{{ $asset->serial_number ?? '-' }}</span>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50/70 border border-slate-100">
                        <span class="text-slate-400 block font-medium text-[11px]">Tanggal Pengadaan</span>
                        <span class="font-bold text-slate-800 text-xs mt-0.5 block">
                            {{ $asset->purchase_date ? $asset->purchase_date->format('d F Y') : '-' }}
                        </span>
                    </div>

                    <div class="sm:col-span-2 p-3 rounded-xl bg-slate-50/70 border border-slate-100 flex items-center justify-between">
                        <div>
                            <span class="text-slate-400 block font-medium text-[11px]">Batas Waktu Garansi Manufaktur</span>
                            <span class="font-bold text-slate-800 text-xs mt-0.5 block font-mono">
                                {{ $asset->warranty_expiry ? $asset->warranty_expiry->format('d F Y') : 'Tidak Tercatat' }}
                            </span>
                        </div>
                        @if($asset->warranty_expiry)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold {{ $asset->isUnderWarranty() ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-200 text-slate-600' }}">
                                @if($asset->isUnderWarranty())
                                    <svg class="w-3 h-3 mr-1 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    Bergaransi
                                @else
                                    Garansi Berakhir
                                @endif
                            </span>
                        @endif
                    </div>
                </div>

                @if($asset->notes)
                    <div class="border-t border-slate-100 pt-3">
                        <span class="text-slate-500 block text-xs font-semibold mb-1">Catatan Khusus &amp; Riwayat Fisik</span>
                        <p class="text-xs text-slate-700 bg-slate-50 p-3.5 rounded-xl border border-slate-100 leading-relaxed font-sans">
                            {{ $asset->notes }}
                        </p>
                    </div>
                @endif
            </div>

            <!-- Incident & Maintenance History Table -->
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Riwayat Tiket &amp; Servis Terkait
                        </h2>
                        <p class="text-[11px] text-slate-400 mt-0.5">Daftar laporan gangguan dan riwayat servis pemeliharaan perangkat ini</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold font-mono bg-blue-50 text-blue-700 border border-blue-200">
                        {{ $tickets->total() }} Insiden
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50/80 text-[11px] uppercase tracking-wider text-slate-500 border-b border-slate-100 font-bold">
                            <tr>
                                <th class="py-3 px-4">No. Tiket</th>
                                <th class="py-3 px-4">Subjek Masalah</th>
                                <th class="py-3 px-4">Pelapor</th>
                                <th class="py-3 px-4">Teknisi</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4">Waktu Dibuat</th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($tickets as $ticket)
                                <tr class="hover:bg-slate-50/75 transition-colors">
                                    <td class="py-3 px-4 font-mono font-bold text-brand-700 whitespace-nowrap">
                                        <a href="{{ route('tickets.show', $ticket) }}" class="hover:underline">
                                            {{ $ticket->ticket_number }}
                                        </a>
                                    </td>
                                    <td class="py-3 px-4 max-w-xs truncate font-medium text-slate-800">
                                        {{ $ticket->subject }}
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <div class="font-semibold text-slate-800">{{ $ticket->requester->name }}</div>
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        @if($ticket->assignee)
                                            <span class="font-medium text-slate-700">{{ $ticket->assignee->name }}</span>
                                        @else
                                            <span class="text-slate-400 italic">Belum ditugaskan</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-center whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold status-badge-{{ $ticket->status->value }}">
                                            {{ $ticket->status->label() }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap font-mono text-[11px] text-slate-500">
                                        {{ $ticket->created_at->setTimezone('Asia/Makassar')->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="py-3 px-4 text-right whitespace-nowrap">
                                        <a href="{{ route('tickets.show', $ticket) }}" class="font-bold text-brand-600 hover:text-brand-800">
                                            Buka Tiket &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-10 text-center text-slate-400 italic">
                                        Belum ada riwayat insiden atau tiket servis yang dilaporkan untuk perangkat ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($tickets->hasPages())
                    <div class="p-3 border-t border-slate-100 bg-slate-50/50">
                        {{ $tickets->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Side: Allocation & Location Information -->
        <div class="space-y-6">

            <!-- Assigned User Card -->
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-5 space-y-4">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-2.5 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Pengguna / Penanggung Jawab
                </h3>

                @if($asset->assignedTo)
                    <div class="flex items-center space-x-3">
                        <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-brand-700 to-blue-600 text-white font-bold flex items-center justify-center text-sm font-mono shadow-xs">
                            {{ strtoupper(substr($asset->assignedTo->name, 0, 1)) }}
                        </div>
                        <div>
                            <div class="font-extrabold text-slate-800 text-sm leading-tight">{{ $asset->assignedTo->name }}</div>
                            <div class="text-[11px] text-slate-400 font-mono mt-0.5">NIP: {{ $asset->assignedTo->employee_number ?? '-' }}</div>
                            <div class="text-[11px] text-brand-600 font-medium mt-0.5">{{ $asset->assignedTo->email }}</div>
                        </div>
                    </div>
                @else
                    <div class="p-4 rounded-xl bg-slate-50 border border-dashed border-slate-200 text-center text-xs text-slate-400">
                        Perangkat ini belum dialokasikan ke pengguna individu (Aset Bersama / Pool Perangkat).
                    </div>
                @endif
            </div>

            <!-- Location & Department Card -->
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-5 space-y-4">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-2.5 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Penempatan Lokasi Galangan
                </h3>

                <div class="space-y-3.5 text-xs">
                    <div>
                        <span class="text-slate-400 block font-medium text-[11px]">Unit / Area Galangan</span>
                        <span class="font-bold text-slate-800 text-sm block mt-0.5">
                            {{ $asset->location?->name ?? 'Semua Lokasi Operasional' }}
                        </span>
                        <span class="text-[11px] text-slate-400 block font-mono mt-0.5">Kode Area: {{ $asset->location?->code ?? '-' }}</span>
                    </div>

                    <div class="pt-2 border-t border-slate-100">
                        <span class="text-slate-400 block font-medium text-[11px]">Departemen Pemilik</span>
                        <span class="font-semibold text-slate-800 block mt-0.5">
                            {{ $asset->department?->name ?? '-' }}
                        </span>
                    </div>
                </div>
            </div>

            @if(auth()->user()->hasPermission('assets.manage'))
                <div class="p-4 rounded-2xl bg-rose-50/50 border border-rose-100 text-center">
                    <form action="{{ route('assets.destroy', $asset) }}" method="POST"
                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus atau memurnakan aset ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs text-rose-600 hover:text-rose-800 font-bold transition-colors cursor-pointer">
                            Hapus / Purnakan Aset Ini
                        </button>
                    </form>
                </div>
            @endif

        </div>

    </div>

</div>
@endsection
