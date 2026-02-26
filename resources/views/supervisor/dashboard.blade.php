@extends('layout')
@section('content')
    {{-- ========================================== --}}
    {{-- TAMPILAN MOBILE (< 768px): KODE ASLI UTUH --}}
    {{-- ========================================== --}}
    <div class="md:hidden">
        <div class="px-5 py-6">
            <h1 class="text-2xl font-bold mb-2">Dashboard Supervisor</h1>
            <p class="text-sm text-gray-600 mb-6">Monitor absensi sales yang ditugaskan ke Anda</p>
    
            {{-- STATISTIK HARI INI --}}
            <div class="grid grid-cols-2 gap-3 mb-6">
                <div class="bg-blue-50 rounded-xl p-4">
                    <p class="text-xs text-gray-600 mb-1">Total Sales</p>
                    <p class="text-2xl font-bold text-blue-600">{{ count($sales) }}</p>
                </div>
                <div class="bg-green-50 rounded-xl p-4">
                    <p class="text-xs text-gray-600 mb-1">Sudah Absen</p>
                    <p class="text-2xl font-bold text-green-600">
                        {{ collect($todayAbsensi)->where('hasStarted', true)->count() }}
                    </p>
                </div>
            </div>
    
            {{-- MENU APPROVAL --}}
            <div class="mb-6">
                <a href="{{ route('supervisor.reimburse.approval') }}"
                    class="flex items-center justify-between bg-white p-4 rounded-xl shadow-sm border border-orange-100 hover:shadow-md transition">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800">Persetujuan Reimburse</h3>
                            <p class="text-xs text-gray-500">Cek pengajuan biaya dari sales</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        @if(($stats['pending_spv'] ?? 0) > 0)
                            <span class="bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full">{{ $stats['pending_spv'] }}</span>
                        @endif
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </div>
                </a>
            </div>
    
            {{-- LIST SALES --}}
            <div class="space-y-3">
                <h2 class="font-bold text-lg text-gray-800 mb-3">Tim Sales</h2>
                @forelse($sales as $sale)
                    @php
                        $absensi = $todayAbsensi[$sale->id] ?? null;
                    @endphp
                    <a href="{{ route('supervisor.show.sales', $sale->id) }}"
                        class="block bg-white rounded-2xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3 flex-1">
                                <div
                                    class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 font-bold">
                                    {{ substr($sale->name, 0, 1) }}
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-bold text-gray-800">{{ $sale->name }}</h3>
                                    <p class="text-xs text-gray-500">{{ $sale->username }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                @if($absensi && $absensi['hasStarted'])
                                    @if($absensi['hasEnded'])
                                        <span
                                            class="bg-green-100 text-green-700 text-[10px] font-bold px-2 py-1 rounded-full">Selesai</span>
                                    @else
                                        <span class="bg-blue-100 text-blue-700 text-[10px] font-bold px-2 py-1 rounded-full">Aktif</span>
                                    @endif
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $absensi['completedVisits'] }}/{{ $absensi['visitsCount'] }} kunjungan</p>
                                @else
                                    <span class="bg-gray-100 text-gray-600 text-[10px] font-bold px-2 py-1 rounded-full">Belum
                                        Absen</span>
                                @endif
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="text-center py-10 bg-gray-50 rounded-xl">
                        <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        <p class="text-sm text-gray-500">Belum ada sales yang ditugaskan</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- TAMPILAN DESKTOP (>= 768px): KODE BARU     --}}
    {{-- ========================================== --}}
    <div class="hidden md:block px-8 py-8 h-full bg-slate-50/50">
        {{-- HEADER WIDGET --}}
        <div class="bg-blue-600 rounded-3xl p-8 text-white shadow-xl mb-8 flex justify-between items-center relative overflow-hidden">
            <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"></div>
            <div class="relative z-10 w-full flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight mb-2">Dashboard Supervisor ✨</h1>
                    <p class="text-blue-100 font-medium">Monitoring kehadiran & aktivitas tim sales secara real-time.</p>
                </div>
                <div class="flex gap-4">
                    <div class="bg-white/20 backdrop-blur-md rounded-2xl px-6 py-4 border border-white/20 shadow-inner text-center">
                        <p class="text-xs uppercase tracking-widest text-blue-100 font-bold mb-1">Total Sales</p>
                        <p class="text-3xl font-black">{{ count($sales) }}</p>
                    </div>
                    <div class="bg-green-500/20 backdrop-blur-md rounded-2xl px-6 py-4 border border-green-400/30 shadow-inner text-center">
                        <p class="text-xs uppercase tracking-widest text-green-100 font-bold mb-1">Sudah Absen</p>
                        <p class="text-3xl font-black text-green-300">{{ collect($todayAbsensi)->where('hasStarted', true)->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- GRID LAYOUT DESKTOP --}}
        <div class="grid grid-cols-12 gap-8 items-start">
            
            {{-- KOLOM KIRI (8 KOLOM): DAFTAR SALES --}}
            <div class="col-span-8">
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <h2 class="font-extrabold text-lg text-gray-800 tracking-tight">Anggota Tim Sales</h2>
                        <span class="bg-blue-100 text-blue-700 text-xs font-bold px-3 py-1 rounded-full">{{ count($sales) }} Orang</span>
                    </div>
                    
                    <div class="divide-y divide-gray-50">
                        @forelse($sales as $sale)
                            @php
                                $absensi = $todayAbsensi[$sale->id] ?? null;
                            @endphp
                            <a href="{{ route('supervisor.show.sales', $sale->id) }}" class="flex items-center justify-between p-5 hover:bg-slate-50 transition group">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center font-black text-lg shadow-inner group-hover:scale-110 transition-transform">
                                        {{ substr($sale->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-gray-800 group-hover:text-blue-600 transition-colors">{{ $sale->name }}</h3>
                                        <p class="text-sm text-gray-500">{{ $sale->username }}</p>
                                    </div>
                                </div>
                                <div class="text-right flex items-center gap-4">
                                    @if($absensi && $absensi['hasStarted'])
                                        <div class="flex flex-col items-end">
                                            @if($absensi['hasEnded'])
                                                <span class="bg-green-100 text-green-700 text-xs font-bold px-3 py-1 rounded-md border border-green-200">Selesai Kerja</span>
                                            @else
                                                <span class="bg-blue-100 text-blue-700 text-xs font-bold px-3 py-1 rounded-md border border-blue-200 relative"><span class="absolute -top-1 -right-1 flex h-3 w-3"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span><span class="relative inline-flex rounded-full h-3 w-3 bg-blue-500"></span></span>Sedang Bekeria</span>
                                            @endif
                                            <p class="text-xs text-gray-500 font-medium mt-1.5"><span class="text-gray-900 font-bold">{{ $absensi['completedVisits'] }}</span> dari {{ $absensi['visitsCount'] }} kunjungan beres</p>
                                        </div>
                                    @else
                                        <span class="bg-gray-100 text-gray-500 text-xs font-bold px-3 py-1 rounded-md border border-gray-200">Belum Absen</span>
                                    @endif
                                    <svg class="w-5 h-5 text-gray-300 group-hover:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                </div>
                            </a>
                        @empty
                            <div class="p-12 text-center">
                                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                <p class="text-gray-500 font-medium">Belum ada tim sales yang terdaftar.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN (4 KOLOM): ACTION CENTER --}}
            <div class="col-span-4 sticky top-6 space-y-6">
                
                {{-- QUICK ACTION MENU --}}
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="font-bold text-gray-800 text-sm uppercase tracking-wider text-center">Menu Supervisor</h3>
                    </div>
                    <div class="p-4">
                        <a href="{{ route('supervisor.reimburse.approval') }}" class="flex items-center gap-4 bg-orange-50/50 p-4 rounded-2xl border border-orange-100 hover:bg-orange-50 hover:border-orange-200 transition group">
                            <div class="w-12 h-12 bg-orange-100 text-orange-600 rounded-xl flex items-center justify-center group-hover:rotate-12 transition-transform shadow-sm">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-bold text-gray-900 group-hover:text-orange-700 transition">Persetujuan Reimburse</h4>
                                <p class="text-xs text-gray-500 mt-0.5">Mute pengajuan biaya</p>
                            </div>
                            @if(($stats['pending_spv'] ?? 0) > 0)
                                <div class="w-8 h-8 bg-red-500 text-white rounded-full flex items-center justify-center font-bold text-xs shadow-md animate-pulse">
                                    {{ $stats['pending_spv'] }}
                                </div>
                            @else
                                <svg class="w-5 h-5 text-gray-300 group-hover:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            @endif
                        </a>
                    </div>
                </div>

                {{-- INFO WIDGET --}}
                <div class="bg-gradient-to-b from-gray-50 to-white rounded-3xl p-6 border border-gray-100 shadow-sm text-center">
                    <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow mx-auto">
                        <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h4 class="font-bold text-gray-800 mb-2">Panduan Penggunaan</h4>
                    <p class="text-xs text-gray-500 leading-relaxed mx-auto max-w-xs">Gunakan kolom sebelah kiri untuk memonitor aktivitas check-in dan kunjungan sales. Pastikan tidak ada pengajuan reimburse yang tertunda.</p>
                </div>

            </div>
        </div>
    </div>
@endsection