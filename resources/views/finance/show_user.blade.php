@extends('layout')
@section('content')
    <div class="px-5 md:px-8 py-6 md:py-8">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('finance.dashboard') }}" class="text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold">{{ $user->name }}</h1>
                <p class="text-sm text-gray-500">{{ $user->username }} • {{ ucfirst($user->role) }}</p>
            </div>
        </div>

        {{-- STATISTIK --}}
        <div class="grid grid-cols-3 gap-3 mb-6">
            <div class="bg-blue-50 rounded-xl p-3 text-center">
                <p class="text-xs text-gray-600">Absensi</p>
                <p class="text-xl font-bold text-blue-600">{{ $dailyLogs->total() }}</p>
            </div>
            <div class="bg-green-50 rounded-xl p-3 text-center">
                <p class="text-xs text-gray-600">Kunjungan</p>
                <p class="text-xl font-bold text-green-600">
                    {{ \App\Models\Visit::whereHas('dailyLog', fn($q) => $q->where('user_id', $user->id))->count() }}
                </p>
            </div>
            <div class="bg-orange-50 rounded-xl p-3 text-center">
                <p class="text-xs text-gray-600">Reimburse</p>
                <p class="text-xl font-bold text-orange-600">Rp {{ number_format(\App\Models\Expense::where('user_id', $user->id)->sum('amount') / 1000, 0) }}K</p>
            </div>
        </div>

        {{-- LIST ABSENSI --}}
        <h2 class="font-bold text-lg text-gray-800 mb-3">Riwayat Absensi</h2>
        <div class="space-y-3">
            @forelse($dailyLogs as $dailyLog)
                <a href="{{ route('finance.history.detail', $dailyLog->id) }}"
                    class="block bg-white rounded-2xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-bold text-gray-800">{{ \Carbon\Carbon::parse($dailyLog->date)->format('d M Y') }}</h3>
                            <p class="text-xs text-gray-500">
                                Masuk: {{ $dailyLog->start_time?->format('H:i') ?? '-' }}
                                @if($dailyLog->end_time)
                                    | Keluar: {{ $dailyLog->end_time->format('H:i') }}
                                @endif
                            </p>
                            @if($dailyLog->visits->count() > 0)
                                <p class="text-xs text-blue-500 mt-1"><svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg> {{ $dailyLog->visits->count() }} Kunjungan</p>
                            @endif
                            @if($dailyLog->expenses->count() > 0)
                                <p class="text-xs text-orange-500"><svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> {{ $dailyLog->expenses->count() }} Expense</p>
                            @endif
                        </div>
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </a>
            @empty
                <div class="text-center py-10 bg-gray-50 rounded-xl">
                    <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <p class="text-sm text-gray-500">Tidak ada data absensi</p>
                </div>
            @endforelse
        </div>

        {{-- PAGINATION --}}
        @if($dailyLogs->hasPages())
            <div class="flex justify-center mt-6">
                {{ $dailyLogs->links() }}
            </div>
        @endif
    </div>
@endsection
