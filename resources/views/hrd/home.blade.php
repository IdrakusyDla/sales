@extends('layout')

@section('content')

@php
    use Carbon\Carbon;
    $todayStr = Carbon::today()->format('Y-m-d');
    $yesterdayStr = Carbon::yesterday()->format('Y-m-d');
    $mondayStr = Carbon::now()->startOfWeek()->format('Y-m-d');
    $reqFrom = request('date_from');
    $reqTo = request('date_to');
    $reqType = request('type', 'all');

    $reqPreset = request('preset');
    $activePreset = $reqPreset ?: ($reqFrom || $reqTo ? 'custom' : 'all');

    $buildUrl = function (array $override = [], array $remove = []) {
        $q = request()->query();
        foreach ($remove as $k) {
            unset($q[$k]);
        }
        foreach ($override as $k => $v) {
            $q[$k] = $v;
        }
        $q['page'] = 1;
        return route('hrd.home') . '?' . http_build_query($q);
    };

    $todayUrl = $buildUrl(['date_from' => $todayStr, 'date_to' => $todayStr, 'preset' => 'today']);
    $yesterdayUrl = $buildUrl(['date_from' => $yesterdayStr, 'date_to' => $yesterdayStr, 'preset' => 'yesterday']);
    $weekUrl = $buildUrl(['date_from' => $mondayStr, 'date_to' => $todayStr, 'preset' => 'week']);
    $allUrl = $buildUrl(['preset' => 'all'], ['date_from', 'date_to']);
    $typeUrl = fn($t) => $t === 'all' ? $buildUrl([], ['type']) : $buildUrl(['type' => $t]);

    $isFiltered = request()->hasAny(['date_from', 'date_to', 'type', 'job_position_id', 'company_id', 'per_page']);

    $totalActivities = $activities->total();
    $firstItem = $totalActivities ? ($activities->currentPage() - 1) * $perPage + 1 : 0;
    $lastItem = min($totalActivities, $activities->currentPage() * $perPage);
@endphp

