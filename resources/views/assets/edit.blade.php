@extends('layouts.app')

@section('title', 'Ubah Aset TI — ' . $asset->asset_tag)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <nav class="flex items-center space-x-2 text-xs text-slate-500 mb-1.5" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-brand-700 transition-colors">Dashboard</a>
                <span>/</span>
                <a href="{{ route('assets.index') }}" class="hover:text-brand-700 transition-colors">Inventaris Aset TI</a>
                <span>/</span>
                <a href="{{ route('assets.show', $asset) }}" class="hover:text-brand-700 transition-colors font-mono">{{ $asset->asset_tag }}</a>
                <span>/</span>
                <span class="font-semibold text-slate-800">Ubah Data</span>
            </nav>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">
                Perbarui Informasi Aset TI
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Ubah spesifikasi, alokasi penanggung jawab, atau penempatan fisik aset <span class="font-mono font-bold text-slate-700">[{{ $asset->asset_tag }}]</span>.
            </p>
        </div>
        <a href="{{ route('assets.show', $asset) }}"
           class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-50 transition-colors shadow-2xs">
            Kembali
        </a>
    </div>

    <!-- Error Alert if validation fails -->
    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
            <div class="font-bold mb-1">Terdapat beberapa data yang perlu diperiksa:</div>
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form Card with Grouped Sections -->
    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-xs p-6 sm:p-8">
        <form method="POST" action="{{ route('assets.update', $asset) }}" class="space-y-8">
            @csrf
            @method('PUT')

            <!-- Section 1: Identitas & Klasifikasi -->
            <div>
                <div class="border-b border-slate-100 pb-3 mb-5">
                    <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-brand-50 text-brand-700 flex items-center justify-center text-xs font-bold font-mono">1</span>
                        Identitas &amp; Klasifikasi Perangkat
                    </h2>
                    <p class="text-[11px] text-slate-400 mt-0.5">Kode tag stiker inventaris dan kategori fungsional perangkat</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Asset Tag -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Nomor Tag Aset (Asset Tag) <span class="text-rose-500">*</span>
                        </label>
                        <input type="text"
                               name="asset_tag"
                               value="{{ old('asset_tag', $asset->asset_tag) }}"
                               class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 font-mono @error('asset_tag') border-rose-500 @enderror"
                               required>
                    </div>

                    <!-- Asset Name -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Nama Perangkat / Aset <span class="text-rose-500">*</span>
                        </label>
                        <input type="text"
                               name="name"
                               value="{{ old('name', $asset->name) }}"
                               class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 @error('name') border-rose-500 @enderror"
                               required>
                    </div>

                    <!-- Category -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Kategori Perangkat <span class="text-rose-500">*</span>
                        </label>
                        <select name="category" class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white" required>
                            <option value="hardware" {{ old('category', $asset->category) === 'hardware' ? 'selected' : '' }}>Hardware / Komputer &amp; Server</option>
                            <option value="peripheral" {{ old('category', $asset->category) === 'peripheral' ? 'selected' : '' }}>Periferal &amp; Printer</option>
                            <option value="network" {{ old('category', $asset->category) === 'network' ? 'selected' : '' }}>Network / Jaringan &amp; Router</option>
                            <option value="software" {{ old('category', $asset->category) === 'software' ? 'selected' : '' }}>Software / Lisensi</option>
                        </select>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Status Aset <span class="text-rose-500">*</span>
                        </label>
                        <select name="status" class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white" required>
                            <option value="active" {{ old('status', $asset->status) === 'active' ? 'selected' : '' }}>Aktif (Siap Pakai)</option>
                            <option value="maintenance" {{ old('status', $asset->status) === 'maintenance' ? 'selected' : '' }}>Dalam Perawatan (Maintenance)</option>
                            <option value="retired" {{ old('status', $asset->status) === 'retired' ? 'selected' : '' }}>Purna Pakai (Retired)</option>
                            <option value="disposed" {{ old('status', $asset->status) === 'disposed' ? 'selected' : '' }}>Dihapuskan (Disposed)</option>
                        </select>
                    </div>

                    <!-- Model / Type -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Model / Tipe Perangkat</label>
                        <input type="text"
                               name="model"
                               value="{{ old('model', $asset->model) }}"
                               class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>

                    <!-- Serial Number -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Nomor Seri Manufaktur (S/N)</label>
                        <input type="text"
                               name="serial_number"
                               value="{{ old('serial_number', $asset->serial_number) }}"
                               class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 font-mono">
                    </div>
                </div>
            </div>

            <!-- Section 2: Penempatan & Tanggung Jawab -->
            <div>
                <div class="border-b border-slate-100 pb-3 mb-5">
                    <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-brand-50 text-brand-700 flex items-center justify-center text-xs font-bold font-mono">2</span>
                        Penempatan Fisik &amp; Alokasi Pengguna
                    </h2>
                    <p class="text-[11px] text-slate-400 mt-0.5">Penetapan unit kerja galangan, departemen, serta personel pemegang</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Location -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Penempatan Lokasi Kerja</label>
                        <select name="location_id" class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white">
                            <option value="">Pilih Lokasi Galangan / Kantor</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}" {{ old('location_id', $asset->location_id) == $loc->id ? 'selected' : '' }}>
                                    {{ $loc->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Department -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Departemen Pemilik</label>
                        <select name="department_id" class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white">
                            <option value="">Pilih Departemen</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id', $asset->department_id) == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Assigned To User -->
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Pegawai Penanggung Jawab (Opsional)</label>
                        <select name="assigned_to_user_id" class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white">
                            <option value="">Tidak Ditugaskan (Aset Bersama / Pool)</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('assigned_to_user_id', $asset->assigned_to_user_id) == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }}) — NIP: {{ $user->employee_number ?? '-' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Section 3: Garansi & Catatan -->
            <div>
                <div class="border-b border-slate-100 pb-3 mb-5">
                    <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-brand-50 text-brand-700 flex items-center justify-center text-xs font-bold font-mono">3</span>
                        Informasi Pengadaan &amp; Garansi
                    </h2>
                    <p class="text-[11px] text-slate-400 mt-0.5">Masa garansi resmi dan dokumentasi riwayat fisik perangkat</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Purchase Date -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Tanggal Pengadaan / Pembelian</label>
                        <input type="date"
                               name="purchase_date"
                               value="{{ old('purchase_date', $asset->purchase_date?->format('Y-m-d')) }}"
                               class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white">
                    </div>

                    <!-- Warranty Expiry -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Batas Waktu Garansi</label>
                        <input type="date"
                               name="warranty_expiry"
                               value="{{ old('warranty_expiry', $asset->warranty_expiry?->format('Y-m-d')) }}"
                               class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white">
                    </div>

                    <!-- Notes -->
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Catatan Tambahan &amp; Riwayat Khusus</label>
                        <textarea name="notes"
                                  rows="3"
                                  class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500">{{ old('notes', $asset->notes) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Actions Bar -->
            <div class="flex items-center justify-end space-x-3 border-t border-slate-100 pt-5">
                <a href="{{ route('assets.show', $asset) }}"
                   class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold hover:bg-slate-50 transition-colors">
                    Batal
                </a>
                <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-brand-700 hover:bg-brand-800 text-white text-xs font-bold shadow-md hover:shadow-brand-700/30 transition-all cursor-pointer">
                    Perbarui Data Aset
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
