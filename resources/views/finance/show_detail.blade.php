@extends('layout')
@section('content')
    <div class="px-5 md:px-8 py-6 md:py-8">
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
            <h2 class="font-bold text-lg mb-4"><svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg> Foto Absensi</h2>
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
                <h2 class="font-bold text-lg mb-4"><svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg> Kunjungan Toko ({{ $dailyLog->visits->count() }})</h2>
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
                <h2 class="font-bold text-lg mb-4"><svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Reimburse ({{ $dailyLog->expenses->count() }})</h2>
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
                                    <p class="text-green-600"><svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> SPV: {{ $expense->approvedBySpv?->name }} ({{ \Carbon\Carbon::parse($expense->approved_by_spv_at)->format('d M H:i') }})</p>
                                @endif
                                @if($expense->approved_by_hrd_at)
                                    <p class="text-green-600"><svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> HRD: {{ $expense->approvedByHrd?->name }} ({{ \Carbon\Carbon::parse($expense->approved_by_hrd_at)->format('d M H:i') }})</p>
                                @endif
                                @if($expense->approved_by_finance_at)
                                    <p class="text-green-600"><svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Finance: {{ $expense->approvedByFinance?->name }} ({{ \Carbon\Carbon::parse($expense->approved_by_finance_at)->format('d M H:i') }})</p>
                                @endif
                                @if($expense->rejection_note)
                                    <p class="text-red-600"><svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg> Catatan: {{ $expense->rejection_note }}</p>
                                @endif
                            </div>

                            @if($expense->note)
                                <p class="text-xs text-gray-600 mb-2">{{ $expense->note }}</p>
                            @endif
                            @if($expense->photo_receipt)
                                <a href="{{ asset('storage/' . $expense->photo_receipt) }}" target="_blank" class="text-blue-500 text-xs flex items-center gap-1">
                                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg> Lihat Bukti
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection
