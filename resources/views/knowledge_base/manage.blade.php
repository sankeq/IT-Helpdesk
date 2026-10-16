@extends('layouts.app')

@section('title', 'Kelola Basis Pengetahuan TI — IKI IT Helpdesk')

@section('content')
<div class="space-y-6">

    <!-- Top Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <nav class="flex items-center space-x-2 text-xs text-slate-500 mb-1.5" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-brand-700 transition-colors">Dashboard</a>
                <span>/</span>
                <a href="{{ route('knowledge-base.index') }}" class="hover:text-brand-700 transition-colors">Basis Pengetahuan</a>
                <span>/</span>
                <span class="font-semibold text-slate-800">Manajemen Konten</span>
            </nav>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">
                Manajemen Artikel &amp; Solusi Mandiri
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Kelola draf panduan, publikasikan solusi operasional, dan pantau efektivitas artikel bantuan TI PT IKI.
            </p>
        </div>

        <div class="flex items-center space-x-2.5">
            <a href="{{ route('knowledge-base.index') }}"
               class="px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-50 transition-colors shadow-2xs">
                Lihat Tampilan Karyawan
            </a>
            <a href="{{ route('knowledge-base.create') }}"
               class="inline-flex items-center px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md hover:shadow-emerald-600/30 transition-all cursor-pointer">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                Tulis Artikel Baru
            </a>
        </div>
    </div>

    <!-- Quick Stats KPI Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-xs">
            <span class="text-xs font-semibold text-slate-500">Total Artikel</span>
            <div class="text-2xl font-extrabold text-slate-800 mt-1 font-mono">{{ $stats['total'] }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Semua status</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-emerald-200/90 shadow-xs">
            <span class="text-xs font-semibold text-emerald-700">Diterbitkan (Published)</span>
            <div class="text-2xl font-extrabold text-emerald-800 mt-1 font-mono">{{ $stats['published'] }}</div>
            <div class="text-[10px] text-emerald-600 mt-0.5">Dapat dibaca karyawan</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-amber-200/90 shadow-xs">
            <span class="text-xs font-semibold text-amber-700">Draf (Draft)</span>
            <div class="text-2xl font-extrabold text-amber-800 mt-1 font-mono">{{ $stats['draft'] }}</div>
            <div class="text-[10px] text-amber-600 mt-0.5">Menunggu review/rilis</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-blue-200/90 shadow-xs">
            <span class="text-xs font-semibold text-brand-700">Total Dibaca Pengguna</span>
            <div class="text-2xl font-extrabold text-brand-800 mt-1 font-mono">{{ $stats['total_views'] }}</div>
            <div class="text-[10px] text-brand-600 mt-0.5">Total interaksi pembaca</div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-5">
        <form method="GET" action="{{ route('knowledge-base.manage') }}" class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Cari Judul Artikel</label>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Kata kunci judul..."
                       class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white placeholder-slate-400">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Status Publikasi</label>
                <select name="status" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white">
                    <option value="">Semua Status</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published (Tayang)</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft (Konsep)</option>
                    <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archived (Arsip)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Kategori</label>
                <select name="category_id" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit"
                        class="flex-1 inline-flex items-center justify-center px-4 py-2 rounded-xl bg-brand-700 hover:bg-brand-800 text-white font-bold text-xs shadow-xs transition-colors cursor-pointer">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'status', 'category_id']))
                    <a href="{{ route('knowledge-base.manage') }}"
                       class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-xs transition-colors cursor-pointer"
                       title="Reset Filter">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Articles Table -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50/80 text-[11px] uppercase tracking-wider text-slate-500 border-b border-slate-100 font-bold">
                    <tr>
                        <th class="py-3.5 px-4 sm:px-6">Judul Panduan</th>
                        <th class="py-3.5 px-4">Kategori</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4">Penulis</th>
                        <th class="py-3.5 px-4 text-center">Pembaca</th>
                        <th class="py-3.5 px-4 text-center">Membantu</th>
                        <th class="py-3.5 px-4">Tanggal Rilis</th>
                        <th class="py-3.5 px-4 sm:px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($articles as $article)
                        <tr class="hover:bg-slate-50/75 transition-colors">
                            <td class="py-3.5 px-4 sm:px-6 font-bold text-slate-900 max-w-xs">
                                <a href="{{ route('knowledge-base.show', $article->slug) }}" class="hover:text-brand-700 line-clamp-1">
                                    {{ $article->title }}
                                </a>
                                <div class="text-[11px] text-slate-400 font-mono">/article/{{ $article->slug }}</div>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700">
                                    {{ $article->category?->name ?? 'Umum' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold
                                    {{ $article->status === 'published' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : '' }}
                                    {{ $article->status === 'draft' ? 'bg-amber-50 text-amber-700 border border-amber-200' : '' }}
                                    {{ $article->status === 'archived' ? 'bg-slate-100 text-slate-600 border border-slate-200' : '' }}">
                                    @if($article->status === 'published')
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                    @elseif($article->status === 'draft')
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5"></span>
                                    @endif
                                    {{ ucfirst($article->status) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="font-medium text-slate-800">{{ $article->author->name }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono">
                                {{ $article->views_count }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono font-bold text-emerald-700">
                                {{ $article->helpful_count }}
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap text-slate-500 font-mono text-[11px]">
                                {{ $article->published_at ? $article->published_at->setTimezone('Asia/Makassar')->format('d/m/Y') : '-' }}
                            </td>
                            <td class="py-3.5 px-4 sm:px-6 text-right whitespace-nowrap space-x-1">
                                @if($article->isDraft())
                                    <form action="{{ route('knowledge-base.publish', $article) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit"
                                                class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition-colors cursor-pointer"
                                                title="Terbitkan Artikel">
                                            Terbitkan
                                        </button>
                                    </form>
                                @endif

                                <a href="{{ route('knowledge-base.edit', $article) }}"
                                   class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors">
                                    Edit
                                </a>

                                <form action="{{ route('knowledge-base.destroy', $article) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus artikel ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-rose-50 text-rose-700 hover:bg-rose-100 transition-colors cursor-pointer">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                Belum ada artikel panduan yang tercatat pada filter saat ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($articles->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $articles->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
