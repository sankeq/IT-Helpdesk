@extends('layouts.app')

@section('title', 'Tiket ' . $ticket->ticket_number)

@section('content')
<div class="space-y-6">

    <!-- Breadcrumb & Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <nav class="flex items-center space-x-2 text-xs text-slate-500 font-medium">
            <a href="{{ route('dashboard') }}" class="hover:text-brand-700 flex items-center gap-1 cursor-pointer transition-colors">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Dashboard
            </a>
            <span class="text-slate-300">/</span>
            <a href="{{ route('tickets.index') }}" class="hover:text-brand-700 cursor-pointer transition-colors">Daftar Tiket</a>
            <span class="text-slate-300">/</span>
            <span class="font-mono font-bold text-brand-700 bg-brand-50 px-2 py-0.5 rounded border border-brand-200">
                {{ $ticket->ticket_number }}
            </span>
        </nav>
        <div class="flex items-center space-x-2">
            <a href="{{ route('tickets.index') }}"
               class="px-3.5 py-1.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors cursor-pointer inline-flex items-center gap-1.5 shadow-2xs">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke Daftar
            </a>
        </div>
    </div>

    <!-- Ticket Header Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-6">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5">
            <div class="space-y-2.5">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="font-mono text-xs font-extrabold px-2.5 py-1 rounded-lg bg-brand-50 text-brand-800 border border-brand-200">
                        {{ $ticket->ticket_number }}
                    </span>
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold status-badge-{{ $ticket->status->value }}">
                        ● {{ $ticket->status->label() }}
                    </span>
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold priority-badge-{{ $ticket->priority->value }}">
                        Prioritas {{ $ticket->priority->label() }}
                    </span>
                    <span class="text-xs text-slate-400 font-mono flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Dibuat {{ $ticket->created_at->timezone('Asia/Makassar')->format('d M Y, H:i') }} WITA ({{ $ticket->created_at->diffForHumans() }})
                    </span>
                </div>

                <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 leading-snug tracking-tight">
                    {{ $ticket->subject }}
                </h1>

                <div class="text-xs text-slate-500 flex items-center gap-1.5">
                    <span class="text-slate-400">Kategori:</span>
                    <span class="inline-flex items-center gap-1 font-semibold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-md">
                        @if($ticket->category->parent)
                            <span>{{ $ticket->category->parent->name }}</span>
                            <span class="text-slate-400">&gt;</span>
                        @endif
                        <span>{{ $ticket->category->name }}</span>
                    </span>
                </div>
            </div>

            <!-- Action Toolbar (Workflow Transitions & Assignment) -->
            <div class="flex flex-wrap items-center gap-2 pt-3 lg:pt-0 border-t lg:border-t-0 border-slate-100">
                @if($canAssign && $agents)
                    <button type="button"
                            onclick="openModal('assign-modal')"
                            class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-xs hover:shadow transition-all duration-200 cursor-pointer flex items-center space-x-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                        <span>{{ $ticket->assignee_id ? 'Tugaskan Ulang' : 'Tugaskan Teknisi' }}</span>
                    </button>
                @endif

                @foreach($availableTransitions as $targetStatus)
                    @if($targetStatus->value === 'pending')
                        <button type="button"
                                onclick="openModal('pending-modal')"
                                class="px-3.5 py-2 rounded-xl bg-orange-600 hover:bg-orange-700 text-white font-bold text-xs shadow-xs hover:shadow transition-all duration-200 cursor-pointer flex items-center space-x-1.5 focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Tandai Pending</span>
                        </button>
                    @elseif($targetStatus->value === 'resolved')
                        <button type="button"
                                onclick="openModal('resolve-modal')"
                                class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs hover:shadow transition-all duration-200 cursor-pointer flex items-center space-x-1.5 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Selesaikan Tiket</span>
                        </button>
                    @elseif($targetStatus->value === 'in_progress')
                        <form action="{{ route('tickets.transition', $ticket) }}" method="POST" class="inline">
                            @csrf
                            <input type="hidden" name="status" value="in_progress">
                            <button type="submit"
                                    class="px-3.5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-xs hover:shadow transition-all duration-200 cursor-pointer flex items-center space-x-1.5 focus:outline-none focus:ring-2 focus:ring-amber-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>{{ $ticket->status->value === 'pending' ? 'Lanjutkan Pengerjaan' : 'Mulai Kerjakan' }}</span>
                            </button>
                        </form>
                    @elseif($targetStatus->value === 'closed')
                        <button type="button"
                                onclick="openModal('close-modal')"
                                class="px-3.5 py-2 rounded-xl bg-slate-700 hover:bg-slate-800 text-white font-bold text-xs shadow-xs hover:shadow transition-all duration-200 cursor-pointer flex items-center space-x-1.5 focus:outline-none focus:ring-2 focus:ring-slate-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                            </svg>
                            <span>Tutup Tiket</span>
                        </button>
                    @elseif($targetStatus->value === 'cancelled')
                        <button type="button"
                                onclick="openModal('cancel-modal')"
                                class="px-3.5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-xs hover:shadow transition-all duration-200 cursor-pointer flex items-center space-x-1.5 focus:outline-none focus:ring-2 focus:ring-rose-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Batalkan Tiket</span>
                        </button>
                    @endif
                @endforeach
            </div>
        </div>

        <!-- Visual Lifecycle Stepper Partial -->
        <div class="pt-6 border-t border-slate-100">
            @include('tickets.partials.stepper')
        </div>
    </div>

    <!-- Main Grid Layout (2 Columns) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left Column: Details, Attachments, Comments (2 Cols Span) -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Problem Description Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-4">
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-700 flex items-center space-x-2">
                    <svg class="w-4 h-4 text-brand-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Deskripsi Permasalahan</span>
                </h2>
                <div class="prose max-w-none text-sm text-slate-800 whitespace-pre-line bg-slate-50/80 p-4 sm:p-5 rounded-xl border border-slate-200/80 leading-relaxed font-sans">
                    {{ $ticket->description }}
                </div>

                <!-- Approval Status Card if Ticket Has Approvals -->
                @if($latestApproval)
                    <div class="p-4 sm:p-5 rounded-xl border {{ $latestApproval->isPending() ? 'bg-amber-50/80 border-amber-300 text-amber-950' : ($latestApproval->isApproved() ? 'bg-emerald-50/80 border-emerald-300 text-emerald-950' : 'bg-rose-50/80 border-rose-300 text-rose-950') }} space-y-3">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div class="flex items-center space-x-2">
                                <span class="w-2.5 h-2.5 rounded-full {{ $latestApproval->isPending() ? 'bg-amber-500 animate-pulse' : ($latestApproval->isApproved() ? 'bg-emerald-600' : 'bg-rose-600') }}"></span>
                                <span class="text-xs font-bold uppercase tracking-wider">Status Persetujuan Akses / Layanan</span>
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $latestApproval->status->badgeClasses() }}">
                                    {{ $latestApproval->status->label() }}
                                </span>
                            </div>
                            <span class="text-[11px] text-slate-500 font-mono">
                                Diajukan: {{ $latestApproval->requested_at->timezone('Asia/Makassar')->format('d M Y, H:i') }} WITA
                            </span>
                        </div>

                        <div class="text-xs flex flex-col sm:flex-row sm:items-center justify-between gap-2 pt-2 border-t border-slate-200/60">
                            <div>
                                Approver: <strong class="font-semibold">{{ $latestApproval->approver?->name ?? 'Pejabat Ditunjuk' }}</strong>
                                @if($latestApproval->decision_reason)
                                    <div class="text-[11px] mt-1 italic">
                                        "{{ $latestApproval->decision_reason }}"
                                    </div>
                                @endif
                            </div>

                            @if($canDecideApproval)
                                <div class="flex items-center space-x-2 self-end sm:self-auto pt-2 sm:pt-0">
                                    <form action="{{ route('approvals.decide', $latestApproval) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="decision" value="approved">
                                        <button type="submit"
                                                onclick="return confirm('Apakah Anda yakin menyetujui permohonan akses ini?')"
                                                class="px-3.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition-colors cursor-pointer flex items-center space-x-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            <span>Setujui</span>
                                        </button>
                                    </form>

                                    <button type="button"
                                            onclick="openRejectModal('{{ $latestApproval->id }}', '{{ $ticket->ticket_number }}', '{{ addslashes($ticket->requester->name) }}')"
                                            class="px-3.5 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-xs transition-colors cursor-pointer flex items-center space-x-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        <span>Tolak</span>
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Pending Reason Banner if Pending -->
                @if($ticket->status->value === 'pending' && $ticket->pending_reason)
                    <div class="p-4 rounded-xl bg-orange-50 border border-orange-200 text-orange-900 space-y-1.5">
                        <div class="text-xs font-bold uppercase tracking-wider flex items-center space-x-2 text-orange-800">
                            <svg class="w-4 h-4 text-orange-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Alasan Status Pending: {{ $ticket->pending_reason instanceof \App\Enums\PendingReason ? $ticket->pending_reason->label() : $ticket->pending_reason }}</span>
                        </div>
                        @if($ticket->pending_note)
                            <p class="text-xs text-orange-800 pl-6">{{ $ticket->pending_note }}</p>
                        @endif
                    </div>
                @endif

                <!-- Resolution Summary if Resolved/Closed -->
                @if($ticket->resolution_summary)
                    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 space-y-1.5">
                        <div class="text-xs font-bold uppercase tracking-wider flex items-center space-x-2 text-emerald-800">
                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Ringkasan Solusi Penyelesaian:</span>
                        </div>
                        <p class="text-xs text-emerald-900 whitespace-pre-line pl-6 leading-relaxed">{{ $ticket->resolution_summary }}</p>
                        @if($ticket->resolved_at)
                            <div class="text-[11px] text-emerald-700 font-mono pt-1 pl-6">
                                Diselesaikan pada: {{ $ticket->resolved_at->timezone('Asia/Makassar')->format('d M Y, H:i') }} WITA
                            </div>
                        @endif
                    </div>
                @endif

                <!-- CSAT Customer Satisfaction Rating Card -->
                @if($ticket->isRated())
                    <div class="p-4 rounded-xl bg-amber-50/80 border border-amber-200 text-amber-950 space-y-2">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <span class="text-amber-500 font-bold text-base">★</span>
                                <span class="text-xs font-bold uppercase tracking-wider text-amber-800">Penilaian Layanan Pelapor (CSAT)</span>
                            </div>
                            <div class="flex items-center space-x-1">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-4 h-4 {{ $i <= $ticket->rating ? 'text-amber-500 fill-amber-400' : 'text-slate-200 fill-slate-100' }}" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                                <span class="text-xs font-bold text-amber-800 ml-1.5 font-mono">({{ $ticket->rating }} / 5)</span>
                            </div>
                        </div>
                        @if($ticket->rating_comment)
                            <p class="text-xs text-amber-900 italic bg-white/80 p-3 rounded-lg border border-amber-200/60 leading-relaxed">
                                "{{ $ticket->rating_comment }}"
                            </p>
                        @endif
                        @if($ticket->rated_at)
                            <div class="text-[11px] text-amber-700/80 font-mono">
                                Diberikan pada: {{ $ticket->rated_at->timezone('Asia/Makassar')->format('d M Y, H:i') }} WITA
                            </div>
                        @endif
                    </div>
                @elseif($ticket->canBeRatedBy(auth()->user()))
                    <div class="p-5 rounded-2xl bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 text-amber-950 space-y-3 shadow-xs">
                        <div class="flex items-center space-x-2">
                            <span class="w-6 h-6 rounded-lg bg-amber-200/80 text-amber-700 flex items-center justify-center text-xs font-bold">★</span>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-amber-900">
                                Survei Kepuasan Layanan (CSAT)
                            </h3>
                        </div>
                        <p class="text-xs text-amber-800 leading-relaxed">
                            Tiket ini telah diselesaikan oleh tim IT. Seberapa puas Anda dengan solusi dan pelayanan yang diberikan?
                        </p>
                        <form action="{{ route('tickets.rate', $ticket) }}" method="POST" class="space-y-3">
                            @csrf
                            <div>
                                <label class="block text-xs font-semibold text-amber-900 mb-1.5">Tingkat Kepuasan:</label>
                                <div class="flex flex-wrap items-center gap-2.5">
                                    @foreach([1 => '1 - Sangat Buruk', 2 => '2 - Buruk', 3 => '3 - Cukup', 4 => '4 - Puas', 5 => '5 - Sangat Puas'] as $val => $lbl)
                                        <label class="inline-flex items-center space-x-1.5 cursor-pointer text-xs text-amber-900 font-semibold bg-white px-3 py-1.5 rounded-xl border border-amber-200 hover:border-amber-400 transition-colors shadow-2xs">
                                            <input type="radio" name="rating" value="{{ $val }}" {{ old('rating') == $val ? 'checked' : ($val === 5 ? 'checked' : '') }} class="text-amber-600 focus:ring-amber-500">
                                            <span>{{ $lbl }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-amber-900 mb-1">Komentar / Saran Tambahan (Opsional):</label>
                                <textarea name="rating_comment"
                                          rows="2"
                                          maxlength="1000"
                                          placeholder="Tuliskan masukan Anda untuk peningkatan pelayanan IT..."
                                          class="w-full text-xs p-3 rounded-xl border border-amber-300 focus:outline-none focus:ring-2 focus:ring-amber-500 bg-white placeholder:text-slate-400"></textarea>
                            </div>
                            <button type="submit"
                                    class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs shadow-xs transition-colors cursor-pointer">
                                Kirim Penilaian Layanan
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            <!-- Attachments Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-700 flex items-center space-x-2">
                        <svg class="w-4 h-4 text-brand-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                        </svg>
                        <span>Lampiran Berkas ({{ $ticket->attachments->count() }})</span>
                    </h2>
                </div>

                @if($ticket->attachments->isNotEmpty())
                    <div class="divide-y divide-slate-100 border border-slate-200 rounded-xl overflow-hidden">
                        @foreach($ticket->attachments as $attachment)
                            <div class="p-3.5 bg-slate-50/50 hover:bg-slate-50 flex items-center justify-between gap-3 text-xs transition-colors">
                                <div class="flex items-center space-x-3 truncate">
                                    <div class="w-9 h-9 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-500 shrink-0 shadow-2xs">
                                        <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <div class="truncate">
                                        <div class="font-bold text-slate-800 truncate">
                                            {{ $attachment->original_filename }}
                                        </div>
                                        <div class="text-[11px] text-slate-400">
                                            {{ $attachment->file_size_formatted }} • Diunggah oleh {{ $attachment->uploader->name }} • {{ $attachment->created_at->timezone('Asia/Makassar')->format('d/m/Y H:i') }} WITA
                                        </div>
                                    </div>
                                </div>
                                <a href="{{ route('tickets.downloadAttachment', ['ticket' => $ticket, 'attachment' => $attachment]) }}"
                                   class="px-3 py-1.5 rounded-lg bg-white border border-slate-300 hover:bg-brand-50 hover:text-brand-700 font-semibold text-slate-700 transition-colors shrink-0 cursor-pointer shadow-2xs">
                                    Unduh
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-slate-400 italic">Tidak ada berkas lampiran pada tiket ini.</p>
                @endif

                <!-- Upload Attachment Form if permitted -->
                @if($canUpload)
                    <form action="{{ route('tickets.uploadAttachment', $ticket) }}" method="POST" enctype="multipart/form-data" class="pt-3 border-t border-slate-100 flex flex-wrap items-center gap-3">
                        @csrf
                        <input type="file" name="attachment" required class="text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
                        <button type="submit" class="px-4 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold shrink-0 cursor-pointer transition-colors shadow-2xs">
                            Unggah Berkas
                        </button>
                    </form>
                @endif
            </div>

            <!-- Comments & Discussion Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-6">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-700 flex items-center space-x-2">
                        <svg class="w-4 h-4 text-brand-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <span>Aktivitas &amp; Komentar ({{ $comments->count() }})</span>
                    </h2>
                    @if($canViewInternal)
                        <span class="text-[11px] px-2.5 py-1 rounded-full bg-amber-50 text-amber-800 border border-amber-200 font-semibold flex items-center space-x-1">
                            <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            <span>Mode Tim IT Aktif</span>
                        </span>
                    @endif
                </div>

                <!-- Comments Thread -->
                <div class="space-y-4">
                    @forelse($comments as $comment)
                        @php $isInternal = $comment->visibility->value === 'internal'; @endphp
                        <div class="p-4 sm:p-4.5 rounded-2xl border {{ $isInternal ? 'bg-amber-50/60 border-amber-200' : 'bg-slate-50/70 border-slate-200/80' }} space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center space-x-2.5">
                                    <div class="w-7 h-7 rounded-lg {{ $isInternal ? 'bg-amber-600' : 'bg-brand-700' }} text-white font-bold flex items-center justify-center text-xs font-mono shadow-2xs">
                                        {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                                    </div>
                                    <span class="font-bold text-slate-900">{{ $comment->user->name }}</span>
                                    <span class="text-[11px] text-slate-400">({{ $comment->user->roles->first()?->label ?? 'User' }})</span>
                                    @if($isInternal)
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-200 text-amber-900 uppercase tracking-wider inline-flex items-center space-x-1">
                                            <svg class="w-3 h-3 text-amber-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                            <span>Catatan Internal</span>
                                        </span>
                                    @endif
                                </div>
                                <span class="text-[11px] text-slate-400 font-mono">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="text-sm text-slate-700 whitespace-pre-line pl-9.5 leading-relaxed font-sans">
                                {{ $comment->body }}
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-slate-400 text-xs italic">
                            Belum ada komentar pada tiket ini.
                        </div>
                    @endforelse
                </div>

                <!-- Add Comment Form -->
                @if($canComment || $canCommentInternal)
                    <form action="{{ route('tickets.comment', $ticket) }}" method="POST" class="pt-4 border-t border-slate-100 space-y-3">
                        @csrf
                        <div>
                            <label for="comment-body" class="block text-xs font-bold text-slate-700 mb-1">
                                Tambahkan Respon / Pembaruan Tiket
                            </label>
                            <textarea name="body"
                                      id="comment-body"
                                      rows="3"
                                      required
                                      placeholder="Tuliskan respon penanganan, instruksi teknis, atau pertanyaan tindak lanjut..."
                                      class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 focus:border-brand-500 focus:outline-none transition-colors"></textarea>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                @if($canCommentInternal)
                                    <label class="flex items-center space-x-2 cursor-pointer text-xs text-amber-900 font-semibold bg-amber-50 px-3 py-1.5 rounded-xl border border-amber-200 shadow-2xs">
                                        <input type="checkbox" name="visibility" value="internal" class="w-4 h-4 rounded text-amber-600 focus:ring-amber-500 border-amber-300 cursor-pointer">
                                        <span class="inline-flex items-center space-x-1">
                                            <svg class="w-3.5 h-3.5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                            <span>Kirim sebagai Catatan Internal (Hanya terlihat oleh Tim IT)</span>
                                        </span>
                                    </label>
                                @else
                                    <input type="hidden" name="visibility" value="public">
                                @endif
                            </div>

                            <button type="submit"
                                    class="px-5 py-2.5 rounded-xl bg-brand-700 hover:bg-brand-800 text-white font-bold text-xs shadow-xs hover:shadow transition-all duration-200 cursor-pointer self-end focus:outline-none focus:ring-2 focus:ring-brand-500">
                                Kirim Komentar
                            </button>
                        </div>
                    </form>
                @endif
            </div>

        </div>

        <!-- Right Column: Sidebar Metadata & Audit Trail (1 Col Span) -->
        <div class="space-y-6">
            <!-- Sidebar Metadata Partial -->
            @include('tickets.partials.sidebar-metadata')

            <!-- Audit History Trail Partial -->
            @include('tickets.partials.audit-trail')
        </div>

    </div>

</div>

<!-- Workflow Transition Modals Partial -->
@include('tickets.partials.workflow-modals')

<!-- Reject Modal Dialog -->
<div id="reject-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-md w-full p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="font-bold text-sm text-slate-900 flex items-center space-x-2">
                <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span>Tolak Permintaan Persetujuan</span>
            </h3>
            <button type="button" onclick="closeRejectModal()" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <p class="text-xs text-slate-500">
            Berikan alasan penolakan permohonan akses tiket <span id="reject-ticket-number" class="font-mono font-bold text-slate-800"></span> untuk pemohon <span id="reject-requester-name" class="font-semibold text-slate-800"></span>.
        </p>
        <form id="reject-form" method="POST" action="" class="space-y-4">
            @csrf
            <input type="hidden" name="decision" value="rejected">
            <div>
                <label for="reject-reason" class="block text-xs font-semibold text-slate-700 mb-1">
                    Alasan Penolakan <span class="text-rose-500">*</span>
                </label>
                <textarea id="reject-reason"
                          name="reason"
                          rows="3"
                          required
                          placeholder="Jelaskan alasan mengapa permohonan akses ini tidak dapat disetujui..."
                          class="w-full text-xs p-3 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-rose-500 transition-colors"></textarea>
            </div>
            <div class="flex items-center justify-end space-x-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeRejectModal()" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50 cursor-pointer transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-xs hover:shadow transition-all duration-200 cursor-pointer">
                    Konfirmasi Tolak
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    function openRejectModal(approvalId, ticketNumber, requesterName) {
        const modal = document.getElementById('reject-modal');
        const form = document.getElementById('reject-form');
        const numSpan = document.getElementById('reject-ticket-number');
        const reqSpan = document.getElementById('reject-requester-name');

        if (form) {
            form.action = `/approvals/${approvalId}/decide`;
        }
        if (numSpan) numSpan.textContent = ticketNumber;
        if (reqSpan) reqSpan.textContent = requesterName;

        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    function closeRejectModal() {
        const modal = document.getElementById('reject-modal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }
</script>
@endpush
@endsection
