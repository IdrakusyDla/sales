@extends('layout')
@section('content')
    <div class="px-5 py-6">
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
                                <p class="text-xs text-blue-500 mt-1">📍 {{ $dailyLog->visits->count() }} Kunjungan</p>
                            @endif
                            @if($dailyLog->expenses->count() > 0)
                                <p class="text-xs text-orange-500">💰 {{ $dailyLog->expenses->count() }} Expense</p>
                            @endif
                        </div>
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </a>
            @empty
                <div class="text-center py-10 bg-gray-50 rounded-xl">
                    <div class="text-4xl mb-2">📅</div>
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
