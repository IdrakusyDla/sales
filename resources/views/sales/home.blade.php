@extends('layout')
@section('content')
    {{-- 1. HEADER PROFIL (Simpel) --}}
    <div class="bg-blue-600 text-white p-6 rounded-b-3xl shadow-lg mb-6 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-10 rounded-full -mr-10 -mt-10 pointer-events-none">
        </div>

        <div class="relative z-10 mb-6">
            <h1 class="text-2xl font-bold mb-1">Halo, {{ explode(' ', $user->name)[0] }}!</h1>
            <p class="text-blue-100 text-sm">Selamat Bekerja</p>
        </div>

        {{-- Kartu Status --}}
        <div
            class="bg-white/20 backdrop-blur-sm rounded-xl p-4 flex justify-between items-center relative z-10 border border-white/10">
            <div>
                <p class="text-xs text-blue-100 mb-1">Status Hari Ini</p>
                @if ($latestLog)
                    @if ($latestLog->hasEnded())
                        <div class="flex items-center gap-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-lg font-bold">Selesai Kerja</p>
                        </div>
                    @else
                        <div class="flex items-center gap-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            <p class="text-lg font-bold">Sedang Kerja</p>
                        </div>
                    @endif
                @else
                    <div class="flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                        </svg>
                        <p class="text-lg font-bold">Belum Absen</p>
                    </div>
                @endif
            </div>
            <div class="text-right">
                <p class="text-xs text-blue-100 mb-1">Total Kunjungan</p>
                @php
                    $totalVisits = 0;
                    foreach($todayLogs as $log) {
                        $totalVisits += $log->visits->count();
                    }
                @endphp
                <p class="text-xl font-bold">{{ $totalVisits }}</p>
            </div>
        </div>
    </div>

    {{-- NOTIFIKASI REVISI REIMBURSE --}}
    @php
        $expensesNeedingRevision = \App\Models\Expense::where('user_id', $user->id)
            ->where('status', 'needs_revision_sales')
            ->count();
    @endphp
    @if($expensesNeedingRevision > 0)
        <div class="mx-5 mb-4">
            <a href="{{ route('sales.history') }}"
                class="block bg-orange-50 border-2 border-orange-300 rounded-xl p-4 hover:bg-orange-100 transition">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 bg-orange-500 text-white rounded-full flex items-center justify-center font-bold text-lg">
                        {{ $expensesNeedingRevision }}
                    </div>
                    <div class="flex-1">
                        <p class="font-bold text-orange-800">Reimburse Perlu Revisi</p>
                        <p class="text-xs text-orange-600">Klik untuk melihat dan merevisi</p>
                    </div>
                    <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
            </a>
        </div>
    @endif

    {{-- 2. TOMBOL ABSENSI UTAMA (Mengacu pada latestLog) --}}
    @php
        // Cek pending visits dari log yang sedang aktif (jika ada)
        $hasPendingVisits = $latestLog ? $latestLog->visits()->where('status', 'pending')->count() > 0 : false;
        $isTodayLog = $latestLog ? \Carbon\Carbon::parse($latestLog->date)->isToday() : false;
    @endphp

    @if (!$latestLog)
        {{-- A. Jika Belum Absen Masuk Hari Ini --}}
        <div class="px-5 mb-6">
            <a href="{{ route('sales.absen.masuk') }}"
                class="block w-full bg-gradient-to-r from-blue-500 to-blue-600 text-white p-5 rounded-2xl shadow-lg text-center font-bold text-lg hover:from-blue-600 hover:to-blue-700 transition transform active:scale-95">
                <div class="flex items-center justify-center gap-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span>Absen Masuk</span>
                </div>
                <p class="text-sm font-normal mt-2 opacity-90">Mulai hari kerja Anda</p>
            </a>
        </div>
    @elseif ($latestLog && !$latestLog->hasEnded())
        {{-- B. Jika Sedang Kerja (Belum Checkout) --}}
        @if (!$hasPendingVisits)
            <div class="px-5 mb-6">
                <a href="{{ route('sales.absen.keluar') }}"
                    class="block w-full bg-gradient-to-r from-red-500 to-red-600 text-white p-5 rounded-2xl shadow-lg text-center font-bold text-lg hover:from-red-600 hover:to-red-700 transition transform active:scale-95">
                    <div class="flex items-center justify-center gap-3">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        <span>Absen Keluar</span>
                    </div>
                    <p class="text-sm font-normal mt-2 opacity-90">Selesaikan hari kerja Anda</p>
                </a>
            </div>
        @else
            <div class="px-5 mb-6">
                <div class="bg-yellow-50 border-2 border-yellow-200 text-yellow-800 p-4 rounded-2xl text-center">
                    <div class="flex items-center justify-center gap-2 mb-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <p class="font-bold text-sm">Selesaikan Semua Kunjungan Terlebih Dahulu</p>
                    </div>
                    <p class="text-xs opacity-80">Anda masih memiliki {{ $latestLog->visits()->where('status', 'pending')->count() }}
                        kunjungan yang belum dilaporkan</p>
                </div>
            </div>
        @endif
    @elseif ($latestLog && $latestLog->hasEnded())
        {{-- C. Jika Sudah Selesai (Bisa Absen Lagi/Lembur) --}}
        <div class="px-5 mb-6">
            <a href="{{ route('sales.absen.masuk') }}"
                onclick="return confirm('Apakah Anda ingin memulai sesi baru (Lembur/Emergency)?')"
                class="block w-full bg-white border-2 border-dashed border-blue-300 text-blue-600 p-4 rounded-2xl text-center hover:bg-blue-50 transition">
                <div class="flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    <span class="font-bold">Kunjungan Tambahan (Urgent)</span>
                </div>
            </a>
        </div>
    @endif

    {{-- 3. TIMELINE AKTIVITAS (Loop Semua Log Hari Ini) --}}
    <div class="px-5 pb-24">
        <h2 class="text-gray-800 font-bold text-lg mb-4">Aktivitas Hari Ini</h2>

        @foreach($todayLogs as $dailyLog)
            {{-- SEPARATOR JIKA MULTIPLE LOG --}}
            @if(!$loop->first)
                <div class="flex items-center gap-2 my-6 opacity-50">
                    <div class="h-px bg-gray-300 flex-1"></div>
                    <span class="text-xs font-bold text-gray-500">SESI SEBELUMNYA</span>
                    <div class="h-px bg-gray-300 flex-1"></div>
                </div>
            @endif

            {{-- A. Log Absen Keluar (Jika Ada) --}}
            @if ($dailyLog->hasEnded())
                <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex items-start space-x-4 mb-4">
                    <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <div class="flex justify-between">
                            <h3 class="font-bold text-gray-800">Absen Keluar</h3>
                            <span
                                class="text-xs text-gray-400 font-mono">{{ \Carbon\Carbon::parse($dailyLog->end_time)->format('H:i') }}</span>
                        </div>
                        @if ($dailyLog->end_type)
                            <p class="text-xs text-gray-500 mt-1">
                                @if ($dailyLog->end_type == 'home')
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                    </svg>
                                    Pulang ke rumah
                                @elseif ($dailyLog->end_type == 'last_store')
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                    Dari toko terakhir
                                @else
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    Lokasi lain
                                @endif
                            </p>
                        @endif
                    </div>
                </div>
            @endif

            {{-- B. List Kunjungan --}}
            <div class="space-y-4 mb-4">
                @foreach($dailyLog->visits()->orderBy('time', 'desc')->get() as $item)
                    <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex items-start space-x-4">
                        <div
                            class="w-12 h-12 rounded-full flex items-center justify-center shrink-0
                                                                        {{ $item->status == 'pending' ? 'bg-gray-100' : ($item->status == 'failed' ? 'bg-red-100' : 'bg-blue-100') }}">
                            @if ($item->status == 'completed')
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            @elseif($item->status == 'failed')
                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            @else
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            @endif
                        </div>

                        <div class="flex-1">
                            <div class="flex justify-between items-start">
                                <h3 class="font-bold text-gray-800">{{ $item->client_name }}</h3>
                                @if ($item->status != 'pending')
                                    <span
                                        class="text-xs text-gray-400 font-mono">{{ \Carbon\Carbon::parse($item->time)->format('H:i') }}</span>
                                @endif
                            </div>
                            @if ($item->status == 'pending')
                                <span
                                    class="inline-block mt-2 bg-gray-100 text-gray-500 text-[10px] font-bold px-2 py-0.5 rounded-full">Pending</span>
                            @elseif($item->status == 'failed')
                                <p class="text-xs text-red-500 mt-1 line-clamp-1">{{ $item->reason }}</p>
                            @else
                                <p class="text-xs text-gray-500 mt-1 line-clamp-1">{{ $item->notes }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- C. Log Absen Masuk --}}
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex items-start space-x-4 mb-4">
                <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <div class="flex justify-between">
                        <h3 class="font-bold text-gray-800">Absen Masuk</h3>
                        <span
                            class="text-xs text-gray-400 font-mono">{{ \Carbon\Carbon::parse($dailyLog->start_time)->format('H:i') }}</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 line-clamp-1">Plan: {{ $dailyLog->daily_plan }}</p>
                </div>
            </div>

        @endforeach

        @if ($todayLogs->isEmpty())
             <div class="text-center py-10 opacity-50">
                <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-sm text-gray-500">Selamat bekerja, {{ explode(' ', $user->name)[0] }}!</p>
            </div>
        @endif
    </div>
@endsection
