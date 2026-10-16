@extends('layouts.app')

@section('title', $article->title . ' — Basis Pengetahuan — IKI IT Helpdesk')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">

    <!-- Breadcrumbs & Meta Top Header -->
    <div>
        <nav class="flex items-center space-x-2 text-xs text-slate-500 mb-2" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}" class="hover:text-brand-700 transition-colors">Dashboard</a>
            <span>/</span>
            <a href="{{ route('knowledge-base.index') }}" class="hover:text-brand-700 transition-colors">Basis Pengetahuan</a>
            <span>/</span>
            <span class="font-semibold text-slate-800">{{ $article->category?->name ?? 'Umum' }}</span>
        </nav>

        <div class="flex items-center space-x-2 mb-3">
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                {{ $article->category?->name ?? 'Panduan Umum' }}
            </span>
            @if(!$article->isPublished())
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 uppercase">
                    Status: {{ $article->status }}
                </span>
            @endif
        </div>

        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight leading-tight">
            {{ $article->title }}
        </h1>

        <div class="flex flex-wrap items-center gap-y-2 gap-x-4 text-xs text-slate-400 mt-3 pt-3 border-t border-slate-100">
            <div class="flex items-center space-x-2">
                <div class="w-6 h-6 rounded-full bg-brand-700 text-white font-bold flex items-center justify-center text-[10px] font-mono">
                    {{ strtoupper(substr($article->author->name, 0, 1)) }}
                </div>
                <span class="text-slate-700 font-semibold">{{ $article->author->name }}</span>
            </div>
            <span>•</span>
            <span>Diterbitkan: {{ $article->published_at ? $article->published_at->setTimezone('Asia/Makassar')->format('d F Y, H:i') . ' WITA' : 'Belum diterbitkan' }}</span>
            <span>•</span>
            <span class="font-mono">{{ $article->views_count }} kali dibaca</span>
        </div>
    </div>

    <!-- Article Content Card -->
    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-xs p-6 sm:p-10 space-y-8">
        <div class="prose prose-slate max-w-none text-slate-800 text-sm sm:text-base leading-relaxed whitespace-pre-line font-sans">
            {{ $article->content }}
        </div>

        <!-- Helpful Feedback Widget -->
        <div class="border-t border-slate-100 pt-6 mt-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50 p-6 rounded-2xl border border-slate-100">
            <div>
                <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider">
                    Apakah artikel panduan ini membantu Anda?
                </h4>
                <p class="text-[11px] text-slate-500 mt-0.5">
                    {{ $article->helpful_count }} pengguna merasa panduan ini bermanfaat
                </p>
            </div>

            <form action="{{ route('knowledge-base.helpful', $article) }}" method="POST">
                @csrf
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 rounded-xl bg-white hover:bg-emerald-50 border border-slate-200 hover:border-emerald-300 text-slate-700 hover:text-emerald-700 text-xs font-bold shadow-2xs transition-colors cursor-pointer">
                    <svg class="w-4 h-4 mr-1.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/>
                    </svg>
                    Ya, Panduan Ini Membantu
                </button>
            </form>
        </div>
    </div>

    <!-- Related Articles -->
    @if($relatedArticles->isNotEmpty())
        <div class="space-y-4">
            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-brand-600"></span>
                Panduan Terkait dalam Kategori Ini
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($relatedArticles as $rel)
                    <a href="{{ route('knowledge-base.show', $rel->slug) }}"
                       class="p-4 rounded-2xl bg-white border border-slate-200/90 hover:border-brand-300 hover:shadow-md transition-all group block">
                        <h4 class="font-bold text-slate-900 group-hover:text-brand-700 text-xs transition-colors line-clamp-1">
                            {{ $rel->title }}
                        </h4>
                        <p class="text-[11px] text-slate-400 mt-1 line-clamp-1">
                            {{ Str::limit(strip_tags($rel->content), 80) }}
                        </p>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Back to KB and Create Ticket Link -->
    <div class="flex items-center justify-between pt-4 border-t border-slate-200">
        <a href="{{ route('knowledge-base.index') }}"
           class="inline-flex items-center text-xs font-semibold text-slate-600 hover:text-brand-700 transition-colors">
            &larr; Kembali ke Daftar Basis Pengetahuan
        </a>

        <a href="{{ route('tickets.create') }}"
           class="inline-flex items-center text-xs font-bold text-brand-700 hover:underline">
            Butuh Bantuan Lain? Ajukan Tiket &rarr;
        </a>
    </div>

</div>
@endsection
