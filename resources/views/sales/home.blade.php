@extends('layout')
@section('content')
@php
    // Tentukan log AKTIF (belum absen keluar) paling terakhir hari ini
    $activeLog = ($latestLog && !$latestLog->hasEnded() && \Carbon\Carbon::parse($latestLog->date)->isToday()) ? $latestLog : null;
    // Late-night checkout: log kemarin masih aktif
    if (!$activeLog && now()->hour < 4 && $latestLog && !$latestLog->hasEnded()) {
        $activeLog = $latestLog;
    }

    $inProgressVisit = null;
    $pendingVisitsToday = collect();
    $pendingCount = 0;
    if ($activeLog) {
        $inProgressVisit  = $activeLog->visits()->where('status', 'in_progress')->orderBy('arrival_time', 'desc')->first();
        $pendingVisitsToday = $activeLog->visits()->where('status', 'pending')->where('is_planned', true)->orderBy('id')->get();
        $pendingCount     = $pendingVisitsToday->count();
    }
    $allVisitsDone = $activeLog && !$inProgressVisit && $pendingCount === 0;

    // Total kunjungan hari ini (semua sesi)
    $totalVisitsToday = 0;
    foreach ($todayLogs as $log) { $totalVisitsToday += $log->visits->count(); }

    // Reimburse perlu revisi
    $expensesNeedingRevision = \App\Models\Expense::where('user_id', $user->id)
        ->whereIn('status', ['needs_revision_sales', 'needs_revision_spv'])->count();
@endphp

<div class="px-5 md:px-8 py-6 md:py-8 md:bg-slate-50/50 md:min-h-screen">
    <div class="md:grid md:grid-cols-12 md:gap-8 md:items-start">

        {{-- ========================================== --}}
        {{-- KOLOM UTAMA (kiri desktop / atas mobile)  --}}
        {{-- ========================================== --}}
        <div class="md:col-span-8 space-y-6">

            {{-- HEADER BIRU (responsive) --}}
            <div class="bg-blue-600 text-white p-6 md:p-10 rounded-3xl md:rounded-[2rem] shadow-lg md:shadow-xl relative overflow-hidden">
                <div class="absolute top-0 right-0 w-40 h-40 bg-white opacity-10 rounded-full -mr-12 -mt-12 pointer-events-none"></div>
                <div class="absolute -right-10 -top-10 w-48 h-48 bg-white opacity-5 rounded-full blur-3xl pointer-events-none hidden md:block"></div>
                <div class="relative z-10">
                    <h1 class="text-2xl md:text-4xl font-bold md:font-black mb-1 md:mb-3 md:tracking-tight">Halo, {{ explode(' ', $user->name)[0] }}!</h1>
                    <p class="text-blue-100 text-sm md:text-lg md:font-medium md:opacity-90 mb-5">Selamat bekerja, pantau target kunjungan Anda hari ini.</p>

                    {{-- Mini stats --}}
                    <div class="flex gap-3 md:gap-4 flex-wrap">
                        <div class="bg-white/15 backdrop-blur-sm rounded-xl px-4 py-3 border border-white/10">
                            <p class="text-[10px] md:text-xs text-blue-100 mb-0.5 md:mb-1">Status</p>
                            @if(!$activeLog)
                                @if($latestLog && $latestLog->hasEnded())
                                    <p class="text-sm md:text-lg font-bold">Selesai Kerja</p>
                                @else
                                    <p class="text-sm md:text-lg font-bold">Belum Absen</p>
                                @endif
                            @else
                                <p class="text-sm md:text-lg font-bold">Sedang Kerja</p>
                            @endif
                        </div>
                        <div class="bg-white/15 backdrop-blur-sm rounded-xl px-4 py-3 border border-white/10">
                            <p class="text-[10px] md:text-xs text-blue-100 mb-0.5 md:mb-1">Total Kunjungan</p>
                            <p class="text-sm md:text-lg font-bold">{{ $totalVisitsToday }}</p>
                        </div>
                        @if($activeLog && $pendingCount > 0)
                        <div class="bg-amber-400/30 backdrop-blur-sm rounded-xl px-4 py-3 border border-amber-200/30">
                            <p class="text-[10px] md:text-xs text-amber-50 mb-0.5 md:mb-1">Belum Dikunjungi</p>
                            <p class="text-sm md:text-lg font-bold">{{ $pendingCount }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- PENGINGAT (PERINGATAN KUAT): kunjungan belum selesai / belum absen keluar --}}
            @if($activeLog)
                @if($inProgressVisit)
                    <a href="{{ route('sales.absen.toko.checkout', $inProgressVisit->id) }}"
                        class="relative flex items-center gap-3 bg-red-500 text-white rounded-2xl md:rounded-[2rem] p-4 md:p-5 shadow-lg shadow-red-500/40 hover:bg-red-600 transition overflow-hidden active:scale-[0.98]">
                        <span class="absolute left-0 inset-y-0 w-1.5 bg-red-700"></span>
                        <svg class="w-6 h-6 md:w-7 md:h-7 shrink-0 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4a2 2 0 00-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z"></path></svg>
                        <span class="flex-1 text-xs md:text-sm font-bold">PERHATIKAN! Kamu masih di <strong>{{ $inProgressVisit->client_name }}</strong>. Check-out dulu sebelum pindah toko.</span>
                        <svg class="w-5 h-5 shrink-0 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                @elseif($pendingCount > 0)
                    <a href="{{ route('sales.absen.toko.checkin') }}"
                        class="relative flex items-center gap-3 bg-red-500 text-white rounded-2xl md:rounded-[2rem] p-4 md:p-5 shadow-lg shadow-red-500/40 hover:bg-red-600 transition overflow-hidden active:scale-[0.98]">
                        <span class="absolute left-0 inset-y-0 w-1.5 bg-red-700"></span>
                        <svg class="w-6 h-6 md:w-7 md:h-7 shrink-0 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4a2 2 0 00-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z"></path></svg>
                        <span class="flex-1 text-xs md:text-sm font-bold">PERHATIKAN! Masih ada <strong>{{ $pendingCount }} toko</strong> belum dikunjungi hari ini.</span>
                        <svg class="w-5 h-5 shrink-0 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                @elseif($allVisitsDone)
                    <div x-data="{ choiceOpen: false }">
                        <button type="button" @click="choiceOpen = true"
                            class="relative w-full flex items-center gap-3 bg-orange-500 text-white rounded-2xl md:rounded-[2rem] p-4 md:p-5 shadow-lg shadow-orange-500/40 hover:bg-orange-600 transition overflow-hidden active:scale-[0.98]">
                            <span class="absolute left-0 inset-y-0 w-1.5 bg-orange-700"></span>
                            <svg class="w-6 h-6 md:w-7 md:h-7 shrink-0 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4a2 2 0 00-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z"></path></svg>
                            <span class="flex-1 text-left text-xs md:text-sm font-bold">PENTING! Semua kunjungan rencana selesai. Klik untuk <strong>absen keluar</strong> atau <strong>tambah kunjungan dadakan</strong>.</span>
                            <svg class="w-5 h-5 shrink-0 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                        </button>

                        {{-- Bottom sheet pilihan (pola sama dgn layout.blade.php) --}}
                        <template x-teleport="body">
                            <div x-show="choiceOpen" x-cloak
                                 @keydown.escape.window="choiceOpen = false"
                                 class="fixed inset-0 z-[100] flex items-end justify-center bg-black/40"
                                 @click.self="choiceOpen = false">
                                <div x-show="choiceOpen"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="translate-y-full"
                                     x-transition:enter-end="translate-y-0"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="translate-y-0"
                                     x-transition:leave-end="translate-y-full"
                                     class="relative bg-white w-full max-w-[480px] rounded-t-3xl p-5 pb-8 shadow-2xl">
                                    <div class="w-10 h-1 bg-gray-300 rounded-full mx-auto mb-4"></div>
                                    <h3 class="font-bold text-gray-800 text-base mb-1 text-center">Semua Kunjungan Selesai</h3>
                                    <p class="text-xs text-gray-500 text-center mb-5">Apa yang ingin Anda lakukan selanjutnya?</p>
                                    <div class="space-y-3">
                                        <a href="{{ route('sales.absen.toko.checkin') }}"
                                            class="flex items-center gap-3 w-full bg-blue-600 text-white p-4 rounded-2xl font-bold text-sm active:scale-95 transition">
                                            <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                            <span class="text-left flex-1"><span class="block">Kunjungan Dadakan</span><span class="block text-xs font-normal text-blue-100">Tambah toko baru</span></span>
                                        </a>
                                        <a href="{{ route('sales.absen.keluar') }}"
                                            class="flex items-center gap-3 w-full bg-red-600 text-white p-4 rounded-2xl font-bold text-sm active:scale-95 transition">
                                            <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                            <span class="text-left flex-1"><span class="block">Absen Keluar</span><span class="block text-xs font-normal text-red-100">Tutup sesi hari ini</span></span>
                                        </a>
                                    </div>
                                    <button type="button" @click="choiceOpen = false"
                                        class="w-full mt-4 py-3 text-gray-500 font-bold text-sm">Batal</button>
                                </div>
                            </div>
                        </template>
                    </div>
                @endif
            @endif

            {{-- NOTIF REIMBURSE --}}
            @if($expensesNeedingRevision > 0)
                <a href="{{ route('sales.history') }}" class="block bg-orange-50 border-2 border-orange-300 rounded-2xl md:rounded-[2rem] p-4 md:p-6 hover:bg-orange-100 transition">
                    <div class="flex items-center gap-3 md:gap-5">
                        <div class="w-10 h-10 md:w-14 md:h-14 bg-orange-500 text-white rounded-full md:rounded-2xl flex items-center justify-center font-bold text-base md:text-xl shrink-0">{{ $expensesNeedingRevision }}</div>
                        <div class="flex-1">
                            <p class="font-bold text-orange-800 md:text-lg">Reimburse Perlu Revisi</p>
                            <p class="text-xs md:text-sm text-orange-600">Klik untuk melihat dan merevisi</p>
                        </div>
                        <svg class="w-5 h-5 md:w-6 md:h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </div>
                </a>
            @endif

            {{-- KARTU AKTIVITAS HARI INI (timeline tunggal sebagai progress, tanpa duplikasi) --}}
            @php
                // Badge progress: hitung toko selesai / total dari log hari ini
                $badgeLog = $activeLog ?? ($latestLog && $latestLog->hasEnded() ? $latestLog : null);
                $sCompleted = $badgeLog ? $badgeLog->visits()->whereIn('status', ['completed','failed'])->count() : 0;
                $sTotal = $badgeLog ? $badgeLog->visits()->count() : 0;
            @endphp
            <div class="bg-white rounded-3xl md:rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 md:px-8 py-4 md:py-6 border-b border-gray-100 flex justify-between items-center gap-2 bg-gray-50/50">
                    <h2 class="font-bold md:font-extrabold text-base md:text-xl text-gray-800">Aktivitas Hari Ini</h2>
                    @if($sTotal > 0)
                        <span class="text-xs font-bold text-blue-600 bg-blue-50 px-3 py-1 rounded-full whitespace-nowrap">{{ $sCompleted }}/{{ $sTotal }} toko</span>
                    @endif
                </div>

                <div class="p-5 md:p-8">
                    @forelse($todayLogs as $dailyLog)
                        @if(!$loop->first)
                            <div class="flex items-center gap-2 md:gap-4 my-6 md:my-8 opacity-50">
                                <div class="h-px bg-gray-300 flex-1"></div>
                                <span class="text-[10px] md:text-xs font-bold text-gray-500 tracking-widest uppercase">Sesi Sebelumnya</span>
                                <div class="h-px bg-gray-300 flex-1"></div>
                            </div>
                        @endif

                        <div class="relative before:absolute before:inset-0 before:ml-5 md:before:ml-7 before:-translate-x-px before:h-full before:w-0.5 before:bg-gradient-to-b before:from-gray-100 before:via-gray-200 before:to-gray-100 space-y-4 md:space-y-6">

                        {{-- Absen Keluar (jika ada) --}}
                        @if ($dailyLog->hasEnded())
                            @include('sales.partials._timeline_item', [
                                'bg' => 'bg-red-100 text-red-600 md:bg-red-50 md:hover:bg-red-500 md:hover:text-white md:group-hover:bg-red-500',
                                'icon' => 'logout',
                                'title' => 'Absen Keluar',
                                'time' => $dailyLog->end_time,
                                'sub' => match($dailyLog->end_type) { 'home' => 'Pulang ke rumah', 'last_store' => 'Dari toko terakhir', 'auto_rollover' => 'Ditutup otomatis (lupa absen keluar)', default => 'Lokasi lain' },
                            ])
                        @endif

                        {{-- Daftar Kunjungan --}}
                        @foreach($dailyLog->visits()->orderBy('arrival_time', 'desc')->get() as $item)
                            @php
                                $vBg = match($item->status) {
                                    'completed' => 'bg-blue-100 text-blue-600',
                                    'failed' => 'bg-red-100 text-red-600',
                                    'in_progress' => 'bg-amber-100 text-amber-600',
                                    default => 'bg-gray-100 text-gray-400'
                                };
                                $vIcon = match($item->status) {
                                    'completed' => 'check',
                                    'failed' => 'x',
                                    'in_progress' => 'clock',
                                    default => 'clock'
                                };
                                $vTitle = $item->client_name . (!$item->is_planned ? ' (Dadakan)' : '');
                                $vTime = $item->arrival_time ? \Carbon\Carbon::parse($item->arrival_time)->format('H:i') . ' - ' . ($item->departure_time ? \Carbon\Carbon::parse($item->departure_time)->format('H:i') : '...') : null;
                            @endphp
                            @include('sales.partials._timeline_item', [
                                'bg' => $vBg,
                                'icon' => $vIcon,
                                'title' => $vTitle,
                                'time' => null,
                                'subTime' => $vTime,
                                'badge' => match($item->status) { 'in_progress' => 'Di Toko', 'pending' => 'Pending', 'completed' => 'Selesai', 'failed' => 'Gagal' },
                                'badgeColor' => match($item->status) { 'in_progress' => 'amber', 'pending' => 'gray', 'completed' => 'green', 'failed' => 'red' },
                                'sub' => $item->status === 'failed' ? $item->reason : ($item->status === 'completed' ? $item->notes : ''),
                            ])
                        @endforeach

                        {{-- Absen Masuk --}}
                        @include('sales.partials._timeline_item', [
                            'bg' => 'bg-green-100 text-green-600 md:bg-green-50 md:hover:bg-green-500 md:hover:text-white',
                            'icon' => 'login',
                            'title' => 'Absen Masuk',
                            'time' => $dailyLog->start_time,
                            'sub' => 'Plan: ' . $dailyLog->daily_plan,
                        ])
                        </div>

                    @empty
                        <div class="text-center py-10 md:py-20 opacity-50">
                            <div class="w-16 h-16 md:w-24 md:h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4 md:mb-6">
                                <svg class="w-8 h-8 md:w-12 md:h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <p class="text-sm md:text-xl text-gray-500 font-bold mb-1 md:mb-2">Belum Ada Aktivitas</p>
                            <p class="text-xs md:text-base text-gray-400">Mulai dengan Absen Masuk untuk melacak aktivitas hari ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ========================================== --}}
        {{-- SIDEBAR KANAN (desktop sticky / mobile inline) --}}
        {{-- ========================================== --}}
        <div class="hidden md:block md:col-span-4 space-y-6 md:sticky md:top-8">

            {{-- KARTU STATUS + CTA --}}
            <div class="bg-white rounded-3xl md:rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 md:px-8 py-4 md:py-5 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-800 text-xs md:text-sm uppercase tracking-wider">Status Kehadiran</h3>
                </div>
                <div class="p-5 md:p-8 text-center">
                    <div class="w-20 h-20 md:w-24 md:h-24 rounded-full mx-auto flex items-center justify-center mb-4 md:mb-6 shadow-md border-4 border-white ring-4 ring-gray-50
                        @if(!$activeLog && !($latestLog && $latestLog->hasEnded())) bg-gray-100 text-gray-400
                        @elseif($activeLog) bg-blue-600 text-white
                        @else bg-green-500 text-white @endif">
                        @if(!$activeLog)
                            @if($latestLog && $latestLog->hasEnded())
                                <svg class="w-9 h-9 md:w-10 md:h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            @else
                                <svg class="w-9 h-9 md:w-10 md:h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                            @endif
                        @else
                            <svg class="w-9 h-9 md:w-10 md:h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        @endif
                    </div>

                    @if(!$activeLog)
                        @if($latestLog && $latestLog->hasEnded())
                            <h4 class="text-xl md:text-2xl font-black text-gray-800 mb-1">Selesai Kerja</h4>
                            <p class="text-gray-500 font-medium text-sm">Sesi hari ini telah ditutup.</p>
                        @else
                            <h4 class="text-xl md:text-2xl font-black text-gray-800 mb-1">Belum Absen</h4>
                            <p class="text-gray-500 font-medium text-sm">Mulai hari kerja Anda sekarang.</p>
                        @endif
                    @else
                        <h4 class="text-xl md:text-2xl font-black text-blue-900 mb-1">Sedang Bekerja</h4>
                        <p class="text-blue-600 font-medium text-sm">Sesi aktif sedang berjalan.</p>
                    @endif
                </div>

                {{-- CTA KONTEKSTUAL --}}
                <div class="px-5 md:px-8 pb-6 md:pb-8 space-y-3">
                    @if(!$activeLog)
                        {{-- Belum absen / lembur --}}
                        @if($latestLog && $latestLog->hasEnded())
                            <a href="{{ route('sales.absen.masuk') }}" onclick="return confirm('Mulai sesi baru (Lembur/Emergency)?')"
                                class="flex items-center justify-center gap-2 w-full bg-white text-blue-700 p-4 md:p-5 rounded-2xl md:rounded-[2rem] border-2 border-blue-200 border-dashed hover:bg-blue-50 hover:border-blue-400 transition font-bold text-sm md:text-lg">
                                <svg class="w-5 h-5 md:w-7 md:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                Kunjungan Tambahan (Urgent)
                            </a>
                        @else
                            <a href="{{ route('sales.absen.masuk') }}"
                                class="flex items-center justify-center gap-2 w-full bg-blue-600 text-white p-5 md:p-6 rounded-2xl md:rounded-[2rem] shadow-lg md:shadow-xl md:shadow-blue-600/20 hover:bg-blue-700 transition font-bold text-base md:text-xl active:scale-95">
                                <svg class="w-6 h-6 md:w-8 md:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                                Absen Masuk
                            </a>
                        @endif
                    @elseif($inProgressVisit)
                        {{-- Sedang di toko: lanjut check-out --}}
                        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 mb-1">
                            <p class="text-xs text-amber-700 font-bold flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Sedang berada di toko
                            </p>
                            <p class="text-sm font-bold text-amber-900 mt-1">{{ $inProgressVisit->client_name }}</p>
                        </div>
                        <a href="{{ route('sales.absen.toko.checkout', $inProgressVisit->id) }}"
                            class="flex items-center justify-center gap-2 w-full bg-green-600 text-white p-5 md:p-6 rounded-2xl md:rounded-[2rem] shadow-lg md:shadow-xl md:shadow-green-600/20 hover:bg-green-700 transition font-bold text-base md:text-xl active:scale-95">
                            <svg class="w-6 h-6 md:w-8 md:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            Check-out Toko
                        </a>
                        @if($pendingCount > 0)
                            <p class="text-xs text-center text-gray-500">Masih ada {{ $pendingCount }} toko lain menunggu check-in.</p>
                        @endif
                    @elseif($pendingCount > 0)
                        {{-- Ada toko pending: ajak check-in --}}
                        <a href="{{ route('sales.absen.toko.checkin') }}"
                            class="flex items-center justify-center gap-2 w-full bg-blue-600 text-white p-5 md:p-6 rounded-2xl md:rounded-[2rem] shadow-lg md:shadow-xl md:shadow-blue-600/20 hover:bg-blue-700 transition font-bold text-base md:text-xl active:scale-95">
                            <svg class="w-6 h-6 md:w-8 md:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            Lapor Kunjungan ({{ $pendingCount }})
                        </a>
                        {{-- Quick pick toko --}}
                        <div class="space-y-2">
                            @foreach($pendingVisitsToday->take(3) as $pv)
                                <a href="{{ route('sales.absen.toko.checkin', $pv->id) }}" class="flex items-center gap-2 p-3 rounded-xl border border-gray-200 hover:border-blue-400 hover:bg-blue-50/50 transition text-sm">
                                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                    <span class="flex-1 font-medium text-gray-700 truncate">{{ $pv->client_name }}</span>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                </a>
                            @endforeach
                        </div>
                    @else
                        {{-- Semua rencana selesai: absen keluar + opsi kunjungan dadakan --}}
                        <a href="{{ route('sales.absen.keluar') }}"
                            class="flex items-center justify-center gap-2 w-full bg-red-600 text-white p-5 md:p-6 rounded-2xl md:rounded-[2rem] shadow-lg md:shadow-xl md:shadow-red-600/20 hover:bg-red-700 transition font-bold text-base md:text-xl active:scale-95">
                            <svg class="w-6 h-6 md:w-8 md:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            Absen Keluar
                        </a>
                        <a href="{{ route('sales.absen.toko.checkin') }}"
                            class="flex items-center justify-center gap-2 w-full bg-white text-blue-700 p-4 md:p-5 rounded-2xl md:rounded-[2rem] border-2 border-blue-200 border-dashed hover:bg-blue-50 hover:border-blue-400 transition font-bold text-sm md:text-lg">
                            <svg class="w-5 h-5 md:w-7 md:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            Kunjungan Dadakan
                        </a>
                        <p class="text-xs text-center text-green-600 font-medium flex items-center justify-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Semua kunjungan rencana selesai!
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
