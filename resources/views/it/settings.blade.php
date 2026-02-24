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
            <h1 class="text-2xl font-bold">Pengaturan</h1>
        </div>

        {{-- CONFIG AUTO CLEANUP --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <h2 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                <span class="text-xl">⚙️</span> Konfigurasi Sistem
            </h2>

            <form action="{{ route('it.settings.update') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Masa Simpan Foto (Bulan)</label>
                    <p class="text-xs text-gray-500 mb-2">Foto absen dan struk yang lebih lama dari masa simpan akan dihapus
                        otomatis setiap hari.</p>
                    <input type="number" name="retention_months" value="{{ $retention_months }}" min="1" required
                        class="w-full border border-gray-300 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <button type="submit"
                    class="w-full bg-blue-600 text-white py-3 rounded-xl font-bold text-sm hover:bg-blue-700 transition">
                    Simpan Pengaturan
                </button>
            </form>
        </div>

        {{-- MANUAL CLEANUP --}}
        <div class="bg-white rounded-2xl shadow-sm border border-red-100 p-6">
            <h2 class="font-bold text-red-800 mb-4 flex items-center gap-2">
                <span class="text-xl">🗑️</span> Pembersihan Manual
                {{-- MANUAL CLEANUP USER --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">Pembersihan Manual</h2>
                    <p class="text-sm text-gray-600 mb-4">
                        Jalankan perintah ini untuk menghapus user yang sudah tidak aktif lebih dari periode retensi
                        ({{ $retention_months }} bulan).
                    </p>

                    <form action="{{ route('it.settings.cleanup_users') }}" method="POST">
                        @csrf
                        <button type="submit"
                            onclick="return confirm('Yakin ingin menghapus user tidak aktif? Tindakan ini tidak bisa dibatalkan.')"
                            class="bg-red-600 text-white font-bold py-2 px-4 rounded-xl hover:bg-red-700 transition">
                            🗑️ Hapus User Tidak Aktif Sekarang
                        </button>
                    </form>

                    @if(session('cleanup_output'))
                        <div class="mt-4 p-4 bg-gray-900 text-green-400 font-mono text-xs rounded-xl overflow-x-auto">
                            <pre>{{ session('cleanup_output') }}</pre>
                        </div>
                    @endif
                </div>
        </div>
@endsection