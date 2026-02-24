@extends('layout')
@section('content')
    <div class="px-5 py-6">
        {{-- HEADER --}}
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('finance.show.user', $dailyLog->user_id) }}" class="text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div class="flex-1">
                <h1 class="text-2xl font-bold">Detail Absen</h1>
                <p class="text-sm text-gray-600">{{ $dailyLog->user->name }} ({{ ucfirst($dailyLog->user->role) }}) • {{ \Carbon\Carbon::parse($dailyLog->date)->format('d M Y') }}</p>
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

        {{-- ABSENSI FOTO --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-4">
            <h2 class="font-bold text-lg mb-4">📸 Foto Absensi</h2>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <p class="text-xs text-gray-600 mb-2">Absen Masuk</p>
                        @if($dailyLog->start_photo)
                            <button type="button" onclick="openImageModal('{{ route('files.daily.photo', [$dailyLog->id, 'start_photo']) }}')" class="w-full block">
                                <img src="{{ asset('storage/' . $dailyLog->start_photo) }}" class="w-full rounded-xl">
                            </button>
                        @endif
                    @if($dailyLog->start_odo_photo)
                        <p class="text-xs text-gray-500 mt-2">Odometer: {{ number_format($dailyLog->start_odo_value, 2) }} KM</p>
                        <img src="{{ asset('storage/' . $dailyLog->start_odo_photo) }}" class="w-full rounded-xl mt-2">
                    @endif
                </div>
                @if($dailyLog->end_photo)
                    <div>
                        <p class="text-xs text-gray-600 mb-2">Absen Keluar</p>
                            <button type="button" onclick="openImageModal('{{ route('files.daily.photo', [$dailyLog->id, 'end_photo']) }}')" class="w-full block">
                                <img src="{{ asset('storage/' . $dailyLog->end_photo) }}" class="w-full rounded-xl">
                            </button>
                        @endif
                        @if($dailyLog->end_odo_photo)
                            <p class="text-xs text-gray-500 mt-2">Odometer: {{ number_format($dailyLog->end_odo_value, 2) }} KM</p>
                            <img src="{{ asset('storage/' . $dailyLog->end_odo_photo) }}" class="w-full rounded-xl mt-2">
                        @endif
                    </div>
                @endif
            </div>
            @if($dailyLog->daily_plan)
                <div class="mt-4 bg-gray-50 rounded-xl p-3">
                    <p class="text-xs text-gray-600 mb-1">Rencana Harian:</p>
                    <p class="text-sm text-gray-800">{{ $dailyLog->daily_plan }}</p>
                </div>
            @endif
        </div>

        {{-- KUNJUNGAN TOKO --}}
        @if($dailyLog->visits->count() > 0)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-4">
                <h2 class="font-bold text-lg mb-4">📍 Kunjungan Toko ({{ $dailyLog->visits->count() }})</h2>
                <div class="space-y-3">
                    @foreach($dailyLog->visits as $visit)
                        <div class="bg-gray-50 rounded-xl p-3">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <h3 class="font-bold text-gray-800">{{ $visit->client_name }}</h3>
                                    <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($visit->time)->format('H:i') }}</p>
                                </div>
                                <span class="text-xs px-2 py-1 rounded-full
                                    @if($visit->status === 'completed') bg-green-100 text-green-700
                                    @elseif($visit->status === 'failed') bg-red-100 text-red-700
                                    @else bg-yellow-100 text-yellow-700 @endif">
                                    {{ ucfirst($visit->status) }}
                                </span>
                            </div>
                            @if($visit->notes)
                                <p class="text-xs text-gray-600 mb-2">{{ $visit->notes }}</p>
                            @endif
                            @if($visit->photo_path)
                                  <button type="button" onclick="openImageModal('{{ route('files.visit.photo', $visit->id) }}')" class="w-full block">
                                     <img src="{{ asset('storage/' . $visit->photo_path) }}" class="w-full rounded-lg">
                                  </button>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- REIMBURSE / EXPENSES --}}
        @if($dailyLog->expenses->count() > 0)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-4">
                <h2 class="font-bold text-lg mb-4">💰 Reimburse ({{ $dailyLog->expenses->count() }})</h2>
                <div class="space-y-3">
                    @foreach($dailyLog->expenses as $expense)
                        <div class="bg-gray-50 rounded-xl p-3">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <h3 class="font-bold text-gray-800">{{ ucfirst($expense->type) }}</h3>
                                    <p class="text-lg font-bold text-blue-600">Rp {{ number_format($expense->amount, 0, ',', '.') }}</p>
                                </div>
                                <span class="text-xs px-2 py-1 rounded-full
                                    @if($expense->status === 'approved') bg-green-100 text-green-700
                                    @elseif($expense->status === 'rejected_permanent') bg-red-100 text-red-700
                                    @elseif(str_starts_with($expense->status, 'needs_revision')) bg-orange-100 text-orange-700
                                    @else bg-yellow-100 text-yellow-700 @endif">
                                    {{ $expense->status_label }}
                                </span>
                            </div>

                            {{-- Approval History --}}
                            <div class="text-xs space-y-1 mb-2 bg-white rounded-lg p-2">
                                @if($expense->approved_by_spv_at)
                                    <p class="text-green-600">✅ SPV: {{ $expense->approvedBySpv?->name }} ({{ \Carbon\Carbon::parse($expense->approved_by_spv_at)->format('d M H:i') }})</p>
                                @endif
                                @if($expense->approved_by_hrd_at)
                                    <p class="text-green-600">✅ HRD: {{ $expense->approvedByHrd?->name }} ({{ \Carbon\Carbon::parse($expense->approved_by_hrd_at)->format('d M H:i') }})</p>
                                @endif
                                @if($expense->approved_by_finance_at)
                                    <p class="text-green-600">✅ Finance: {{ $expense->approvedByFinance?->name }} ({{ \Carbon\Carbon::parse($expense->approved_by_finance_at)->format('d M H:i') }})</p>
                                @endif
                                @if($expense->rejection_note)
                                    <p class="text-red-600">❌ Catatan: {{ $expense->rejection_note }}</p>
                                @endif
                            </div>

                            @if($expense->note)
                                <p class="text-xs text-gray-600 mb-2">{{ $expense->note }}</p>
                            @endif
                            @if($expense->photo_receipt)
                                <a href="{{ asset('storage/' . $expense->photo_receipt) }}" target="_blank" class="text-blue-500 text-xs">
                                    📎 Lihat Bukti
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection
