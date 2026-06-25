@extends('layout')
@section('content')
    <div class="px-5 md:px-8 py-6 md:py-8">
        <h1 class="text-2xl font-bold mb-1">Dashboard IT (Superadmin)</h1>
        <p class="text-sm text-gray-600 mb-4 md:mb-6">Manajemen semua karyawan</p>

        {{-- STATISTIK --}}
        <div class="grid grid-cols-5 gap-1.5 md:gap-3 mb-3 md:mb-6">
            <div class="bg-blue-50 rounded-xl p-2 md:p-4 text-center md:text-left">
                <p class="text-[9px] md:text-xs text-gray-600 mb-0.5 md:mb-1">Sales</p>
                <p class="text-base md:text-2xl font-bold text-blue-600">{{ $users->where('role', 'sales')->count() }}</p>
            </div>
            <div class="bg-purple-50 rounded-xl p-2 md:p-4 text-center md:text-left">
                <p class="text-[9px] md:text-xs text-gray-600 mb-0.5 md:mb-1">SPV</p>
                <p class="text-base md:text-2xl font-bold text-purple-600">{{ $users->where('role', 'supervisor')->count() }}</p>
            </div>
            <div class="bg-indigo-50 rounded-xl p-2 md:p-4 text-center md:text-left">
                <p class="text-[9px] md:text-xs text-gray-600 mb-0.5 md:mb-1">HRD</p>
                <p class="text-base md:text-2xl font-bold text-indigo-600">{{ $users->where('role', 'hrd')->count() }}</p>
            </div>
            <div class="bg-green-50 rounded-xl p-2 md:p-4 text-center md:text-left">
                <p class="text-[9px] md:text-xs text-gray-600 mb-0.5 md:mb-1">Finance</p>
                <p class="text-base md:text-2xl font-bold text-green-600">{{ $users->where('role', 'finance')->count() }}</p>
            </div>
            <div class="bg-red-50 rounded-xl p-2 md:p-4 text-center md:text-left">
                <p class="text-[9px] md:text-xs text-gray-600 mb-0.5 md:mb-1">IT</p>
                <p class="text-base md:text-2xl font-bold text-red-600">{{ $users->where('role', 'it')->count() }}</p>
            </div>
        </div>

        {{-- TOMBOL AKSI CEPAT --}}
        <div class="grid grid-cols-3 gap-1.5 mb-3 md:grid-cols-3 md:gap-3 md:mb-6">
            <a href="{{ route('it.users.create') }}"
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
            <a href="{{ route('it.fuel_settings.index') }}"
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
            <a href="{{ route('it.settings') }}"
                class="flex flex-col items-center gap-1 p-2 md:flex-row md:gap-3 md:p-4 rounded-xl bg-white border border-gray-200 shadow-sm hover:bg-gray-50">
                <div class="w-8 h-8 md:w-10 md:h-10 bg-gray-100 rounded-full flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 md:w-6 md:h-6 text-gray-600" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                        <g id="SVGRepo_iconCarrier">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M12 8.25C9.92894 8.25 8.25 9.92893 8.25 12C8.25 14.0711 9.92894 15.75 12 15.75C14.0711 15.75 15.75 14.0711 15.75 12C15.75 9.92893 14.0711 8.25 12 8.25ZM9.75 12C9.75 10.7574 10.7574 9.75 12 9.75C13.2426 9.75 14.25 10.7574 14.25 12C14.25 13.2426 13.2426 14.25 12 14.25C10.7574 14.25 9.75 13.2426 9.75 12Z"
                                fill="currentColor"></path>
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M11.9747 1.25C11.5303 1.24999 11.1592 1.24999 10.8546 1.27077C10.5375 1.29241 10.238 1.33905 9.94761 1.45933C9.27379 1.73844 8.73843 2.27379 8.45932 2.94762C8.31402 3.29842 8.27467 3.66812 8.25964 4.06996C8.24756 4.39299 8.08454 4.66251 7.84395 4.80141C7.60337 4.94031 7.28845 4.94673 7.00266 4.79568C6.64714 4.60777 6.30729 4.45699 5.93083 4.40743C5.20773 4.31223 4.47642 4.50819 3.89779 4.95219C3.64843 5.14353 3.45827 5.3796 3.28099 5.6434C3.11068 5.89681 2.92517 6.21815 2.70294 6.60307L2.67769 6.64681C2.45545 7.03172 2.26993 7.35304 2.13562 7.62723C1.99581 7.91267 1.88644 8.19539 1.84541 8.50701C1.75021 9.23012 1.94617 9.96142 2.39016 10.5401C2.62128 10.8412 2.92173 11.0602 3.26217 11.2741C3.53595 11.4461 3.68788 11.7221 3.68786 12C3.68785 12.2778 3.53592 12.5538 3.26217 12.7258C2.92169 12.9397 2.62121 13.1587 2.39007 13.4599C1.94607 14.0385 1.75012 14.7698 1.84531 15.4929C1.88634 15.8045 1.99571 16.0873 2.13552 16.3727C2.26983 16.6469 2.45535 16.9682 2.67758 17.3531L2.70284 17.3969C2.92507 17.7818 3.11058 18.1031 3.28089 18.3565C3.45817 18.6203 3.64833 18.8564 3.89769 19.0477C4.47632 19.4917 5.20763 19.6877 5.93073 19.5925C6.30717 19.5429 6.647 19.3922 7.0025 19.2043C7.28833 19.0532 7.60329 19.0596 7.8439 19.1986C8.08452 19.3375 8.24756 19.607 8.25964 19.9301C8.27467 20.3319 8.31403 20.7016 8.45932 21.0524C8.73843 21.7262 9.27379 22.2616 9.94761 22.5407C10.238 22.661 10.5375 22.7076 10.8546 22.7292C11.1592 22.75 11.5303 22.75 11.9747 22.75H12.0252C12.4697 22.75 12.8407 22.75 13.1454 22.7292C13.4625 22.7076 13.762 22.661 14.0524 22.5407C14.7262 22.2616 15.2616 21.7262 15.5407 21.0524C15.686 20.7016 15.7253 20.3319 15.7403 19.93C15.7524 19.607 15.9154 19.3375 16.156 19.1985C16.3966 19.0596 16.7116 19.0532 16.9974 19.2042C17.3529 19.3921 17.6927 19.5429 18.0692 19.5924C18.7923 19.6876 19.5236 19.4917 20.1022 19.0477C20.3516 18.8563 20.5417 18.6203 20.719 18.3565C20.8893 18.1031 21.0748 17.7818 21.297 17.3969L21.3223 17.3531C21.5445 16.9682 21.73 16.6469 21.8643 16.3727C22.0041 16.0873 22.1135 15.8045 22.1545 15.4929C22.2497 14.7698 22.0538 14.0385 21.6098 13.4599C21.3787 13.1587 21.0782 12.9397 20.7377 12.7258C20.4639 12.5538 20.312 12.2778 20.312 12C20.312 11.7221 20.4639 11.4461 20.7377 11.2741C21.0782 11.0602 21.3787 10.8412 21.6098 10.5401C22.0538 9.96142 22.2497 9.23012 22.1545 8.50701C22.1135 8.19539 22.0041 7.91267 21.8643 7.62723C21.73 7.35304 21.5445 7.03172 21.3223 6.64681L21.297 6.60307C21.0748 6.21815 20.8893 5.89681 20.719 5.6434C20.5417 5.3796 20.3516 5.14353 20.1022 4.95219C19.5236 4.50819 18.7923 4.31223 18.0692 4.40743C17.6927 4.45699 17.3529 4.60777 16.9974 4.79568C16.7116 4.94673 16.3966 4.94031 16.156 4.80141C15.9154 4.66251 15.7524 4.39299 15.7403 4.06996C15.7253 3.66812 15.686 3.29842 15.5407 2.94762C15.2616 2.27379 14.7262 1.73844 14.0524 1.45933C13.762 1.33905 13.4625 1.29241 13.1454 1.27077C12.8407 1.24999 12.4697 1.24999 12.0252 1.25H11.9747ZM10.5005 2.76502C10.7313 2.75069 11.0242 2.75 11.5 2.75H12.5C12.9758 2.75 13.2687 2.75069 13.4995 2.76502C13.7228 2.77881 13.8333 2.80217 13.9064 2.83247C14.1848 2.94782 14.4058 3.16877 14.5212 3.44711C14.5598 3.54023 14.5861 3.66272 14.6004 3.90293C14.6175 4.19042 14.6843 4.4685 14.7934 4.72519C15.0077 5.2278 15.4055 5.62559 15.9081 5.83987C16.3001 6.00596 16.7254 6.00145 17.0772 5.80371C17.2954 5.68153 17.5172 5.58375 17.7567 5.52606C17.9898 5.46995 18.2054 5.57341 18.3438 5.75168C18.4501 5.88903 18.5494 6.03524 18.688 6.27535C18.8266 6.51545 18.9054 6.6698 18.9625 6.81542C19.049 7.03614 18.9999 7.29015 18.8118 7.44909C18.5569 7.66445 18.3423 7.92704 18.1817 8.22543C18.0555 8.45976 18.0001 8.72828 18 9.00142C17.9999 9.27435 18.0551 9.54249 18.1812 9.77657C18.3418 10.0751 18.5566 10.3377 18.8117 10.5531C18.9998 10.712 19.0489 10.966 18.9624 11.1868C18.9053 11.3324 18.8265 11.4868 18.6879 11.7269C18.5493 11.967 18.45 12.1132 18.3437 12.2506C18.2053 12.4289 17.9898 12.5324 17.7568 12.4762C17.5172 12.4185 17.2954 12.3208 17.0772 12.1986C16.7254 12.0008 16.3001 11.9963 15.9081 12.1624C15.4055 12.3767 15.0077 12.7745 14.7934 13.2771C14.6843 13.5338 14.6175 13.8119 14.6004 14.0993C14.5861 14.3396 14.5598 14.462 14.5212 14.5552C14.4058 14.8335 14.1848 15.0545 13.9064 15.1698C13.8333 15.2001 13.7228 15.2235 13.4995 15.2373C13.2687 15.2516 12.9758 15.2513 12.5 15.2513H11.5C11.0242 15.2513 10.7313 15.2516 10.5005 15.2373C10.2772 15.2235 10.1667 15.2001 10.0936 15.1698C9.81524 15.0545 9.59429 14.8335 9.47894 14.5552C9.44036 14.462 9.414 14.3396 9.39967 14.0993C9.38264 13.8119 9.31583 13.5338 9.2067 13.2771C8.99242 12.7745 8.59463 12.3767 8.09202 12.1624C7.7 11.9963 7.27472 12.0008 6.92287 12.1986C6.70471 12.3208 6.48293 12.4185 6.24336 12.4762C6.01029 12.5324 5.79469 12.4289 5.65633 12.2506C5.55 12.1132 5.4507 11.967 5.31211 11.7269C5.17353 11.4868 5.09469 11.3324 5.03761 11.1868C4.95111 10.966 5.00024 10.712 5.18836 10.5531C5.44341 10.3377 5.65823 10.0751 5.81878 9.77657C5.94488 9.54249 6.00012 9.27435 6 9.00142C5.99988 8.72828 5.94453 8.45976 5.81833 8.22543C5.65776 7.92704 5.44314 7.66445 5.18836 7.44909C5.00024 7.29015 4.95111 7.03614 5.03761 6.81542C5.09469 6.6698 5.17353 6.51545 5.31211 6.27535C5.4507 6.03524 5.55 5.88903 5.65633 5.75168C5.79469 5.57341 6.01029 5.46995 6.24336 5.52606C6.48293 5.58375 6.70471 5.68153 6.92287 5.80371C7.27472 6.00145 7.7 6.00596 8.09202 5.83987C8.59463 5.62559 8.99242 5.2278 9.2067 4.72519C9.31583 4.4685 9.38264 4.19042 9.39967 3.90293C9.414 3.66272 9.44036 3.54023 9.47894 3.44711C9.59429 3.16877 9.81524 2.94782 10.0936 2.83247C10.1667 2.80217 10.2772 2.77881 10.5005 2.76502Z"
                                fill="currentColor"></path>
                        </g>
                    </svg>
                </div>
                <span class="text-[9px] md:text-sm font-bold text-gray-800 text-center leading-tight md:text-left">Pengaturan</span>
                <svg class="hidden md:block w-5 h-5 text-gray-400 shrink-0 md:ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>

        {{-- SEARCH & FILTER --}}
        <form method="GET" action="{{ route('it.dashboard') }}" class="mb-4 md:mb-6">
            <div class="space-y-2 md:flex md:flex-row md:gap-2 md:space-y-0">
                <div class="flex gap-2 md:contents">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama..."
                        class="flex-1 border border-gray-300 rounded-xl p-3 text-sm">
                    <button type="submit" class="bg-indigo-600 text-white px-5 py-3 rounded-xl font-bold text-sm shrink-0 md:order-last">
                        Cari
                    </button>
                </div>
                <div class="grid grid-cols-3 gap-2 md:contents">
                    <select name="role" class="border border-gray-300 rounded-xl p-3 text-sm">
                        <option value="">Semua</option>
                        <option value="sales" {{ request('role') == 'sales' ? 'selected' : '' }}>Sales</option>
                        <option value="supervisor" {{ request('role') == 'supervisor' ? 'selected' : '' }}>Supervisor</option>
                        <option value="hrd" {{ request('role') == 'hrd' ? 'selected' : '' }}>HRD</option>
                        <option value="finance" {{ request('role') == 'finance' ? 'selected' : '' }}>Finance</option>
                        <option value="it" {{ request('role') == 'it' ? 'selected' : '' }}>IT</option>
                    </select>
                    <select name="company_id" class="border border-gray-300 rounded-xl p-3 text-sm">
                        <option value="">Semua Perusahaan</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                        @endforeach
                    </select>
                    <select name="job_position_id" class="border border-gray-300 rounded-xl p-3 text-sm">
                        <option value="">Semua Jabatan</option>
                        @foreach($jobPositions as $jobPosition)
                            <option value="{{ $jobPosition->id }}" {{ request('job_position_id') == $jobPosition->id ? 'selected' : '' }}>{{ $jobPosition->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>

        {{-- LIST KARYAWAN --}}
        <div class="space-y-3 md:grid md:grid-cols-2 md:gap-4 md:space-y-0">
            <h2 class="font-bold text-lg text-gray-800 mb-3 md:col-span-2">Daftar Karyawan</h2>
            @forelse($users as $user)
                <a href="{{ route('it.show.user', $user->id) }}"
                    class="block bg-white rounded-2xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3 flex-1">
                            <div
                                class="w-12 h-12 bg-{{ $user->role == 'sales' ? 'blue' : ($user->role == 'supervisor' ? 'purple' : ($user->role == 'hrd' ? 'indigo' : ($user->role == 'finance' ? 'green' : 'red'))) }}-100 rounded-full flex items-center justify-center text-{{ $user->role == 'sales' ? 'blue' : ($user->role == 'supervisor' ? 'purple' : ($user->role == 'hrd' ? 'indigo' : ($user->role == 'finance' ? 'green' : 'red'))) }}-600 font-bold shrink-0">
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
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </a>
            @empty
                <div class="text-center py-10 bg-gray-50 rounded-xl">
                    <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                        </path>
                    </svg>
                    <p class="text-sm text-gray-500">Tidak ada karyawan</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection
