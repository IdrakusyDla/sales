@extends('layout')
@section('content')
    <div class="px-5 md:px-8 py-6 md:py-8">
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
            </a>
            <div class="flex-1">
                <h1 class="text-2xl font-bold">Detail Absen</h1>
                <p class="text-sm text-gray-600">{{ $dailyLog->user->name }} •
                    {{ \Carbon\Carbon::parse($dailyLog->date)->format('d M Y') }}
                </p>
                {{-- INFO DEADLINE DI HEADER --}}
                @if($dailyLog->hasEnded())
                    @php
                        $deadline = \App\Models\Expense::calculateDeadline($dailyLog->date);
                        $isDeadlinePassed = \Carbon\Carbon::today()->gt($deadline);
                        $fuelExpense = $dailyLog->expenses->where('type', 'fuel')->where('is_auto_calculated', true)->first();
                        $hasIncompleteFuelReceipt = $fuelExpense && !$fuelExpense->photo_receipt;
                        $hasIncompleteExpenses = $dailyLog->expenses->whereNull('photo_receipt')->count() > 0;
                    @endphp
                    <div
                        class="mt-2 {{ $isDeadlinePassed ? 'bg-red-50 border border-red-200' : ($hasIncompleteFuelReceipt || $hasIncompleteExpenses ? 'bg-orange-50 border border-orange-200' : 'bg-yellow-50 border border-yellow-200') }} rounded-lg p-2 inline-block">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">
                                @if($isDeadlinePassed)
                                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                        </path>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                @endif
                            </span>
                            <div>
                                <p
                                    class="text-xs font-bold {{ $isDeadlinePassed ? 'text-red-700' : ($hasIncompleteFuelReceipt || $hasIncompleteExpenses ? 'text-orange-700' : 'text-yellow-700') }}">
                                    @if($isDeadlinePassed)
                                        Batas Waktu Sudah Lewat
                                    @elseif($hasIncompleteFuelReceipt || $hasIncompleteExpenses)
                                        Batas Melengkapi Berkas • Belum Lengkap
                                    @else
                                        Batas Melengkapi Berkas
                                    @endif
                                </p>
                                <p
                                    class="text-xs {{ $isDeadlinePassed ? 'text-red-600' : ($hasIncompleteFuelReceipt || $hasIncompleteExpenses ? 'text-orange-600' : 'text-yellow-600') }} font-bold">
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
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                        </path>
                    </svg> Ringkasan
                </button>
                <button id="tab-btn-absensi" onclick="switchTab('absensi')"
                    class="flex-1 py-2 text-xs font-bold rounded-lg text-gray-500 hover:bg-gray-100 transition-all">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg> Absensi
                </button>
                <button id="tab-btn-kunjungan" onclick="switchTab('kunjungan')"
                    class="flex-1 py-2 text-xs font-bold rounded-lg text-gray-500 hover:bg-gray-100 transition-all">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg> Kunjungan
                </button>
                @if($dailyLog->expenses->count() > 0)
                    <button id="tab-btn-reimburse" onclick="switchTab('reimburse')"
                        class="flex-1 py-2 text-xs font-bold rounded-lg text-gray-500 hover:bg-gray-100 transition-all">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg> Reimburse
                    </button>
                @endif
            </div>
        </div>

        {{-- TAB CONTENT: RINGKASAN --}}
        <div id="content-summary" class="tab-content">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-4">
                <h2 class="font-bold text-lg mb-4"><svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                        </path>
                    </svg> Ringkasan</h2>

                <div class="space-y-4">
                    {{-- Info Odometer --}}
                    @if($dailyLog->start_odo_value && $dailyLog->end_odo_value)
                        <div class="bg-blue-50 rounded-xl p-4">
                            <p class="text-xs text-gray-600 mb-2">Odometer</p>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <p class="text-xs text-gray-500">Awal</p>
                                    <p class="text-sm font-bold text-blue-600">
                                        {{ number_format($dailyLog->start_odo_value, 2) }} KM
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Akhir</p>
                                    <p class="text-sm font-bold text-blue-600">{{ number_format($dailyLog->end_odo_value, 2) }}
                                        KM</p>
                                </div>
                            </div>
                            @if($dailyLog->total_km > 0)
                                <div class="mt-3 pt-3 border-t border-blue-200">
                                    <div class="flex justify-between items-center">
                                        <p class="text-sm font-bold text-gray-700">Total KM:</p>
                                        <p class="text-xl font-bold text-blue-600">{{ number_format($dailyLog->total_km, 2) }} KM
                                        </p>
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
                            <div id="verified-distance-container"
                                class="{{ $dailyLog->system_calculated_distance ? '' : 'hidden' }}">
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
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7">
                                        </path>
                                    </svg>
                                    <span
                                        id="btn-verify-text">{{ $dailyLog->system_calculated_distance ? 'Hitung Ulang' : 'Verifikasi Jarak' }}</span>
                                </button>
                            </div>

                            {{-- Map Container --}}
                            <div id="map-container" class="hidden mt-3">
                                <div id="map" class="w-full h-48 rounded-lg border border-gray-300"></div>
                                <p class="text-[10px] text-gray-500 mt-1">*Perhitungan menggunakan rute jalan tercepat (via
                                    OSRM, bukan garis lurus)</p>
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
                                <p class="text-xl font-bold text-green-600">
                                    {{ $dailyLog->visits->where('status', 'completed')->count() }}
                                </p>
                            </div>
                            <div class="bg-red-50 rounded-xl p-3 text-center">
                                <p class="text-xs text-gray-600 mb-1">Gagal</p>
                                <p class="text-xl font-bold text-red-600">
                                    {{ $dailyLog->visits->where('status', 'failed')->count() }}
                                </p>
                            </div>
                            <div class="bg-yellow-50 rounded-xl p-3 text-center">
                                <p class="text-xs text-gray-600 mb-1">Pending</p>
                                <p class="text-xl font-bold text-yellow-600">
                                    {{ $dailyLog->visits->where('status', 'pending')->count() }}
                                </p>
                            </div>
                        </div>
                    @endif

                    {{-- Total Reimburse --}}
                    @if($dailyLog->expenses->count() > 0)
                        <div class="bg-green-50 rounded-xl p-4 border-2 border-green-200">
                            <div class="flex justify-between items-center">
                                <p class="font-bold text-gray-800">Total Reimburse:</p>
                                <p class="text-2xl font-bold text-green-600">Rp
                                    {{ number_format($dailyLog->expenses->sum('amount'), 0, ',', '.') }}
                                </p>
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
                    <span
                        class="bg-green-100 text-green-600 w-8 h-8 rounded-full flex items-center justify-center text-sm">✓</span>
                    Absen Masuk
                </h2>

                @if($dailyLog->hasStarted())
                    <div class="space-y-4">
                        {{-- Foto Selfie --}}
                        @if($dailyLog->start_photo)
                            <div>
                                <p class="text-xs font-bold text-gray-600 mb-2">Foto Selfie</p>
                                <button type="button"
                                    onclick="openImageModal('{{ route('files.daily.photo', [$dailyLog->id, 'start_photo']) }}')"
                                    class="block w-full p-0 bg-transparent border-0 focus:outline-none">
                                    <img src="{{ route('files.daily.photo', [$dailyLog->id, 'start_photo']) }}"
                                        alt="Foto Absen Masuk" class="w-full rounded-xl border border-gray-200">
                                </button>
                            </div>
                        @endif

                        {{-- Foto Odometer --}}
                        @if($dailyLog->start_odo_photo)
                            <div>
                                <p class="text-xs font-bold text-gray-600 mb-2">Foto Odometer Awal</p>
                                <button type="button"
                                    onclick="openImageModal('{{ route('files.daily.photo', [$dailyLog->id, 'start_odo_photo']) }}')"
                                    class="block w-full p-0 bg-transparent border-0 focus:outline-none">
                                    <img src="{{ route('files.daily.photo', [$dailyLog->id, 'start_odo_photo']) }}"
                                        alt="Foto Odometer Awal" class="w-full rounded-xl border border-gray-200">
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
                                        width="100%" height="200" style="border:0; border-radius: 8px;" allowfullscreen=""
                                        loading="lazy" referrerpolicy="no-referrer-when-downgrade">
                                    </iframe>
                                </div>
                                <a href="https://www.google.com/maps?q={{ $dailyLog->lat }},{{ $dailyLog->long }}" target="_blank"
                                    class="flex items-center gap-2 text-blue-600 text-xs hover:text-blue-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
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
                        <span
                            class="bg-red-100 text-red-600 w-8 h-8 rounded-full flex items-center justify-center text-sm">→</span>
                        Absen Keluar
                    </h2>

                    <div class="space-y-4">
                        {{-- Foto Selfie --}}
                        @if($dailyLog->end_photo)
                            <div>
                                <p class="text-xs font-bold text-gray-600 mb-2">Foto Selfie</p>
                                <button type="button"
                                    onclick="openImageModal('{{ route('files.daily.photo', [$dailyLog->id, 'end_photo']) }}')"
                                    class="block w-full p-0 bg-transparent border-0 focus:outline-none">
                                    <img src="{{ route('files.daily.photo', [$dailyLog->id, 'end_photo']) }}"
                                        alt="Foto Absen Keluar" class="w-full rounded-xl border border-gray-200">
                                </button>
                            </div>
                        @endif

                        {{-- Foto Odometer --}}
                        @if($dailyLog->end_odo_photo)
                            <div>
                                <p class="text-xs font-bold text-gray-600 mb-2">Foto Odometer Akhir</p>
                                <button type="button"
                                    onclick="openImageModal('{{ route('files.daily.photo', [$dailyLog->id, 'end_odo_photo']) }}')"
                                    class="block w-full p-0 bg-transparent border-0 focus:outline-none">
                                    <img src="{{ route('files.daily.photo', [$dailyLog->id, 'end_odo_photo']) }}"
                                        alt="Foto Odometer Akhir" class="w-full rounded-xl border border-gray-200">
                                </button>
                            </div>
                        @endif

                        {{-- Info Odometer & Total KM --}}
                        @if($dailyLog->end_odo_value)
                            <div class="bg-blue-50 rounded-xl p-3 space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-xs text-gray-600">Odometer Akhir:</span>
                                    <span class="text-sm font-bold text-blue-600">{{ number_format($dailyLog->end_odo_value, 2) }}
                                        KM</span>
                                </div>
                                @if($dailyLog->total_km > 0)
                                    <div class="flex justify-between pt-2 border-t border-blue-200">
                                        <span class="text-xs font-bold text-gray-700">Total KM:</span>
                                        <span class="text-lg font-bold text-blue-600">{{ number_format($dailyLog->total_km, 2) }}
                                            KM</span>
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
                                        width="100%" height="200" style="border:0; border-radius: 8px;" allowfullscreen=""
                                        loading="lazy" referrerpolicy="no-referrer-when-downgrade">
                                    </iframe>
                                </div>
                                <a href="https://www.google.com/maps?q={{ $dailyLog->end_lat }},{{ $dailyLog->end_long }}"
                                    target="_blank" class="flex items-center gap-2 text-blue-600 text-xs hover:text-blue-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                    </svg>
                                    Buka di Google Maps
                                </a>
                            </div>
                        @endif

                        {{-- Tipe & Catatan --}}
                        @if($dailyLog->end_type)
                            <div>
                                <p class="text-xs font-bold text-gray-600 mb-1">Tipe Keluar</p>
                                <p class="text-sm text-gray-800 flex items-center gap-2">
                                    @if($dailyLog->end_type === 'home')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                                            </path>
                                        </svg> Pulang ke Rumah
                                    @elseif($dailyLog->end_type === 'last_store')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                            </path>
                                        </svg> Dari Toko Terakhir
                                    @else
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                            </path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg> Lokasi Lain
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
                        <span
                            class="bg-blue-100 text-blue-600 w-8 h-8 rounded-full flex items-center justify-center text-sm"><svg
                                class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg></span>
                        Kunjungan ({{ $dailyLog->visits->count() }})
                    </h2>

                    <div class="space-y-4">
                        @foreach($dailyLog->visits as $visit)
                            <div
                                class="border border-gray-200 rounded-xl p-4 {{ $visit->status === 'completed' ? 'bg-green-50' : ($visit->status === 'failed' ? 'bg-red-50' : 'bg-yellow-50') }}">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            @if($visit->status === 'completed')
                                                <span
                                                    class="bg-green-500 text-white w-6 h-6 rounded flex items-center justify-center text-xs">✓</span>
                                            @elseif($visit->status === 'failed')
                                                <span
                                                    class="bg-red-500 text-white w-6 h-6 rounded flex items-center justify-center text-xs">✗</span>
                                            @else
                                                <span
                                                    class="bg-yellow-500 text-white w-6 h-6 rounded flex items-center justify-center text-xs"><svg
                                                        class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg></span>
                                            @endif
                                            <h3 class="font-bold text-gray-800">{{ $visit->client_name }}</h3>
                                            @if(!$visit->is_planned)
                                                <span
                                                    class="bg-purple-100 text-purple-700 text-[10px] font-bold px-2 py-0.5 rounded-full">Dadakan</span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($visit->time)->format('H:i') }}
                                        </p>
                                    </div>
                                    <span
                                        class="bg-{{ $visit->status === 'completed' ? 'green' : ($visit->status === 'failed' ? 'red' : 'yellow') }}-100 text-{{ $visit->status === 'completed' ? 'green' : ($visit->status === 'failed' ? 'red' : 'yellow') }}-700 text-[10px] font-bold px-2 py-1 rounded-full">
                                        {{ ucfirst($visit->status) }}
                                    </span>
                                </div>

                                {{-- Foto Kunjungan --}}
                                @if($visit->photo_path)
                                    <div class="mb-3">
                                        <p class="text-xs font-bold text-gray-600 mb-2">Foto Kunjungan</p>
                                        <button type="button" onclick="openImageModal('{{ route('files.visit.photo', $visit->id) }}')"
                                            class="block w-full p-0 bg-transparent border-0 focus:outline-none">
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
                                                width="100%" height="150" style="border:0; border-radius: 8px;" allowfullscreen=""
                                                loading="lazy" referrerpolicy="no-referrer-when-downgrade">
                                            </iframe>
                                        </div>
                                        <a href="https://www.google.com/maps?q={{ $visit->lat }},{{ $visit->long }}" target="_blank"
                                            class="flex items-center gap-2 text-blue-600 text-xs hover:text-blue-700">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14">
                                                </path>
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
                            <span
                                class="bg-green-100 text-green-600 w-8 h-8 rounded-full flex items-center justify-center text-sm"><svg
                                    class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                    </path>
                                </svg></span>
                            Reimburse ({{ $dailyLog->expenses->count() }})
                        </h2>

                        <div class="space-y-4">
                            @foreach($dailyLog->expenses as $expense)
                                <div class="border border-gray-200 rounded-xl p-4 bg-gray-50">
                                    {{-- Header dengan Nama dan Harga --}}
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="flex-1">
                                            <p class="font-bold text-gray-800 flex items-center gap-2">
                                                @if($expense->type == 'fuel' && $expense->is_auto_calculated)
                                                    <span
                                                        class="w-7 h-7 rounded-full bg-blue-100 flex items-center justify-center shrink-0"><svg
                                                            class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                                        </svg></span> Bahan Bakar (Auto)
                                                @elseif($expense->type == 'fuel')
                                                    <span
                                                        class="w-7 h-7 rounded-full bg-blue-100 flex items-center justify-center shrink-0"><svg
                                                            class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                                        </svg></span> Bahan Bakar
                                                @elseif($expense->type == 'parking')
                                                    <span
                                                        class="w-7 h-7 rounded-full bg-blue-100 flex items-center justify-center shrink-0">
                                                        <svg class="w-3.5 h-3.5 text-blue-500" version="1.1" id="_x32_"
                                                            xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                                            viewBox="-40 -40 592 592" xml:space="preserve" fill="currentColor">
                                                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                                            </g>
                                                            <g id="SVGRepo_iconCarrier">
                                                                <g>
                                                                    <path
                                                                        d="M404.751,54.102C371.523,20.771,324.986-0.026,274.178,0h-90.85h-8.682H53.16v512h130.167V369.324h90.85 c50.808,0.026,97.333-20.771,130.573-54.074c33.331-33.229,54.115-79.78,54.089-130.575 C458.866,133.854,438.082,87.329,404.751,54.102z M321.923,232.394c-12.408,12.305-28.919,19.754-47.745,19.779h-90.85V117.15 h90.85c18.826,0.026,35.338,7.474,47.732,19.779c12.318,12.408,19.754,28.906,19.779,47.745 C341.664,203.488,334.228,219.988,321.923,232.394z">
                                                                    </path>
                                                                </g>
                                                            </g>
                                                        </svg>
                                                    </span> Parkir
                                                @elseif($expense->type == 'hotel')
                                                    <span
                                                        class="w-7 h-7 rounded-full bg-purple-100 flex items-center justify-center shrink-0"><svg
                                                            class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                                            </path>
                                                        </svg></span> Hotel
                                                @elseif($expense->type == 'toll')
                                                    <span
                                                        class="w-7 h-7 rounded-full bg-green-100 flex items-center justify-center shrink-0">
                                                        <svg class="w-4 h-4 text-green-500" version="1.1" id="Capa_1"
                                                            xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                                            viewBox="-20 -20 440 440" xml:space="preserve" fill="currentColor">
                                                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                                            </g>
                                                            <g id="SVGRepo_iconCarrier">
                                                                <g>
                                                                    <g>
                                                                        <path
                                                                            d="M295.225,142.697c-9.9-44.668-19.801-89.336-29.707-134.003c-16.718,0-33.435,0-50.15,0 c2.389,44.668,4.781,89.336,7.172,134.003H295.225z">
                                                                        </path>
                                                                        <path
                                                                            d="M226.354,214.003c3.145,58.703,6.286,117.404,9.426,176.107c38.094,0,76.188,0,114.281,0 c-13.014-58.702-26.021-117.404-39.029-176.107H226.354z">
                                                                        </path>
                                                                        <path
                                                                            d="M183.435,8.694c-16.716,0-33.434,0-50.149,0c-9.902,44.667-19.798,89.335-29.698,134.003h72.682 C178.656,98.029,181.043,53.361,183.435,8.694z">
                                                                        </path>
                                                                        <path
                                                                            d="M48.742,390.11c38.096,0,76.188,0,114.281,0c3.152-58.702,6.293-117.404,9.43-176.107H87.785 C74.775,272.706,61.763,331.409,48.742,390.11z">
                                                                        </path>
                                                                        <path
                                                                            d="M394.176,161.212H4.628c-2.556,0-4.628,2.072-4.628,4.628v25.02c0,2.556,2.072,4.628,4.628,4.628h25.048v37.476 c0,2.556,2.071,4.629,4.627,4.629h24.996c2.117,0,3.964-1.438,4.484-3.488l9.818-38.615h251.602l9.816,38.615 c0.52,2.052,2.369,3.488,4.486,3.488h24.992c2.559,0,4.629-2.073,4.629-4.629v-37.476h25.049c2.557,0,4.629-2.072,4.629-4.628 v-25.02C398.805,163.284,396.732,161.212,394.176,161.212z">
                                                                        </path>
                                                                    </g>
                                                                </g>
                                                            </g>
                                                        </svg>
                                                    </span> Tol
                                                @elseif($expense->type == 'transport')
                                                    <span
                                                        class="w-7 h-7 rounded-full bg-orange-100 flex items-center justify-center shrink-0">
                                                        <svg class="w-4 h-4 text-orange-500" fill="currentColor"
                                                            viewBox="0 -3.6 30.859 30.859" xmlns="http://www.w3.org/2000/svg">
                                                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                                            </g>
                                                            <g id="SVGRepo_iconCarrier">
                                                                <path id="Path_1" data-name="Path 1"
                                                                    d="M141.314,136.63l1.055-.085a.568.568,0,0,0,.52-.61l-.129-1.58a.565.565,0,0,0-.609-.519l-2.354,0-2.549-5.724a2.074,2.074,0,0,0-2.032-1.116h-15a2.084,2.084,0,0,0-2.036,1.116l-2.546,5.724-2.354,0a.568.568,0,0,0-.61.519l-.127,1.58a.567.567,0,0,0,.519.61l1.055.085a10.131,10.131,0,0,0-1.833,5.852l.238,3.025a1.649,1.649,0,0,0,.9,1.355v1.6c.1,2.185.788,2.185,2.319,2.185s2.32,0,2.423-2.185v-1.417l9.551,0,9.468,0v1.415c.1,2.185.787,2.185,2.319,2.185s2.32,0,2.422-2.185v-1.6a1.734,1.734,0,0,0,.978-1.355l.242-3.025A10.131,10.131,0,0,0,141.314,136.63ZM122.257,143.5a.568.568,0,0,1-.566.567h-5.577a.567.567,0,0,1-.568-.567v-2.04a.565.565,0,0,1,.568-.567l5.577.453a.568.568,0,0,1,.566.566Zm-4.9-7.98,2.742-6.307h15.232l2.741,6.307H117.359Zm22.53,7.98a.567.567,0,0,1-.567.567h-5.577a.568.568,0,0,1-.567-.567v-1.588a.569.569,0,0,1,.567-.566l5.577-.453a.565.565,0,0,1,.567.567Z"
                                                                    transform="translate(-112.289 -126.994)"></path>
                                                            </g>
                                                        </svg>
                                                    </span> Transport
                                                @else
                                                    <span
                                                        class="w-7 h-7 rounded-full bg-gray-100 flex items-center justify-center shrink-0"><svg
                                                            class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z">
                                                            </path>
                                                        </svg></span> Lainnya
                                                @endif
                                            </p>
                                            @if($expense->km_total)
                                                <p class="text-xs text-gray-500">{{ number_format($expense->km_total, 2) }} KM</p>
                                            @endif
                                        </div>
                                        <p class="text-lg font-bold text-green-600">Rp
                                            {{ number_format($expense->amount, 0, ',', '.') }}
                                        </p>
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
                                        <span
                                            class="inline-block text-xs font-bold px-3 py-1 rounded-full {{ $statusColors[$status] ?? 'bg-gray-100 text-gray-500' }}">
                                            {{ $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status)) }}
                                        </span>
                                        @if($expense->revision_count > 0)
                                            <span
                                                class="inline-block text-xs font-bold px-2 py-1 rounded-full bg-gray-100 text-gray-600 ml-1">
                                                Revisi ke-{{ $expense->revision_count }}
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Foto Bon --}}
                                    @if($expense->photo_receipt)
                                        <div class="mb-3">
                                            <p class="text-xs font-bold text-gray-600 mb-2">Foto Bon / Struk</p>
                                            <button type="button"
                                                onclick="openImageModal('{{ route('expenses.receipt.show', $expense->id) }}')"
                                                class="block w-full p-0 bg-transparent border-0 focus:outline-none">
                                                <img src="{{ route('expenses.receipt.show', $expense->id) }}" alt="Foto Bon"
                                                    class="w-full rounded-xl border border-gray-200">
                                            </button>
                                        </div>
                                    @elseif($expense->isFuel() && \Carbon\Carbon::today()->lte(\App\Models\Expense::calculateDeadline($expense->dailyLog->date)))
                                        {{-- Tombol Lampirkan Struk untuk fuel expense yang belum punya struk --}}
                                        <div class="mb-3">
                                            <a href="{{ route('sales.fuel.receipt.form', $expense->id) }}"
                                                class="block w-full bg-blue-600 text-white py-3 rounded-xl font-bold text-sm text-center hover:bg-blue-700 transition">
                                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13">
                                                    </path>
                                                </svg> Lampirkan Struk Bahan Bakar
                                            </a>
                                            <p class="text-xs text-gray-500 mt-2 text-center">Belum ada struk yang dilampirkan</p>
                                        </div>
                                    @elseif($expense->isFuel())
                                        <div class="mb-3">
                                            <p class="text-xs text-gray-500 text-center italic">Struk belum dilampirkan (Batas waktu sudah
                                                lewat)</p>
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
                                            <p class="text-sm font-bold text-orange-800 mb-3"><svg class="w-4 h-4 inline mr-1" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                                    </path>
                                                </svg> Lakukan Revisi</p>

                                            {{-- OPSI: Generate Struk Parkir jika tipe parkir --}}
                                            @if($expense->type === 'parking')
                                                <div class="mb-3">
                                                    <label
                                                        class="flex items-center space-x-3 p-3 bg-blue-50 border border-blue-100 rounded-xl cursor-pointer">
                                                        <input type="checkbox" id="revise_generate_{{ $expense->id }}"
                                                            onchange="toggleReviseMode({{ $expense->id }})"
                                                            class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500">
                                                        <div>
                                                            <span class="block text-sm font-bold text-gray-700">Tidak ada struk?</span>
                                                            <span class="text-xs text-gray-500">Centang untuk buat struk otomatis</span>
                                                        </div>
                                                    </label>
                                                </div>

                                                {{-- Detail Parkir untuk Generate --}}
                                                <div id="revise_parking_details_{{ $expense->id }}"
                                                    class="hidden mb-3 p-3 bg-gray-50 rounded-lg border">
                                                    <p class="text-xs font-bold text-gray-700 mb-2">Detail untuk Struk:</p>
                                                    <input type="text" id="revise_plate_{{ $expense->id }}"
                                                        placeholder="No. Kendaraan (B 1234 ABC)"
                                                        class="w-full border border-gray-300 rounded-lg p-2 text-sm mb-2">
                                                    <input type="text" id="revise_location_{{ $expense->id }}" placeholder="Lokasi Parkir"
                                                        class="w-full border border-gray-300 rounded-lg p-2 text-sm">
                                                </div>
                                            @endif

                                            <form action="{{ route('sales.reimburse.revise', $expense->id) }}" method="POST"
                                                id="revise-form-{{ $expense->id }}">
                                                @csrf
                                                <input type="hidden" name="photo_receipt" id="revise_photo_{{ $expense->id }}">
                                                <input type="hidden" name="generate_receipt" id="revise_gen_flag_{{ $expense->id }}"
                                                    value="0">
                                                <input type="hidden" name="license_plate" id="revise_plate_input_{{ $expense->id }}">
                                                <input type="hidden" name="parking_location" id="revise_loc_input_{{ $expense->id }}">

                                                {{-- Bagian Kamera Langsung --}}
                                                <div id="revise_camera_section_{{ $expense->id }}" class="mb-3">
                                                    <p class="text-xs font-bold text-gray-600 mb-2"><svg class="w-4 h-4 inline mr-1"
                                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z">
                                                            </path>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        </svg> Ambil Foto Struk Baru *</p>
                                                    <div class="relative w-full h-48 bg-black rounded-xl overflow-hidden mb-2">
                                                        <video id="revise_video_{{ $expense->id }}" autoplay playsinline
                                                            class="w-full h-full object-cover"></video>
                                                        <canvas id="revise_canvas_{{ $expense->id }}"
                                                            class="hidden w-full h-full object-cover"></canvas>
                                                        <button type="button" onclick="switchReviseCamera({{ $expense->id }})"
                                                            class="absolute top-2 right-2 bg-white/30 backdrop-blur p-2 rounded-full text-white">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                                                </path>
                                                            </svg>
                                                        </button>
                                                        <button type="button" onclick="takeRevisePicture({{ $expense->id }})"
                                                            id="revise_snap_{{ $expense->id }}"
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
                                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m0 0l4 4m0 0l4-4m4 4V4">
                                                        </path>
                                                    </svg> Kirim Revisi
                                                </button>
                                            </form>
                                        </div>
                                    @endif

                                    {{-- ACTION BUTTONS HRD/IT --}}
                                    @if(in_array(Auth::user()->role, ['hrd', 'it']))
                                        <div class="mt-3 pt-3 border-t border-gray-200 flex gap-2">
                                            {{-- Generate Button --}}
                                            <button
                                                onclick="openGenerateModal({{ $expense->id }}, '{{ $expense->date->format('Y-m-d') }}', {{ $expense->amount }}, '{{ $expense->isFuel() ? 'SPBU' : ($expense->type == 'parking' ? 'Area Parkir' : '') }}')"
                                                class="flex-1 bg-indigo-50 text-indigo-700 py-2 rounded-lg text-xs font-bold hover:bg-indigo-100 transition flex justify-center items-center gap-1">
                                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                                </svg> Generate Struk
                                            </button>

                                            {{-- Delete/Revert Button --}}
                                            @if($expense->photo_receipt && $expense->is_generated_receipt)
                                                <button onclick="deleteReceipt({{ $expense->id }})"
                                                    class="flex-1 bg-orange-50 text-orange-700 py-2 rounded-lg text-xs font-bold hover:bg-orange-100 transition flex justify-center items-center gap-1">
                                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                                                    </svg> Kembalikan Asli
                                                </button>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endforeach

                            {{-- Total --}}
                            <div class="bg-green-50 rounded-xl p-4 border-2 border-green-200 mt-4">
                                <p class="text-2xl font-bold text-green-600">Rp
                                    {{ number_format($dailyLog->expenses->sum('amount'), 0, ',', '.') }}
                                </p>
                            </div>

                            {{-- TOMBOL TAMBAH BIAYA TAMBAHAN --}}
                            @if(\Carbon\Carbon::today()->lte(\App\Models\Expense::calculateDeadline($dailyLog->date)) && Auth::user()->role === 'sales')
                                <a href="{{ route('sales.reimburse.form', $dailyLog->id) }}"
                                    class="block w-full bg-green-600 text-white py-4 rounded-xl font-bold text-center mt-4 shadow-lg hover:bg-green-700 transition">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg> Tambah Biaya Tambahan
                                </a>
                            @endif
                        </div>

                        {{-- MODAL GENERATE RECEIPT (HRD/IT) --}}
                        @if(in_array(Auth::user()->role, ['hrd', 'it']))
                            <div id="modal-generate-receipt"
                                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
                                <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-sm mx-4">
                                    <h3 class="text-lg font-bold mb-4">Generate Struk Manual</h3>
                                    <form id="form-generate-receipt" onsubmit="submitGenerateReceipt(event)">
                                        <input type="hidden" id="gen-expense-id">
                                        <div class="mb-3">
                                            <label class="block text-xs font-bold text-gray-700 mb-1">Tanggal</label>
                                            <input type="date" id="gen-date"
                                                class="w-full border border-gray-300 rounded-lg p-2 text-sm" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="block text-xs font-bold text-gray-700 mb-1">Nominal</label>
                                            <input type="number" id="gen-amount"
                                                class="w-full border border-gray-300 rounded-lg p-2 text-sm" required>
                                        </div>
                                        <div class="mb-4">
                                            <label class="block text-xs font-bold text-gray-700 mb-1">Lokasi / Keterangan</label>
                                            <input type="text" id="gen-location"
                                                class="w-full border border-gray-300 rounded-lg p-2 text-sm"
                                                placeholder="Contoh: SPBU ... / Hotel ..." required>
                                        </div>
                                        <div class="flex gap-2">
                                            <button type="button" onclick="closeGenerateModal()"
                                                class="flex-1 bg-gray-100 text-gray-700 py-2 rounded-lg font-bold text-sm">Batal</button>
                                            <button type="submit"
                                                class="flex-1 bg-indigo-600 text-white py-2 rounded-lg font-bold text-sm">Generate</button>
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
                    [{{ $visit->lat }}, {{ $visit->long }}],
                @endif
            @endforeach
        ];

        // Prepare waypoints
        let waypoints = [];
        if (startLat && startLong) waypoints.push([startLat, startLong]);
        waypoints = waypoints.concat(visits);
        if (endLat && endLong) waypoints.push([endLat, endLong]);

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
                styles: [{ color: 'blue', opacity: 0.7, weight: 5 }]
            },
            show: false,
            createMarker: function (i, wp, nWps) {
                return L.marker(wp).bindPopup(i === 0 ? 'Start' : (i === nWps - 1 ? 'End' : 'Visit ' + i));
            }
        }).addTo(mapInstance);

        // Events
        routingControl.on('routesfound', function (e) {
            const summary = e.routes[0].summary;
            const distanceKm = (summary.totalDistance / 1000).toFixed(2);

            // Update UI
            document.getElementById('verified-distance-container').classList.remove('hidden');
            document.getElementById('verified-distance-value').innerText = distanceKm + ' KM';
            document.getElementById('verification-status').innerText = 'Baru saja diverifikasi';

            resetButton(true);
            saveDistance(distanceKm);
        });

        routingControl.on('routingerror', function (e) {
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
                document.getElementById('verification-status').innerHTML = '<svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Tersimpan ke server';
            })
            .catch(function (error) {
                console.error('Error saving distance:', error);
                document.getElementById('verification-status').innerHTML = '<svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg> Gagal menyimpan, mencoba ulang...';
                // Silent retry after 2 seconds
                setTimeout(function () {
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
        if (!confirm('Apakah anda yakin ingin mengembalikan struk ke versi asli (jika ada)?')) return;

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
            document.getElementById('revise_status_' + expenseId).innerHTML = '<svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg> Kamera tidak tersedia';
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
        status.innerHTML = '<svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Foto berhasil diambil';

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
        form.addEventListener('submit', function (e) {
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
    document.addEventListener('DOMContentLoaded', function () {
        // Init cameras when tab is clicked
        const reimburseTab = document.querySelector('[data-tab="reimburse"]');
        if (reimburseTab) {
            reimburseTab.addEventListener('click', function () {
                setTimeout(function () {
                    document.querySelectorAll('video[id^="revise_video_"]').forEach(video => {
                        const expenseId = video.id.replace('revise_video_', '');
                        initReviseCamera(expenseId);
                    });
                }, 300);
            });
        }
    });
</script>