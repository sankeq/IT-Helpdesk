@php
    $stages = [
        'open' => ['step' => 1, 'title' => 'Terbuka', 'desc' => 'Tiket dibuat'],
        'assigned' => ['step' => 2, 'title' => 'Ditugaskan', 'desc' => 'Teknisi ditunjuk'],
        'in_progress' => ['step' => 3, 'title' => 'Dikerjakan', 'desc' => 'Proses perbaikan'],
        'resolved' => ['step' => 4, 'title' => 'Selesai', 'desc' => 'Menunggu verifikasi'],
        'closed' => ['step' => 5, 'title' => 'Ditutup', 'desc' => 'Tuntas diarsipkan'],
    ];

    $currentIdx = match($ticket->status->value) {
        'open' => 0,
        'assigned' => 1,
        'in_progress', 'pending' => 2,
        'resolved' => 3,
        'closed' => 4,
        'cancelled' => -1,
    };
@endphp

@if($ticket->status->value === 'cancelled')
    <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-center text-xs font-semibold flex items-center justify-center space-x-2.5 shadow-xs">
        <div class="w-6 h-6 rounded-full bg-rose-100 flex items-center justify-center text-rose-600 shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </div>
        <span>Tiket ini telah dibatalkan dari alur kerja operasional.</span>
    </div>
@else
    <div class="relative">
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 sm:gap-2">
            @foreach($stages as $key => $stage)
                @php
                    $i = array_search($key, array_keys($stages));
                    $isPassed = $i < $currentIdx;
                    $isCurrent = $i === $currentIdx;
                    $isPendingState = $isCurrent && $ticket->status->value === 'pending';
                @endphp
                <div class="relative flex flex-col items-center text-center p-3 sm:p-3.5 rounded-2xl border transition-all duration-300 {{ $isCurrent ? ($isPendingState ? 'bg-orange-50/70 border-orange-300 ring-2 ring-orange-200 shadow-xs' : 'bg-brand-50/70 border-brand-300 ring-2 ring-brand-100 shadow-xs') : ($isPassed ? 'bg-emerald-50/40 border-emerald-200 text-slate-800' : 'bg-slate-50/60 border-slate-200 text-slate-400') }}">
                    <!-- Step Badge Circle -->
                    <div class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-xs mb-2 transition-transform {{ $isCurrent ? ($isPendingState ? 'bg-orange-500 text-white shadow-xs scale-105' : 'bg-brand-600 text-white shadow-xs scale-105') : ($isPassed ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-500') }}">
                        @if($isPassed)
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                        @elseif($isPendingState)
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        @else
                            <span>{{ $stage['step'] }}</span>
                        @endif
                    </div>

                    <!-- Step Title -->
                    <div class="text-xs font-bold leading-tight {{ $isCurrent ? ($isPendingState ? 'text-orange-950' : 'text-brand-950') : ($isPassed ? 'text-slate-800' : 'text-slate-400') }}">
                        {{ $stage['title'] }}
                        @if($isPendingState)
                            <span class="block text-[10px] font-semibold text-orange-600">(Pending)</span>
                        @endif
                    </div>

                    <!-- Step Subtitle / Description -->
                    <div class="text-[10px] text-slate-400 hidden sm:block mt-0.5">
                        {{ $stage['desc'] }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
