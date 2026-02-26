@extends('layout')
@section('content')
    <div class="px-5 md:px-8 py-6 md:py-8">
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
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c-.94 1.543.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 002.573-1.066c.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                Konfigurasi Sistem
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
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                Pembersihan Manual
            </h2>

            <p class="text-sm text-gray-600 mb-4">
                <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Jalankan perintah ini untuk menghapus user yang sudah tidak aktif lebih dari periode retensi
                ({{ $retention_months }} bulan).
            </p>

            <form action="{{ route('it.settings.cleanup_users') }}" method="POST">
                @csrf
                <button type="submit"
                    onclick="return confirm('Yakin ingin menghapus user tidak aktif? Tindakan ini tidak bisa dibatalkan.')"
                    class="w-full bg-red-600 text-white font-bold py-3 px-4 rounded-xl hover:bg-red-700 transition flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    Hapus User Tidak Aktif Sekarang
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
