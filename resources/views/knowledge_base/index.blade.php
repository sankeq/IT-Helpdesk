@extends('layouts.app')

@section('title', 'Pusat Solusi Mandiri — Basis Pengetahuan TI — IKI IT Helpdesk')

@section('content')
<div class="space-y-8">

    <!-- Top Hero Search Banner with Maritime Styling -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-brand-950 via-brand-900 to-navy-900 p-6 sm:p-10 text-white shadow-xl border border-brand-800/60 text-center sm:text-left">
        <!-- Blueprint Grid Pattern Overlay -->
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_1px_1px,rgba(255,255,255,0.08)_1px,transparent_0)] bg-[size:24px_24px] pointer-events-none opacity-60"></div>
        <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-blue-500/15 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 max-w-3xl">
            <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-200 text-xs font-semibold uppercase tracking-wider mb-3 backdrop-blur-xs">
                <svg class="w-3.5 h-3.5 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                <span>Self-Service &amp; Dokumentasi Solusi</span>
            </div>

            <!-- Main Heading (Verified in Tests) -->
            <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight leading-tight">
                Bagaimana kami dapat membantu Anda hari ini?
            </h1>
            <p class="text-blue-100/90 text-xs sm:text-sm mt-2.5 leading-relaxed max-w-2xl">
                Temukan panduan resmi konfigurasi jaringan, prosedur akses sistem ERP, petunjuk penanganan mandiri printer &amp; komputer galangan PT Industri Kapal Indonesia (Persero).
            </p>

            <!-- Search Form Bar -->
            <form method="GET" action="{{ route('knowledge-base.index') }}" class="mt-6 flex flex-col sm:flex-row gap-2 max-w-xl">
                <div class="relative flex-1">
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Ketik kata kunci (misal: Wi-Fi, ERP, printer, VPN)..."
                           class="w-full pl-10 pr-4 py-3 rounded-2xl bg-white text-slate-800 text-xs sm:text-sm shadow-md focus:outline-none focus:ring-2 focus:ring-blue-400 placeholder-slate-400">
                    <svg class="w-5 h-5 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <button type="submit"
                        class="px-6 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs sm:text-sm shadow-md hover:shadow-emerald-600/30 transition-all cursor-pointer shrink-0">
                    Cari Solusi
                </button>
            </form>

            <!-- Quick Popular Keyword Chips -->
            <div class="mt-4 flex flex-wrap items-center justify-center sm:justify-start gap-2 text-[11px] text-blue-200">
                <span class="text-blue-300 font-semibold">Pencarian Populer:</span>
                <a href="{{ route('knowledge-base.index', ['search' => 'Wi-Fi']) }}" class="px-2.5 py-0.5 rounded-full bg-white/10 hover:bg-white/20 text-white border border-white/15 transition-colors">Wi-Fi Galangan</a>
                <a href="{{ route('knowledge-base.index', ['search' => 'ERP']) }}" class="px-2.5 py-0.5 rounded-full bg-white/10 hover:bg-white/20 text-white border border-white/15 transition-colors">Sistem ERP</a>
                <a href="{{ route('knowledge-base.index', ['search' => 'Printer']) }}" class="px-2.5 py-0.5 rounded-full bg-white/10 hover:bg-white/20 text-white border border-white/15 transition-colors">Printer Dokumen</a>
                <a href="{{ route('knowledge-base.index', ['search' => 'Password']) }}" class="px-2.5 py-0.5 rounded-full bg-white/10 hover:bg-white/20 text-white border border-white/15 transition-colors">Reset Password</a>
            </div>
        </div>

        @if(auth()->user()->hasPermission('knowledge.manage'))
            <div class="mt-6 sm:mt-0 sm:absolute sm:top-8 sm:right-8 z-10 flex items-center justify-center sm:justify-end">
                <a href="{{ route('knowledge-base.manage') }}"
                   class="inline-flex items-center px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white text-xs font-semibold backdrop-blur-xs transition-colors shadow-xs">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Kelola Artikel (Admin/Teknisi)
                </a>
            </div>
        @endif
    </div>

    <!-- Category Filters Grid -->
    <div>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-bold text-slate-900">Jelajahi Berdasarkan Kategori Layanan</h2>
            @if(request('category_id') || request('search'))
                <a href="{{ route('knowledge-base.index') }}" class="text-xs font-semibold text-brand-600 hover:underline">
                    &larr; Tampilkan Semua Panduan
                </a>
            @endif
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5">
            @foreach($categories as $category)
                <a href="{{ route('knowledge-base.index', ['category_id' => $category->id]) }}"
                   class="p-4 rounded-2xl border transition-all duration-200 cursor-pointer group {{ request('category_id') == $category->id ? 'bg-brand-50/80 border-brand-400 shadow-xs' : 'bg-white border-slate-200/90 hover:border-brand-300 hover:shadow-md' }}">
                    <div class="w-8 h-8 rounded-xl {{ request('category_id') == $category->id ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-600 group-hover:bg-brand-50 group-hover:text-brand-600' }} flex items-center justify-center mb-2.5 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <div class="font-bold text-slate-900 text-xs sm:text-sm group-hover:text-brand-700 transition-colors">
                        {{ $category->name }}
                    </div>
                    <div class="text-[11px] text-slate-400 mt-1 font-mono">
                        {{ $category->articles_count }} Panduan
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    <!-- Main Articles Grid & Popular Sidebar -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Articles List (2 columns on lg) -->
        <div class="lg:col-span-2 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-200/80 pb-2.5">
                <h2 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-brand-600"></span>
                    Daftar Panduan &amp; Solusi ({{ $articles->total() }})
                </h2>
                @if(request('search'))
                    <span class="text-xs text-slate-500">Pencarian: "<strong>{{ request('search') }}</strong>"</span>
                @endif
            </div>

            <div class="grid grid-cols-1 gap-4">
                @forelse($articles as $article)
                    <article class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-xs hover:shadow-md hover:border-brand-200 transition-all duration-200 group">
                        <div class="flex items-center space-x-2 mb-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                {{ $article->category?->name ?? 'Umum' }}
                            </span>
                            <span class="text-slate-300">•</span>
                            <span class="text-[11px] text-slate-400 font-mono">
                                {{ $article->published_at ? $article->published_at->setTimezone('Asia/Makassar')->format('d M Y') : '-' }}
                            </span>
                        </div>

                        <h3 class="text-base font-bold text-slate-900 group-hover:text-brand-700 transition-colors leading-snug">
                            <a href="{{ route('knowledge-base.show', $article->slug) }}" class="hover:underline">
                                {{ $article->title }}
                            </a>
                        </h3>

                        <p class="text-xs text-slate-500 mt-2 line-clamp-2 leading-relaxed">
                            {{ Str::limit(strip_tags($article->content), 160) }}
                        </p>

                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
                            <div class="flex items-center space-x-4">
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    {{ $article->views_count }} dilihat
                                </span>
                                <span class="flex items-center gap-1.5 text-emerald-600 font-semibold">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/></svg>
                                    {{ $article->helpful_count }} terbantu
                                </span>
                            </div>

                            <a href="{{ route('knowledge-base.show', $article->slug) }}"
                               class="text-brand-600 font-bold group-hover:translate-x-1 transition-transform inline-flex items-center text-xs">
                                Baca Panduan &rarr;
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="bg-white rounded-3xl border border-slate-200 p-8 text-center space-y-3">
                        <div class="w-12 h-12 mx-auto rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-slate-800">Tidak Menemukan Panduan yang Cocok</h3>
                        <p class="text-xs text-slate-500 max-w-sm mx-auto">
                            Belum ada artikel panduan yang sesuai dengan kata kunci pencarian Anda. Anda dapat mengajukan tiket bantuan langsung ke tim IT IKI.
                        </p>
                        <div class="pt-2">
                            <a href="{{ route('tickets.create') }}"
                               class="inline-flex items-center px-4 py-2 rounded-xl bg-brand-700 hover:bg-brand-800 text-white font-bold text-xs shadow-xs">
                                Ajukan Tiket Bantuan Sekarang
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>

            @if($articles->hasPages())
                <div class="p-4 bg-white rounded-2xl border border-slate-200 shadow-xs">
                    {{ $articles->links() }}
                </div>
            @endif
        </div>

        <!-- Sidebar: Popular Articles & Contact Support -->
        <div class="space-y-6">

            <!-- Popular Articles Card -->
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-5 space-y-4">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-2.5 flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    Panduan Paling Membantu
                </h3>

                <div class="divide-y divide-slate-100 text-xs">
                    @foreach($popularArticles as $pop)
                        <div class="py-3 first:pt-0 last:pb-0">
                            <a href="{{ route('knowledge-base.show', $pop->slug) }}"
                               class="font-semibold text-slate-800 hover:text-brand-700 line-clamp-2 leading-snug transition-colors">
                                {{ $pop->title }}
                            </a>
                            <div class="text-[11px] text-slate-400 mt-1 flex items-center space-x-2">
                                <span>{{ $pop->category?->name ?? 'Umum' }}</span>
                                <span>•</span>
                                <span class="text-emerald-600 font-semibold">{{ $pop->helpful_count }} menyukai</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Contact Helpdesk Card -->
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-950 to-brand-900 text-white p-6 shadow-md border border-brand-800/60 space-y-4">
                <div class="w-10 h-10 rounded-xl bg-blue-500/20 border border-blue-400/30 flex items-center justify-center text-blue-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold">Masih Mengalami Kendala?</h3>
                    <p class="text-xs text-blue-200 mt-1.5 leading-relaxed">
                        Jika panduan mandiri belum menyelesaikan masalah Anda, tim teknisi Helpdesk IT IKI siap memberikan penanganan langsung di lokasi unit kerja.
                    </p>
                </div>
                <a href="{{ route('tickets.create') }}"
                   class="block text-center w-full px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md hover:shadow-emerald-600/30 transition-all cursor-pointer">
                    Buat Permohonan Tiket
                </a>
            </div>

        </div>

    </div>

</div>
@endsection
