@extends('layouts.app')

@section('title', 'Buat Tiket Baru')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header Navigation -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('tickets.index') }}" class="hover:text-brand-700 cursor-pointer transition-colors">Daftar Tiket</a>
                <span class="text-slate-300">/</span>
                <span class="text-slate-700 font-semibold">Buat Baru</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Form Permohonan Bantuan IT</h1>
            <p class="text-xs text-slate-500 mt-0.5">Sampaikan keluhan teknis atau pengajuan layanan TI PT Industri Kapal Indonesia (Persero)</p>
        </div>
        <a href="{{ route('tickets.index') }}"
           class="inline-flex items-center px-3.5 py-1.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors cursor-pointer shadow-2xs">
            <svg class="w-3.5 h-3.5 mr-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali
        </a>
    </div>

    <!-- Form Container -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">

        <!-- Requester Info Bar (Maritime Corporate Style) -->
        <div class="relative overflow-hidden bg-gradient-to-r from-brand-950 via-slate-900 to-navy-900 p-5 sm:p-6 text-white border-b border-brand-800/40">
            <div class="absolute -right-8 -bottom-8 w-48 h-48 bg-blue-500/10 rounded-full blur-2xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center space-x-3.5">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-blue-500/30 to-brand-600/40 border border-blue-400/40 flex items-center justify-center text-blue-200 font-extrabold text-sm shadow-inner">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="text-[11px] text-blue-300 uppercase tracking-wider font-bold flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            Identitas Pemohon
                        </div>
                        <div class="text-base font-bold text-white">{{ auth()->user()->name }}</div>
                    </div>
                </div>
                <div class="text-xs text-blue-200/90 flex flex-wrap gap-x-4 gap-y-1.5 pt-2 sm:pt-0 border-t sm:border-t-0 border-white/10">
                    <span class="inline-flex items-center gap-1">
                        <span class="text-blue-300">Departemen:</span>
                        <strong class="text-white font-semibold">{{ auth()->user()->department?->name ?? 'Umum' }}</strong>
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <span class="text-blue-300">NIK:</span>
                        <strong class="text-white font-mono">{{ auth()->user()->employee_number ?? '-' }}</strong>
                    </span>
                </div>
            </div>
        </div>

        <form action="{{ route('tickets.store') }}" method="POST" enctype="multipart/form-data" class="p-6 sm:p-8 space-y-6">
            @csrf

            <!-- Subject -->
            <div>
                <label for="subject" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5 flex items-center justify-between">
                    <span>Subjek Permasalahan <span class="text-rose-500">*</span></span>
                    <span id="subject-counter" class="text-[11px] text-slate-400 font-mono font-normal">0/150</span>
                </label>
                <input type="text"
                       name="subject"
                       id="subject"
                       required
                       maxlength="150"
                       value="{{ old('subject') }}"
                       placeholder="Contoh: Komputer Dok Galangan 2 tidak dapat terhubung ke server ERP"
                       class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('subject') ? 'border-rose-400 bg-rose-50/20 ring-1 ring-rose-400' : 'border-slate-300' }} text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all placeholder:text-slate-400">
                <p class="text-[11px] text-slate-400 mt-1">Tuliskan ringkasan kendala atau kebutuhan dengan padat dan jelas.</p>
                @error('subject')
                    <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Category & Location Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                <!-- Category -->
                <div>
                    <label for="category_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Kategori Layanan <span class="text-rose-500">*</span>
                    </label>
                    <select name="category_id"
                            id="category_id"
                            required
                            class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('category_id') ? 'border-rose-400 bg-rose-50/20 ring-1 ring-rose-400' : 'border-slate-300 bg-white' }} text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 cursor-pointer transition-colors">
                        <option value="">Pilih Kategori Layanan...</option>
                        @foreach($categories as $root)
                            <optgroup label="{{ $root->name }}">
                                @if($root->children->isNotEmpty())
                                    @foreach($root->children as $child)
                                        <option value="{{ $child->id }}" {{ old('category_id') == $child->id ? 'selected' : '' }}>
                                            {{ $root->name }} &gt; {{ $child->name }}
                                        </option>
                                    @endforeach
                                @else
                                    <option value="{{ $root->id }}" {{ old('category_id') == $root->id ? 'selected' : '' }}>
                                        {{ $root->name }}
                                    </option>
                                @endif
                            </optgroup>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Location -->
                <div>
                    <label for="location_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Lokasi / Unit Kerja <span class="text-slate-400 text-[11px] font-normal">(Opsional)</span>
                    </label>
                    <select name="location_id"
                            id="location_id"
                            class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('location_id') ? 'border-rose-400 bg-rose-50/20 ring-1 ring-rose-400' : 'border-slate-300 bg-white' }} text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 cursor-pointer transition-colors">
                        <option value="">Pilih Lokasi Unit Kerja...</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}" {{ (old('location_id') ?? auth()->user()->location_id) == $location->id ? 'selected' : '' }}>
                                {{ $location->name }} ({{ $location->code }})
                            </option>
                        @endforeach
                    </select>
                    @error('location_id')
                        <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Linked Asset -->
                <div class="sm:col-span-2">
                    <label for="asset_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Perangkat / Aset TI Terkait <span class="text-slate-400 text-[11px] font-normal">(Opsional — pilih jika kendala terjadi pada perangkat spesifik terdaftar)</span>
                    </label>
                    <select name="asset_id"
                            id="asset_id"
                            class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('asset_id') ? 'border-rose-400 bg-rose-50/20 ring-1 ring-rose-400' : 'border-slate-300 bg-white' }} text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 cursor-pointer transition-colors">
                        <option value="">Tidak terhubung ke aset spesifik...</option>
                        @if(isset($assets))
                            @foreach($assets as $asset)
                                <option value="{{ $asset->id }}" {{ old('asset_id') == $asset->id ? 'selected' : '' }}>
                                    [{{ $asset->asset_tag }}] {{ $asset->name }} — {{ $asset->model ?? $asset->category }} ({{ $asset->location?->name ?? 'Semua Lokasi' }})
                                </option>
                            @endforeach
                        @endif
                    </select>
                    @error('asset_id')
                        <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            <!-- Impact, Urgency, & Priority Calculator Row -->
            <div class="p-5 sm:p-6 rounded-2xl bg-gradient-to-br from-slate-50 to-blue-50/30 border border-slate-200/90 space-y-4 shadow-2xs">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200/60 pb-3">
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            Penilaian Dampak &amp; Urgensi (Kalkulator SLA)
                        </h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">Prioritas tiket akan ditentukan secara otomatis berdasarkan matriks baku IT Helpdesk IKI</p>
                    </div>

                    <!-- Live Priority Badge -->
                    <div class="flex items-center gap-2 self-start sm:self-auto">
                        <span class="text-[10px] uppercase font-bold text-slate-400">Hasil:</span>
                        <div id="priority-result-badge" class="inline-flex px-3 py-1 rounded-full text-xs font-extrabold priority-badge-P4 transition-all duration-200 shadow-2xs">
                            P4 - Low
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Impact -->
                    <div>
                        <label for="impact" class="block text-xs font-bold text-slate-700 mb-1.5">
                            Tingkat Dampak (Impact) <span class="text-rose-500">*</span>
                        </label>
                        <select name="impact"
                                id="impact"
                                required
                                onchange="calculatePriority()"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 focus:border-brand-500 bg-white cursor-pointer transition-colors">
                            @foreach($impacts as $impact)
                                <option value="{{ $impact->value }}" {{ old('impact', 'low') === $impact->value ? 'selected' : '' }}>
                                    {{ $impact->label() }} — {{ $impact->description() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Urgency -->
                    <div>
                        <label for="urgency" class="block text-xs font-bold text-slate-700 mb-1.5">
                            Tingkat Urgensi (Urgency) <span class="text-rose-500">*</span>
                        </label>
                        <select name="urgency"
                                id="urgency"
                                required
                                onchange="calculatePriority()"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 focus:border-brand-500 bg-white cursor-pointer transition-colors">
                            @foreach($urgencies as $urgency)
                                <option value="{{ $urgency->value }}" {{ old('urgency', 'low') === $urgency->value ? 'selected' : '' }}>
                                    {{ $urgency->label() }} — {{ $urgency->description() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Rincian Masalah / Kebutuhan <span class="text-rose-500">*</span>
                </label>
                <textarea name="description"
                          id="description"
                          rows="5"
                          required
                          placeholder="Jelaskan secara detail:&#10;1. Apa yang sedang dikerjakan saat kendala terjadi?&#10;2. Pesan error atau perilaku sistem yang tidak normal.&#10;3. Apakah kendala ini berulang atau baru pertama kali terjadi?"
                          class="w-full px-4 py-3 rounded-xl border {{ $errors->has('description') ? 'border-rose-400 bg-rose-50/20 ring-1 ring-rose-400' : 'border-slate-300' }} text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all placeholder:text-slate-400 font-sans leading-relaxed">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- File Attachments Upload -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Lampiran Berkas / Tangkapan Layar <span class="text-slate-400 text-[11px] font-normal">(Maks. 5 berkas, @ 10MB)</span>
                </label>
                <div class="border-2 border-dashed border-slate-300 hover:border-brand-500 rounded-2xl p-6 text-center bg-slate-50/60 hover:bg-slate-50 transition-all duration-200 cursor-pointer group"
                     onclick="document.getElementById('attachments').click()">
                    <div class="w-12 h-12 mx-auto mb-2 rounded-2xl bg-white border border-slate-200/80 shadow-2xs flex items-center justify-center text-slate-400 group-hover:text-brand-600 group-hover:border-brand-300 transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                        </svg>
                    </div>
                    <div class="text-xs font-bold text-slate-800 group-hover:text-brand-700 transition-colors">
                        Klik untuk memilih berkas atau seret berkas ke sini
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">
                        Format didukung: {{ implode(', ', $allowedTypes) }}
                    </p>
                    <input type="file"
                           name="attachments[]"
                           id="attachments"
                           multiple
                           class="hidden"
                           onchange="updateFileList(this)">
                </div>
                <div id="file-list-preview" class="mt-2.5 space-y-2"></div>
                @error('attachments')
                    <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
                @error('attachments.*')
                    <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Action Buttons -->
            <div class="pt-5 border-t border-slate-100 flex items-center justify-end space-x-3">
                <a href="{{ route('tickets.index') }}"
                   class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-semibold text-xs sm:text-sm hover:bg-slate-50 transition-colors cursor-pointer">
                    Batal
                </a>
                <button type="submit"
                        class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-brand-700 to-blue-700 hover:from-brand-800 hover:to-blue-800 text-white font-bold text-xs sm:text-sm shadow-md shadow-brand-950/20 hover:shadow-lg hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 cursor-pointer focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-600">
                    Kirim Tiket Bantuan
                </button>
            </div>

        </form>
    </div>

</div>

<script>
    // Character counter for subject
    const subjectInput = document.getElementById('subject');
    const counter = document.getElementById('subject-counter');
    if (subjectInput && counter) {
        subjectInput.addEventListener('input', () => {
            counter.textContent = `${subjectInput.value.length}/150`;
        });
    }

    // Dynamic Priority Calculation (matches Priority::fromMatrix)
    const priorityMatrix = {
        high: {
            high: { label: 'P1 - Critical', class: 'priority-badge-P1' },
            medium: { label: 'P2 - High', class: 'priority-badge-P2' },
            low: { label: 'P2 - High', class: 'priority-badge-P2' },
        },
        medium: {
            high: { label: 'P2 - High', class: 'priority-badge-P2' },
            medium: { label: 'P3 - Medium', class: 'priority-badge-P3' },
            low: { label: 'P3 - Medium', class: 'priority-badge-P3' },
        },
        low: {
            high: { label: 'P3 - Medium', class: 'priority-badge-P3' },
            medium: { label: 'P4 - Low', class: 'priority-badge-P4' },
            low: { label: 'P4 - Low', class: 'priority-badge-P4' },
        }
    };

    function calculatePriority() {
        const impact = document.getElementById('impact').value;
        const urgency = document.getElementById('urgency').value;
        const badge = document.getElementById('priority-result-badge');

        if (priorityMatrix[impact] && priorityMatrix[impact][urgency]) {
            const result = priorityMatrix[impact][urgency];
            badge.textContent = result.label;
            badge.className = `inline-flex px-3 py-1 rounded-full text-xs font-extrabold ${result.class} transition-all duration-200 shadow-2xs`;
        }
    }

    // File list preview with Document SVG icon
    function updateFileList(input) {
        const preview = document.getElementById('file-list-preview');
        preview.innerHTML = '';
        if (input.files && input.files.length > 0) {
            for (let i = 0; i < input.files.length; i++) {
                const file = input.files[i];
                const sizeKB = (file.size / 1024).toFixed(1);
                const item = document.createElement('div');
                item.className = 'flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-200/80 text-xs text-slate-700 shadow-2xs';
                item.innerHTML = `
                    <div class="flex items-center space-x-2.5 truncate">
                        <div class="w-7 h-7 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-brand-600 shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        </div>
                        <span class="font-bold truncate text-slate-800">${file.name}</span>
                    </div>
                    <span class="text-slate-400 font-mono text-[11px]">${sizeKB} KB</span>
                `;
                preview.appendChild(item);
            }
        }
    }

    // Run on initial load
    document.addEventListener('DOMContentLoaded', calculatePriority);
</script>
@endsection
