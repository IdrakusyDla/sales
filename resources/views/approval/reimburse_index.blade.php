@extends('layout')

@section('content')
    <div class="px-5 py-6">
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

        <div class="space-y-4 pb-24">
            @forelse($pendingReimburses as $expense)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
                    {{-- Header: Nama & Tanggal --}}
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h3 class="font-bold text-gray-800">{{ $expense->user->name }}
                                <span class="text-xs text-gray-500">({{ ucfirst($expense->user->role) }})</span>
                            </h3>
                            <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($expense->date)->format('d M Y') }}</p>
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
                            <p class="text-green-700">✅ Disetujui SPV: {{ $expense->approvedBySpv?->name ?? '-' }}
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
                                📎 Lihat Struk/Bukti
                            </button>
                        @endif
                    </div>

                    {{-- Revision Info (jika ada) --}}
                    @if($expense->revision_count > 0)
                        <div class="bg-orange-50 border border-orange-200 rounded-lg p-2 mb-3">
                            <p class="text-xs text-orange-700">🔄 Revisi ke-{{ $expense->revision_count }}</p>
                        </div>
                    @endif

                    {{-- Action Buttons --}}
                    <div class="space-y-3">
                        {{-- Tombol Tolak & Setuju (Default View) --}}
                        <div id="buttons-{{ $expense->id }}" class="flex gap-3">
                            <button type="button" onclick="showRejectForm({{ $expense->id }})"
                                class="flex-1 bg-red-100 text-red-700 py-3 rounded-xl font-bold text-sm hover:bg-red-200 transition">
                                ❌ Tolak
                            </button>
                            <button type="button" onclick="showApproveForm({{ $expense->id }})"
                                class="flex-1 bg-green-100 text-green-700 py-3 rounded-xl font-bold text-sm hover:bg-green-200 transition">
                                ✅ Setujui
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
                                onsubmit="return confirm('Tolak reimburse ini?')">
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
                                            <span class="text-xs font-bold text-yellow-700">🔄 Minta Revisi</span>
                                        </label>
                                        <label
                                            class="flex-1 flex items-center gap-2 bg-red-50 border border-red-200 rounded-lg p-3 cursor-pointer hover:bg-red-100">
                                            <input type="radio" name="rejection_type" value="permanent" required
                                                class="text-red-600">
                                            <span class="text-xs font-bold text-red-700">⛔ Tolak Permanen</span>
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

                            <form action="{{ route(auth()->user()->role . '.reimburse.approve', $expense->id) }}" method="POST">
                                @csrf
                                <p class="text-sm text-green-700 mb-3">Apakah Anda yakin ingin menyetujui reimburse ini?</p>
                                <button type="submit"
                                    class="w-full bg-green-600 text-white py-3 rounded-xl font-bold text-sm hover:bg-green-700 transition">
                                    ✅ Konfirmasi Persetujuan
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-10">
                    <div class="text-4xl mb-2">✨</div>
                    <p class="text-sm text-gray-500">Tidak ada permintaan pending.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Image Modal (hidden) --}}
    <div id="image-modal-overlay" class="hidden fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50" onclick="if(event.target.id==='image-modal-overlay') closeImageModal()">
        <div class="bg-white rounded-xl overflow-hidden max-w-4xl w-full mx-4" role="dialog" aria-modal="true">
            <div class="flex justify-end p-2">
                <button onclick="closeImageModal()" class="text-gray-600 px-3 py-1">Tutup ✖</button>
            </div>
            <div class="p-4" style="max-height:calc(100vh - 180px); overflow:auto;">
                <img id="image-modal-img" src="" alt="Struk" style="max-height:calc(100vh - 220px); max-width:100%; object-fit:contain; display:block; margin:0 auto;" class="rounded-lg" />
            </div>
        </div>
    </div>

    @section('scripts')
        <script>
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
        </script>
    @endsection
@endsection