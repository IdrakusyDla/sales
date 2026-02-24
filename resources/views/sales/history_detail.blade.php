@extends('layout')
@section('content')
    <div class="px-5 py-6">
        {{-- HEADER --}}
        <div class="flex items-center gap-3 mb-6">
            @php
                $backUrl = route('sales.history');
                if (Auth::user()->role === 'supervisor') {
                    // Cek apakah supervisor melihat datanya sendiri atau bawahan
                    if ($dailyLog->user_id === Auth::user()->id) {
                        $backUrl = route('sales.history'); // Liat history sendiri
                    } else {
                        $backUrl = route('supervisor.show.sales', $dailyLog->user_id); // Liat history bawahan
                    }
                } elseif (in_array(Auth::user()->role, ['hrd', 'it'])) {
                    $backUrl = route('hrd.show.user', $dailyLog->user_id);
                }
            @endphp
            <a href="{{ $backUrl }}" class="text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div class="flex-1">
                <h1 class="text-2xl font-bold">Detail Absen</h1>
                <p class="text-sm text-gray-600">{{ $dailyLog->user->name }} • {{ \Carbon\Carbon::parse($dailyLog->date)->format('d M Y') }}</p>
                {{-- INFO DEADLINE DI HEADER --}}
                @if($dailyLog->hasEnded())
                    @php
                        $deadline = \App\Models\Expense::calculateDeadline($dailyLog->date);
                        $isDeadlinePassed = \Carbon\Carbon::today()->gt($deadline);
                        $fuelExpense = $dailyLog->expenses->where('type', 'fuel')->where('is_auto_calculated', true)->first();
                        $hasIncompleteFuelReceipt = $fuelExpense && !$fuelExpense->photo_receipt;
                        $hasIncompleteExpenses = $dailyLog->expenses->whereNull('photo_receipt')->count() > 0;
                    @endphp
                    <div class="mt-2 {{ $isDeadlinePassed ? 'bg-red-50 border border-red-200' : ($hasIncompleteFuelReceipt || $hasIncompleteExpenses ? 'bg-orange-50 border border-orange-200' : 'bg-yellow-50 border border-yellow-200') }} rounded-lg p-2 inline-block">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">{{ $isDeadlinePassed ? '⚠️' : '⏰' }}</span>
                            <div>
                                <p class="text-xs font-bold {{ $isDeadlinePassed ? 'text-red-700' : ($hasIncompleteFuelReceipt || $hasIncompleteExpenses ? 'text-orange-700' : 'text-yellow-700') }}">
                                    @if($isDeadlinePassed)
                                        Batas Waktu Sudah Lewat
                                    @elseif($hasIncompleteFuelReceipt || $hasIncompleteExpenses)
                                        Batas Melengkapi Berkas • Belum Lengkap
                                    @else
                                        Batas Melengkapi Berkas
                                    @endif
                                </p>
                                <p class="text-xs {{ $isDeadlinePassed ? 'text-red-600' : ($hasIncompleteFuelReceipt || $hasIncompleteExpenses ? 'text-orange-600' : 'text-yellow-600') }} font-bold">
                                    Sampai: {{ \Carbon\Carbon::parse($deadline)->format('d M Y') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- STATUS CARD --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-4">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-xs text-gray-500 mb-1">Status</p>
                    <p class="font-bold text-gray-800">
                        @if($dailyLog->hasEnded())
                            Selesai
                        @else
                            Berlangsung
                        @endif
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500 mb-1">Waktu</p>
                    <p class="font-bold text-gray-800">
                        @if($dailyLog->hasStarted())
                            {{ \Carbon\Carbon::parse($dailyLog->start_time)->format('H:i') }}
                        @endif
                        @if($dailyLog->hasStarted() && $dailyLog->hasEnded())
                            -
                        @endif
                        @if($dailyLog->hasEnded())
                            {{ \Carbon\Carbon::parse($dailyLog->end_time)->format('H:i') }}
                        @endif
                    </p>
                </div>
                @if($dailyLog->total_km > 0)
                    <div class="text-right">
                        <p class="text-xs text-gray-500 mb-1">Total KM</p>
                        <p class="font-bold text-blue-600">{{ number_format($dailyLog->total_km, 2) }} KM</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- TAB NAVIGATION --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-2 mb-4">
            <div class="flex gap-2">
                <button id="tab-btn-summary" onclick="switchTab('summary')" 
                    class="flex-1 py-2 text-xs font-bold rounded-lg bg-blue-600 text-white shadow-sm transition-all">
                    📊 Ringkasan
                </button>
                <button id="tab-btn-absensi" onclick="switchTab('absensi')" 
                    class="flex-1 py-2 text-xs font-bold rounded-lg text-gray-500 hover:bg-gray-100 transition-all">
                    ✅ Absensi
                </button>
                <button id="tab-btn-kunjungan" onclick="switchTab('kunjungan')" 
                    class="flex-1 py-2 text-xs font-bold rounded-lg text-gray-500 hover:bg-gray-100 transition-all">
                    📍 Kunjungan
                </button>
                @if($dailyLog->expenses->count() > 0)
                    <button id="tab-btn-reimburse" onclick="switchTab('reimburse')" 
                        class="flex-1 py-2 text-xs font-bold rounded-lg text-gray-500 hover:bg-gray-100 transition-all">
                        💰 Reimburse
                    </button>
                @endif
            </div>
        </div>

        {{-- TAB CONTENT: RINGKASAN --}}
        <div id="content-summary" class="tab-content">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-4">
                <h2 class="font-bold text-lg mb-4">📊 Ringkasan</h2>
                
                <div class="space-y-4">
                    {{-- Info Odometer --}}
                    @if($dailyLog->start_odo_value && $dailyLog->end_odo_value)
                        <div class="bg-blue-50 rounded-xl p-4">
                            <p class="text-xs text-gray-600 mb-2">Odometer</p>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <p class="text-xs text-gray-500">Awal</p>
                                    <p class="text-sm font-bold text-blue-600">{{ number_format($dailyLog->start_odo_value, 2) }} KM</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Akhir</p>
                                    <p class="text-sm font-bold text-blue-600">{{ number_format($dailyLog->end_odo_value, 2) }} KM</p>
                                </div>
                            </div>
                            @if($dailyLog->total_km > 0)
                                <div class="mt-3 pt-3 border-t border-blue-200">
                                    <div class="flex justify-between items-center">
                                        <p class="text-sm font-bold text-gray-700">Total KM:</p>
                                        <p class="text-xl font-bold text-blue-600">{{ number_format($dailyLog->total_km, 2) }} KM</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Verifikasi Jarak (HRD/IT/SPV) --}}
                    @if(in_array(Auth::user()->role, ['hrd', 'it', 'supervisor']) && $dailyLog->lat && $dailyLog->end_lat)
                        <div class="bg-indigo-50 rounded-xl p-4 border border-indigo-200">
                            <h3 class="text-sm font-bold text-indigo-800 mb-3">Estimasi Jarak (Sistem)</h3>
                            
                            {{-- Hasil Verifikasi --}}
                             <div id="verified-distance-container" class="{{ $dailyLog->system_calculated_distance ? '' : 'hidden' }}">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-xs text-indigo-600">Jarak Terverifikasi:</span>
                                    <span class="text-lg font-bold text-indigo-700" id="verified-distance-value">
                                        {{ $dailyLog->system_calculated_distance ? number_format($dailyLog->system_calculated_distance, 2) . ' KM' : '-' }}
                                    </span>
                                </div>
                                <div class="text-xs text-indigo-500 italic" id="verification-status">
                                    {{ $dailyLog->system_calculated_distance ? 'Sudah diverifikasi' : 'Belum diverifikasi' }}
                                </div>
                            </div>

                            {{-- Tombol Verifikasi --}}
                             <div id="verification-action-container" class="mt-2 text-center">

                                <button onclick="verifyDistance()" id="btn-verify" 
                                    class="w-full bg-indigo-600 text-white py-2 rounded-lg font-bold text-xs hover:bg-indigo-700 transition flex justify-center items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                                    <span id="btn-verify-text">{{ $dailyLog->system_calculated_distance ? 'Hitung Ulang' : 'Verifikasi Jarak' }}</span>
                                </button>
                            </div>

                            {{-- Map Container --}}
                            <div id="map-container" class="hidden mt-3">
                                <div id="map" class="w-full h-48 rounded-lg border border-gray-300"></div>
                                <p class="text-[10px] text-gray-500 mt-1">*Perhitungan menggunakan rute jalan tercepat (via OSRM, bukan garis lurus)</p>
                            </div>
                        </div>
                    @endif

                    {{-- Rencana Kunjungan --}}
                    @if($dailyLog->daily_plan)
                        <div>
                            <p class="text-xs font-bold text-gray-600 mb-2">Rencana Kunjungan</p>
                            <p class="text-sm text-gray-800 bg-gray-50 rounded-xl p-3">{{ $dailyLog->daily_plan }}</p>
                        </div>
                    @endif

                    {{-- Statistik Kunjungan --}}
                    @if($dailyLog->visits->count() > 0)
                        <div class="grid grid-cols-3 gap-2">
                            <div class="bg-green-50 rounded-xl p-3 text-center">
                                <p class="text-xs text-gray-600 mb-1">Berhasil</p>
                                <p class="text-xl font-bold text-green-600">{{ $dailyLog->visits->where('status', 'completed')->count() }}</p>
                            </div>
                            <div class="bg-red-50 rounded-xl p-3 text-center">
                                <p class="text-xs text-gray-600 mb-1">Gagal</p>
                                <p class="text-xl font-bold text-red-600">{{ $dailyLog->visits->where('status', 'failed')->count() }}</p>
                            </div>
                            <div class="bg-yellow-50 rounded-xl p-3 text-center">
                                <p class="text-xs text-gray-600 mb-1">Pending</p>
                                <p class="text-xl font-bold text-yellow-600">{{ $dailyLog->visits->where('status', 'pending')->count() }}</p>
                            </div>
                        </div>
                    @endif

                    {{-- Total Reimburse --}}
                    @if($dailyLog->expenses->count() > 0)
                        <div class="bg-green-50 rounded-xl p-4 border-2 border-green-200">
                            <div class="flex justify-between items-center">
                                <p class="font-bold text-gray-800">Total Reimburse:</p>
                                <p class="text-2xl font-bold text-green-600">Rp {{ number_format($dailyLog->expenses->sum('amount'), 0, ',', '.') }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- TAB CONTENT: ABSENSI --}}
        <div id="content-absensi" class="tab-content hidden">
            {{-- ABSEN MASUK --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-4">
                <h2 class="font-bold text-lg mb-4 flex items-center gap-2">
                    <span class="bg-green-100 text-green-600 w-8 h-8 rounded-full flex items-center justify-center text-sm">✓</span>
                    Absen Masuk
                </h2>
                
                @if($dailyLog->hasStarted())
                    <div class="space-y-4">
                        {{-- Foto Selfie --}}
                        @if($dailyLog->start_photo)
                            <div>
                                <p class="text-xs font-bold text-gray-600 mb-2">Foto Selfie</p>
                                <button type="button" onclick="openImageModal('{{ route('files.daily.photo', [$dailyLog->id, 'start_photo']) }}')" class="block w-full p-0 bg-transparent border-0 focus:outline-none">
                                    <img src="{{ route('files.daily.photo', [$dailyLog->id, 'start_photo']) }}" alt="Foto Absen Masuk" 
                                        class="w-full rounded-xl border border-gray-200">
                                </button>
                            </div>
                        @endif

                        {{-- Foto Odometer --}}
                        @if($dailyLog->start_odo_photo)
                            <div>
                                <p class="text-xs font-bold text-gray-600 mb-2">Foto Odometer Awal</p>
                                <button type="button" onclick="openImageModal('{{ route('files.daily.photo', [$dailyLog->id, 'start_odo_photo']) }}')" class="block w-full p-0 bg-transparent border-0 focus:outline-none">
                                    <img src="{{ route('files.daily.photo', [$dailyLog->id, 'start_odo_photo']) }}" alt="Foto Odometer Awal" 
                                        class="w-full rounded-xl border border-gray-200">
                                </button>
                            </div>
                        @endif

                        {{-- Info Odometer --}}
                        @if($dailyLog->start_odo_value)
                            <div class="bg-blue-50 rounded-xl p-3">
                                <p class="text-xs text-gray-600 mb-1">Odometer Awal</p>
                                <p class="text-lg font-bold text-blue-600">{{ number_format($dailyLog->start_odo_value, 2) }} KM</p>
                            </div>
                        @endif

                        {{-- Lokasi --}}
                        @if($dailyLog->lat && $dailyLog->long)
                            <div>
                                <p class="text-xs font-bold text-gray-600 mb-2">Lokasi Absen Masuk</p>
                                <div class="bg-gray-50 rounded-xl p-3 border border-gray-200 mb-2">
                                    <p class="text-xs text-gray-500 mb-2">{{ $dailyLog->lat }}, {{ $dailyLog->long }}</p>
                                    <iframe 
                                        src="https://www.google.com/maps?q={{ $dailyLog->lat }},{{ $dailyLog->long }}&output=embed&zoom=15" 
                                        width="100%" 
                                        height="200" 
                                        style="border:0; border-radius: 8px;" 
                                        allowfullscreen="" 
                                        loading="lazy" 
                                        referrerpolicy="no-referrer-when-downgrade">
                                    </iframe>
                                </div>
                                <a href="https://www.google.com/maps?q={{ $dailyLog->lat }},{{ $dailyLog->long }}" 
                                    target="_blank" 
                                    class="flex items-center gap-2 text-blue-600 text-xs hover:text-blue-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                    </svg>
                                    Buka di Google Maps
                                </a>
                            </div>
                        @endif
                    </div>
                @else
                    <p class="text-sm text-gray-500">Belum absen masuk</p>
                @endif
            </div>

            {{-- ABSEN KELUAR --}}
            @if($dailyLog->hasEnded())
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-24">
                    <h2 class="font-bold text-lg mb-4 flex items-center gap-2">
                        <span class="bg-red-100 text-red-600 w-8 h-8 rounded-full flex items-center justify-center text-sm">→</span>
                        Absen Keluar
                    </h2>
                    
                    <div class="space-y-4">
                        {{-- Foto Selfie --}}
                        @if($dailyLog->end_photo)
                            <div>
                                <p class="text-xs font-bold text-gray-600 mb-2">Foto Selfie</p>
                                <button type="button" onclick="openImageModal('{{ route('files.daily.photo', [$dailyLog->id, 'end_photo']) }}')" class="block w-full p-0 bg-transparent border-0 focus:outline-none">
                                    <img src="{{ route('files.daily.photo', [$dailyLog->id, 'end_photo']) }}" alt="Foto Absen Keluar" 
                                        class="w-full rounded-xl border border-gray-200">
                                </button>
                            </div>
                        @endif

                        {{-- Foto Odometer --}}
                        @if($dailyLog->end_odo_photo)
                            <div>
                                <p class="text-xs font-bold text-gray-600 mb-2">Foto Odometer Akhir</p>
                                <button type="button" onclick="openImageModal('{{ route('files.daily.photo', [$dailyLog->id, 'end_odo_photo']) }}')" class="block w-full p-0 bg-transparent border-0 focus:outline-none">
                                    <img src="{{ route('files.daily.photo', [$dailyLog->id, 'end_odo_photo']) }}" alt="Foto Odometer Akhir" 
                                        class="w-full rounded-xl border border-gray-200">
                                </button>
                            </div>
                        @endif

                        {{-- Info Odometer & Total KM --}}
                        @if($dailyLog->end_odo_value)
                            <div class="bg-blue-50 rounded-xl p-3 space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-xs text-gray-600">Odometer Akhir:</span>
                                    <span class="text-sm font-bold text-blue-600">{{ number_format($dailyLog->end_odo_value, 2) }} KM</span>
                                </div>
                                @if($dailyLog->total_km > 0)
                                    <div class="flex justify-between pt-2 border-t border-blue-200">
                                        <span class="text-xs font-bold text-gray-700">Total KM:</span>
                                        <span class="text-lg font-bold text-blue-600">{{ number_format($dailyLog->total_km, 2) }} KM</span>
                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- Lokasi --}}
                        @if($dailyLog->end_lat && $dailyLog->end_long)
                            <div>
                                <p class="text-xs font-bold text-gray-600 mb-2">Lokasi Absen Keluar</p>
                                <div class="bg-gray-50 rounded-xl p-3 border border-gray-200 mb-2">
                                    <p class="text-xs text-gray-500 mb-2">{{ $dailyLog->end_lat }}, {{ $dailyLog->end_long }}</p>
                                    <iframe 
                                        src="https://www.google.com/maps?q={{ $dailyLog->end_lat }},{{ $dailyLog->end_long }}&output=embed&zoom=15" 
                                        width="100%" 
                                        height="200" 
                                        style="border:0; border-radius: 8px;" 
                                        allowfullscreen="" 
                                        loading="lazy" 
                                        referrerpolicy="no-referrer-when-downgrade">
                                    </iframe>
                                </div>
                                <a href="https://www.google.com/maps?q={{ $dailyLog->end_lat }},{{ $dailyLog->end_long }}" 
                                    target="_blank" 
                                    class="flex items-center gap-2 text-blue-600 text-xs hover:text-blue-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                    </svg>
                                    Buka di Google Maps
                                </a>
                            </div>
                        @endif

                        {{-- Tipe & Catatan --}}
                        @if($dailyLog->end_type)
                            <div>
                                <p class="text-xs font-bold text-gray-600 mb-1">Tipe Keluar</p>
                                <p class="text-sm text-gray-800">
                                    @if($dailyLog->end_type === 'home')
                                        🏠 Pulang ke Rumah
                                    @elseif($dailyLog->end_type === 'last_store')
                                        🏪 Dari Toko Terakhir
                                    @else
                                        📍 Lokasi Lain
                                    @endif
                                </p>
                            </div>
                        @endif

                        @if($dailyLog->end_notes)
                            <div>
                                <p class="text-xs font-bold text-gray-600 mb-1">Catatan</p>
                                <p class="text-sm text-gray-800 bg-gray-50 rounded-xl p-3">{{ $dailyLog->end_notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- TAB CONTENT: KUNJUNGAN --}}
        <div id="content-kunjungan" class="tab-content hidden">
            @if($dailyLog->visits->count() > 0)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-24">
                    <h2 class="font-bold text-lg mb-4 flex items-center gap-2">
                        <span class="bg-blue-100 text-blue-600 w-8 h-8 rounded-full flex items-center justify-center text-sm">📍</span>
                        Kunjungan ({{ $dailyLog->visits->count() }})
                    </h2>
                    
                    <div class="space-y-4">
                        @foreach($dailyLog->visits as $visit)
                            <div class="border border-gray-200 rounded-xl p-4 {{ $visit->status === 'completed' ? 'bg-green-50' : ($visit->status === 'failed' ? 'bg-red-50' : 'bg-yellow-50') }}">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            @if($visit->status === 'completed')
                                                <span class="bg-green-500 text-white w-6 h-6 rounded flex items-center justify-center text-xs">✓</span>
                                            @elseif($visit->status === 'failed')
                                                <span class="bg-red-500 text-white w-6 h-6 rounded flex items-center justify-center text-xs">✗</span>
                                            @else
                                                <span class="bg-yellow-500 text-white w-6 h-6 rounded flex items-center justify-center text-xs">⏳</span>
                                            @endif
                                            <h3 class="font-bold text-gray-800">{{ $visit->client_name }}</h3>
                                            @if(!$visit->is_planned)
                                                <span class="bg-purple-100 text-purple-700 text-[10px] font-bold px-2 py-0.5 rounded-full">Dadakan</span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($visit->time)->format('H:i') }}</p>
                                    </div>
                                    <span class="bg-{{ $visit->status === 'completed' ? 'green' : ($visit->status === 'failed' ? 'red' : 'yellow') }}-100 text-{{ $visit->status === 'completed' ? 'green' : ($visit->status === 'failed' ? 'red' : 'yellow') }}-700 text-[10px] font-bold px-2 py-1 rounded-full">
                                        {{ ucfirst($visit->status) }}
                                    </span>
                                </div>

                                {{-- Foto Kunjungan --}}
                                @if($visit->photo_path)
                                    <div class="mb-3">
                                        <p class="text-xs font-bold text-gray-600 mb-2">Foto Kunjungan</p>
                                        <button type="button" onclick="openImageModal('{{ route('files.visit.photo', $visit->id) }}')" class="block w-full p-0 bg-transparent border-0 focus:outline-none">
                                            <img src="{{ route('files.visit.photo', $visit->id) }}" alt="Foto Kunjungan" 
                                                class="w-full rounded-xl border border-gray-200">
                                        </button>
                                    </div>
                                @endif

                                {{-- Lokasi Kunjungan --}}
                                @if($visit->lat && $visit->long)
                                    <div class="mb-3">
                                        <p class="text-xs font-bold text-gray-600 mb-2">Lokasi Kunjungan</p>
                                        <div class="bg-white rounded-xl p-2 border border-gray-200 mb-2">
                                            <p class="text-xs text-gray-500 mb-2">{{ $visit->lat }}, {{ $visit->long }}</p>
                                            <iframe 
                                                src="https://www.google.com/maps?q={{ $visit->lat }},{{ $visit->long }}&output=embed&zoom=15" 
                                                width="100%" 
                                                height="150" 
                                                style="border:0; border-radius: 8px;" 
                                                allowfullscreen="" 
                                                loading="lazy" 
                                                referrerpolicy="no-referrer-when-downgrade">
                                            </iframe>
                                        </div>
                                        <a href="https://www.google.com/maps?q={{ $visit->lat }},{{ $visit->long }}" 
                                            target="_blank" 
                                            class="flex items-center gap-2 text-blue-600 text-xs hover:text-blue-700">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                            </svg>
                                            Buka di Google Maps
                                        </a>
                                    </div>
                                @endif

                                {{-- Keterangan / Alasan --}}
                                @if($visit->status === 'completed' && $visit->notes)
                                    <div class="mb-2">
                                        <p class="text-xs font-bold text-gray-600 mb-1">Keterangan</p>
                                        <p class="text-sm text-gray-800">{{ $visit->notes }}</p>
                                    </div>
                                @endif

                                @if($visit->status === 'failed' && $visit->reason)
                                    <div class="mb-2">
                                        <p class="text-xs font-bold text-gray-600 mb-1">Alasan Gagal</p>
                                        <p class="text-sm text-red-600">{{ $visit->reason }}</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 mb-24 text-center">
                    <p class="text-gray-500">Belum ada kunjungan</p>
                </div>
            @endif
        </div>

        {{-- TAB CONTENT: REIMBURSE --}}
        @if($dailyLog->expenses->count() > 0)
            <div id="content-reimburse" class="tab-content hidden">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-24">
                    <h2 class="font-bold text-lg mb-4 flex items-center gap-2">
                        <span class="bg-green-100 text-green-600 w-8 h-8 rounded-full flex items-center justify-center text-sm">💰</span>
                        Reimburse ({{ $dailyLog->expenses->count() }})
                    </h2>
                    
                    <div class="space-y-4">
                        @foreach($dailyLog->expenses as $expense)
                            <div class="border border-gray-200 rounded-xl p-4 bg-gray-50">
                                {{-- Header dengan Nama dan Harga --}}
                                <div class="flex justify-between items-start mb-2">
                                    <div class="flex-1">
                                        <p class="font-bold text-gray-800">
                                            @if($expense->is_auto_calculated)
                                                ⛽ Bahan Bakar (Auto)
                                            @else
                                                {{ ucfirst($expense->type) }}
                                            @endif
                                        </p>
                                        @if($expense->km_total)
                                            <p class="text-xs text-gray-500">{{ number_format($expense->km_total, 2) }} KM</p>
                                        @endif
                                    </div>
                                    <p class="text-lg font-bold text-green-600">Rp {{ number_format($expense->amount, 0, ',', '.') }}</p>
                                </div>

                                {{-- Status Badge (Row terpisah) --}}
                                <div class="mb-3">
                                    @php
                                        $statusColors = [
                                            'pending_spv' => 'bg-yellow-100 text-yellow-700',
                                            'pending_finance' => 'bg-blue-100 text-blue-700',
                                            'approved' => 'bg-green-100 text-green-700',
                                            'needs_revision_sales' => 'bg-orange-100 text-orange-700',
                                            'needs_revision_spv' => 'bg-orange-100 text-orange-700',
                                            'rejected_permanent' => 'bg-red-100 text-red-700',
                                            'rejected_spv' => 'bg-red-100 text-red-700',
                                            'rejected_finance' => 'bg-red-100 text-red-700',
                                        ];
                                        $statusLabels = [
                                            'pending_spv' => 'Menunggu SPV',
                                            'pending_finance' => 'Menunggu Finance',
                                            'approved' => 'Disetujui',
                                            'needs_revision_sales' => 'Perlu Revisi',
                                            'needs_revision_spv' => 'Perlu Revisi SPV',
                                            'rejected_permanent' => 'Ditolak Permanen',
                                            'rejected_spv' => 'Ditolak SPV',
                                            'rejected_finance' => 'Ditolak Finance',
                                        ];
                                        $status = $expense->status ?? 'pending_spv';
                                    @endphp
                                    <span class="inline-block text-xs font-bold px-3 py-1 rounded-full {{ $statusColors[$status] ?? 'bg-gray-100 text-gray-500' }}">
                                        {{ $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status)) }}
                                    </span>
                                    @if($expense->revision_count > 0)
                                        <span class="inline-block text-xs font-bold px-2 py-1 rounded-full bg-gray-100 text-gray-600 ml-1">
                                            Revisi ke-{{ $expense->revision_count }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Foto Bon --}}
                                @if($expense->photo_receipt)
                                    <div class="mb-3">
                                        <p class="text-xs font-bold text-gray-600 mb-2">Foto Bon / Struk</p>
                                                <button type="button" onclick="openImageModal('{{ route('expenses.receipt.show', $expense->id) }}')" class="block w-full p-0 bg-transparent border-0 focus:outline-none">
                                                    <img src="{{ route('expenses.receipt.show', $expense->id) }}" alt="Foto Bon" 
                                                        class="w-full rounded-xl border border-gray-200">
                                                </button>
                                    </div>
                                @elseif($expense->isFuel() && \Carbon\Carbon::today()->lte(\App\Models\Expense::calculateDeadline($expense->dailyLog->date)))
                                    {{-- Tombol Lampirkan Struk untuk fuel expense yang belum punya struk --}}
                                    <div class="mb-3">
                                        <a href="{{ route('sales.fuel.receipt.form', $expense->id) }}"
                                            class="block w-full bg-blue-600 text-white py-3 rounded-xl font-bold text-sm text-center hover:bg-blue-700 transition">
                                            📎 Lampirkan Struk Bahan Bakar
                                        </a>
                                        <p class="text-xs text-gray-500 mt-2 text-center">Belum ada struk yang dilampirkan</p>
                                    </div>
                                @elseif($expense->isFuel())
                                    <div class="mb-3">
                                        <p class="text-xs text-gray-500 text-center italic">Struk belum dilampirkan (Batas waktu sudah lewat)</p>
                                    </div>
                                @endif

                                {{-- Catatan --}}
                                @if($expense->note)
                                    <div>
                                        <p class="text-xs font-bold text-gray-600 mb-1">Catatan</p>
                                        <p class="text-sm text-gray-800">{{ $expense->note }}</p>
                                    </div>
                                @endif

                                {{-- Alasan Penolakan dan Form Revisi --}}
                                @if($expense->rejection_note)
                                    <div class="mt-2 bg-red-50 border border-red-200 p-3 rounded-lg">
                                        <p class="text-xs font-bold text-red-700 mb-1">Alasan Penolakan:</p>
                                        <p class="text-sm text-red-600 italic">"{{ $expense->rejection_note }}"</p>
                                    </div>
                                @endif

                                {{-- FORM REVISI UNTUK SALES --}}
                                @if($expense->needsRevisionBySales() && Auth::user()->id === $expense->user_id)
                                    <div class="mt-3 bg-orange-50 border-2 border-orange-300 rounded-xl p-4">
                                        <p class="text-sm font-bold text-orange-800 mb-3">🔄 Lakukan Revisi</p>
                                        
                                        {{-- OPSI: Generate Struk Parkir jika tipe parkir --}}
                                        @if($expense->type === 'parking')
                                            <div class="mb-3">
                                                <label class="flex items-center space-x-3 p-3 bg-blue-50 border border-blue-100 rounded-xl cursor-pointer">
                                                    <input type="checkbox" id="revise_generate_{{ $expense->id }}" onchange="toggleReviseMode({{ $expense->id }})" class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500">
                                                    <div>
                                                        <span class="block text-sm font-bold text-gray-700">Tidak ada struk?</span>
                                                        <span class="text-xs text-gray-500">Centang untuk buat struk otomatis</span>
                                                    </div>
                                                </label>
                                            </div>
                                            
                                            {{-- Detail Parkir untuk Generate --}}
                                            <div id="revise_parking_details_{{ $expense->id }}" class="hidden mb-3 p-3 bg-gray-50 rounded-lg border">
                                                <p class="text-xs font-bold text-gray-700 mb-2">Detail untuk Struk:</p>
                                                <input type="text" id="revise_plate_{{ $expense->id }}" placeholder="No. Kendaraan (B 1234 ABC)" class="w-full border border-gray-300 rounded-lg p-2 text-sm mb-2">
                                                <input type="text" id="revise_location_{{ $expense->id }}" placeholder="Lokasi Parkir" class="w-full border border-gray-300 rounded-lg p-2 text-sm">
                                            </div>
                                        @endif
                                        
                                        <form action="{{ route('sales.reimburse.revise', $expense->id) }}" method="POST" id="revise-form-{{ $expense->id }}">
                                            @csrf
                                            <input type="hidden" name="photo_receipt" id="revise_photo_{{ $expense->id }}">
                                            <input type="hidden" name="generate_receipt" id="revise_gen_flag_{{ $expense->id }}" value="0">
                                            <input type="hidden" name="license_plate" id="revise_plate_input_{{ $expense->id }}">
                                            <input type="hidden" name="parking_location" id="revise_loc_input_{{ $expense->id }}">
                                            
                                            {{-- Bagian Kamera Langsung --}}
                                            <div id="revise_camera_section_{{ $expense->id }}" class="mb-3">
                                                <p class="text-xs font-bold text-gray-600 mb-2">📷 Ambil Foto Struk Baru *</p>
                                                <div class="relative w-full h-48 bg-black rounded-xl overflow-hidden mb-2">
                                                    <video id="revise_video_{{ $expense->id }}" autoplay playsinline class="w-full h-full object-cover"></video>
                                                    <canvas id="revise_canvas_{{ $expense->id }}" class="hidden w-full h-full object-cover"></canvas>
                                                    <button type="button" onclick="switchReviseCamera({{ $expense->id }})"
                                                        class="absolute top-2 right-2 bg-white/30 backdrop-blur p-2 rounded-full text-white">
                                                        🔄
                                                    </button>
                                                    <button type="button" onclick="takeRevisePicture({{ $expense->id }})" id="revise_snap_{{ $expense->id }}"
                                                        class="absolute bottom-2 left-1/2 transform -translate-x-1/2 w-12 h-12 bg-white rounded-full border-4 border-gray-300 shadow-lg flex items-center justify-center">
                                                        <div class="w-8 h-8 bg-orange-500 rounded-full"></div>
                                                    </button>
                                                </div>
                                                <p id="revise_status_{{ $expense->id }}" class="text-xs text-gray-500"></p>
                                            </div>
                                            
                                            {{-- Catatan Tambahan --}}
                                            <div class="mb-3">
                                                <p class="text-xs font-bold text-gray-600 mb-1">Catatan (opsional)</p>
                                                <input type="text" name="note" value="{{ $expense->note }}" 
                                                    class="w-full border border-gray-300 rounded-lg p-2 text-sm"
                                                    placeholder="Tambahkan catatan...">
                                            </div>
                                            
                                            <button type="submit" id="revise_btn_{{ $expense->id }}" disabled
                                                class="w-full bg-orange-600 text-white py-3 rounded-xl font-bold text-sm hover:bg-orange-700 transition disabled:bg-gray-400">
                                                📤 Kirim Revisi
                                            </button>
                                        </form>
                                    </div>
                                @endif

                                {{-- ACTION BUTTONS HRD/IT --}}
                                @if(in_array(Auth::user()->role, ['hrd', 'it']))
                                    <div class="mt-3 pt-3 border-t border-gray-200 flex gap-2">
                                        {{-- Generate Button --}}
                                        <button onclick="openGenerateModal({{ $expense->id }}, '{{ $expense->date->format('Y-m-d') }}', {{ $expense->amount }}, '{{ $expense->isFuel() ? 'SPBU' : ($expense->type == 'parking' ? 'Area Parkir' : '') }}')"
                                            class="flex-1 bg-indigo-50 text-indigo-700 py-2 rounded-lg text-xs font-bold hover:bg-indigo-100 transition flex justify-center items-center gap-1">
                                            <span>⚡ Generate Struk</span>
                                        </button>
                                        
                                        {{-- Delete/Revert Button --}}
                                        @if($expense->photo_receipt && $expense->is_generated_receipt)
                                             <button onclick="deleteReceipt({{ $expense->id }})"
                                                class="flex-1 bg-orange-50 text-orange-700 py-2 rounded-lg text-xs font-bold hover:bg-orange-100 transition flex justify-center items-center gap-1">
                                                <span>↩️ Kembalikan Asli</span>
                                            </button>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach

                        {{-- Total --}}
                        <div class="bg-green-50 rounded-xl p-4 border-2 border-green-200 mt-4">
                            <p class="text-2xl font-bold text-green-600">Rp {{ number_format($dailyLog->expenses->sum('amount'), 0, ',', '.') }}</p>
                        </div>
                        
                        {{-- TOMBOL TAMBAH BIAYA TAMBAHAN --}}
                        @if(\Carbon\Carbon::today()->lte(\App\Models\Expense::calculateDeadline($dailyLog->date)) && Auth::user()->role === 'sales')
                            <a href="{{ route('sales.reimburse.form', $dailyLog->id) }}"
                                class="block w-full bg-green-600 text-white py-4 rounded-xl font-bold text-center mt-4 shadow-lg hover:bg-green-700 transition">
                                ➕ Tambah Biaya Tambahan
                            </a>
                        @endif
                    </div>

                         {{-- MODAL GENERATE RECEIPT (HRD/IT) --}}
                        @if(in_array(Auth::user()->role, ['hrd', 'it']))
                            <div id="modal-generate-receipt" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
                                <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-sm mx-4">
                                    <h3 class="text-lg font-bold mb-4">Generate Struk Manual</h3>
                                    <form id="form-generate-receipt" onsubmit="submitGenerateReceipt(event)">
                                        <input type="hidden" id="gen-expense-id">
                                        <div class="mb-3">
                                            <label class="block text-xs font-bold text-gray-700 mb-1">Tanggal</label>
                                            <input type="date" id="gen-date" class="w-full border border-gray-300 rounded-lg p-2 text-sm" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="block text-xs font-bold text-gray-700 mb-1">Nominal</label>
                                            <input type="number" id="gen-amount" class="w-full border border-gray-300 rounded-lg p-2 text-sm" required>
                                        </div>
                                        <div class="mb-4">
                                            <label class="block text-xs font-bold text-gray-700 mb-1">Lokasi / Keterangan</label>
                                            <input type="text" id="gen-location" class="w-full border border-gray-300 rounded-lg p-2 text-sm" placeholder="Contoh: SPBU ... / Hotel ..." required>
                                        </div>
                                        <div class="flex gap-2">
                                            <button type="button" onclick="closeGenerateModal()" class="flex-1 bg-gray-100 text-gray-700 py-2 rounded-lg font-bold text-sm">Batal</button>
                                            <button type="submit" class="flex-1 bg-indigo-600 text-white py-2 rounded-lg font-bold text-sm">Generate</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

    <script>
        function switchTab(tab) {
            // Hide all content
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });

            // Reset all buttons
            document.querySelectorAll('[id^="tab-btn-"]').forEach(btn => {
                btn.className = "flex-1 py-2 text-xs font-bold rounded-lg text-gray-500 hover:bg-gray-100 transition-all";
            });

            // Show selected
            document.getElementById('content-' + tab).classList.remove('hidden');
            document.getElementById('tab-btn-' + tab).className = 
                "flex-1 py-2 text-xs font-bold rounded-lg bg-blue-600 text-white shadow-sm transition-all";
        }
    </script>
