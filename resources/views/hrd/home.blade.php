@extends('layout')
@section('content')

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

    {{-- FILTER HARI --}}
    <div class="flex gap-2 mb-3 overflow-x-auto pb-1" id="dateFilterMobile">
        <button onclick="filterDate('today', this)" class="date-filter-btn active shrink-0 px-4 py-2 rounded-xl text-xs font-bold bg-blue-600 text-white transition">Hari Ini</button>
        <button onclick="filterDate('yesterday', this)" class="date-filter-btn shrink-0 px-4 py-2 rounded-xl text-xs font-bold bg-gray-100 text-gray-600 transition">Kemarin</button>
        <button onclick="filterDate('week', this)" class="date-filter-btn shrink-0 px-4 py-2 rounded-xl text-xs font-bold bg-gray-100 text-gray-600 transition">Minggu Ini</button>
        <button onclick="filterDate('all', this)" class="date-filter-btn shrink-0 px-4 py-2 rounded-xl text-xs font-bold bg-gray-100 text-gray-600 transition">Semua</button>
    </div>

    {{-- FILTER JENIS AKTIVITAS (Mobile) --}}
    <div class="flex gap-2 mb-5 overflow-x-auto pb-1">
        <button onclick="filterType('all', this)" class="type-filter-btn active shrink-0 px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-700 text-white transition">Semua</button>
        <button onclick="filterType('check_in', this)" class="type-filter-btn shrink-0 px-3 py-1.5 rounded-lg text-xs font-bold bg-blue-50 text-blue-600 transition">Masuk</button>
        <button onclick="filterType('check_out', this)" class="type-filter-btn shrink-0 px-3 py-1.5 rounded-lg text-xs font-bold bg-green-50 text-green-600 transition">Keluar</button>
        <button onclick="filterType('visit', this)" class="type-filter-btn shrink-0 px-3 py-1.5 rounded-lg text-xs font-bold bg-purple-50 text-purple-600 transition">Kunjungan</button>
    </div>

    {{-- TIMELINE FEED --}}
    @if($activities->count() > 0)
        <div class="space-y-3 mb-20" id="activityFeedMobile">
            @foreach($activities as $activity)
                @include('hrd.partials._activity_item_mobile', ['activity' => $activity])
            @endforeach
        </div>

        {{-- PAGINATION --}}
        <div class="mb-20">
            {{ $activities->withQueryString()->links('pagination::tailwind') }}
        </div>
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

                    {{-- FILTER BAR --}}
                    <div class="flex items-center gap-3 flex-wrap">
                        {{-- Filter Tanggal --}}
                        <div class="flex gap-1.5" id="dateFilterDesktop">
                            <button onclick="filterDate('today', this)" class="date-filter-btn active px-3 py-1.5 rounded-lg text-xs font-bold bg-blue-600 text-white transition">Hari Ini</button>
                            <button onclick="filterDate('yesterday', this)" class="date-filter-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-gray-100 text-gray-600 hover:bg-gray-200 transition">Kemarin</button>
                            <button onclick="filterDate('week', this)" class="date-filter-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-gray-100 text-gray-600 hover:bg-gray-200 transition">Minggu Ini</button>
                            <button onclick="filterDate('all', this)" class="date-filter-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-gray-100 text-gray-600 hover:bg-gray-200 transition">Semua</button>
                        </div>

                        <div class="w-px h-6 bg-gray-200"></div>

                        {{-- Filter Tanggal Custom --}}
                        <div class="flex items-center gap-2">
                            <input type="date" id="filter-date-from" onchange="applyFilters()" class="border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs text-gray-700 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            <span class="text-xs text-gray-400">s/d</span>
                            <input type="date" id="filter-date-to" onchange="applyFilters()" class="border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs text-gray-700 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>

                        <div class="w-px h-6 bg-gray-200"></div>

                        {{-- Filter Jenis Aktivitas --}}
                        <div class="flex gap-1.5">
                            <button onclick="filterType('all', this)" class="type-filter-btn active px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-700 text-white transition">Semua</button>
                            <button onclick="filterType('check_in', this)" class="type-filter-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-blue-50 text-blue-600 hover:bg-blue-100 transition">Absen Masuk</button>
                            <button onclick="filterType('check_out', this)" class="type-filter-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-green-50 text-green-600 hover:bg-green-100 transition">Absen Keluar</button>
                            <button onclick="filterType('visit', this)" class="type-filter-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-purple-50 text-purple-600 hover:bg-purple-100 transition">Kunjungan</button>
                        </div>
                    </div>
                </div>

                {{-- TIMELINE --}}
                @if($activities->count() > 0)
                    <div class="px-8 pb-8 space-y-3" id="activityFeedDesktop">
                        @foreach($activities as $activity)
                            @include('hrd.partials._activity_item_desktop', ['activity' => $activity])
                        @endforeach
                    </div>

                    {{-- PAGINATION --}}
                    @if($activities->hasMorePages())
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