{{-- ========================================== --}}
{{-- TAMPILAN MOBILE (< 768px)                  --}}
{{-- ========================================== --}}
<div class="md:hidden px-5 py-6">
    {{-- HEADER --}}
    <div class="mb-5">
        <h1 class="text-2xl font-bold">Aktivitas Terbaru</h1>
        <p class="text-xs text-gray-500 mt-1">Pantau aktivitas absensi & kunjungan tim</p>
    </div>

    {{-- STATISTIK HARI INI --}}
    <div class="grid grid-cols-4 gap-2 mb-6">
        <div class="bg-blue-50 rounded-xl p-3 text-center">
            <p class="text-lg font-bold text-blue-600">{{ $todayStats['check_in'] }}</p>
            <p class="text-[10px] text-blue-700 font-medium">Masuk</p>
        </div>
        <div class="bg-green-50 rounded-xl p-3 text-center">
            <p class="text-lg font-bold text-green-600">{{ $todayStats['check_out'] }}</p>
            <p class="text-[10px] text-green-700 font-medium">Keluar</p>
        </div>
        <div class="bg-purple-50 rounded-xl p-3 text-center">
            <p class="text-lg font-bold text-purple-600">{{ $todayStats['visits'] }}</p>
            <p class="text-[10px] text-purple-700 font-medium">Kunjungan</p>
        </div>
        <div class="bg-amber-50 rounded-xl p-3 text-center">
            <p class="text-lg font-bold text-amber-600">{{ $todayStats['total_active'] }}</p>
            <p class="text-[10px] text-amber-700 font-medium">Aktif</p>
        </div>
    </div>

    {{-- FILTER (MOBILE) --}}
    <div class="mb-5">
        {{-- FILTER HARI (preset links) --}}
        <div class="flex gap-2 mb-3 overflow-x-auto pb-1">
            <a href="{{ $todayUrl }}" class="shrink-0 px-4 py-2 rounded-xl text-xs font-bold transition {{ $activePreset === 'today' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600' }}">Hari Ini</a>
            <a href="{{ $yesterdayUrl }}" class="shrink-0 px-4 py-2 rounded-xl text-xs font-bold transition {{ $activePreset === 'yesterday' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600' }}">Kemarin</a>
            <a href="{{ $weekUrl }}" class="shrink-0 px-4 py-2 rounded-xl text-xs font-bold transition {{ $activePreset === 'week' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600' }}">Minggu Ini</a>
            <a href="{{ $allUrl }}" class="shrink-0 px-4 py-2 rounded-xl text-xs font-bold transition {{ $activePreset === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600' }}">Semua</a>
        </div>

        {{-- FILTER JENIS AKTIVITAS (type links) --}}
        <div class="flex gap-2 mb-3 overflow-x-auto pb-1">
            <a href="{{ $typeUrl('all') }}" class="shrink-0 px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $reqType === 'all' ? 'bg-slate-700 text-white' : 'bg-gray-100 text-gray-600' }}">Semua</a>
            <a href="{{ $typeUrl('check_in') }}" class="shrink-0 px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $reqType === 'check_in' ? 'bg-slate-700 text-white' : 'bg-gray-100 text-gray-600' }}">Masuk</a>
            <a href="{{ $typeUrl('check_out') }}" class="shrink-0 px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $reqType === 'check_out' ? 'bg-slate-700 text-white' : 'bg-gray-100 text-gray-600' }}">Keluar</a>
            <a href="{{ $typeUrl('visit') }}" class="shrink-0 px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $reqType === 'visit' ? 'bg-slate-700 text-white' : 'bg-gray-100 text-gray-600' }}">Kunjungan</a>
        </div>

        {{-- FILTER FORM: range tanggal + selects --}}
        <form method="GET" action="{{ route('hrd.home') }}" id="filterFormMobile">
            <input type="hidden" name="type" value="{{ $reqType }}">
            <input type="hidden" name="preset" value="{{ $reqPreset }}">

            {{-- RANGE TANGGAL --}}
            <div class="flex items-center gap-2 mb-3">
                <input type="date" name="date_from" value="{{ request('date_from') }}" onchange="this.form.querySelector('[name=preset]').value='';this.form.submit()" class="flex-1 border border-gray-200 rounded-lg px-2.5 py-2 text-xs text-gray-700 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none">
                <span class="text-xs text-gray-400 shrink-0">s/d</span>
                <input type="date" name="date_to" value="{{ request('date_to') }}" onchange="this.form.querySelector('[name=preset]').value='';this.form.submit()" class="flex-1 border border-gray-200 rounded-lg px-2.5 py-2 text-xs text-gray-700 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>

            {{-- JABATAN / PERUSAHAAN / PER HALAMAN --}}
            <div class="grid grid-cols-3 gap-2">
                <select name="job_position_id" onchange="this.form.submit()" class="w-full border border-gray-200 rounded-lg pl-2 pr-6 py-2 text-[11px] text-gray-700 bg-white focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    <option value="">Semua Jabatan</option>
                    @foreach($jobPositions as $jp)
                        <option value="{{ $jp->id }}" @selected(request('job_position_id') == $jp->id)>{{ $jp->name }}</option>
                    @endforeach
                </select>
                <select name="company_id" onchange="this.form.submit()" class="w-full border border-gray-200 rounded-lg pl-2 pr-6 py-2 text-[11px] text-gray-700 bg-white focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    <option value="">Semua</option>
                    @foreach($companies as $c)
                        <option value="{{ $c->id }}" @selected(request('company_id') == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
                <select name="per_page" onchange="this.form.submit()" class="w-full border border-gray-200 rounded-lg pl-2 pr-6 py-2 text-[11px] text-gray-700 bg-white focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    @foreach([10, 20, 50, 100] as $opt)
                        <option value="{{ $opt }}" @selected($perPage == $opt)>{{ $opt }}/hal</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    {{-- TIMELINE FEED --}}
    @if($activities->count() > 0)
        <div class="space-y-3 mb-4" id="activityFeedMobile">
            @foreach($activities as $activity)
                @include('hrd.partials._activity_item_mobile', ['activity' => $activity])
            @endforeach
        </div>

        <p class="text-[11px] text-gray-400 text-center mb-3">Menampilkan {{ $firstItem }}–{{ $lastItem }} dari {{ $totalActivities }} aktivitas</p>

        {{-- PAGINATION --}}
        @if($activities->hasPages())
            <div class="mb-20">
                {{ $activities->withQueryString()->links('pagination::tailwind') }}
            </div>
        @endif
    @else
        <div class="text-center py-16">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <p class="text-sm font-bold text-gray-500">Belum ada aktivitas</p>
            <p class="text-xs text-gray-400 mt-1">Aktivitas terbaru akan muncul di sini</p>
        </div>
    @endif
</div>

{{-- ========================================== --}}
{{-- TAMPILAN DESKTOP (>= 768px)                --}}
{{-- ========================================== --}}
<div class="hidden md:block min-h-screen bg-slate-50/50 px-8 py-8">
    <div class="grid grid-cols-12 gap-6">

        {{-- KOLOM KIRI: TIMELINE FEED (8 kolom) --}}
        <div class="col-span-8">
            <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">

                {{-- HEADER --}}
                <div class="px-8 pt-8 pb-5">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h1 class="text-xl font-extrabold text-gray-800">Aktivitas Terbaru</h1>
                            <p class="text-sm text-gray-500 mt-1">Pantau aktivitas absensi & kunjungan tim</p>
                        </div>
                    </div>

                    {{-- FILTER FORM (DESKTOP) --}}
                    <form method="GET" action="{{ route('hrd.home') }}" id="filterFormDesktop" class="space-y-3">
                        <input type="hidden" name="type" value="{{ $reqType }}">
                        <input type="hidden" name="preset" value="{{ $reqPreset }}">

                        {{-- Row 1: Periode + Range + Tipe --}}
                        <div class="flex items-center gap-3 flex-wrap">
                            <div class="flex gap-1.5">
                                <a href="{{ $todayUrl }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $activePreset === 'today' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Hari Ini</a>
                                <a href="{{ $yesterdayUrl }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $activePreset === 'yesterday' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Kemarin</a>
                                <a href="{{ $weekUrl }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $activePreset === 'week' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Minggu Ini</a>
                                <a href="{{ $allUrl }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $activePreset === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Semua</a>
                            </div>

                            <div class="w-px h-6 bg-gray-200"></div>

                            <div class="flex items-center gap-2">
                                <input type="date" name="date_from" value="{{ request('date_from') }}" onchange="this.form.querySelector('[name=preset]').value='';this.form.submit()" class="border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs text-gray-700 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                <span class="text-xs text-gray-400">s/d</span>
                                <input type="date" name="date_to" value="{{ request('date_to') }}" onchange="this.form.querySelector('[name=preset]').value='';this.form.submit()" class="border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs text-gray-700 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            </div>

                            <div class="w-px h-6 bg-gray-200"></div>

                            <div class="flex gap-1.5">
                                <a href="{{ $typeUrl('all') }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $reqType === 'all' ? 'bg-slate-700 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Semua</a>
                                <a href="{{ $typeUrl('check_in') }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $reqType === 'check_in' ? 'bg-slate-700 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Absen Masuk</a>
                                <a href="{{ $typeUrl('check_out') }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $reqType === 'check_out' ? 'bg-slate-700 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Absen Keluar</a>
                                <a href="{{ $typeUrl('visit') }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $reqType === 'visit' ? 'bg-slate-700 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Kunjungan</a>
                            </div>
                        </div>

                        {{-- Row 2: Jabatan + Perusahaan + Per halaman + Reset (sebaris) --}}
                        <div class="flex items-center gap-3 flex-wrap">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-semibold text-gray-500 shrink-0">Jabatan</span>
                                <select name="job_position_id" onchange="this.form.submit()" class="w-[150px] border border-gray-200 rounded-lg pl-3 pr-8 py-1.5 text-xs text-gray-700 bg-white focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                    <option value="">Semua</option>
                                    @foreach($jobPositions as $jp)
                                        <option value="{{ $jp->id }}" @selected(request('job_position_id') == $jp->id)>{{ $jp->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="text-xs font-semibold text-gray-500 shrink-0">Perusahaan</span>
                                <select name="company_id" onchange="this.form.submit()" class="w-[150px] border border-gray-200 rounded-lg pl-3 pr-8 py-1.5 text-xs text-gray-700 bg-white focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                    <option value="">Semua</option>
                                    @foreach($companies as $c)
                                        <option value="{{ $c->id }}" @selected(request('company_id') == $c->id)>{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex items-center gap-2 shrink-0">
                                <span class="text-xs font-semibold text-gray-500 shrink-0">Per Halaman</span>
                                <select name="per_page" onchange="this.form.submit()" class="w-[72px] border border-gray-200 rounded-lg pl-3 pr-7 py-1.5 text-xs text-gray-700 bg-white focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                    @foreach([10, 20, 50, 100] as $opt)
                                        <option value="{{ $opt }}" @selected($perPage == $opt)>{{ $opt }}</option>
                                    @endforeach
                                </select>
                            </div>

                            @if($isFiltered)
                                <a href="{{ route('hrd.home') }}" class="text-xs font-bold text-gray-400 hover:text-red-500 transition shrink-0 ml-auto">Reset Filter</a>
                            @endif
                        </div>
                    </form>
                </div>

                {{-- TIMELINE --}}
                @if($activities->count() > 0)
                    <div class="px-8 pb-5 space-y-3" id="activityFeedDesktop">
                        @foreach($activities as $activity)
                            @include('hrd.partials._activity_item_desktop', ['activity' => $activity])
                        @endforeach
                    </div>

                    <div class="px-8 pb-3">
                        <p class="text-xs text-gray-400">Menampilkan {{ $firstItem }}–{{ $lastItem }} dari {{ $totalActivities }} aktivitas</p>
                    </div>

                    {{-- PAGINATION --}}
                    @if($activities->hasPages())
                        <div class="px-8 pb-8">
                            {{ $activities->withQueryString()->links('pagination::tailwind') }}
                        </div>
                    @endif
                @else
                    <div class="px-8 pb-12 text-center py-12">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <p class="text-sm font-bold text-gray-500">Belum ada aktivitas</p>
                        <p class="text-xs text-gray-400 mt-1">Aktivitas terbaru akan muncul di sini</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- KOLOM KANAN: STATS & INFO (4 kolom) --}}
        <div class="col-span-4 space-y-5">
            {{-- STATISTIK HARI INI --}}
            <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6">
                <h2 class="font-bold text-base mb-4">Statistik Hari Ini</h2>
                <div class="space-y-3">
                    <div class="flex items-center gap-3 bg-blue-50 rounded-xl p-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs text-gray-500">Absen Masuk</p>
                            <p class="text-lg font-bold text-blue-600">{{ $todayStats['check_in'] }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 bg-green-50 rounded-xl p-3">
                        <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs text-gray-500">Absen Keluar</p>
                            <p class="text-lg font-bold text-green-600">{{ $todayStats['check_out'] }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 bg-purple-50 rounded-xl p-3">
                        <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs text-gray-500">Kunjungan</p>
                            <p class="text-lg font-bold text-purple-600">{{ $todayStats['visits'] }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 bg-amber-50 rounded-xl p-3">
                        <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs text-gray-500">Karyawan Aktif</p>
                            <p class="text-lg font-bold text-amber-600">{{ $todayStats['total_active'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- LEGENDA --}}
            <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6">
                <h2 class="font-bold text-sm mb-3">Keterangan</h2>
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 bg-blue-500 rounded-full shrink-0"></span>
                        <span class="text-xs text-gray-600">Absen Masuk</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 bg-green-500 rounded-full shrink-0"></span>
                        <span class="text-xs text-gray-600">Absen Keluar</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 bg-purple-500 rounded-full shrink-0"></span>
                        <span class="text-xs text-gray-600">Kunjungan Customer</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
