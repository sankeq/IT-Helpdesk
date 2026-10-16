<!-- ══════════════════ WORKFLOW MODALS ══════════════════ -->

<!-- Assign Modal -->
@if($canAssign && $agents)
<div id="assign-modal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-base font-bold text-slate-900">Tugaskan Tiket ke Teknisi IT</h3>
            <button type="button" onclick="closeModal('assign-modal')" class="text-slate-400 hover:text-slate-600 p-1 cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form action="{{ route('tickets.assign', $ticket) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="assignee_id" class="block text-xs font-semibold text-slate-700 mb-1">Pilih Teknisi / Agen IT</label>
                <select name="assignee_id" id="assignee_id" required class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 bg-white focus:ring-2 focus:ring-brand-500 cursor-pointer">
                    <option value="">Pilih Teknisi...</option>
                    @foreach($agents as $agent)
                        <option value="{{ $agent->id }}" {{ $ticket->assignee_id == $agent->id ? 'selected' : '' }}>
                            {{ $agent->name }} ({{ $agent->roles->first()?->label ?? 'Agent' }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="assign-reason" class="block text-xs font-semibold text-slate-700 mb-1">Catatan Penugasan (Opsional)</label>
                <textarea name="reason" id="assign-reason" rows="2" placeholder="Catatan instruksi penanganan..." class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand-500 focus:outline-none"></textarea>
            </div>
            <div class="flex justify-end space-x-2 pt-2">
                <button type="button" onclick="closeModal('assign-modal')" class="px-4 py-2 rounded-lg border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50 cursor-pointer transition-colors">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold cursor-pointer shadow-xs hover:shadow transition-all duration-200">Tugaskan Sekarang</button>
            </div>
        </form>
    </div>
</div>
@endif

<!-- Pending Modal -->
<div id="pending-modal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-base font-bold text-slate-900">Tandai Tiket Sebagai Pending</h3>
            <button type="button" onclick="closeModal('pending-modal')" class="text-slate-400 hover:text-slate-600 p-1 cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <p class="text-xs text-slate-500">Gunakan status ini jika pekerjaan tertunda karena faktor eksternal atau menunggu konfirmasi.</p>
        <form action="{{ route('tickets.transition', $ticket) }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="status" value="pending">
            <div>
                <label for="pending_reason" class="block text-xs font-semibold text-slate-700 mb-1">Alasan Pending <span class="text-rose-500">*</span></label>
                <select name="pending_reason" id="pending_reason" required class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 bg-white cursor-pointer focus:ring-2 focus:ring-brand-500">
                    <option value="">Pilih Alasan Pending...</option>
                    @foreach($pendingReasons as $reason)
                        <option value="{{ $reason->value }}">{{ $reason->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="pending_note" class="block text-xs font-semibold text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
                <textarea name="pending_note" id="pending_note" rows="3" placeholder="Jelaskan kebutuhan suku cadang, vendor yang dihubungi, atau respon yang ditunggu..." class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand-500 focus:outline-none"></textarea>
            </div>
            <div class="flex justify-end space-x-2 pt-2">
                <button type="button" onclick="closeModal('pending-modal')" class="px-4 py-2 rounded-lg border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50 cursor-pointer transition-colors">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-orange-600 hover:bg-orange-700 text-white text-xs font-semibold cursor-pointer shadow-xs hover:shadow transition-all duration-200">Tandai Pending</button>
            </div>
        </form>
    </div>
</div>

<!-- Resolve Modal -->
<div id="resolve-modal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-base font-bold text-slate-900">Selesaikan Tiket Helpdesk</h3>
            <button type="button" onclick="closeModal('resolve-modal')" class="text-slate-400 hover:text-slate-600 p-1 cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <p class="text-xs text-slate-500">Tuliskan ringkasan tindakan atau perbaikan yang telah dilakukan untuk menyelesaikan tiket ini.</p>
        <form action="{{ route('tickets.transition', $ticket) }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="status" value="resolved">
            <div>
                <label for="resolution_summary" class="block text-xs font-semibold text-slate-700 mb-1">Ringkasan Solusi <span class="text-rose-500">*</span></label>
                <textarea name="resolution_summary" id="resolution_summary" rows="4" required placeholder="Contoh: Penggantian kabel LAN di Galangan 2, pengujian koneksi jaringan berhasil dengan kecepatan optimal." class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand-500 focus:outline-none"></textarea>
            </div>
            <div class="flex justify-end space-x-2 pt-2">
                <button type="button" onclick="closeModal('resolve-modal')" class="px-4 py-2 rounded-lg border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50 cursor-pointer transition-colors">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold cursor-pointer shadow-xs hover:shadow transition-all duration-200">Konfirmasi Selesai</button>
            </div>
        </form>
    </div>
</div>

<!-- Close Modal -->
<div id="close-modal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-base font-bold text-slate-900">Tutup Tiket Secara Permanen</h3>
            <button type="button" onclick="closeModal('close-modal')" class="text-slate-400 hover:text-slate-600 p-1 cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <p class="text-xs text-slate-500">Tiket yang telah ditutup menandakan seluruh proses telah selesai dan terverifikasi.</p>
        <form action="{{ route('tickets.transition', $ticket) }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="status" value="closed">
            <div>
                <label for="close-note" class="block text-xs font-semibold text-slate-700 mb-1">Catatan Penutupan (Opsional)</label>
                <textarea name="note" id="close-note" rows="2" placeholder="Catatan konfirmasi dari pengguna / supervisor..." class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand-500 focus:outline-none"></textarea>
            </div>
            <div class="flex justify-end space-x-2 pt-2">
                <button type="button" onclick="closeModal('close-modal')" class="px-4 py-2 rounded-lg border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50 cursor-pointer transition-colors">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold cursor-pointer shadow-xs hover:shadow transition-all duration-200">Tutup Tiket</button>
            </div>
        </form>
    </div>
</div>

<!-- Cancel Modal -->
<div id="cancel-modal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-base font-bold text-rose-700">Batalkan Tiket Ini?</h3>
            <button type="button" onclick="closeModal('cancel-modal')" class="text-slate-400 hover:text-slate-600 p-1 cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <p class="text-xs text-slate-500">Tiket yang dibatalkan tidak akan diproses lebih lanjut oleh tim IT.</p>
        <form action="{{ route('tickets.transition', $ticket) }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="status" value="cancelled">
            <div>
                <label for="cancel-note" class="block text-xs font-semibold text-slate-700 mb-1">Alasan Pembatalan <span class="text-rose-500">*</span></label>
                <textarea name="note" id="cancel-note" rows="2" required placeholder="Jelaskan alasan tiket ini dibatalkan..." class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand-500 focus:outline-none"></textarea>
            </div>
            <div class="flex justify-end space-x-2 pt-2">
                <button type="button" onclick="closeModal('cancel-modal')" class="px-4 py-2 rounded-lg border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50 cursor-pointer transition-colors">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold cursor-pointer shadow-xs hover:shadow transition-all duration-200">Batalkan Tiket</button>
            </div>
        </form>
    </div>
</div>
