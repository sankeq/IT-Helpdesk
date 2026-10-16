@extends('layouts.app')

@section('title', 'Ubah Artikel — ' . $article->title)

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
                <span class="font-semibold text-slate-800">Ubah Artikel</span>
            </nav>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">
                Perbarui Panduan Solusi
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Sesuaikan instruksi teknis atau perbarui materi untuk artikel <span class="font-bold text-slate-700">[{{ $article->title }}]</span>.
            </p>
        </div>
        <a href="{{ route('knowledge-base.manage') }}"
           class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-50 transition-colors shadow-2xs">
            Batal
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-xs p-6 sm:p-8">
        <form method="POST" action="{{ route('knowledge-base.update', $article) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Article Title -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Judul Panduan Solusi <span class="text-rose-500">*</span>
                </label>
                <input type="text"
                       name="title"
                       value="{{ old('title', $article->title) }}"
                       class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 font-semibold"
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
                            <option value="{{ $category->id }}" {{ old('category_id', $article->category_id) == $category->id ? 'selected' : '' }}>
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
                        <option value="draft" {{ old('status', $article->status) === 'draft' ? 'selected' : '' }}>Draft (Konsep Internal)</option>
                        <option value="published" {{ old('status', $article->status) === 'published' ? 'selected' : '' }}>Published (Tayang untuk Karyawan)</option>
                        <option value="archived" {{ old('status', $article->status) === 'archived' ? 'selected' : '' }}>Archived (Diarsipkan)</option>
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
                          class="w-full px-3.5 py-3 text-xs sm:text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 font-sans leading-relaxed"
                          required>{{ old('content', $article->content) }}</textarea>
            </div>

            <div class="flex items-center justify-end space-x-3 border-t border-slate-100 pt-5">
                <a href="{{ route('knowledge-base.manage') }}"
                   class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold hover:bg-slate-50 transition-colors">
                    Batal
                </a>
                <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-brand-700 hover:bg-brand-800 text-white text-xs font-bold shadow-md hover:shadow-brand-700/30 transition-all cursor-pointer">
                    Perbarui Artikel
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
