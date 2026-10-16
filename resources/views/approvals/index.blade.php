@extends('layouts.app')

@section('title', 'Daftar Persetujuan Layanan TI — IKI IT Helpdesk')

@section('content')
<div class="space-y-6">

    <!-- Header Section with Maritime Depth -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-brand-950 via-brand-900 to-navy-900 p-6 sm:p-8 text-white shadow-xl border border-brand-800/60">
        <!-- Blueprint Grid Pattern Overlay -->
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_1px_1px,rgba(255,255,255,0.08)_1px,transparent_0)] bg-[size:24px_24px] pointer-events-none opacity-60"></div>
        <div class="absolute -right-8 -bottom-8 w-60 h-60 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-200 text-xs font-semibold uppercase tracking-wider mb-2.5 backdrop-blur-xs">
                    <svg class="w-3.5 h-3.5 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <span>Otorisasi &amp; Tata Kelola Akses TI</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Persetujuan Permintaan Akses &amp; Perangkat
                </h1>
                <p class="text-blue-100/90 text-xs sm:text-sm mt-1.5 max-w-2xl leading-relaxed">
                    Daftar permohonan hak akses modul ERP, fasilitas jaringan VPN galangan, atau pengadaan perangkat yang memerlukan persetujuan pejabat/approver berwenang.
                </p>
            </div>

            <div class="shrink-0 flex items-center gap-3">
                <div class="px-4 py-2.5 rounded-2xl bg-white/10 backdrop-blur-xs border border-white/15 text-center">
                    <span class="text-[10px] text-blue-200 uppercase tracking-wider block font-semibold">Menunggu Keputusan</span>
                    <span class="text-xl font-extrabold font-mono text-white">{{ $pendingApprovals->total() }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Error Alert if validation fails -->
    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
            <div class="font-bold mb-1">Terdapat kesalahan:</div>
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Pending Approvals Card -->
    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div class="flex items-center space-x-2.5">
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                </span>
                <h2 class="font-extrabold text-xs sm:text-sm text-slate-800 uppercase tracking-wider">
                    Menunggu Keputusan ({{ $pendingApprovals->total() }})
                </h2>
            </div>
            <span class="text-xs text-slate-400">Persetujuan tertunda</span>
        </div>

        @if($pendingApprovals->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50/80 border-b border-slate-100 text-slate-500 font-bold uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="py-3.5 px-4 sm:px-6">No. Tiket</th>
                            <th class="py-3.5 px-4">Pelapor &amp; Unit</th>
                            <th class="py-3.5 px-4">Kategori Permintaan</th>
                            <th class="py-3.5 px-4">Subjek &amp; Deskripsi</th>
                            <th class="py-3.5 px-4">Waktu Pengajuan</th>
                            <th class="py-3.5 px-4 sm:px-6 text-right">Aksi Keputusan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @foreach($pendingApprovals as $approval)
                            <tr class="hover:bg-slate-50/75 transition-colors group">
                                <td class="py-4 px-4 sm:px-6 font-mono font-bold text-brand-700 whitespace-nowrap">
                                    <a href="{{ route('tickets.show', $approval->ticket) }}" class="inline-flex items-center space-x-1.5 hover:text-brand-900 group-hover:underline">
                                        <svg class="w-3.5 h-3.5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                                        </svg>
                                        <span>{{ $approval->ticket->ticket_number }}</span>
                                    </a>
                                </td>
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <div class="font-bold text-slate-900">{{ $approval->requester->name }}</div>
                                    <div class="text-[11px] text-slate-400 mt-0.5">
                                        {{ $approval->requester->department->name ?? '-' }} (NIP: {{ $approval->requester->employee_number ?? '-' }})
                                    </div>
                                </td>
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-slate-100 border border-slate-200 text-slate-700 font-semibold text-[11px]">
                                        {{ $approval->ticket->category->name }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 max-w-xs">
                                    <div class="font-bold text-slate-800 truncate">{{ $approval->ticket->subject }}</div>
                                    <div class="text-[11px] text-slate-400 truncate mt-0.5">{{ Str::limit($approval->ticket->description, 60) }}</div>
                                </td>
                                <td class="py-4 px-4 font-mono text-[11px] text-slate-500 whitespace-nowrap">
                                    <div class="font-semibold text-slate-700">{{ $approval->requested_at->format('d M Y, H:i') }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $approval->requested_at->diffForHumans() }}</div>
                                </td>
                                <td class="py-4 px-4 sm:px-6 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end space-x-2">
                                        <!-- Approve Form -->
                                        <form action="{{ route('approvals.decide', $approval) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="decision" value="approved">
                                            <button type="submit"
                                                    onclick="return confirm('Apakah Anda yakin menyetujui permohonan ini?')"
                                                    class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-xs transition-all cursor-pointer flex items-center space-x-1.5">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                <span>Setujui</span>
                                            </button>
                                        </form>

                                        <!-- Reject Trigger Button -->
                                        <button type="button"
                                                onclick="openRejectModal('{{ $approval->id }}', '{{ $approval->ticket->ticket_number }}', '{{ addslashes($approval->requester->name) }}')"
                                                class="px-3.5 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold text-xs shadow-2xs transition-all cursor-pointer flex items-center space-x-1.5">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                            <span>Tolak</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($pendingApprovals->hasPages())
                <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $pendingApprovals->links() }}
                </div>
            @endif
        @else
            <div class="py-16 text-center text-slate-400 text-xs">
                <div class="w-12 h-12 mx-auto mb-3 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="font-bold text-slate-700 text-sm">Tidak Ada Persetujuan yang Tertunda</div>
                <p class="mt-1 text-slate-400 max-w-sm mx-auto">Semua permohonan hak akses atau perangkat saat ini telah selesai diproses.</p>
            </div>
        @endif
    </div>

    <!-- Decided Approvals Card (History Audit Trail) -->
    @if($decidedApprovals->isNotEmpty())
        <div class="bg-white rounded-3xl border border-slate-200/90 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div class="flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-slate-400"></span>
                    <h2 class="font-bold text-xs sm:text-sm text-slate-800 uppercase tracking-wider">
                        Riwayat Keputusan Terakhir
                    </h2>
                </div>
                <span class="text-xs text-slate-400 font-mono">10 keputusan terbaru</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50/80 border-b border-slate-100 text-slate-500 font-bold uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="py-3 px-4 sm:px-6">No. Tiket</th>
                            <th class="py-3 px-4">Pelapor</th>
                            <th class="py-3 px-4">Keputusan</th>
                            <th class="py-3 px-4">Diputuskan Oleh</th>
                            <th class="py-3 px-4">Catatan / Alasan</th>
                            <th class="py-3 px-4 sm:px-6">Waktu Keputusan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @foreach($decidedApprovals as $approval)
                            <tr class="hover:bg-slate-50/75 transition-colors">
                                <td class="py-3.5 px-4 sm:px-6 font-mono font-bold text-brand-700 whitespace-nowrap">
                                    <a href="{{ route('tickets.show', $approval->ticket) }}" class="hover:underline">
                                        {{ $approval->ticket->ticket_number }}
                                    </a>
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="font-bold text-slate-900">{{ $approval->requester->name }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $approval->requester->department->name ?? '-' }}</div>
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $approval->status->badgeClasses() }}">
                                        @if($approval->status->value === 'approved')
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                        @elseif($approval->status->value === 'rejected')
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 mr-1.5"></span>
                                        @endif
                                        {{ $approval->status->label() }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-semibold text-slate-800 whitespace-nowrap">
                                    {{ $approval->approver?->name ?? 'Approver' }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-600 italic">
                                    {{ $approval->decision_reason ?? '-' }}
                                </td>
                                <td class="py-3.5 px-4 sm:px-6 font-mono text-[11px] text-slate-500 whitespace-nowrap">
                                    {{ $approval->decided_at?->format('d M Y, H:i') ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>

<!-- Reject Modal Dialog -->
<div id="reject-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/60 backdrop-blur-xs p-4">
    <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-md w-full p-6 space-y-4 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="font-bold text-sm text-slate-900 flex items-center space-x-2">
                <div class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <span>Tolak Permintaan Persetujuan</span>
            </h3>
            <button type="button" onclick="closeRejectModal()" class="text-slate-400 hover:text-slate-600 cursor-pointer p-1 rounded-lg hover:bg-slate-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form id="reject-form" method="POST" action="" class="space-y-4">
            @csrf
            <input type="hidden" name="decision" value="rejected">

            <div class="text-xs text-slate-600 p-3 rounded-xl bg-slate-50 border border-slate-100">
                Anda akan menolak permohonan untuk tiket <strong id="reject-ticket-number" class="font-mono text-brand-700"></strong> dari <strong id="reject-requester-name"></strong>.
            </div>

            <div>
                <label for="reject-reason" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Alasan Penolakan <span class="text-rose-500">*</span>
                </label>
                <textarea name="reason"
                          id="reject-reason"
                          rows="3"
                          required
                          placeholder="Jelaskan pertimbangan mengapa permintaan akses/perangkat ini ditolak..."
                          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-rose-500 focus:outline-none transition-colors"></textarea>
                <p class="text-[11px] text-slate-400 mt-1">Alasan penolakan akan dicatat pada jejak audit dan status tiket akan diperbarui.</p>
            </div>

            <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeRejectModal()" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-50 transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-xs transition-colors cursor-pointer">
                    Konfirmasi Penolakan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openRejectModal(approvalId, ticketNumber, requesterName) {
        document.getElementById('reject-ticket-number').textContent = ticketNumber;
        document.getElementById('reject-requester-name').textContent = requesterName;
        document.getElementById('reject-form').action = `/approvals/${approvalId}/decide`;
        const modal = document.getElementById('reject-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeRejectModal() {
        const modal = document.getElementById('reject-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeRejectModal();
        }
    });
</script>
@endsection
