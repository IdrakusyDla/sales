@extends('layout')
@section('content')
    <div class="px-5 md:px-8 py-6 md:py-8">
        <h1 class="text-2xl font-bold mb-2">Dashboard Supervisor</h1>
        <p class="text-sm text-gray-600 mb-6">Monitor absensi sales yang ditugaskan ke Anda</p>

        {{-- STATISTIK HARI INI --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
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
        <div class="space-y-3 md:grid md:grid-cols-2 md:gap-4 md:space-y-0">
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
@endsection