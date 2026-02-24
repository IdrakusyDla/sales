@extends('layout')

@section('content')
    <div class="px-5 py-6">
        {{-- BACK BUTTON & HEADER --}}
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('finance.dashboard') }}" class="text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold">Persetujuan Reimburse</h1>
                <p class="text-gray-500 text-sm">Daftar reimburse yang menunggu persetujuan Finance.</p>
            </div>
        </div>

        {{-- FILTERS --}}
        <form method="GET" action="{{ route('finance.reimburse.approval') }}" class="mb-4 space-y-2">
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
        @if($expenses->total() > 0)
            <form id="bulkApproveForm" action="{{ route('finance.reimburse.bulk_approve') }}" method="POST" class="mb-4">
                @csrf
                <div class="flex items-center justify-between bg-blue-50 rounded-xl p-3">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" id="selectAll" onchange="toggleAllCheckboxes()"
                            class="w-5 h-5 text-blue-600 rounded">
                        <span class="text-sm font-bold text-blue-700">Pilih Semua</span>
                    </label>
                    <button type="submit" onclick="return confirm('Setujui semua yang dipilih?')"
                        class="bg-green-600 text-white px-4 py-2 rounded-lg font-bold text-sm hover:bg-green-700">
                        ✅ Bulk Approve
                    </button>
                </div>
                <input type="hidden" name="expense_ids" value="">
                <input type="text" name="notes" placeholder="Catatan (opsional)..."
                    class="w-full border border-gray-300 rounded-lg p-2 text-sm mt-2">
            </form>
        @endif

        <div class="space-y-4 pb-24">
            @forelse($expenses as $expense)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
                    {{-- Checkbox untuk bulk action --}}
                    <div class="flex items-start gap-3">
                        <input type="checkbox" class="expense-checkbox mt-4 w-5 h-5 text-blue-600 rounded"
                            value="{{ $expense->id }}">

                        <div class="flex-1">
                            {{-- Header: Nama & Tanggal --}}
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <h3 class="font-bold text-gray-800">{{ $expense->user->name }}
                                        <span class="text-xs text-gray-500">({{ ucfirst($expense->user->role) }})</span>
                                    </h3>
                                    <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($expense->date)->format('d M Y') }}</p>
                                </div>
                                <span class="bg-yellow-100 text-yellow-700 text-[10px] font-bold px-2 py-1 rounded-full uppercase">
                                    Menunggu Finance
                                </span>
                            </div>

                            {{-- Approval History --}}
                            @if($expense->approved_by_spv_at || $expense->approved_by_hrd_at)
                                <div class="bg-green-50 border border-green-200 rounded-lg p-2 mb-3 text-xs">
                                    @if($expense->approved_by_spv_at)
                                        <p class="text-green-700">✅ Disetujui SPV: {{ $expense->approvedBySpv?->name ?? '-' }}
                                            ({{ \Carbon\Carbon::parse($expense->approved_by_spv_at)->format('d M H:i') }})</p>
                                    @endif
                                    @if($expense->approved_by_hrd_at)
                                        <p class="text-green-700">✅ Disetujui HRD: {{ $expense->approvedByHrd?->name ?? '-' }}
                                            ({{ \Carbon\Carbon::parse($expense->approved_by_hrd_at)->format('d M H:i') }})</p>
                                    @endif
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
                                        *Auto calculated: {{ number_format($expense->km_total, 2) }} KM
                                    </p>
                                @endif

                                {{-- Link Foto/Struk --}}
                                @if($expense->photo_receipt)
                                    <a href="{{ asset('storage/' . $expense->photo_receipt) }}" target="_blank"
                                        class="text-blue-500 text-xs flex items-center gap-1 mt-2 hover:underline">
                                        📎 Lihat Struk/Bukti
                                    </a>
                                @endif
                            </div>

                            {{-- Revision Info --}}
                            @if($expense->revision_count > 0)
                                <div class="bg-orange-50 border border-orange-200 rounded-lg p-2 mb-3">
                                    <p class="text-xs text-orange-700">🔄 Revisi ke-{{ $expense->revision_count }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="space-y-3 mt-3">
                        {{-- Tombol Tolak & Setuju --}}
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

                        {{-- Form Reject --}}
                        <div id="reject-form-{{ $expense->id }}" class="hidden bg-red-50 border border-red-200 rounded-xl p-4">
                            <div class="flex items-center justify-between mb-3">
                                <p class="font-bold text-red-800 text-sm">Alasan Penolakan</p>
                                <button type="button" onclick="hideAllForms({{ $expense->id }})"
                                    class="text-gray-500 hover:text-gray-700 text-xs">
                                    ← Batal
                                </button>
                            </div>

                            <form action="{{ route('finance.reimburse.reject', $expense->id) }}" method="POST"
                                onsubmit="return confirm('Tolak reimburse ini?')">
                                @csrf
                                <div class="space-y-3">
                                    <input type="text" name="rejection_note" placeholder="Masukkan alasan penolakan..." required
                                        class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-red-300 focus:border-red-400">

                                    <button type="submit"
                                        class="w-full bg-red-600 text-white py-3 rounded-xl font-bold text-sm hover:bg-red-700 transition">
                                        Konfirmasi Penolakan
                                    </button>
                                </div>
                            </form>
                        </div>

                        {{-- Form Approve --}}
                        <div id="approve-form-{{ $expense->id }}"
                            class="hidden bg-green-50 border border-green-200 rounded-xl p-4">
                            <div class="flex items-center justify-between mb-3">
                                <p class="font-bold text-green-800 text-sm">Konfirmasi Persetujuan</p>
                                <button type="button" onclick="hideAllForms({{ $expense->id }})"
                                    class="text-gray-500 hover:text-gray-700 text-xs">
                                    ← Batal
                                </button>
                            </div>

                            <form action="{{ route('finance.reimburse.approve', $expense->id) }}" method="POST">
                                @csrf
                                <p class="text-sm text-green-700 mb-3">Apakah Anda yakin ingin menyetujui reimburse ini?</p>
                                <input type="text" name="notes" placeholder="Catatan (opsional)..."
                                    class="w-full border border-gray-300 rounded-lg p-2 text-sm mb-3">
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

        {{-- Pagination --}}
        @if($expenses->hasPages())
            <div class="flex justify-center mt-6">
                {{ $expenses->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

    @section('scripts')
        <script>
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
