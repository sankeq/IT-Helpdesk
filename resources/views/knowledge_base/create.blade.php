@extends('layouts.app')

@section('title', 'Tulis Artikel Basis Pengetahuan Baru — IKI IT Helpdesk')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <nav class="flex items-center space-x-2 text-xs text-slate-500 mb-1.5" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-brand-700 transition-colors">Dashboard</a>
                <span>/</span>
                <a href="{{ route('knowledge-base.index') }}" class="hover:text-brand-700 transition-colors">Basis Pengetahuan</a>
                <span>/</span>
                <a href="{{ route('knowledge-base.manage') }}" class="hover:text-brand-700 transition-colors">Manajemen</a>
                <span>/</span>
                <span class="font-semibold text-slate-800">Tulis Baru</span>
            </nav>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">
                Tulis Panduan &amp; Solusi Baru
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Dokumentasikan solusi standar, panduan setting perangkat, atau petunjuk pemecahan masalah galangan.
            </p>
        </div>
        <a href="{{ route('knowledge-base.manage') }}"
           class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-50 transition-colors shadow-2xs">
            Batal
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-xs p-6 sm:p-8">
        <form method="POST" action="{{ route('knowledge-base.store') }}" class="space-y-6">
            @csrf

            <!-- Article Title -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Judul Panduan Solusi <span class="text-rose-500">*</span>
                </label>
                <input type="text"
                       name="title"
                       value="{{ old('title') }}"
                       placeholder="Contoh: Cara Menghubungkan Laptop ke Jaringan Wi-Fi Galangan"
                       class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 font-semibold placeholder-slate-400"
                       required>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Category -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Kategori Layanan <span class="text-rose-500">*</span>
                    </label>
                    <select name="category_id" class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white" required>
                        <option value="">Pilih Kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Status Publikasi <span class="text-rose-500">*</span>
                    </label>
                    <select name="status" class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white" required>
                        <option value="draft" {{ old('status', 'draft') === 'draft' ? 'selected' : '' }}>Draft (Konsep Internal)</option>
                        <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Published (Segera Tayang untuk Karyawan)</option>
                        <option value="archived" {{ old('status') === 'archived' ? 'selected' : '' }}>Archived (Diarsipkan)</option>
                    </select>
                </div>
            </div>

            <!-- Content Area -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Isi Panduan / Langkah-langkah Solusi <span class="text-rose-500">*</span>
                </label>
                <textarea name="content"
                          rows="12"
                          placeholder="Tuliskan petunjuk teknis langkah demi langkah secara jelas. Format penomoran (1, 2, 3) sangat disarankan..."
                          class="w-full px-3.5 py-3 text-xs sm:text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 font-sans leading-relaxed placeholder-slate-400"
                          required>{{ old('content') }}</textarea>
                <span class="text-[11px] text-slate-400 mt-1 block">Tips: Gunakan kalimat instruktif yang mudah diikuti staf operasional lapangan.</span>
            </div>

            <div class="flex items-center justify-end space-x-3 border-t border-slate-100 pt-5">
                <a href="{{ route('knowledge-base.manage') }}"
                   class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold hover:bg-slate-50 transition-colors">
                    Batal
                </a>
                <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-brand-700 hover:bg-brand-800 text-white text-xs font-bold shadow-md hover:shadow-brand-700/30 transition-all cursor-pointer">
                    Simpan Artikel
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
