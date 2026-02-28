@extends('layout')
@section('content')
    <div class="px-5 md:px-8 py-6 md:py-8">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('dashboard') }}" class="text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
            </a>
            <h1 class="text-2xl font-bold">Profil</h1>
        </div>

        <div class="md:grid md:grid-cols-2 md:gap-8 items-start">
            {{-- Bagian Kiri: DATA USER --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6 md:mb-0 md:sticky md:top-8">
                <div class="flex items-center md:flex-col md:text-center gap-4 mb-6">
                    <div
                        class="w-20 h-20 md:w-32 md:h-32 rounded-full bg-blue-100 flex items-center justify-center md:mx-auto">
                        <svg class="w-10 h-10 md:w-16 md:h-16 text-blue-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-xl md:text-2xl font-bold text-gray-800">{{ $user->name }}</h2>
                        <p class="text-sm md:text-base text-gray-500">{{ $user->username }}</p>
                        <span class="inline-block mt-2 bg-blue-100 text-blue-700 text-xs font-bold px-3 py-1 rounded-full">
                            {{ strtoupper($user->role) }}
                        </span>
                    </div>
                </div>

                <div class="space-y-4 pt-4 border-t border-gray-100">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Username:</span>
                        <span class="text-sm font-bold text-gray-800">{{ $user->username }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Nama:</span>
                        <span class="text-sm font-bold text-gray-800">{{ $user->name }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Role:</span>
                        <span class="text-sm font-bold text-gray-800">{{ ucfirst($user->role) }}</span>
                    </div>
                    @if($user->supervisor)
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Supervisor:</span>
                            <span class="text-sm font-bold text-gray-800">{{ $user->supervisor->name }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Bagian Kanan: MENU --}}
            <div class="space-y-3 mb-24 md:mb-0">
                <h2 class="hidden md:block text-xl font-bold text-gray-800 mb-4 px-1">Pengaturan Akun</h2>
                {{-- UBAH PASSWORD --}}
                <a href="{{ route('password.edit') }}"
                    class="block bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex items-center justify-between hover:bg-gray-50 transition">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z">
                                </path>
                            </svg>
                        </div>
                        <span class="font-bold text-gray-800">Ubah Password</span>
                    </div>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>

                {{-- LOGOUT --}}
                <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                    class="block bg-white rounded-2xl shadow-sm border border-red-200 p-4 flex items-center justify-between hover:bg-red-50 transition w-full text-left cursor-pointer">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                </path>
                            </svg>
                        </div>
                        <span class="font-bold text-red-600">Logout</span>
                    </div>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
                <form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
                    @csrf
                </form>
            </div>
        </div>
    </div>
@endsection