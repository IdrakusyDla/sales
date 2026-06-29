@extends('layout')
@section('content')
    {{-- ========================================== --}}
    {{-- TAMPILAN MOBILE (< 768px)                  --}}
    {{-- ========================================== --}}
    <div class="md:hidden px-5 py-6">
        {{-- HEADER --}}
        <div class="mb-5">
            <h1 class="text-2xl font-bold">Tim Saya</h1>
            <p class="text-xs text-gray-500 mt-1">Monitor absensi & aktivitas tim sales</p>
        </div>

        {{-- STATISTIK HARI INI --}}
        <div class="grid grid-cols-2 gap-3 mb-5">
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

        {{-- TOMBOL PERSETUJUAN REIMBURSE --}}
        <div class="mb-5">
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
        <div class="space-y-3 mb-20">
            <h2 class="font-bold text-lg text-gray-800 mb-3">Anggota Tim</h2>
            @forelse($sales as $sale)
                @php
                    $absensi = $todayAbsensi[$sale->id] ?? null;
                @endphp
                <a href="{{ route('supervisor.show.sales', $sale->id) }}"
                    class="block bg-white rounded-2xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3 flex-1">
                            <div class="w-11 h-11 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 font-bold text-sm">
                                {{ substr($sale->name, 0, 1) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-gray-800 text-sm truncate">{{ $sale->name }}</h3>
                                <p class="text-xs text-gray-500">{{ $sale->username }}</p>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            @if($absensi && $absensi['hasStarted'])
                                @if($absensi['hasEnded'])
                                    <span class="bg-green-100 text-green-700 text-[10px] font-bold px-2 py-1 rounded-full">Selesai</span>
                                @else
                                    <span class="bg-blue-100 text-blue-700 text-[10px] font-bold px-2 py-1 rounded-full">Sedang Bekerja</span>
                                @endif
                                <div class="text-[10px] text-gray-500 mt-1 flex items-center gap-1.5 justify-end">
                                    <span><span class="text-green-700 font-bold">{{ $absensi['completedVisits'] }}</span> berhasil</span>
                                    @if($absensi['failedVisits'] > 0)
                                        <span class="text-gray-300">·</span>
                                        <span><span class="text-red-600 font-bold">{{ $absensi['failedVisits'] }}</span> gagal</span>
                                    @endif
                                    @if($absensi['activeVisits'] > 0)
                                        <span class="text-gray-300">·</span>
                                        <span><span class="text-blue-700 font-bold">{{ $absensi['activeVisits'] }}</span> aktif</span>
                                    @endif
                                </div>
                            @else
                                <span class="bg-gray-100 text-gray-600 text-[10px] font-bold px-2 py-1 rounded-full">Belum Absen</span>
                            @endif
                        </div>
                    </div>
                </a>
            @empty
                <div class="text-center py-12">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <p class="text-sm font-bold text-gray-500">Belum ada sales yang ditugaskan</p>
                    <p class="text-xs text-gray-400 mt-1">Hubungi HRD untuk menambahkan anggota tim</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- TAMPILAN DESKTOP (>= 768px)                --}}
    {{-- ========================================== --}}
    <div class="hidden md:block min-h-screen bg-slate-50/50 px-8 py-8">
        <div class="grid grid-cols-12 gap-6">

            {{-- KOLOM KIRI: DAFTAR SALES (8 kolom) --}}
            <div class="col-span-8">
                <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">

                    {{-- HEADER --}}
                    <div class="px-8 pt-8 pb-5">
                        <div class="flex items-center justify-between mb-1">
                            <div>
                                <h1 class="text-xl font-extrabold text-gray-800">Tim Saya</h1>
                                <p class="text-sm text-gray-500 mt-1">Monitor absensi & aktivitas tim sales</p>
                            </div>
                            <span class="bg-blue-100 text-blue-700 text-xs font-bold px-3 py-1 rounded-full">{{ count($sales) }} Orang</span>
                        </div>
                    </div>

                    {{-- LIST SALES --}}
                    <div class="px-8 pb-8 space-y-3">
                        @forelse($sales as $sale)
                            @php
                                $absensi = $todayAbsensi[$sale->id] ?? null;
                            @endphp
                            <a href="{{ route('supervisor.show.sales', $sale->id) }}" class="flex items-center justify-between p-4 bg-gray-50/50 rounded-2xl border border-gray-100 hover:bg-white hover:shadow-sm transition group">
                                <div class="flex items-center gap-4">
                                    <div class="w-11 h-11 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center font-bold text-sm shadow-inner group-hover:scale-110 transition-transform">
                                        {{ substr($sale->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-gray-800 group-hover:text-blue-600 transition-colors">{{ $sale->name }}</h3>
                                        <p class="text-xs text-gray-500">{{ $sale->username }}</p>
                                    </div>
                                </div>
                                <div class="text-right flex items-center gap-4">
                                    @if($absensi && $absensi['hasStarted'])
                                        <div class="flex flex-col items-end">
                                            @if($absensi['hasEnded'])
                                                <span class="bg-green-100 text-green-700 text-xs font-bold px-3 py-1 rounded-md border border-green-200">Selesai</span>
                                            @else
                                                <span class="bg-blue-100 text-blue-700 text-xs font-bold px-3 py-1 rounded-md border border-blue-200 relative">
                                                    <span class="absolute -top-1 -right-1 flex h-3 w-3"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span><span class="relative inline-flex rounded-full h-3 w-3 bg-blue-500"></span></span>
                                                    Sedang Bekerja
                                                </span>
                                            @endif
                                            <div class="flex items-center gap-2 text-xs font-medium mt-1.5">
                                                <span class="inline-flex items-center gap-1">
                                                    <span class="text-gray-900 font-bold">{{ $absensi['completedVisits'] }}</span>
                                                    <span class="text-gray-500">berhasil</span>
                                                </span>
                                                @if($absensi['failedVisits'] > 0)
                                                    <span class="text-gray-300">·</span>
                                                    <span class="inline-flex items-center gap-1">
                                                        <span class="text-red-600 font-bold">{{ $absensi['failedVisits'] }}</span>
                                                        <span class="text-gray-500">gagal</span>
                                                    </span>
                                                @endif
                                                @if($absensi['activeVisits'] > 0)
                                                    <span class="text-gray-300">·</span>
                                                    <span class="inline-flex items-center gap-1">
                                                        <span class="text-blue-700 font-bold">{{ $absensi['activeVisits'] }}</span>
                                                        <span class="text-gray-500">aktif</span>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <span class="bg-gray-100 text-gray-500 text-xs font-bold px-3 py-1 rounded-md border border-gray-200">Belum Absen</span>
                                    @endif
                                    <svg class="w-5 h-5 text-gray-300 group-hover:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                </div>
                            </a>
                        @empty
                            <div class="text-center py-12">
                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                </div>
                                <p class="text-sm font-bold text-gray-500">Belum ada tim sales yang terdaftar</p>
                                <p class="text-xs text-gray-400 mt-1">Hubungi HRD untuk menambahkan anggota tim</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: STATS & MENU (4 kolom) --}}
            <div class="col-span-4 space-y-5">
                {{-- STATISTIK --}}
                <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6">
                    <h2 class="font-bold text-base mb-4">Statistik Hari Ini</h2>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3 bg-blue-50 rounded-xl p-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-xs text-gray-500">Total Sales</p>
                                <p class="text-lg font-bold text-blue-600">{{ count($sales) }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 bg-green-50 rounded-xl p-3">
                            <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-xs text-gray-500">Sudah Absen</p>
                                <p class="text-lg font-bold text-green-600">{{ collect($todayAbsensi)->where('hasStarted', true)->count() }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- TOMBOL PERSETUJUAN REIMBURSE --}}
                <a href="{{ route('supervisor.reimburse.approval') }}" class="block bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6 hover:shadow-md transition group">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-orange-100 text-orange-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-bold text-gray-900 group-hover:text-orange-700 transition">Persetujuan Reimburse</h4>
                            <p class="text-xs text-gray-500 mt-0.5">Cek pengajuan biaya dari sales</p>
                        </div>
                        @if(($stats['pending_spv'] ?? 0) > 0)
                            <div class="w-8 h-8 bg-red-500 text-white rounded-full flex items-center justify-center font-bold text-xs shadow-md">
                                {{ $stats['pending_spv'] }}
                            </div>
                        @else
                            <svg class="w-5 h-5 text-gray-300 group-hover:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        @endif
                    </div>
                </a>
            </div>
        </div>
    </div>
@endsection