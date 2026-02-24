@extends('layout')
@section('content')
    <div class="px-5 py-6">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('it.dashboard') }}" class="text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold flex items-center gap-2">
                    {{ $user->name }}
                    @if(!$user->is_active)
                        <span
                            class="bg-red-100 text-red-600 text-xs px-2 py-1 rounded-full border border-red-200">Nonaktif</span>
                    @endif
                </h1>
                <p class="text-sm text-gray-500">
                    {{ $user->username }} • {{ ucfirst($user->role) }}
                    @if($user->last_activity_at)
                        • Aktif: {{ \Carbon\Carbon::parse($user->last_activity_at)->diffForHumans() }}
                    @endif
                </p>
            </div>
        </div>

        {{-- STATISTIK --}}
        <div class="grid grid-cols-3 gap-3 mb-6">
            <div class="bg-blue-50 rounded-xl p-3 text-center">
                <p class="text-xs text-gray-600">Absensi</p>
                <p class="text-xl font-bold text-blue-600">{{ $stats['total_absensi'] }}</p>
            </div>
            <div class="bg-green-50 rounded-xl p-3 text-center">
                <p class="text-xs text-gray-600">Kunjungan</p>
                <p class="text-xl font-bold text-green-600">{{ $stats['total_visits'] }}</p>
            </div>
            <div class="bg-orange-50 rounded-xl p-3 text-center">
                <p class="text-xs text-gray-600">Reimburse</p>
                <p class="text-xl font-bold text-orange-600">Rp {{ number_format($stats['total_expenses'] / 1000, 0) }}K</p>
            </div>
        </div>

        {{-- TOMBOL BLOCK / UNBLOCK --}}
        <form action="{{ route('it.user.toggle_status', $user->id) }}" method="POST" class="mb-3">
            @csrf
            @if($user->is_active)
                <button type="submit"
                    onclick="return confirm('Nonaktifkan akun {{ $user->name }}? User tidak akan bisa login.')"
                    class="w-full bg-red-600 text-white py-3 rounded-xl font-bold text-sm">
                    🚫 Nonaktifkan Akun
                </button>
            @else
                <button type="submit" onclick="return confirm('Aktifkan kembali akun {{ $user->name }}?')"
                    class="w-full bg-green-600 text-white py-3 rounded-xl font-bold text-sm">
                    ✅ Aktifkan Akun
                </button>
            @endif
        </form>

        {{-- TOMBOL RESET PASSWORD --}}
        <form action="{{ route('it.reset.password', $user->id) }}" method="POST" class="mb-3">
            @csrf
            <button type="submit" onclick="return confirm('Reset password untuk {{ $user->name }}?')"
                class="w-full bg-yellow-500 text-white py-3 rounded-xl font-bold text-sm">
                🔑 Reset Password
            </button>
        </form>

        {{-- TOMBOL HAPUS AKUN (KHUSUS IT) --}}
        <form action="{{ route('it.user.delete', $user->id) }}" method="POST" class="mb-6">
            @csrf
            @method('DELETE')
            <button type="submit"
                onclick="return confirm('⚠️ HAPUS AKUN {{ strtoupper($user->name) }}?\n\nTindakan ini TIDAK DAPAT dibatalkan!\nSemua data absensi, kunjungan, dan reimburse akan dihapus permanen.\n\nLanjutkan menghapus?')"
                class="w-full bg-gray-800 text-white py-3 rounded-xl font-bold text-sm border-2 border-gray-900">
                🗑️ Hapus Akun Permanen
            </button>
        </form>

        {{-- FILTER TANGGAL --}}
        <form method="GET" action="{{ route('it.show.user', $user->id) }}" class="mb-6">
            <div class="grid grid-cols-2 gap-3">
                <input type="date" name="start_date" value="{{ request('start_date') }}"
                    class="w-full border border-gray-300 rounded-xl p-3 text-sm">
                <input type="date" name="end_date" value="{{ request('end_date') }}"
                    class="w-full border border-gray-300 rounded-xl p-3 text-sm">
            </div>
            <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-xl font-bold text-sm mt-3">
                Filter
            </button>
        </form>

        {{-- LIST RIWAYAT (Sama seperti HRD) --}}
        <div class="space-y-4">
            @forelse($dailyLogs as $log)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
                    <div class="flex justify-between items-center mb-3 pb-3 border-b border-gray-100">
                        <div>
                            <h3 class="font-bold text-gray-800">{{ \Carbon\Carbon::parse($log->date)->format('d M Y') }}</h3>
                            <p class="text-xs text-gray-500">
                                @if($log->hasStarted())
                                    Masuk: {{ \Carbon\Carbon::parse($log->start_time)->format('H:i') }}
                                @endif
                                @if($log->hasEnded())
                                    | Keluar: {{ \Carbon\Carbon::parse($log->end_time)->format('H:i') }}
                                @endif
                            </p>
                        </div>
                        @if($log->hasEnded())
                            <span class="bg-green-100 text-green-700 text-[10px] font-bold px-2 py-1 rounded-full">Selesai</span>
                        @else
                            <span
                                class="bg-yellow-100 text-yellow-700 text-[10px] font-bold px-2 py-1 rounded-full">Berlangsung</span>
                        @endif
                    </div>

                    <div class="space-y-2 mb-3">
                        @foreach($log->visits as $visit)
                            <div class="flex items-start gap-3 p-2 bg-gray-50 rounded-lg">
                                <div
                                    class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 text-sm
                                                {{ $visit->status == 'completed' ? 'bg-green-100' : ($visit->status == 'failed' ? 'bg-red-100' : 'bg-gray-200') }}">
                                    @if($visit->status == 'completed') ✅
                                    @elseif($visit->status == 'failed') ❌
                                    @else ⏳
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <p class="font-bold text-sm">{{ $visit->client_name }}</p>
                                    <p class="text-xs text-gray-500">
                                        {{ \Carbon\Carbon::parse($visit->time)->format('H:i') }}
                                        @if(!$visit->is_planned) <span class="text-orange-600">(Dadakan)</span> @endif
                                    </p>
                                    @if($visit->notes)
                                        <p class="text-xs text-gray-600 mt-1">{{ $visit->notes }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($log->expenses->count() > 0)
                        <div class="pt-3 border-t border-gray-100">
                            <p class="text-xs font-bold text-gray-600 mb-2">Reimburse:</p>
                            <div class="space-y-1">
                                @foreach($log->expenses as $expense)
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">
                                            @if($expense->is_auto_calculated)
                                                ⛽ Bahan Bakar (Auto)
                                            @else
                                                {{ ucfirst($expense->type) }}
                                            @endif
                                        </span>
                                        <span class="font-bold text-green-600">Rp
                                            {{ number_format($expense->amount, 0, ',', '.') }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center py-10">
                    <div class="text-4xl mb-2">📋</div>
                    <p class="text-sm text-gray-500">Belum ada riwayat aktivitas</p>
                </div>
            @endforelse
        </div>

        @if($dailyLogs->hasPages())
            <div class="mt-6">
                {{ $dailyLogs->links() }}
            </div>
        @endif
    </div>
@endsection