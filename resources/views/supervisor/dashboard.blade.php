@extends('layout')
@section('content')
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
                    <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center text-xl">
                        📝
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800">Persetujuan Reimburse</h3>
                        <p class="text-xs text-gray-500">Cek pengajuan biaya dari sales</p>
                    </div>
                </div>
                <div>
                    <span class="text-gray-400">➡️</span>
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
                    <div class="text-4xl mb-2">👥</div>
                    <p class="text-sm text-gray-500">Belum ada sales yang ditugaskan</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection