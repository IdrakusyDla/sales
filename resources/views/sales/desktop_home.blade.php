{{-- ========================================== --}}
{{-- TAMPILAN DESKTOP (>= 768px): KODE BARU     --}}
{{-- ========================================== --}}
<div class="hidden md:block px-8 py-8 min-h-screen bg-slate-50/50">
    <div class="grid grid-cols-12 gap-8 items-start">
        
        {{-- ========================================== --}}
        {{-- KOLOM KIRI: 8 KOLOM (WELCOME & TIMELINE)   --}}
        {{-- ========================================== --}}
        <div class="col-span-8 space-y-8">
            
            {{-- WELCOME BANNER --}}
            <div class="bg-blue-600 rounded-[2rem] p-10 text-white shadow-xl relative overflow-hidden">
                <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"></div>
                <div class="absolute -right-10 -top-10 w-64 h-64 bg-white opacity-5 rounded-full blur-3xl pointer-events-none"></div>
                <div class="relative z-10">
                    <h1 class="text-4xl font-black mb-3 tracking-tight">Halo, {{ explode(' ', $user->name)[0] }}!</h1>
                    <p class="text-blue-100 text-lg font-medium opacity-90 max-w-lg">Selamat bekerja! Pantau target kunjungan dan selesaikan rute Anda hari ini dengan penuh semangat.</p>
                </div>
            </div>

            {{-- TIMELINE AKTIVITAS --}}
            <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-8 py-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <h2 class="font-extrabold text-xl text-gray-800 tracking-tight">Timeline Aktivitas</h2>
                </div>
                <div class="p-8">
                    @forelse($todayLogs as $dailyLog)
                        @if(!$loop->first)
                            <div class="flex items-center gap-4 my-8 opacity-50">
                                <div class="h-px bg-gray-300 flex-1"></div>
                                <span class="text-xs font-bold text-gray-500 tracking-widest uppercase">Sesi Sebelumnya</span>
                                <div class="h-px bg-gray-300 flex-1"></div>
                            </div>
                        @endif

                        <div class="relative before:absolute before:inset-0 before:ml-7 before:-translate-x-px before:h-full before:w-0.5 before:bg-gradient-to-b before:from-gray-100 before:via-gray-200 before:to-gray-100 space-y-8">
                        {{-- Keluar (Jika Ada) --}}
                        @if ($dailyLog->hasEnded())
                            <div class="relative flex items-start gap-6 group">
                                <div class="w-14 h-14 rounded-2xl bg-red-50 text-red-600 flex items-center justify-center shrink-0 border-4 border-white shadow-sm group-hover:scale-110 group-hover:bg-red-500 group-hover:text-white transition-all z-10 relative">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                </div>
                                <div class="flex-1 bg-gray-50 rounded-2xl p-5 border border-gray-100 hover:shadow-md transition">
                                    <div class="flex justify-between items-center mb-2">
                                        <h3 class="font-bold text-gray-800 text-lg">Absen Keluar</h3>
                                        <span class="text-sm font-mono bg-white px-3 py-1 rounded-lg shadow-sm font-medium text-gray-600 border border-gray-200">{{ \Carbon\Carbon::parse($dailyLog->end_time)->format('H:i') }}</span>
                                    </div>
                                    @if ($dailyLog->end_type)
                                        <p class="text-sm text-gray-600 flex items-center gap-2">
                                            @if ($dailyLog->end_type == 'home') Pulang ke rumah
                                            @elseif ($dailyLog->end_type == 'last_store') Dari toko terakhir
                                            @else Lokasi lain @endif
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- DAFTAR KUNJUNGAN --}}
                        @foreach($dailyLog->visits()->orderBy('time', 'desc')->get() as $item)
                            <div class="relative flex items-start gap-6 group">
                                <div class="w-14 h-14 rounded-full flex items-center justify-center shrink-0 border-4 border-white shadow-sm z-10 relative transition-transform group-hover:scale-110
                                    {{ $item->status == 'pending' ? 'bg-gray-100 text-gray-400' : ($item->status == 'failed' ? 'bg-red-100 text-red-500' : 'bg-blue-100 text-blue-500') }}">
                                    @if ($item->status == 'completed') <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    @elseif($item->status == 'failed') <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    @else <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> @endif
                                </div>
                                <div class="flex-1 bg-white p-5 rounded-2xl shadow-sm border hover:shadow-md transition
                                    {{ $item->status == 'pending' ? 'border-gray-200' : ($item->status == 'failed' ? 'border-red-200/50' : 'border-blue-100') }}">
                                    <div class="flex justify-between items-start mb-2">
                                        <h3 class="font-bold text-gray-800 text-lg">{{ $item->client_name }}</h3>
                                        @if ($item->status != 'pending') <span class="text-sm font-mono text-gray-500 bg-gray-50 px-2.5 py-1 rounded-md border border-gray-100">{{ \Carbon\Carbon::parse($item->time)->format('H:i') }}</span> @endif
                                    </div>
                                    @if ($item->status == 'pending') <span class="inline-block bg-gray-100 text-gray-500 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">Pending</span>
                                    @elseif($item->status == 'failed') <p class="text-sm text-red-600 bg-red-50 p-3 rounded-xl border border-red-100/50 mt-2">{{ $item->reason }}</p>
                                    @else <p class="text-sm text-gray-600 bg-blue-50/50 p-3 rounded-xl border border-blue-50 mt-2">{{ $item->notes }}</p> @endif
                                </div>
                            </div>
                        @endforeach

                        {{-- MASUK AWAL --}}
                        <div class="relative flex items-start gap-6 group">
                            <div class="w-14 h-14 rounded-2xl bg-green-50 text-green-600 flex items-center justify-center shrink-0 border-4 border-white shadow-sm group-hover:scale-110 group-hover:bg-green-500 group-hover:text-white transition-all z-10 relative">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </div>
                            <div class="flex-1 bg-gray-50 rounded-2xl p-5 border border-gray-100 hover:shadow-md transition">
                                <div class="flex justify-between items-center mb-2">
                                    <h3 class="font-bold text-gray-800 text-lg">Absen Masuk</h3>
                                    <span class="text-sm font-mono bg-white px-3 py-1 rounded-lg shadow-sm font-medium text-gray-600 border border-gray-200">{{ \Carbon\Carbon::parse($dailyLog->start_time)->format('H:i') }}</span>
                                </div>
                                <p class="text-sm text-gray-600 flex items-center gap-2">Plan: <span class="font-medium">{{ $dailyLog->daily_plan }}</span></p>
                            </div>
                        </div>
                        </div>

                    @empty
                        <div class="text-center py-20 opacity-50">
                            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <p class="text-xl text-gray-500 font-bold mb-2">Belum Ada Aktivitas</p>
                            <p class="text-gray-400">Silakan lakukan Absen Masuk untuk mulai melacak aktivitas Anda hari ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ========================================== --}}
        {{-- KOLOM KANAN: 4 KOLOM (STATUS & ACTION)       --}}
        {{-- ========================================== --}}
        <div class="col-span-4 space-y-8 sticky top-8">
            
            {{-- KARTU STATUS --}}
            <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden transform transition hover:shadow-md">
                <div class="px-8 py-5 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-800 text-sm uppercase tracking-wider">Status Kehadiran</h3>
                    @php
                        $totalVisits = 0;
                        foreach ($todayLogs as $log) { $totalVisits += $log->visits->count(); }
                    @endphp
                    <span class="bg-blue-100 text-blue-700 text-xs font-black px-3 py-1 rounded-full shadow-inner">{{ $totalVisits }} Kunjungan</span>
                </div>
                <div class="p-8 text-center">
                    <div class="w-24 h-24 rounded-full mx-auto flex items-center justify-center mb-6 shadow-md border-4 border-white ring-4 ring-gray-50
                        @if(!$latestLog) bg-gray-100 text-gray-400
                        @elseif($latestLog && !$latestLog->hasEnded()) bg-blue-600 text-white
                        @else bg-green-500 text-white @endif">
                        @if(!$latestLog) <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                        @elseif($latestLog && !$latestLog->hasEnded()) <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        @else <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> @endif
                    </div>
                    
                    @if(!$latestLog)
                        <h4 class="text-2xl font-black text-gray-800 mb-1">Belum Absen</h4>
                        <p class="text-gray-500 font-medium">Anda sedang dalam mode offline.</p>
                    @elseif($latestLog && !$latestLog->hasEnded())
                        <h4 class="text-2xl font-black text-blue-900 mb-1">Sedang Bekerja</h4>
                        <p class="text-blue-600 font-medium">Sesi aktif hari ini sedang berjalan.</p>
                    @else
                        <h4 class="text-2xl font-black text-green-900 mb-1">Selesai Kerja</h4>
                        <p class="text-green-600 font-medium">Sesi hari ini telah ditutup.</p>
                    @endif
                </div>
            </div>

            {{-- REIMBURSE NOTIF --}}
            @php
                $expensesNeedingRevision = \App\Models\Expense::where('user_id', $user->id)
                    ->where('status', 'needs_revision_sales')
                    ->count();
            @endphp
            @if($expensesNeedingRevision > 0)
                <a href="{{ route('sales.history') }}" class="block bg-orange-50 border border-orange-200 rounded-[2rem] p-6 hover:bg-orange-100 hover:border-orange-300 transition shadow-sm group">
                    <div class="flex items-center gap-5">
                        <div class="w-14 h-14 bg-orange-500 text-white rounded-2xl flex items-center justify-center font-black text-xl shadow-md group-hover:scale-110 transition-transform">
                            {{ $expensesNeedingRevision }}
                        </div>
                        <div class="flex-1">
                            <p class="font-extrabold text-orange-900 text-lg mb-0.5">Revisi Reimburse</p>
                            <p class="text-sm text-orange-700 font-medium">Ada form yang perlu diperbaiki</p>
                        </div>
                        <svg class="w-6 h-6 text-orange-500 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </div>
                </a>
            @endif

            {{-- TOMBOL UTAMA ABSENSI --}}
            @php
                $hasPendingVisits = $latestLog ? $latestLog->visits()->where('status', 'pending')->count() > 0 : false;
            @endphp

            @if (!$latestLog)
                <a href="{{ route('sales.absen.masuk') }}" class="flex items-center justify-center gap-3 w-full bg-blue-600 text-white p-6 rounded-[2rem] shadow-xl shadow-blue-600/20 hover:bg-blue-700 hover:shadow-blue-700/30 transition-all font-bold text-xl active:scale-95 group">
                    <svg class="w-8 h-8 group-hover:-translate-y-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                    Mulai Absen Masuk
                </a>
            @elseif ($latestLog && !$latestLog->hasEnded())
                @if (!$hasPendingVisits)
                    <a href="{{ route('sales.absen.keluar') }}" class="flex items-center justify-center gap-3 w-full bg-red-600 text-white p-6 rounded-[2rem] shadow-xl shadow-red-600/20 hover:bg-red-700 hover:shadow-red-700/30 transition-all font-bold text-xl active:scale-95 group">
                        <svg class="w-8 h-8 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        Absen Keluar
                    </a>
                @else
                    <div class="bg-yellow-50 border-2 border-yellow-200 text-yellow-800 p-6 rounded-[2rem] text-center shadow-sm">
                        <div class="flex items-center justify-center gap-3 mb-3">
                            <div class="w-12 h-12 bg-yellow-200 text-yellow-700 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            </div>
                            <h4 class="font-extrabold text-lg text-left leading-tight">Selesaikan<br>Kunjungan</h4>
                        </div>
                        <p class="text-sm font-medium opacity-90">Ada <strong class="font-black text-yellow-900 bg-yellow-200 px-2 py-0.5 rounded-md mx-1">{{ $latestLog->visits()->where('status', 'pending')->count() }}</strong> kunjungan yang masih berstatus pending.</p>
                    </div>
                @endif
            @elseif ($latestLog && $latestLog->hasEnded())
                <a href="{{ route('sales.absen.masuk') }}" onclick="return confirm('Mulai sesi darurat/lembur?')" class="flex items-center justify-center gap-3 w-full bg-white text-blue-700 p-6 rounded-[2rem] border-2 border-blue-200 border-dashed hover:bg-blue-50 hover:border-blue-400 transition-all font-bold text-lg group shadow-sm">
                    <svg class="w-7 h-7 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Kunjungan Tambahan
                </a>
            @endif
        </div>
    </div>
</div>
