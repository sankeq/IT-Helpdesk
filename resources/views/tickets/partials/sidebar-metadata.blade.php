<!-- Requester & Assignee Information Card -->
<div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 space-y-4 text-xs">
    <!-- Pelapor -->
    <div>
        <h3 class="font-bold uppercase tracking-wider text-slate-400 text-[11px] mb-2 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            Informasi Pelapor
        </h3>
        <div class="p-3.5 bg-slate-50/80 rounded-xl border border-slate-100 flex items-start gap-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-brand-600 to-brand-800 text-white font-extrabold text-xs flex items-center justify-center shrink-0 shadow-xs">
                {{ strtoupper(substr($ticket->requester->name, 0, 1)) }}
            </div>
            <div class="space-y-0.5 min-w-0 flex-1">
                <div class="font-bold text-slate-900 text-sm truncate">{{ $ticket->requester->name }}</div>
                <div class="text-slate-500 font-mono text-[11px] truncate">{{ $ticket->requester->email }}</div>
                <div class="text-slate-600 text-[11px] pt-1 border-t border-slate-200/60 mt-1 flex flex-col gap-0.5">
                    <div>Departemen: <strong class="text-slate-800">{{ $ticket->requester->department->name ?? '-' }}</strong></div>
                    <div>NIK Karyawan: <strong class="text-slate-800 font-mono">{{ $ticket->requester->employee_number ?? '-' }}</strong></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Teknisi Bertanggung Jawab -->
    <div>
        <h3 class="font-bold uppercase tracking-wider text-slate-400 text-[11px] mb-2 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            Teknisi IT Bertanggung Jawab
        </h3>
        <div class="p-3.5 {{ $ticket->assignee ? 'bg-indigo-50/50 border border-indigo-100' : 'bg-slate-50 border border-slate-100' }} rounded-xl">
            @if($ticket->assignee)
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-600 to-indigo-800 text-white font-extrabold text-xs flex items-center justify-center shrink-0 shadow-xs">
                        {{ strtoupper(substr($ticket->assignee->name, 0, 1)) }}
                    </div>
                    <div class="space-y-0.5 min-w-0 flex-1">
                        <div class="font-bold text-indigo-950 text-sm truncate">{{ $ticket->assignee->name }}</div>
                        <div class="text-indigo-700 font-mono text-[11px] truncate">{{ $ticket->assignee->email }}</div>
                        <div class="inline-flex items-center gap-1 text-[10px] font-semibold text-indigo-800 bg-indigo-100/70 px-2 py-0.5 rounded-md mt-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-600"></span>
                            Teknisi Aktif
                        </div>
                    </div>
                </div>
            @else
                <div class="text-slate-400 italic text-center py-2 flex flex-col items-center gap-1">
                    <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    <span>Belum ditugaskan ke teknisi</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Detail Masalah -->
    <div>
        <h3 class="font-bold uppercase tracking-wider text-slate-400 text-[11px] mb-2 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Parameter Tiket
        </h3>
        <div class="space-y-2 border-t border-slate-100 pt-2 text-slate-600">
            <div class="flex justify-between items-center py-1">
                <span class="text-slate-400">Lokasi Penanganan:</span>
                <span class="font-semibold text-slate-800">{{ $ticket->location?->name ?? '-' }}</span>
            </div>
            @if($ticket->asset)
                <div class="flex justify-between items-center py-1.5 px-2.5 bg-blue-50/70 rounded-xl border border-blue-100">
                    <span class="text-blue-700 font-semibold text-[11px]">Aset TI Terkait:</span>
                    <a href="{{ route('assets.show', $ticket->asset) }}" class="font-mono font-bold text-brand-700 hover:text-brand-900 hover:underline text-[11px] inline-flex items-center gap-1" title="{{ $ticket->asset->name }}">
                        {{ $ticket->asset->asset_tag }}
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                </div>
            @endif
            <div class="flex justify-between items-center py-1 border-t border-slate-50">
                <span class="text-slate-400">Tingkat Dampak:</span>
                <span class="font-semibold text-slate-800">{{ $ticket->impact->label() }}</span>
            </div>
            <div class="flex justify-between items-center py-1 border-t border-slate-50">
                <span class="text-slate-400">Tingkat Urgensi:</span>
                <span class="font-semibold text-slate-800">{{ $ticket->urgency->label() }}</span>
            </div>
            <div class="flex justify-between items-center py-1 border-t border-slate-50">
                <span class="text-slate-400">Kanal Pelaporan:</span>
                <span class="font-semibold text-slate-800 uppercase font-mono text-[11px]">{{ $ticket->source }}</span>
            </div>
        </div>
    </div>
</div>

