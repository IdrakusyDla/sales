@extends('layout')
@section('content')
    {{-- 1. Header Simpel & Tegas --}}
    <div class="bg-indigo-700 text-white px-6 py-6 rounded-b-2xl shadow-md mb-5">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-xl font-bold">Dashboard HRD</h1>
                <p class="text-indigo-200 text-xs mt-1">Halo, {{ Auth::user()->name }}</p>
            </div>

            {{-- Tombol Ganti Password (Teks + Emoji) --}}
            <a href="{{ route('password.edit') }}"
                class="bg-indigo-600 hover:bg-indigo-500 text-white px-3 py-2 rounded-xl shadow-sm border border-indigo-500/50 flex items-center gap-2 transition">
                <span class="text-lg">🔑</span>
                <span class="text-xs font-bold">Ubah Password</span>
            </a>
        </div>
    </div>

    <div class="px-4 pb-24">

        {{-- 2. Menu Utama --}}
        <div class="space-y-3 mb-6">
            {{-- Tombol Tambah Karyawan --}}
            <a href="{{ route('hrd.create') }}"
                class="flex items-center bg-white p-4 rounded-xl shadow-sm border border-gray-200 active:bg-gray-50 transition">
                <div class="w-10 h-10 bg-green-100 text-green-600 rounded-full flex items-center justify-center font-bold mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6">
                        </path>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="font-bold text-gray-800">Tambah Karyawan</h3>
                    <p class="text-xs text-gray-500">Daftarkan sales baru</p>
                </div>
                <div class="text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
            </a>

            {{-- Tombol Setting Bahan Bakar --}}
            <a href="{{ route('fuel_settings.index') }}"
                class="flex items-center bg-white p-4 rounded-xl shadow-sm border border-gray-200 active:bg-gray-50 transition">
                <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-bold mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4">
                        </path>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="font-bold text-gray-800">Setting Bahan Bakar</h3>
                    <p class="text-xs text-gray-500">Atur ratio & harga bahan bakar</p>
                </div>
                <div class="text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
            </a>
        </div>

        {{-- 3. Search & Judul --}}
        <div class="mb-4">
            <form action="{{ route('dashboard') }}" method="GET" class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama sales..."
                    class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm">
                <div class="absolute top-3.5 left-3 text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </form>
        </div>

        <div class="flex justify-between items-end mb-3 px-1">
            <h2 class="text-gray-800 font-bold text-base">Daftar Tim Sales</h2>
            <span class="text-xs text-gray-500">Total: {{ count($users) }}</span>
        </div>

        {{-- 4. List Karyawan (Compact & Rapi) --}}
        <div class="space-y-3">
            @forelse($users as $u)
                <a href="{{ route('hrd.show', $u->id) }}"
                    class="block bg-white p-3 rounded-lg border border-gray-200 shadow-sm flex items-center hover:bg-gray-50 transition">
                    {{-- Avatar --}}
                    <div
                        class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center text-gray-600 font-bold text-sm mr-3">
                        {{ substr($u->name, 0, 1) }}
                    </div>

                    {{-- Text Info --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-gray-800 truncate">{{ $u->name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ $u->username }}</p>
                    </div>

                    {{-- Status Badge --}}
                    <div class="text-right">
                        <span class="bg-green-100 text-green-700 text-[10px] font-bold px-2 py-1 rounded-full">
                            Active
                        </span>
                    </div>
                </a>
            @empty
                <div class="text-center py-8 bg-gray-50 rounded-xl border border-dashed border-gray-300">
                    <p class="text-gray-500 text-sm">Belum ada data sales.</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection
