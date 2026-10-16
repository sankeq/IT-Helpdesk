<!-- Audit History Trail Card -->
<div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 space-y-4">
    <h3 class="font-bold uppercase tracking-wider text-slate-700 text-xs flex items-center space-x-1.5">
        <svg class="w-4 h-4 text-brand-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>Riwayat Jejak Audit</span>
    </h3>

    <div class="relative pl-4 border-l-2 border-slate-200 space-y-4 text-xs">
        @forelse($ticket->histories as $history)
            <div class="relative group">
                <!-- Bullet dot -->
                <div class="absolute -left-5.25 top-1 w-2.5 h-2.5 rounded-full bg-brand-600 border-2 border-white"></div>
                <div>
                    <div class="font-semibold text-slate-800">
                        {{ $history->actor?->name ?? 'Sistem' }}
                        <span class="font-normal text-slate-500">• {{ $history->event_type->label() }}</span>
                    </div>
                    @php
                        $formatVal = function($val) {
                            if (is_array($val)) {
                                if (isset($val['approver_name'])) return 'Approver: ' . $val['approver_name'];
                                if (isset($val['decided_by'])) return 'Oleh: ' . $val['decided_by'] . ' (' . ucfirst($val['status'] ?? '') . ')';
                                if (isset($val['status'])) return ucfirst(str_replace('_', ' ', $val['status']));
                                if (isset($val['assignee_name'])) return $val['assignee_name'];
                                if (isset($val['filename'])) return $val['filename'];
                                if (isset($val['priority'])) return $val['priority'];
                                return implode(', ', array_map(fn($k, $v) => is_array($v) ? "$k: ..." : "$k: $v", array_keys($val), $val));
                            }
                            return (string) $val;
                        };
                    @endphp
                    @if($history->old_value || $history->new_value)
                        <div class="text-[11px] text-slate-600 mt-0.5">
                            @if($history->old_value)
                                <span class="line-through text-slate-400">{{ $formatVal($history->old_value) }}</span> &rarr;
                            @endif
                            @if($history->new_value)
                                <span class="font-semibold text-slate-700">{{ $formatVal($history->new_value) }}</span>
                            @endif
                        </div>
                    @endif
                    @if($history->reason)
                        <div class="text-[11px] text-slate-500 italic mt-0.5">"{{ $history->reason }}"</div>
                    @endif
                    <div class="text-[10px] text-slate-400 font-mono mt-0.5">
                        {{ $history->created_at->format('d/m/Y H:i') }}
                    </div>
                </div>
            </div>
        @empty
            <div class="text-slate-400 italic text-xs">Belum ada riwayat tercatat.</div>
        @endforelse
    </div>
</div>