@section('scripts')
<script>
    let currentDateFilter = 'today';
    let currentTypeFilter = 'all';

    function filterDate(range, btn) {
        currentDateFilter = range;

        // Reset custom date inputs when using preset
        if (range !== 'custom') {
            const fromEl = document.getElementById('filter-date-from');
            const toEl = document.getElementById('filter-date-to');
            if (fromEl) fromEl.value = '';
            if (toEl) toEl.value = '';
        }

        // Update active button
        document.querySelectorAll('.date-filter-btn').forEach(b => {
            b.classList.remove('bg-blue-600', 'text-white', 'active');
            b.classList.add('bg-gray-100', 'text-gray-600');
        });
        btn.classList.remove('bg-gray-100', 'text-gray-600');
        btn.classList.add('bg-blue-600', 'text-white', 'active');

        applyFilters();
    }

    function filterType(type, btn) {
        currentTypeFilter = type;

        // Update active button
        document.querySelectorAll('.type-filter-btn').forEach(b => {
            b.classList.remove('bg-slate-700', 'text-white', 'active');
            b.classList.add('bg-gray-100', 'text-gray-600');
        });
        btn.classList.remove('bg-gray-100', 'text-gray-600');
        btn.classList.add('bg-slate-700', 'text-white', 'active');

        applyFilters();
    }

    function applyFilters() {
        const today = new Date();
        const todayStr = today.toISOString().split('T')[0];
        const items = document.querySelectorAll('.activity-item');

        // Hitung range tanggal
        let dateFrom = null;
        let dateTo = null;

        const customFrom = document.getElementById('filter-date-from');
        const customTo = document.getElementById('filter-date-to');

        if (customFrom && customTo && customFrom.value && customTo.value) {
            dateFrom = customFrom.value;
            dateTo = customTo.value;
            currentDateFilter = 'custom';
        } else if (currentDateFilter === 'today') {
            dateFrom = todayStr;
            dateTo = todayStr;
        } else if (currentDateFilter === 'yesterday') {
            const y = new Date(today);
            y.setDate(y.getDate() - 1);
            const ys = y.toISOString().split('T')[0];
            dateFrom = ys;
            dateTo = ys;
        } else if (currentDateFilter === 'week') {
            const d = new Date(today);
            const day = d.getDay();
            const diff = d.getDate() - day + (day === 0 ? -6 : 1);
            const monday = new Date(today);
            monday.setDate(diff);
            dateFrom = monday.toISOString().split('T')[0];
            dateTo = todayStr;
        }

        items.forEach(item => {
            const d = item.getAttribute('data-date');
            const t = item.getAttribute('data-type');

            // Filter tanggal
            let dateMatch = true;
            if (currentDateFilter !== 'all' && dateFrom && dateTo) {
                dateMatch = d >= dateFrom && d <= dateTo;
            }

            // Filter tipe
            let typeMatch = true;
            if (currentTypeFilter !== 'all') {
                typeMatch = t === currentTypeFilter;
            }

            item.style.display = (dateMatch && typeMatch) ? '' : 'none';
        });
    }
</script>
@endsection
