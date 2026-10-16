@extends('layouts.app')

@section('title', 'Notifikasi & Peringatan SLA')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <svg class="w-7 h-7 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                Pusat Notifikasi & SLA
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Peringatan tenggat waktu SLA, eskalasi pelanggaran, dan penugasan tiket.
            </p>
        </div>

        @if($notifications->total() > 0)
            <form action="{{ route('notifications.markAllRead') }}" method="POST">
                @csrf
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 text-xs font-semibold rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 shadow-xs transition-colors cursor-pointer">
                    <svg class="w-4 h-4 mr-1.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Tandai Semua Sudah Dibaca
                </button>
            </form>
        @endif
    </div>

    <!-- Notification Cards -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="divide-y divide-slate-100">
            @forelse($notifications as $item)
                @php
                    $isUnread = is_null($item->read_at);
                    $type = $item->data['type'] ?? 'general';
                @endphp
                <div class="p-4 sm:p-5 flex items-start justify-between gap-4 transition-colors {{ $isUnread ? 'bg-brand-50/30' : 'hover:bg-slate-50/50' }}">
                    <div class="flex items-start space-x-3.5">
                        <div class="shrink-0 mt-0.5">
                            @if($type === 'sla_breach')
                                <div class="w-9 h-9 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center font-bold">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                </div>
                            @elseif($type === 'sla_warning')
                                <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center font-bold">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                            @else
                                <div class="w-9 h-9 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </div>
                            @endif
                        </div>

                        <div class="space-y-1">
                            <div class="flex items-center space-x-2">
                                <span class="font-mono text-xs font-bold text-brand-700">
                                    {{ $item->data['ticket_number'] ?? 'TIKET' }}
                                </span>
                                @if($type === 'sla_breach')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 border border-rose-200">
                                        PELANGGARAN SLA
                                    </span>
                                @elseif($type === 'sla_warning')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 border border-amber-200">
                                        PERINGATAN 75%
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-700 border border-indigo-200">
                                        PENUGASAN
                                    </span>
                                @endif

                                @if($isUnread)
                                    <span class="w-2 h-2 rounded-full bg-brand-500"></span>
                                @endif
                            </div>

                            <p class="text-xs sm:text-sm text-slate-800 font-medium">
                                {{ $item->data['message'] ?? 'Notifikasi baru' }}
                            </p>

                            <div class="text-[11px] text-slate-400 font-mono">
                                {{ $item->created_at->diffForHumans() }} ({{ $item->created_at->timezone('Asia/Makassar')->format('d/m/Y H:i') }} WITA)
                            </div>
                        </div>
                    </div>

                    <div class="shrink-0 flex items-center">
                        <form action="{{ route('notifications.markAsRead', $item->id) }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-brand-600 text-white hover:bg-brand-500 transition-colors shadow-xs cursor-pointer">
                                Buka Tiket
                                <svg class="w-3.5 h-3.5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="py-16 text-center text-slate-400">
                    <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>
                    <div class="text-sm font-semibold text-slate-700">Belum ada notifikasi</div>
                    <p class="text-xs text-slate-400 mt-1">Anda akan menerima pemberitahuan saat tiket ditugaskan atau mendekati batas SLA.</p>
                </div>
            @endforelse
        </div>

        @if($notifications->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
