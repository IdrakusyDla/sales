@extends('layout')
@section('content')
    {{-- HEADER --}}
    <div class="bg-white p-4 sticky top-0 z-30 border-b border-gray-200 shadow-sm flex items-center gap-3">
        <a href="{{ route('dashboard') }}" class="bg-gray-100 p-2 rounded-full text-gray-600 hover:bg-gray-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div>
            <h1 class="font-bold text-lg text-gray-800">{{ $targetUser->name }}</h1>
            <p class="text-xs text-gray-500">Detail Karyawan</p>
        </div>
    </div>

    <div class="px-5 py-4 pb-24">

        {{-- FILTER TANGGAL --}}
        <div class="bg-gray-50 p-3 rounded-xl border border-gray-200 mb-4">
            <form method="GET" action="{{ route('hrd.show', $targetUser->id) }}">
                <div class="flex gap-2">
                    <input type="date" name="start_date" value="{{ request('start_date') }}"
                        class="flex-1 p-2 text-xs border rounded-lg">
                    <input type="date" name="end_date" value="{{ request('end_date') }}"
                        class="flex-1 p-2 text-xs border rounded-lg">
                    <button type="submit" class="bg-indigo-600 text-white px-3 py-2 rounded-lg text-xs font-bold shadow">
                        Go
                    </button>
                </div>
                @if (request()->has('start_date'))
                    <a href="{{ route('hrd.show', $targetUser->id) }}"
                        class="block text-center text-xs text-red-500 mt-2 underline">Reset</a>
                @endif
            </form>
        </div>

        {{-- TAB SWITCHER --}}
        <div class="flex bg-gray-100 p-1 rounded-xl mb-4">
            <button onclick="switchTab('activity')" id="tab-btn-activity"
                class="flex-1 py-2 text-xs font-bold rounded-lg bg-white text-indigo-600 shadow-sm transition-all">
                📍 Aktivitas ({{ count($history) }})
            </button>
            <button onclick="switchTab('expense')" id="tab-btn-expense"
                class="flex-1 py-2 text-xs font-bold rounded-lg text-gray-500 hover:bg-gray-200 transition-all">
                💸 Keuangan ({{ count($expenses) }})
            </button>
        </div>

        {{-- CONTENT 1: AKTIVITAS (ABSENSI) --}}
        <div id="content-activity" class="space-y-4">
            @forelse($history as $h)
                <div class="bg-white p-3 rounded-xl shadow-sm border border-gray-100 flex items-start gap-3">
                    <div class="w-16 h-16 bg-gray-200 rounded-lg overflow-hidden shrink-0 cursor-pointer border border-gray-300">
                        <button type="button" onclick="openImageModal('{{ route('files.visit.photo', $h->id) }}')" class="w-full h-full block">
                            <img src="{{ asset('storage/' . $h->photo_path) }}" class="w-full h-full object-cover">
                        </button>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-start">
                            <h4 class="font-bold text-sm text-gray-800 truncate">{{ $h->client_name }}</h4>
                            <span
                                class="text-[10px] {{ $h->type == 'IN' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }} px-1.5 py-0.5 rounded font-bold">{{ $h->type }}</span>
                        </div>
                        <span class="text-[10px] text-gray-500 block mb-1">
                            {{ \Carbon\Carbon::parse($h->time)->format('d M • H:i') }}
                        </span>
                        <p class="text-xs text-gray-600 line-clamp-2 italic">"{{ $h->notes ?? '-' }}"</p>
                        @if (isset($h->status) && $h->status == 'failed')
                            <p class="text-[10px] text-red-500 mt-1 font-bold">❌ Kendala: {{ $h->notes }}</p>
                        @endif
                        <a href="http://maps.google.com/maps?q={{ $h->lat }},{{ $h->long }}" target="_blank"
                            class="text-[10px] text-blue-500 underline mt-1 block">Lihat Lokasi</a>
                    </div>
                </div>
            @empty
                <div class="text-center py-10 text-gray-400 text-sm">Tidak ada data aktivitas.</div>
            @endforelse
        </div>

        {{-- CONTENT 2: KEUANGAN (EXPENSE) --}}
        <div id="content-expense" class="space-y-4 hidden">
            @forelse($expenses as $ex)
                <div class="bg-white p-3 rounded-xl shadow-sm border border-orange-100 flex items-start gap-3">
                    <div class="w-16 h-16 bg-gray-200 rounded-lg overflow-hidden shrink-0 cursor-pointer border border-gray-300">
                        <button type="button" onclick="openImageModal('{{ route('expenses.receipt.show', $ex->id) }}')" class="w-full h-full block">
                            <img src="{{ asset('storage/' . $ex->photo_receipt) }}" class="w-full h-full object-cover">
                        </button>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-start">
                            <h4 class="font-bold text-sm text-gray-800">
                                @if ($ex->type == 'gas')
                                    ⛽ Bensin
                                @elseif($ex->type == 'parking')
                                    🅿️ Parkir
                                @else
                                    🛣️ E-Toll
                                @endif
                            </h4>
                            <span class="text-xs font-bold text-orange-600">Rp
                                {{ number_format($ex->amount, 0, ',', '.') }}</span>
                        </div>
                        <span class="text-[10px] text-gray-500 block mb-1">
                            {{ \Carbon\Carbon::parse($ex->created_at)->format('d M • H:i') }}
                        </span>
                        <p class="text-xs text-gray-600 line-clamp-2">"{{ $ex->note ?? '-' }}"</p>

                        @if ($ex->type == 'gas' && $ex->photo_km)
                            <button onclick="openImageModal('{{ route('expenses.receipt.show', $ex->id) }}')"
                                class="mt-2 text-[10px] bg-gray-100 px-2 py-1 rounded border border-gray-300 flex items-center gap-1">
                                <span>🚗</span> Lihat Foto KM
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-10 text-gray-400 text-sm">Tidak ada data pengeluaran.</div>
            @endforelse
        </div>

    </div>

    {{-- MODAL & SCRIPT --}}
    <div id="imageModal"
        class="fixed inset-0 z-[100] bg-black/90 hidden flex items-center justify-center p-4 backdrop-blur-sm"
        onclick="this.classList.add('hidden')">
        <img id="modalImage" src="" class="max-w-full max-h-[90vh] rounded-lg shadow-2xl object-contain">
    </div>

    <script>
        function openModal(src) {
            document.getElementById('modalImage').src = src;
            document.getElementById('imageModal').classList.remove('hidden');
        }

        function switchTab(tab) {
            // Hide all
            document.getElementById('content-activity').classList.add('hidden');
            document.getElementById('content-expense').classList.add('hidden');
            document.getElementById('tab-btn-activity').className =
                "flex-1 py-2 text-xs font-bold rounded-lg text-gray-500 hover:bg-gray-200 transition-all";
            document.getElementById('tab-btn-expense').className =
                "flex-1 py-2 text-xs font-bold rounded-lg text-gray-500 hover:bg-gray-200 transition-all";

            // Show selected
            document.getElementById('content-' + tab).classList.remove('hidden');
            document.getElementById('tab-btn-' + tab).className =
                "flex-1 py-2 text-xs font-bold rounded-lg bg-white text-indigo-600 shadow-sm transition-all";
        }
    </script>
@endsection