<!-- SLA Monitoring Card -->
<div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 space-y-4 text-xs">
    <div class="flex items-center justify-between">
        <h3 class="font-bold uppercase tracking-wider text-slate-400 text-[11px] flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            SLA Monitoring
        </h3>
        @php $slaBadge = $ticket->slaStatusBadge(); @endphp
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $slaBadge['bg'] }} {{ $slaBadge['text'] }} border {{ $slaBadge['border'] }}">
            {{ $slaBadge['label'] }}
        </span>
    </div>

    @if($ticket->sla_policy_snapshot)
        <div class="p-3.5 bg-slate-50/80 rounded-xl border border-slate-100 space-y-3">
            <div class="flex items-center justify-between text-[11px]">
                <span class="text-slate-500 font-medium">Kebijakan SLA:</span>
                <span class="font-semibold text-slate-800">{{ $ticket->sla_policy_snapshot['policy_name'] ?? $ticket->slaPolicy?->name ?? 'Standard SLA' }}</span>
            </div>

            <!-- First Response Target -->
            <div class="border-t border-slate-200/60 pt-2.5 space-y-1.5">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500 font-medium">Respon Pertama:</span>
                    @if($ticket->first_responded_at)
                        @if($ticket->isFirstResponseBreached())
                            <span class="text-rose-600 font-bold text-[10px] bg-rose-50 px-2 py-0.5 rounded border border-rose-200">Terlambat</span>
                        @else
                            <span class="text-emerald-700 font-bold text-[10px] bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">Terpenuhi</span>
                        @endif
                    @elseif($ticket->isFirstResponseBreached())
                        <span class="text-rose-600 font-bold text-[10px] bg-rose-50 px-2 py-0.5 rounded border border-rose-200">Breached</span>
                    @else
                        <span class="text-sky-700 font-semibold text-[10px] bg-sky-50 px-2 py-0.5 rounded border border-sky-100">Menunggu Respon</span>
                    @endif
                </div>
                <div class="flex justify-between text-[11px] font-mono text-slate-600">
                    <span class="text-slate-400">Target:</span>
                    <span>{{ $ticket->first_response_due_at ? $ticket->first_response_due_at->timezone('Asia/Makassar')->format('d M, H:i').' WITA' : '-' }}</span>
                </div>
                @if($ticket->first_responded_at)
                    <div class="flex justify-between text-[11px] font-mono text-slate-600">
                        <span class="text-slate-400">Terealisasi:</span>
                        <span>{{ $ticket->first_responded_at->timezone('Asia/Makassar')->format('d M, H:i').' WITA' }}</span>
                    </div>
                @endif
            </div>

            <!-- Resolution Target -->
            <div class="border-t border-slate-200/60 pt-2.5 space-y-1.5">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500 font-medium">Target Resolusi:</span>
                    @if($ticket->resolved_at)
                        @if($ticket->isResolutionBreached())
                            <span class="text-rose-600 font-bold text-[10px] bg-rose-50 px-2 py-0.5 rounded border border-rose-200">Terlambat</span>
                        @else
                            <span class="text-emerald-700 font-bold text-[10px] bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">Terpenuhi</span>
                        @endif
                    @elseif($ticket->isResolutionBreached())
                        <span class="text-rose-600 font-bold text-[10px] bg-rose-50 px-2 py-0.5 rounded border border-rose-200">Breached</span>
                    @elseif($ticket->is_sla_paused)
                        <span class="text-amber-700 font-bold text-[10px] bg-amber-50 px-2 py-0.5 rounded border border-amber-200">Dijeda (Pending)</span>
                    @else
                        <span class="text-sky-700 font-semibold text-[10px] bg-sky-50 px-2 py-0.5 rounded border border-sky-100">Berjalan Aktif</span>
                    @endif
                </div>
                <div class="flex justify-between text-[11px] font-mono text-slate-600">
                    <span class="text-slate-400">Batas Waktu:</span>
                    <span class="font-semibold {{ $ticket->isResolutionBreached() ? 'text-rose-600' : 'text-slate-800' }}">
                        {{ $ticket->resolution_due_at ? $ticket->resolution_due_at->timezone('Asia/Makassar')->format('d M Y, H:i').' WITA' : '-' }}
                    </span>
                </div>
                @if($ticket->resolved_at)
                    <div class="flex justify-between text-[11px] font-mono text-slate-600">
                        <span class="text-slate-400">Selesai:</span>
                        <span>{{ $ticket->resolved_at->timezone('Asia/Makassar')->format('d M Y, H:i').' WITA' }}</span>
                    </div>
                @endif
                @if($ticket->total_paused_seconds > 0)
                    <div class="flex justify-between text-[11px] font-mono text-amber-800 bg-amber-50/80 p-2 rounded-lg border border-amber-200/80 mt-1.5">
                        <span>Akumulasi Jeda:</span>
                        <span class="font-bold">{{ round($ticket->total_paused_seconds / 60) }} menit</span>
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="p-3.5 bg-slate-50 text-slate-400 italic text-center rounded-xl border border-slate-100">
            Tidak ada kebijakan SLA aktif untuk tiket ini.
        </div>
    @endif
</div>
