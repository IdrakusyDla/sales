@extends('layout')

@section('content')
    <div class="px-5 md:px-8 py-6 md:py-8">
        @php
            $isScoped = !empty($targetUser);
            $typeMeta = [
                'fuel' => ['label' => 'Bahan Bakar', 'bg' => 'bg-blue-100', 'text' => 'text-blue-600'],
                'parking' => ['label' => 'Parkir', 'bg' => 'bg-blue-100', 'text' => 'text-blue-600'],
                'hotel' => ['label' => 'Hotel', 'bg' => 'bg-purple-100', 'text' => 'text-purple-600'],
                'toll' => ['label' => 'Tol', 'bg' => 'bg-green-100', 'text' => 'text-green-600'],
                'transport' => ['label' => 'Transport', 'bg' => 'bg-orange-100', 'text' => 'text-orange-600'],
                'other' => ['label' => 'Lainnya', 'bg' => 'bg-gray-200', 'text' => 'text-gray-600'],
            ];
            $statusMeta = [
                'approved' => ['label' => 'Disetujui', 'bg' => 'bg-green-100', 'text' => 'text-green-700'],
                'rejected_permanent' => ['label' => 'Ditolak', 'bg' => 'bg-red-100', 'text' => 'text-red-700'],
            ];
        @endphp

        {{-- BACK BUTTON & HEADER --}}
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ $isScoped ? route('finance.show.user', $targetUser->id) : route('finance.dashboard') }}" class="text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold">
                    Arsip Reimburse{{ $isScoped ? ' — ' . $targetUser->name : '' }}
                </h1>
                <p class="text-gray-500 text-sm">
                    @if($isScoped)
                        Riwayat reimburse final {{ $targetUser->name }} (disetujui & ditolak).
                    @else
                        Riwayat reimburse yang sudah diselesaikan Finance.
                    @endif
                </p>
            </div>
        </div>

        @if($isScoped)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="shrink-0 w-9 h-9 rounded-full bg-green-100 text-green-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </span>
                    <p class="text-sm text-gray-600 min-w-0">
                        Menampilkan arsip dari <span class="font-bold text-gray-800">{{ $targetUser->name }}</span> saja.
                    </p>
                </div>
                <a href="{{ route('finance.reimburse.archive') }}"
                    class="shrink-0 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-xl font-bold text-sm shadow-sm flex items-center justify-center gap-1.5 whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                    </svg>
                    Semua Karyawan
                </a>
            </div>
        @endif

        {{-- STATISTIK RINGKAS --}}
        <div class="grid grid-cols-3 gap-3 mb-4">
            <div class="bg-green-50 rounded-xl p-3 md:p-4">
                <p class="text-[10px] md:text-xs text-gray-600 mb-1">Disetujui</p>
                <p class="text-lg md:text-2xl font-bold text-green-600">{{ $archiveStats['approved_count'] }}</p>
            </div>
            <div class="bg-red-50 rounded-xl p-3 md:p-4">
                <p class="text-[10px] md:text-xs text-gray-600 mb-1">Ditolak</p>
                <p class="text-lg md:text-2xl font-bold text-red-600">{{ $archiveStats['rejected_count'] }}</p>
            </div>
            <div class="bg-blue-50 rounded-xl p-3 md:p-4">
                <p class="text-[10px] md:text-xs text-gray-600 mb-1">Total Disetujui</p>
                <p class="text-sm md:text-lg font-bold text-blue-600">Rp {{ number_format($archiveStats['approved_total'], 0, ',', '.') }}</p>
            </div>
        </div>

        {{-- FILTERS --}}
        <div class="bg-white rounded-2xl md:rounded-[2rem] shadow-sm border border-gray-100 p-4 md:p-8 mb-4 md:mb-8">
            <form method="GET" action="{{ route('finance.reimburse.archive') }}" class="filter-form">
                @if($isScoped)
                    <input type="hidden" name="user_id" value="{{ $targetUser->id }}">
                @endif
                <div class="grid grid-cols-2 xl:flex xl:items-end gap-3 xl:gap-4">
                    <div class="xl:w-40 xl:flex-none">
                        <label class="block text-xs md:text-sm font-bold text-gray-600 md:text-gray-700 mb-1 md:mb-2 md:uppercase md:tracking-wider">Dari Tanggal</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                            class="w-full border border-gray-300 md:border-gray-200 md:bg-gray-50 rounded-xl p-3 md:p-4 text-sm focus:ring-2 focus:ring-blue-500 md:focus:border-blue-500">
                    </div>
                    <div class="xl:w-40 xl:flex-none">
                        <label class="block text-xs md:text-sm font-bold text-gray-600 md:text-gray-700 mb-1 md:mb-2 md:uppercase md:tracking-wider">Sampai Tanggal</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                            class="w-full border border-gray-300 md:border-gray-200 md:bg-gray-50 rounded-xl p-3 md:p-4 text-sm focus:ring-2 focus:ring-blue-500 md:focus:border-blue-500">
                    </div>
                    @if(!$isScoped)
                        <div class="col-span-2 xl:flex-1">
                            <label class="w-48 block text-xs md:text-sm font-bold text-gray-600 md:text-gray-700 mb-1 md:mb-2 md:uppercase md:tracking-wider">Karyawan</label>
                            <select name="user_id"
                                class="w-full border border-gray-300 md:border-gray-200 md:bg-gray-50 rounded-xl p-3 md:p-4 text-sm focus:ring-2 focus:ring-blue-500 md:focus:border-blue-500">
                                <option value="">Semua Karyawan</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="col-span-2 xl:flex-1">
                        <label class="w-36 block text-xs md:text-sm font-bold text-gray-600 md:text-gray-700 mb-1 md:mb-2 md:uppercase md:tracking-wider">Status</label>
                        <select name="status"
                            class="w-full border border-gray-300 md:border-gray-200 md:bg-gray-50 rounded-xl p-3 md:p-4 text-sm focus:ring-2 focus:ring-blue-500 md:focus:border-blue-500">
                            <option value="">Semua Status</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                            <option value="rejected_permanent" {{ request('status') == 'rejected_permanent' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>
                    <div class="col-span-2 xl:flex-1">
                        <label class="w-36 block text-xs md:text-sm font-bold text-gray-600 md:text-gray-700 mb-1 md:mb-2 md:uppercase md:tracking-wider">Tipe</label>
                        <select name="type"
                            class="w-full border border-gray-300 md:border-gray-200 md:bg-gray-50 rounded-xl p-3 md:p-4 text-sm focus:ring-2 focus:ring-blue-500 md:focus:border-blue-500">
                            <option value="">Semua Tipe</option>
                            <option value="fuel" {{ request('type') == 'fuel' ? 'selected' : '' }}>Bahan Bakar</option>
                            <option value="parking" {{ request('type') == 'parking' ? 'selected' : '' }}>Parkir</option>
                            <option value="hotel" {{ request('type') == 'hotel' ? 'selected' : '' }}>Hotel</option>
                            <option value="toll" {{ request('type') == 'toll' ? 'selected' : '' }}>Toll</option>
                            <option value="transport" {{ request('type') == 'transport' ? 'selected' : '' }}>Transport</option>
                            <option value="other" {{ request('type') == 'other' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                    </div>
                    <div class="col-span-2 xl:flex-none">
                        <button type="submit"
                            class="w-full md:px-10 xl:px-5 xl:w-auto bg-blue-600 hover:bg-blue-700 text-white py-3 md:py-4 rounded-xl font-bold text-sm shadow-sm md:shadow-md md:shadow-blue-600/20 md:active:scale-95 flex items-center justify-center gap-2 md:h-[58px] md:whitespace-nowrap">
                            <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                            </svg>
                            <span class="md:hidden">Filter</span>
                            <span class="hidden md:inline">Terapkan Filter</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- DAFTAR KARTU (1 DailyLog = 1 Kartu) --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pb-24">
            @forelse($groups as $items)
                @php
                    $first = $items->first();
                    $dlId = $first->daily_log_id;
                    $total = $items->sum('amount');
                    $statuses = $items->pluck('status')->unique();
                    if ($statuses->count() === 1) {
                        $cardStatus = $statuses->first();
                    } else {
                        $cardStatus = 'mixed';
                    }
                @endphp
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition flex flex-col">
                    {{-- HEADER KARTU --}}
                    <div class="flex items-start gap-3 mb-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-start gap-2">
                                <div class="min-w-0">
                                    <h3 class="font-bold text-gray-800 truncate">
                                        {{ $first->user->name }}
                                        <span class="text-xs text-gray-500 font-normal">({{ ucfirst($first->user->role) }})</span>
                                    </h3>
                                    <p class="text-xs text-blue-600 font-bold flex items-center gap-1 mt-1">
                                        <svg class="w-3.5 h-3.5 inline text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        {{ \Carbon\Carbon::parse($first->date)->format('d M Y') }}
                                    </p>
                                </div>
                                @php
                                    $cardBadge = $cardStatus === 'mixed'
                                        ? ['label' => 'Campuran', 'bg' => 'bg-gray-200', 'text' => 'text-gray-700']
                                        : ($statusMeta[$cardStatus] ?? null);
                                @endphp
                                @if($cardBadge)
                                    <span class="{{ $cardBadge['bg'] }} {{ $cardBadge['text'] }} text-[10px] font-bold px-2 py-1 rounded-full uppercase whitespace-nowrap">
                                        {{ $cardBadge['label'] }}
                                    </span>
                                @endif
                            </div>

                            {{-- TOTAL & JUMLAH ITEM --}}
                            <div class="mt-2 inline-flex items-center gap-2 bg-green-50 border border-green-100 rounded-full pl-2 pr-3 py-1">
                                <span class="w-5 h-5 rounded-full bg-green-500 text-white text-[10px] font-bold flex items-center justify-center">{{ $items->count() }}</span>
                                <span class="text-xs font-bold text-green-700">Rp {{ number_format($total, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- INFO KEPUTUSAN FINANCE --}}
                    @if($first->status === 'approved' && $first->approved_by_finance_at)
                        <div class="bg-green-50 border border-green-200 rounded-lg p-2 mb-3 text-xs space-y-0.5">
                            <p class="text-green-700"><svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Finance: {{ $first->approvedByFinance?->name ?? '-' }} ({{ \Carbon\Carbon::parse($first->approved_by_finance_at)->format('d M Y H:i') }})</p>
                        </div>
                    @elseif($first->status === 'rejected_permanent' && $first->rejection_note)
                        <div class="bg-red-50 border border-red-200 rounded-lg p-2 mb-3 text-xs">
                            <p class="text-red-700 font-bold flex items-center gap-1 mb-0.5"><svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg> Ditolak Permanen</p>
                            <p class="text-red-600 italic break-words">"{{ $first->rejection_note }}"</p>
                        </div>
                    @endif

                    {{-- DAFTAR ITEM --}}
                    <div class="space-y-2 flex-1">
                        @foreach($items as $expense)
                            @php
                                $meta = $typeMeta[$expense->type] ?? $typeMeta['other'];
                                $itemStatus = $statusMeta[$expense->status] ?? null;
                            @endphp
                            <div class="bg-gray-50 rounded-xl p-3">
                                <div class="flex items-start gap-3">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between gap-2">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <span class="w-7 h-7 rounded-full {{ $meta['bg'] }} flex items-center justify-center shrink-0">
                                                    <span class="text-xs font-black {{ $meta['text'] }} uppercase">{{ strtoupper(mb_substr($meta['label'], 0, 1)) }}</span>
                                                </span>
                                                <span class="font-bold text-sm text-gray-800 truncate">{{ $meta['label'] }}</span>
                                            </div>
                                            <span class="font-bold text-sm text-green-600 whitespace-nowrap">Rp {{ number_format($expense->amount, 0, ',', '.') }}</span>
                                        </div>

                                        @if($expense->note)
                                            <p class="text-xs text-gray-500 italic mt-1 break-words">"{{ $expense->note }}"</p>
                                        @endif

                                        <div class="flex flex-wrap items-center gap-2 mt-1.5">
                                            @if($itemStatus)
                                                <span class="inline-flex items-center gap-1 text-[10px] font-bold {{ $itemStatus['bg'] }} {{ $itemStatus['text'] }} rounded px-1.5 py-0.5 uppercase">
                                                    {{ $itemStatus['label'] }}
                                                </span>
                                            @endif

                                            @if($expense->photo_receipt)
                                                <button type="button" onclick="openImageModal('{{ route('expenses.receipt.show', $expense->id) }}')"
                                                    class="text-blue-500 text-xs flex items-center gap-1 hover:underline">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg> Struk
                                                </button>
                                            @else
                                                <span class="inline-flex items-center gap-1 text-orange-700 text-[10px] bg-orange-50 border border-orange-200 rounded px-1.5 py-0.5">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                                    Tanpa struk
                                                </span>
                                            @endif

                                            @if($expense->status === 'rejected_permanent' && $expense->rejection_note)
                                                <span class="text-[10px] text-red-600 italic truncate max-w-[160px]" title="{{ $expense->rejection_note }}">"{{ $expense->rejection_note }}"</span>
                                            @endif

                                            @if($expense->revision_count > 0)
                                                <span class="inline-flex items-center gap-1 text-orange-700 text-[10px] bg-orange-50 border border-orange-200 rounded px-1.5 py-0.5">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                                    Revisi ke-{{ $expense->revision_count }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- KUNJUNGAN + LINK DETAIL --}}
                    @if($first->dailyLog && $first->dailyLog->visits->count() > 0)
                        <div class="mt-3 pt-2 border-t border-gray-100">
                            <p class="text-[10px] font-bold text-gray-400 uppercase mb-1.5 tracking-wider">Kunjungan Toko:</p>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($first->dailyLog->visits as $visit)
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

                    <a href="{{ route('sales.history.detail', $dlId) }}"
                        class="mt-3 text-xs text-indigo-600 font-bold flex items-center justify-between hover:text-indigo-700">
                        <span>Lihat detail absen lengkap</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </a>
                </div>
            @empty
                <div class="md:col-span-2 text-center py-10">
                    <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <p class="text-sm text-gray-500">Belum ada reimburse yang diselesaikan.</p>
                </div>
            @endforelse
        </div>

        {{-- PAGINATION --}}
        @if($paginator->hasPages())
            <div class="flex justify-center mt-6">
                {{ $paginator->appends(request()->query())->links() }}
            </div>
        @endif
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
            function openImageModal(url) {
                const overlay = document.getElementById('image-modal-overlay');
                document.getElementById('image-modal-img').src = url;
                overlay.classList.remove('hidden');
            }
            function closeImageModal() {
                const overlay = document.getElementById('image-modal-overlay');
                document.getElementById('image-modal-img').src = '';
                overlay.classList.add('hidden');
            }
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    const el = document.getElementById('image-modal-overlay');
                    if (el && !el.classList.contains('hidden')) el.classList.add('hidden');
                }
            });
        </script>
    @endsection
@endsection
