@extends('layout')

@section('content')
    <div class="px-5 md:px-8 py-6 md:py-8">
        {{-- BACK BUTTON & HEADER --}}
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ url()->previous() }}" class="text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold">Persetujuan Reimburse</h1>
                <p class="text-gray-500 text-sm">Daftar permintaan reimburse yang menunggu persetujuan Anda.</p>
            </div>
        </div>

        {{-- FILTERS --}}
        <form method="GET" action="{{ request()->url() }}" class="mb-4 space-y-2">
            <div class="grid grid-cols-2 gap-2">
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="border border-gray-300 rounded-lg p-2 text-sm">
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                    class="border border-gray-300 rounded-lg p-2 text-sm">
            </div>
            <div class="flex gap-2">
                <select name="user_id" class="flex-1 border border-gray-300 rounded-lg p-2 text-sm">
                    <option value="">Semua User</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
                <select name="type" class="flex-1 border border-gray-300 rounded-lg p-2 text-sm">
                    <option value="">Semua Tipe</option>
                    <option value="fuel" {{ request('type') == 'fuel' ? 'selected' : '' }}>Bahan Bakar</option>
                    <option value="hotel" {{ request('type') == 'hotel' ? 'selected' : '' }}>Hotel</option>
                    <option value="toll" {{ request('type') == 'toll' ? 'selected' : '' }}>Toll</option>
                    <option value="transport" {{ request('type') == 'transport' ? 'selected' : '' }}>Transport</option>
                    <option value="other" {{ request('type') == 'other' ? 'selected' : '' }}>Lainnya</option>
                </select>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg font-bold text-sm">
                Filter
            </button>
        </form>

        {{-- BULK ACTION (jika ada data) --}}
        @if($pendingReimburses->total() > 0)
            <form id="bulkApproveForm" action="{{ route(auth()->user()->role . '.reimburse.bulk_approve') }}" method="POST" class="mb-4">
                @csrf
                <div class="flex items-center justify-between bg-blue-50 rounded-xl p-3">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" id="selectAll" onchange="toggleAllCheckboxes()"
                            class="w-5 h-5 text-blue-600 rounded">
                        <span class="text-sm font-bold text-blue-700">Pilih Semua</span>
                    </label>
                    <button type="submit" onclick="return confirm('Setujui semua yang dipilih?')"
                        class="bg-green-600 text-white px-4 py-2 rounded-lg font-bold text-sm hover:bg-green-700">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Bulk Approve
                    </button>
                </div>
                <input type="hidden" name="expense_ids" value="">
            </form>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pb-24">
            @forelse($pendingReimburses as $expense)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition">
                    {{-- Checkbox untuk bulk action --}}
                    <div class="flex items-start gap-3">
                        <input type="checkbox" class="expense-checkbox mt-4 w-5 h-5 text-blue-600 rounded"
                            value="{{ $expense->id }}">

                        <div class="flex-1 cursor-pointer" onclick="goToDetail(event, '{{ route('sales.history.detail', $expense->daily_log_id) }}')">
                            {{-- Header: Nama & Tanggal --}}
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <h3 class="font-bold text-gray-800">{{ $expense->user->name }}
                                        <span class="text-xs text-gray-500">({{ ucfirst($expense->user->role) }})</span>
                                    </h3>
                                    <div class="space-y-0.5 mt-1">
                                        <p class="text-xs text-blue-600 font-bold flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5 inline text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            Kegiatan: {{ \Carbon\Carbon::parse($expense->date)->format('d M Y') }}
                                        </p>
                                        <p class="text-[10px] text-gray-400">
                                            Diajukan: {{ \Carbon\Carbon::parse($expense->created_at)->format('d M Y H:i') }}
                                        </p>
                                    </div>
                                </div>
                                <span class="bg-yellow-100 text-yellow-700 text-[10px] font-bold px-2 py-1 rounded-full uppercase">
                                    @if(auth()->user()->isSupervisor())
                                        Menunggu SPV
                                    @elseif(auth()->user()->isHrd())
                                        Menunggu HRD
                                    @else
                                        {{ str_replace('_', ' ', $expense->status) }}
                                    @endif
                                </span>
                            </div>

                            {{-- Approval History (SPV untuk HRD) --}}
                            @if(auth()->user()->isHrd() && $expense->approved_by_spv_at)
                                <div class="bg-green-50 border border-green-200 rounded-lg p-2 mb-3 text-xs">
                                    <p class="text-green-700"><svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Disetujui SPV: {{ $expense->approvedBySpv?->name ?? '-' }}
                                        ({{ \Carbon\Carbon::parse($expense->approved_by_spv_at)->format('d M H:i') }})</p>
                                </div>
                            @endif

                            {{-- Detail Expense --}}
                            <div class="bg-gray-50 rounded-xl p-3 mb-3">
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-600">{{ ucfirst($expense->type) }}</span>
                                    <span class="font-bold text-gray-900">Rp {{ number_format($expense->amount, 0, ',', '.') }}</span>
                                </div>
                                @if($expense->note)
                                    <p class="text-xs text-gray-500 italic">"{{ $expense->note }}"</p>
                                @endif
                                @if($expense->is_auto_calculated && $expense->km_total)
                                    <p class="text-xs text-gray-400 mt-1">
                                        *Auto calculated: {{ number_format($expense->km_total, 2) }} KM ÷ {{ $expense->km_per_liter ?? 30 }}
                                        KM/L × Rp {{ number_format($expense->fuel_price ?? 10000, 0, ',', '.') }}/L
                                    </p>
                                @endif

                                {{-- Link Foto/Struk --}}
                                @if($expense->photo_receipt)
                                    <button type="button" onclick="openImageModal('{{ route('expenses.receipt.show', $expense->id) }}')"
                                        class="text-blue-500 text-xs flex items-center gap-1 mt-2 hover:underline">
                                        <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg> Lihat Struk/Bukti
                                    </button>
                                @else
                                    <span class="inline-flex items-center gap-1 text-orange-700 text-xs mt-2 bg-orange-50 border border-orange-200 rounded-lg px-2 py-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                        Bukti/struk belum dilampirkan
                                    </span>
                                @endif

                                {{-- Daftar Kunjungan --}}
                                @if($expense->dailyLog && $expense->dailyLog->visits->count() > 0)
                                    <div class="mt-3 pt-2 border-t border-gray-200/60">
                                        <p class="text-[10px] font-bold text-gray-400 uppercase mb-1.5 tracking-wider">Aktivitas Kunjungan Toko:</p>
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach($expense->dailyLog->visits as $visit)
                                                <span class="text-[10px] px-2 py-0.5 rounded-full font-medium bg-blue-50 text-blue-700 border border-blue-100 flex items-center gap-1">
                                                    @if($visit->status === 'completed')
                                                        <span class="text-green-500 font-bold">✓</span>
                                                    @elseif($visit->status === 'failed')
                                                        <span class="text-red-500 font-bold">✗</span>
                                                    @else
                                                        <span class="text-yellow-500 font-bold">?</span>
                                                    @endif
                                                    {{ $visit->client_name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                {{-- Link ke detail absen --}}
                                <a href="{{ route('sales.history.detail', $expense->daily_log_id) }}"
                                    onclick="event.stopPropagation()"
                                    class="mt-3 pt-2 border-t border-gray-200/60 text-xs text-indigo-600 font-bold flex items-center justify-between hover:text-indigo-700">
                                    <span>Lihat detail absen lengkap</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                </a>
                            </div>

                            {{-- Revision Info (jika ada) --}}
                            @if($expense->revision_count > 0)
                                <div class="bg-orange-50 border border-orange-200 rounded-lg p-2 mb-3">
                                    <p class="text-xs text-orange-700"><svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg> Revisi ke-{{ $expense->revision_count }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="space-y-3">
                        {{-- Tombol Tolak & Setuju (Default View) --}}
                        <div id="buttons-{{ $expense->id }}" class="flex gap-3">
                            <button type="button" onclick="showRejectForm({{ $expense->id }})"
                                class="flex-1 bg-red-100 text-red-700 py-3 rounded-xl font-bold text-sm hover:bg-red-200 transition">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg> Tolak
                            </button>
                            <button type="button" onclick="showApproveForm({{ $expense->id }})"
                                class="flex-1 bg-green-100 text-green-700 py-3 rounded-xl font-bold text-sm hover:bg-green-200 transition">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Setujui
                            </button>
                        </div>

                        {{-- Form Reject (Hidden by default) --}}
                        <div id="reject-form-{{ $expense->id }}" class="hidden bg-red-50 border border-red-200 rounded-xl p-4">
                            <div class="flex items-center justify-between mb-3">
                                <p class="font-bold text-red-800 text-sm">Alasan Penolakan</p>
                                <button type="button" onclick="hideAllForms({{ $expense->id }})"
                                    class="text-gray-500 hover:text-gray-700 text-xs">
                                    ← Batal
                                </button>
                            </div>

                            <form action="{{ route(auth()->user()->role . '.reimburse.reject', $expense->id) }}" method="POST"
                                onsubmit="return showRejectConfirm(event, this)" class="card-form">
                                @csrf
                                <div class="space-y-3">
                                    {{-- Alasan Penolakan --}}
                                    <input type="text" name="reason" placeholder="Masukkan alasan penolakan..." required
                                        class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-red-300 focus:border-red-400">

                                    {{-- Tipe Penolakan --}}
                                    <div class="flex gap-2">
                                        <label
                                            class="flex-1 flex items-center gap-2 bg-yellow-50 border border-yellow-200 rounded-lg p-3 cursor-pointer hover:bg-yellow-100">
                                            <input type="radio" name="rejection_type" value="revisi" required
                                                class="text-yellow-600">
                                            <span class="text-xs font-bold text-yellow-700"><svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg> Minta Revisi</span>
                                        </label>
                                        <label
                                            class="flex-1 flex items-center gap-2 bg-red-50 border border-red-200 rounded-lg p-3 cursor-pointer hover:bg-red-100">
                                            <input type="radio" name="rejection_type" value="permanent" required
                                                class="text-red-600">
                                            <span class="text-xs font-bold text-red-700"><svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg> Tolak Permanen</span>
                                        </label>
                                    </div>

                                    <button type="submit"
                                        class="w-full bg-red-600 text-white py-3 rounded-xl font-bold text-sm hover:bg-red-700 transition">
                                        Konfirmasi Penolakan
                                    </button>
                                </div>
                            </form>
                        </div>

                        {{-- Form Approve (Hidden by default) --}}
                        <div id="approve-form-{{ $expense->id }}"
                            class="hidden bg-green-50 border border-green-200 rounded-xl p-4">
                            <div class="flex items-center justify-between mb-3">
                                <p class="font-bold text-green-800 text-sm">Konfirmasi Persetujuan</p>
                                <button type="button" onclick="hideAllForms({{ $expense->id }})"
                                    class="text-gray-500 hover:text-gray-700 text-xs">
                                    ← Batal
                                </button>
                            </div>

                            <form action="{{ route(auth()->user()->role . '.reimburse.approve', $expense->id) }}" method="POST" class="card-form">
                                @csrf
                                <p class="text-sm text-green-700 mb-3">Apakah Anda yakin ingin menyetujui reimburse ini?</p>
                                <button type="submit"
                                    class="w-full bg-green-600 text-white py-3 rounded-xl font-bold text-sm hover:bg-green-700 transition">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Konfirmasi Persetujuan
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-10">
                    <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p class="text-sm text-gray-500">Tidak ada permintaan pending.</p>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($pendingReimburses->hasPages())
            <div class="flex justify-center mt-6">
                {{ $pendingReimburses->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

    {{-- Confirm Reject Modal --}}
    <div id="reject-confirm-modal" class="hidden modal-overlay fixed inset-0 z-[60] flex items-center justify-center bg-black/50" onclick="if(event.target===this) closeRejectConfirm()">
        <div class="modal-box bg-white rounded-2xl shadow-xl w-[90%] max-w-sm overflow-hidden">
            <div class="p-6 text-center">
                <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-2">Konfirmasi Penolakan</h3>
                <p class="text-sm text-gray-500">Apakah Anda yakin ingin menolak reimburse ini?</p>
            </div>
            <div class="flex border-t border-gray-100">
                <button type="button" onclick="closeRejectConfirm()"
                    class="flex-1 py-3.5 text-sm font-bold text-gray-600 hover:bg-gray-50 transition">Batal</button>
                <button type="button" onclick="confirmReject()"
                    class="flex-1 py-3.5 text-sm font-bold text-white bg-red-600 hover:bg-red-700 transition">Ya, Tolak</button>
            </div>
        </div>
    </div>

    {{-- Image Modal (hidden) --}}
    <div id="image-modal-overlay" class="hidden fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50" onclick="if(event.target.id==='image-modal-overlay') closeImageModal()">
        <div class="bg-white rounded-xl overflow-hidden max-w-4xl w-full mx-4" role="dialog" aria-modal="true">
            <div class="flex justify-end p-2">
                <button onclick="closeImageModal()" class="text-gray-600 px-3 py-1 flex items-center gap-1">Tutup <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
            </div>
            <div class="p-4" style="max-height:calc(100vh - 180px); overflow:auto;">
                <img id="image-modal-img" src="" alt="Struk" style="max-height:calc(100vh - 220px); max-width:100%; object-fit:contain; display:block; margin:0 auto;" class="rounded-lg" />
            </div>
        </div>
    </div>

    @section('scripts')
        <script>
            // Reject confirmation modal
            let pendingRejectForm = null;

            function showRejectConfirm(e, form) {
                e.preventDefault();
                pendingRejectForm = form;
                document.getElementById('reject-confirm-modal').classList.remove('hidden');
                return false;
            }

            function confirmReject() {
                if (pendingRejectForm) {
                    pendingRejectForm.submit();
                    pendingRejectForm = null;
                }
            }

            function closeRejectConfirm() {
                document.getElementById('reject-confirm-modal').classList.add('hidden');
                pendingRejectForm = null;
            }
            // Modal image viewer
            function openImageModal(url) {
                var overlay = document.getElementById('image-modal-overlay');
                var img = document.getElementById('image-modal-img');
                img.src = url;
                overlay.classList.remove('hidden');
            }

            function closeImageModal() {
                var overlay = document.getElementById('image-modal-overlay');
                var img = document.getElementById('image-modal-img');
                img.src = '';
                overlay.classList.add('hidden');
            }
            // Close modal with Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    var overlay = document.getElementById('image-modal-overlay');
                    if (overlay && !overlay.classList.contains('hidden')) {
                        closeImageModal();
                    }
                }
            });

            // Klik area info kartu -> detail absen (abaikan elemen interaktif)
            function goToDetail(e, url) {
                if (e.target.closest('button, a, input, select, textarea, label, form')) return;
                window.location.href = url;
            }

            // Toggle all checkboxes
            function toggleAllCheckboxes() {
                const selectAll = document.getElementById('selectAll');
                const checkboxes = document.querySelectorAll('.expense-checkbox');
                checkboxes.forEach(cb => cb.checked = selectAll.checked);
            }

            function showRejectForm(id) {
                document.getElementById('buttons-' + id).classList.add('hidden');
                document.getElementById('reject-form-' + id).classList.remove('hidden');
                document.getElementById('approve-form-' + id).classList.add('hidden');
            }

            function showApproveForm(id) {
                document.getElementById('buttons-' + id).classList.add('hidden');
                document.getElementById('reject-form-' + id).classList.add('hidden');
                document.getElementById('approve-form-' + id).classList.remove('hidden');
            }

            function hideAllForms(id) {
                document.getElementById('buttons-' + id).classList.remove('hidden');
                document.getElementById('reject-form-' + id).classList.add('hidden');
                document.getElementById('approve-form-' + id).classList.add('hidden');
            }

            // Submit bulk approve dengan selected IDs
            document.getElementById('bulkApproveForm')?.addEventListener('submit', function(e) {
                const checked = document.querySelectorAll('.expense-checkbox:checked');
                const ids = Array.from(checked).map(cb => cb.value);
                if (ids.length === 0) {
                    e.preventDefault();
                    alert('Pilih minimal satu expense!');
                    return false;
                }
                this.querySelector('input[name="expense_ids"]').value = ids.join(',');
            });
        </script>
    @endsection
@endsection