@endsection

@section('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
    let mapInstance = null;
    let routingControl = null;

    function verifyDistance() {
        // Show container
        document.getElementById('map-container').classList.remove('hidden');
        document.getElementById('btn-verify-text').innerText = 'Menghitung...';
        document.getElementById('btn-verify').disabled = true;

        // Coordinates
        const startLat = {{ $dailyLog->lat ?? 0 }};
        const startLong = {{ $dailyLog->long ?? 0 }};
        const endLat = {{ $dailyLog->end_lat ?? 0 }};
        const endLong = {{ $dailyLog->end_long ?? 0 }};

        // Visits
        const visits = [
            @foreach($dailyLog->visits as $visit)
                @if($visit->lat && $visit->long)
                    L.latLng({{ $visit->lat }}, {{ $visit->long }}),
                @endif
            @endforeach
        ];

        // Prepare waypoints
        let waypoints = [];
        if(startLat && startLong) waypoints.push(L.latLng(startLat, startLong));
        waypoints = waypoints.concat(visits);
        if(endLat && endLong) waypoints.push(L.latLng(endLat, endLong));

        if (waypoints.length < 2) {
            alert('Lokasi tidak lengkap.');
            resetButton();
            return;
        }

        // Initialize Map
        if (!mapInstance) {
            mapInstance = L.map('map').setView(waypoints[0], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(mapInstance);
             setTimeout(() => mapInstance.invalidateSize(), 400);
        }

        // Clear existing route
        if (routingControl) {
            mapInstance.removeControl(routingControl);
            routingControl = null;
        }

        // Create Control
        const osrmRouter = L.Routing.osrmv1({
            serviceUrl: 'https://router.project-osrm.org/route/v1',
            profile: 'driving'
        });

        routingControl = L.Routing.control({
            waypoints: waypoints,
            router: osrmRouter,
            routeWhileDragging: false,
            draggableWaypoints: false,
            addWaypoints: false,
            lineOptions: {
                styles: [{color: 'blue', opacity: 0.7, weight: 5}]
            },
            show: false,
            createMarker: function(i, wp, nWps) {
                return L.marker(wp.latLng).bindPopup(i === 0 ? 'Start' : (i === nWps - 1 ? 'End' : 'Visit ' + i));
            }
        }).addTo(mapInstance);

        // Events
        routingControl.on('routesfound', function(e) {
            const summary = e.routes[0].summary;
            const distanceKm = (summary.totalDistance / 1000).toFixed(2);
            
            // Update UI
            document.getElementById('verified-distance-container').classList.remove('hidden');
            document.getElementById('verified-distance-value').innerText = distanceKm + ' KM';
            document.getElementById('verification-status').innerText = 'Baru saja diverifikasi';
            
            resetButton(true);
            saveDistance(distanceKm);
        });

        routingControl.on('routingerror', function(e) {
            alert('Gagal menghitung rute. Coba lagi.');
            resetButton();
            console.error(e);
        });
    }

    function resetButton(success = false) {
        const btn = document.getElementById('btn-verify');
        const txt = document.getElementById('btn-verify-text');
        btn.disabled = false;
        txt.innerText = success ? 'Hitung Ulang' : 'Coba Lagi';
    }

    function saveDistance(distance) {
        axios.post('{{ route("sales.history.verify_distance", $dailyLog->id) }}', {
            distance: distance,
            _token: '{{ csrf_token() }}'
        })
        .then(function (response) {
            console.log('Saved:', response.data);
            document.getElementById('verification-status').innerText = '✅ Tersimpan ke server';
        })
        .catch(function (error) {
            console.error('Error saving distance:', error);
            document.getElementById('verification-status').innerText = '⚠️ Gagal menyimpan, mencoba ulang...';
            // Silent retry after 2 seconds
            setTimeout(function() {
                saveDistance(distance);
            }, 2000);
        });
    }

    // --- HRD RECEIPT GENERATION ---
    function openGenerateModal(id, date, amount, defaultLoc) {
        document.getElementById('gen-expense-id').value = id;
        document.getElementById('gen-date').value = date;
        document.getElementById('gen-amount').value = amount;
        document.getElementById('gen-location').value = defaultLoc;
        document.getElementById('modal-generate-receipt').classList.remove('hidden');
    }

    function closeGenerateModal() {
        document.getElementById('modal-generate-receipt').classList.add('hidden');
    }

    function submitGenerateReceipt(e) {
        e.preventDefault();
        const id = document.getElementById('gen-expense-id').value;
        const date = document.getElementById('gen-date').value;
        const amount = document.getElementById('gen-amount').value;
        const receiptLocation = document.getElementById('gen-location').value;
        
        // Disable button
        const btn = e.target.querySelector('button[type="submit"]');
        const originalText = btn.innerText;
        btn.disabled = true;
        btn.innerText = 'Processing...';

        axios.post('/sales/expense/' + id + '/generate-custom-receipt', {
            date: date,
            amount: amount,
            location: receiptLocation,
            _token: '{{ csrf_token() }}'
        })
        .then(function (response) {
            alert('Struk berhasil digenerate!');
            window.location.reload();
        })
        .catch(function (error) {
            console.error(error);
            alert('Gagal generate struk: ' + (error.response?.data?.message || error.message));
            btn.disabled = false;
            btn.innerText = originalText;
        });
    }

    function deleteReceipt(id) {
        if(!confirm('Apakah anda yakin ingin mengembalikan struk ke versi asli (jika ada)?')) return;

        axios.post('/sales/expense/' + id + '/delete-receipt', {
            _token: '{{ csrf_token() }}'
        })
        .then(function (response) {
            alert(response.data.message);
            window.location.reload();
        })
        .catch(function (error) {
            console.error(error);
            alert('Gagal menghapus/mengembalikan struk: ' + (error.response?.data?.message || error.message));
        });
    }

    // =============================================
    // KAMERA LANGSUNG UNTUK REVISI REIMBURSE
    // =============================================
    let reviseStreams = {};
    let reviseFacingModes = {};
    
    // Init camera saat section visible
    function initReviseCamera(expenseId) {
        if (reviseStreams[expenseId]) {
            reviseStreams[expenseId].getTracks().forEach(t => t.stop());
        }
        
        const facingMode = reviseFacingModes[expenseId] || 'environment';
        const video = document.getElementById('revise_video_' + expenseId);
        
        if (!video) return;
        
        navigator.mediaDevices.getUserMedia({
            video: { facingMode: facingMode }
        }).then(stream => {
            reviseStreams[expenseId] = stream;
            video.srcObject = stream;
        }).catch(err => {
            console.log('Camera error for expense ' + expenseId + ':', err);
            document.getElementById('revise_status_' + expenseId).innerText = '⚠️ Kamera tidak tersedia';
        });
    }
    
    function switchReviseCamera(expenseId) {
        reviseFacingModes[expenseId] = (reviseFacingModes[expenseId] || 'environment') === 'environment' ? 'user' : 'environment';
        initReviseCamera(expenseId);
    }
    
    function takeRevisePicture(expenseId) {
        const video = document.getElementById('revise_video_' + expenseId);
        const canvas = document.getElementById('revise_canvas_' + expenseId);
        const btn = document.getElementById('revise_snap_' + expenseId);
        const status = document.getElementById('revise_status_' + expenseId);
        
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0);
        
        // Add timestamp
        ctx.font = 'bold 16px sans-serif';
        ctx.fillStyle = 'white';
        ctx.strokeStyle = 'black';
        ctx.lineWidth = 2;
        const timestamp = new Date().toLocaleString('id-ID');
        ctx.strokeText(timestamp, 10, canvas.height - 15);
        ctx.fillText(timestamp, 10, canvas.height - 15);
        
        // Set photo data
        document.getElementById('revise_photo_' + expenseId).value = canvas.toDataURL('image/png');
        
        // Show canvas, hide video
        video.classList.add('hidden');
        canvas.classList.remove('hidden');
        btn.classList.add('hidden');
        status.innerText = '✅ Foto berhasil diambil';
        
        // Enable submit
        document.getElementById('revise_btn_' + expenseId).disabled = false;
        
        // Stop stream
        if (reviseStreams[expenseId]) {
            reviseStreams[expenseId].getTracks().forEach(t => t.stop());
        }
    }
    
    // Toggle antara mode kamera dan mode generate struk parkir
    function toggleReviseMode(expenseId) {
        const checkbox = document.getElementById('revise_generate_' + expenseId);
        const cameraSection = document.getElementById('revise_camera_section_' + expenseId);
        const parkingDetails = document.getElementById('revise_parking_details_' + expenseId);
        const genFlag = document.getElementById('revise_gen_flag_' + expenseId);
        const submitBtn = document.getElementById('revise_btn_' + expenseId);
        
        if (checkbox.checked) {
            // Mode generate struk
            cameraSection.classList.add('hidden');
            parkingDetails.classList.remove('hidden');
            genFlag.value = '1';
            submitBtn.disabled = false;
            
            // Stop camera
            if (reviseStreams[expenseId]) {
                reviseStreams[expenseId].getTracks().forEach(t => t.stop());
            }
        } else {
            // Mode kamera langsung
            cameraSection.classList.remove('hidden');
            parkingDetails.classList.add('hidden');
            genFlag.value = '0';
            submitBtn.disabled = true;
            initReviseCamera(expenseId);
        }
    }
    
    // Copy values saat submit
    document.querySelectorAll('form[id^="revise-form-"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            const expenseId = this.id.replace('revise-form-', '');
            const plateInput = document.getElementById('revise_plate_' + expenseId);
            const locInput = document.getElementById('revise_location_' + expenseId);
            
            if (plateInput && locInput) {
                document.getElementById('revise_plate_input_' + expenseId).value = plateInput.value;
                document.getElementById('revise_loc_input_' + expenseId).value = locInput.value;
            }
        });
    });
    
    // Auto-init cameras untuk semua revise forms yang visible
    document.addEventListener('DOMContentLoaded', function() {
        // Init cameras when tab is clicked
        const reimburseTab = document.querySelector('[data-tab="reimburse"]');
        if (reimburseTab) {
            reimburseTab.addEventListener('click', function() {
                setTimeout(function() {
                    document.querySelectorAll('video[id^="revise_video_"]').forEach(video => {
                        const expenseId = video.id.replace('revise_video_', '');
                        initReviseCamera(expenseId);
                    });
                }, 300);
            });
        }
    });
</script>
