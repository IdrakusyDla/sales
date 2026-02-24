@extends('layout')
@section('content')
    <div class="px-5 py-6">
        <h1 class="text-2xl font-bold mb-2">Dashboard HRD</h1>
        <p class="text-sm text-gray-600 mb-6">Manajemen karyawan sales & supervisor</p>

        {{-- STATISTIK --}}
        <div class="grid grid-cols-2 gap-3 mb-6">
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
        <div class="grid grid-cols-2 gap-3 mb-6">
            <a href="{{ route('hrd.sales.create') }}"
                class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 flex items-center gap-3 hover:bg-gray-50">
                <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-bold">
                    +
                </div>
                <div class="flex-1">
                    <h3 class="font-bold text-sm">Tambah Sales</h3>
                    <p class="text-xs text-gray-500">Akun baru</p>
                </div>
            </a>
            <a href="{{ route('hrd.supervisor.create') }}"
                class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 flex items-center gap-3 hover:bg-gray-50">
                <div
                    class="w-10 h-10 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center font-bold">
                    +
                </div>
                <div class="flex-1">
                    <h3 class="font-bold text-sm">Tambah Supervisor</h3>
                    <p class="text-xs text-gray-500">Akun baru</p>
                </div>
            </a>
            <a href="{{ route('hrd.finance.create') }}"
                class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 flex items-center gap-3 hover:bg-gray-50 col-span-2">
                <div
                    class="w-10 h-10 bg-green-100 text-green-600 rounded-full flex items-center justify-center font-bold">
                    +
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
                <div
                    class="w-10 h-10 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center font-bold mr-4">
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
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>

        {{-- MENU APPROVAL REIMBURSE --}}
        <div class="mb-6">
            <a href="{{ route('hrd.reimburse.approval') }}"
                class="flex items-center bg-white p-4 rounded-xl shadow-sm border border-gray-200 hover:bg-gray-50 transition">
                <div
                    class="w-10 h-10 bg-green-100 text-green-600 rounded-full flex items-center justify-center font-bold mr-4">
                    📝
                </div>
                <div class="flex-1">
                    <h3 class="font-bold text-gray-800">Persetujuan Reimburse</h3>
                    <p class="text-xs text-gray-500">Verifikasi & pencairan dana</p>
                </div>
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
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
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-xl font-bold text-sm mt-3">
                Cari
            </button>
        </form>

        {{-- LIST KARYAWAN --}}
        <div class="space-y-3">
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
                    <div class="text-4xl mb-2">👥</div>
                    <p class="text-sm text-gray-500">Tidak ada karyawan</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection