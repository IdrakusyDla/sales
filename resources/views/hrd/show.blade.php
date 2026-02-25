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
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg> Aktivitas ({{ count($history) }})
            </button>
            <button onclick="switchTab('expense')" id="tab-btn-expense"
                class="flex-1 py-2 text-xs font-bold rounded-lg text-gray-500 hover:bg-gray-200 transition-all">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Keuangan ({{ count($expenses) }})
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
                            <p class="text-[10px] text-red-500 mt-1 font-bold"><svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg> Kendala: {{ $h->notes }}</p>
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
                                    <span class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center shrink-0 inline-flex mr-1"><svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg></span> Bensin
                                @elseif($ex->type == 'parking')
                                    <span class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center shrink-0 inline-flex mr-1"><svg class="w-3.5 h-3.5 text-blue-500" version="1.1" id="_x32_" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="-40 -40 592 592" xml:space="preserve" fill="currentColor"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <path d="M404.751,54.102C371.523,20.771,324.986-0.026,274.178,0h-90.85h-8.682H53.16v512h130.167V369.324h90.85 c50.808,0.026,97.333-20.771,130.573-54.074c33.331-33.229,54.115-79.78,54.089-130.575 C458.866,133.854,438.082,87.329,404.751,54.102z M321.923,232.394c-12.408,12.305-28.919,19.754-47.745,19.779h-90.85V117.15 h90.85c18.826,0.026,35.338,7.474,47.732,19.779c12.318,12.408,19.754,28.906,19.779,47.745 C341.664,203.488,334.228,219.988,321.923,232.394z"></path> </g> </g></svg></span> Parkir
                                @else
                                    <span class="w-6 h-6 rounded-full bg-green-100 flex items-center justify-center shrink-0 inline-flex mr-1"><svg class="w-3.5 h-3.5 text-green-500" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="-20 -20 440 440" xml:space="preserve" fill="currentColor"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <g> <path d="M295.225,142.697c-9.9-44.668-19.801-89.336-29.707-134.003c-16.718,0-33.435,0-50.15,0 c2.389,44.668,4.781,89.336,7.172,134.003H295.225z"></path> <path d="M226.354,214.003c3.145,58.703,6.286,117.404,9.426,176.107c38.094,0,76.188,0,114.281,0 c-13.014-58.702-26.021-117.404-39.029-176.107H226.354z"></path> <path d="M183.435,8.694c-16.716,0-33.434,0-50.149,0c-9.902,44.667-19.798,89.335-29.698,134.003h72.682 C178.656,98.029,181.043,53.361,183.435,8.694z"></path> <path d="M48.742,390.11c38.096,0,76.188,0,114.281,0c3.152-58.702,6.293-117.404,9.43-176.107H87.785 C74.775,272.706,61.763,331.409,48.742,390.11z"></path> <path d="M394.176,161.212H4.628c-2.556,0-4.628,2.072-4.628,4.628v25.02c0,2.556,2.072,4.628,4.628,4.628h25.048v37.476 c0,2.556,2.071,4.629,4.627,4.629h24.996c2.117,0,3.964-1.438,4.484-3.488l9.818-38.615h251.602l9.816,38.615 c0.52,2.052,2.369,3.488,4.486,3.488h24.992c2.559,0,4.629-2.073,4.629-4.629v-37.476h25.049c2.557,0,4.629-2.072,4.629-4.628 v-25.02C398.805,163.284,396.732,161.212,394.176,161.212z"></path> </g> </g> </g></svg></span> E-Toll
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
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a4 4 0 11-8 0 4 4 0 018 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M23 21v-2a4 4 0 00-3-3.87"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 3.13a4 4 0 010 7.75"></path></svg> Lihat Foto KM
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
