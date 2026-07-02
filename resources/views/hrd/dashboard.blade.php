@extends('layout')
@section('content')
    <div class="px-5 md:px-8 py-6 md:py-8">
        <h1 class="text-2xl font-bold mb-1">Dashboard HRD</h1>
        <p class="text-sm text-gray-600 mb-4 md:mb-6">Manajemen karyawan sales, supervisor & finance</p>

        {{-- STATISTIK --}}
        <div class="grid grid-cols-3 gap-2 md:gap-3 mb-3 md:mb-6">
            <div class="bg-blue-50 rounded-xl p-2.5 md:p-4">
                <p class="text-[10px] md:text-xs text-gray-600 mb-0.5 md:mb-1">Total Sales</p>
                <p class="text-lg md:text-2xl font-bold text-blue-600">{{ $users->where('role', 'sales')->count() }}</p>
            </div>
            <div class="bg-purple-50 rounded-xl p-2.5 md:p-4">
                <p class="text-[10px] md:text-xs text-gray-600 mb-0.5 md:mb-1">Total Supervisor</p>
                <p class="text-lg md:text-2xl font-bold text-purple-600">{{ $users->where('role', 'supervisor')->count() }}</p>
            </div>
            <div class="bg-green-50 rounded-xl p-2.5 md:p-4">
                <p class="text-[10px] md:text-xs text-gray-600 mb-0.5 md:mb-1">Total Finance</p>
                <p class="text-lg md:text-2xl font-bold text-green-600">{{ $users->where('role', 'finance')->count() }}</p>
            </div>
        </div>

        {{-- TOMBOL AKSI CEPAT --}}
        <div class="grid grid-cols-5 gap-1.5 mb-3 md:grid-cols-2 lg:grid-cols-5 md:gap-3 md:mb-6">
            {{-- Tambah Akun Baru --}}
            <a href="{{ route('hrd.users.create') }}"
                class="flex flex-col items-center gap-1 p-2 md:flex-row md:gap-3 md:p-4 rounded-xl bg-white border border-gray-200 shadow-sm hover:bg-gray-50">
                <div class="w-8 h-8 md:w-10 md:h-10 bg-blue-100 rounded-full flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 md:w-6 md:h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </div>
                <span class="text-[9px] md:text-sm font-bold text-gray-800 text-center leading-tight md:text-left">Tambah Akun</span>
                <svg class="hidden md:block w-5 h-5 text-gray-400 shrink-0 md:ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>

            {{-- Daftar Perusahaan --}}
            <a href="{{ route('hrd.companies.index') }}"
                class="flex flex-col items-center gap-1 p-2 md:flex-row md:gap-3 md:p-4 rounded-xl bg-white border border-gray-200 shadow-sm hover:bg-gray-50">
                <div class="w-8 h-8 md:w-10 md:h-10 bg-cyan-100 rounded-full flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 md:w-6 md:h-6 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <span class="text-[9px] md:text-sm font-bold text-gray-800 text-center leading-tight md:text-left">Perusahaan</span>
                <svg class="hidden md:block w-5 h-5 text-gray-400 shrink-0 md:ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>

            {{-- Daftar Jabatan --}}
            <a href="{{ route('hrd.job_positions.index') }}"
                class="flex flex-col items-center gap-1 p-2 md:flex-row md:gap-3 md:p-4 rounded-xl bg-white border border-gray-200 shadow-sm hover:bg-gray-50">
                <div class="w-8 h-8 md:w-10 md:h-10 bg-teal-100 rounded-full flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 md:w-6 md:h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <span class="text-[9px] md:text-sm font-bold text-gray-800 text-center leading-tight md:text-left">Jabatan</span>
                <svg class="hidden md:block w-5 h-5 text-gray-400 shrink-0 md:ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>

            {{-- Persetujuan Reimburse --}}
            <a href="{{ route('hrd.reimburse.approval') }}"
                class="flex flex-col items-center gap-1 p-2 md:flex-row md:gap-3 md:p-4 rounded-xl bg-white border border-gray-200 shadow-sm hover:bg-gray-50">
                <div class="w-8 h-8 md:w-10 md:h-10 bg-green-100 rounded-full flex items-center justify-center shrink-0 relative">
                    <svg class="w-4 h-4 md:w-6 md:h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    @if(($stats['pending_hrd'] ?? 0) > 0)
                        <span class="absolute -top-1.5 -right-1.5 bg-red-500 text-white text-[9px] font-bold min-w-[16px] h-4 px-1 flex items-center justify-center rounded-full border border-white">{{ $stats['pending_hrd'] }}</span>
                    @endif
                </div>
                <span class="text-[9px] md:text-sm font-bold text-gray-800 text-center leading-tight md:text-left">Reimburse</span>
                <svg class="hidden md:block w-5 h-5 text-gray-400 shrink-0 md:ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>

            {{-- Setting Bahan Bakar --}}
            <a href="{{ route('fuel_settings.index') }}"
                class="flex flex-col items-center gap-1 p-2 md:flex-row md:gap-3 md:p-4 rounded-xl bg-white border border-gray-200 shadow-sm hover:bg-gray-50">
                <div class="w-8 h-8 md:w-10 md:h-10 bg-orange-100 rounded-full flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 md:w-6 md:h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4">
                        </path>
                    </svg>
                </div>
                <span class="text-[9px] md:text-sm font-bold text-gray-800 text-center leading-tight md:text-left">Bahan Bakar</span>
                <svg class="hidden md:block w-5 h-5 text-gray-400 shrink-0 md:ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>

        {{-- SEARCH & FILTER --}}
        <form method="GET" action="{{ route('hrd.dashboard') }}" class="filter-form mb-4 md:mb-6">
                    <div class="space-y-2 md:flex md:flex-row md:gap-2 md:space-y-0">
                        <div class="flex gap-2 md:contents">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama..."
                                class="flex-1 border border-gray-300 rounded-xl p-3 text-sm">
                            <button type="submit" class="bg-blue-600 text-white px-5 py-3 rounded-xl font-bold text-sm shrink-0 md:order-last">
                                Cari
                            </button>
                        </div>
                        <div class="grid grid-cols-3 gap-2 md:contents">
                            <select name="role" class="w-36 border border-gray-300 rounded-xl p-3 text-sm">
                                <option value="">Semua Role</option>
                                <option value="sales" {{ request('role') == 'sales' ? 'selected' : '' }}>Sales</option>
                                <option value="supervisor" {{ request('role') == 'supervisor' ? 'selected' : '' }}>Supervisor</option>
                                <option value="finance" {{ request('role') == 'finance' ? 'selected' : '' }}>Finance</option>
                            </select>
                            <select name="company_id" class="w-48 border border-gray-300 rounded-xl p-3 text-sm">
                                <option value="">Semua Perusahaan</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                                @endforeach
                            </select>
                            <select name="job_position_id" class="w-36 border border-gray-300 rounded-xl p-3 text-sm">
                                <option value="">Semua Jabatan</option>
                                @foreach($jobPositions as $jobPosition)
                                    <option value="{{ $jobPosition->id }}" {{ request('job_position_id') == $jobPosition->id ? 'selected' : '' }}>{{ $jobPosition->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </form>

        {{-- LIST KARYAWAN --}}
        <div class="space-y-3 md:grid md:grid-cols-2 md:gap-4 md:space-y-0 pb-8">
            <h2 class="font-bold text-lg text-gray-800 mb-3 md:col-span-2">Daftar Karyawan</h2>
            @forelse($users as $user)
                <a href="{{ route('hrd.show.user', $user->id) }}"
                    class="block bg-white rounded-2xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3 flex-1">
                            <div
                                class="w-12 h-12 bg-{{ $user->role == 'sales' ? 'blue' : ($user->role == 'finance' ? 'green' : 'purple') }}-100 rounded-full flex items-center justify-center text-{{ $user->role == 'sales' ? 'blue' : ($user->role == 'finance' ? 'green' : 'purple') }}-600 font-bold shrink-0">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                            <div class="flex-1">
                                <h3 class="font-bold text-gray-800 flex items-center gap-1.5 flex-wrap">{{ $user->name }}
                                    @if(!$user->fuel_reimbursement_enabled)
                                        <span class="text-[10px] bg-gray-200 text-gray-600 px-2 py-0.5 rounded-full font-bold">Tidak Reimburse Bensin</span>
                                    @endif
                                </h3>
                                <p class="text-xs text-gray-500">{{ $user->username }} • {{ ucfirst($user->role) }}</p>
                                @if($user->company)
                                    <p class="text-xs text-cyan-600">{{ $user->company->name }}</p>
                                @endif
                                <div class="flex items-center gap-2 flex-wrap mt-0.5">
                                    @if($user->jobPosition)
                                        <span class="text-[10px] bg-teal-100 text-teal-700 px-2 py-0.5 rounded-full font-bold">{{ $user->jobPosition->name }}</span>
                                    @endif
                                </div>
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
                <div class="text-center py-10 bg-gray-50 rounded-xl md:col-span-2">
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
