@extends('layout')
@section('content')
    <div class="px-5 md:px-8 py-6 md:py-8">
        <h1 class="text-2xl font-bold mb-2">Dashboard HRD</h1>
        <p class="text-sm text-gray-600 mb-6">Manajemen karyawan sales & supervisor</p>

        {{-- STATISTIK --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
            <div class="bg-blue-50 rounded-xl p-4">
                <p class="text-xs text-gray-600 mb-1">Total Sales</p>
                <p class="text-2xl font-bold text-blue-600">{{ $users->where('role', 'sales')->count() }}</p>
            </div>
            <div class="bg-purple-50 rounded-xl p-4">
                <p class="text-xs text-gray-600 mb-1">Total Supervisor</p>
                <p class="text-2xl font-bold text-purple-600">{{ $users->where('role', 'supervisor')->count() }}</p>
            </div>
        </div>

        {{-- TOMBOL TAMBAH --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
            <a href="{{ route('hrd.sales.create') }}"
                class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 flex items-center gap-3 hover:bg-gray-50">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="font-bold text-sm">Tambah Sales</h3>
                    <p class="text-xs text-gray-500">Akun baru</p>
                </div>
            </a>
            <a href="{{ route('hrd.supervisor.create') }}"
                class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 flex items-center gap-3 hover:bg-gray-50">
                <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="font-bold text-sm">Tambah Supervisor</h3>
                    <p class="text-xs text-gray-500">Akun baru</p>
                </div>
            </a>
            <a href="{{ route('hrd.finance.create') }}"
                class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 flex items-center gap-3 hover:bg-gray-50 col-span-2">
                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="font-bold text-sm">Tambah Finance</h3>
                    <p class="text-xs text-gray-500">Akun baru</p>
                </div>
            </a>
        </div>

        {{-- MENU SETTING BAHAN BAKAR --}}
        <div class="mb-3">
            <a href="{{ route('fuel_settings.index') }}"
                class="flex items-center bg-white p-4 rounded-xl shadow-sm border border-gray-200 hover:bg-gray-50 transition">
                <div class="w-10 h-10 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center mr-4">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4">
                        </path>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="font-bold text-gray-800">Setting Bahan Bakar</h3>
                    <p class="text-xs text-gray-500">Atur ratio & harga bahan bakar</p>
                </div>
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>

        {{-- MENU APPROVAL REIMBURSE --}}
        <div class="mb-6">
            <a href="{{ route('hrd.reimburse.approval') }}"
                class="flex items-center justify-between bg-white p-4 rounded-xl shadow-sm border border-gray-200 hover:bg-gray-50 transition">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800">Persetujuan Reimburse</h3>
                        <p class="text-xs text-gray-500">Verifikasi & pencairan dana</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @if(($stats['pending_hrd'] ?? 0) > 0)
                        <span
                            class="bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full">{{ $stats['pending_hrd'] }}</span>
                    @endif
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
            </a>
        </div>

        {{-- SEARCH & FILTER --}}
        <form method="GET" action="{{ route('hrd.dashboard') }}" class="mb-6">
            <div class="flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama..."
                    class="flex-1 border border-gray-300 rounded-xl p-3 text-sm">
                <select name="role" class="border border-gray-300 rounded-xl p-3 text-sm">
                    <option value="">Semua Role</option>
                    <option value="sales" {{ request('role') == 'sales' ? 'selected' : '' }}>Sales</option>
                    <option value="supervisor" {{ request('role') == 'supervisor' ? 'selected' : '' }}>Supervisor</option>
                </select>
                <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-xl font-bold text-sm">
                    Cari
                </button>
            </div>
        </form>

        {{-- LIST KARYAWAN --}}
        <div class="space-y-3 md:grid md:grid-cols-2 md:gap-4 md:space-y-0">
            <h2 class="font-bold text-lg text-gray-800 mb-3">Daftar Karyawan</h2>
            @forelse($users as $user)
                <a href="{{ route('hrd.show.user', $user->id) }}"
                    class="block bg-white rounded-2xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3 flex-1">
                            <div
                                class="w-12 h-12 bg-{{ $user->role == 'sales' ? 'blue' : 'purple' }}-100 rounded-full flex items-center justify-center text-{{ $user->role == 'sales' ? 'blue' : 'purple' }}-600 font-bold">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                            <div class="flex-1">
                                <h3 class="font-bold text-gray-800">{{ $user->name }}</h3>
                                <p class="text-xs text-gray-500">{{ $user->username }} • {{ ucfirst($user->role) }}</p>
                                @if($user->supervisor)
                                    <p class="text-xs text-gray-400">SPV: {{ $user->supervisor->name }}</p>
                                @endif
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </a>
            @empty
                <div class="text-center py-10 bg-gray-50 rounded-xl">
                    <div class="text-4xl mb-2">
                        <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                    </div>
                    <p class="text-sm text-gray-500">Tidak ada karyawan</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection