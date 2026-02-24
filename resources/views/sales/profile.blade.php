@extends('layout')
@section('content')
    {{-- Header Profil --}}
    <div class="bg-white p-6 mb-2 border-b border-gray-100 flex flex-col items-center">
        <div
            class="w-24 h-24 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-3xl font-bold mb-3 border-4 border-white shadow-lg">
            {{ substr(Auth::user()->name, 0, 1) }}
        </div>
        <h1 class="text-xl font-bold text-gray-800">{{ Auth::user()->name }}</h1>
        <p class="text-sm text-gray-500">@ {{ Auth::user()->username }}</p>
        <span class="mt-2 bg-blue-50 text-blue-600 text-xs px-3 py-1 rounded-full font-bold uppercase tracking-wide">Sales
            Team</span>
    </div>

    <div class="px-5 py-4 space-y-3">

        {{-- Menu: Ganti Password --}}
        <a href="{{ route('password.edit') }}"
            class="flex items-center justify-between bg-white p-4 rounded-2xl border border-gray-100 shadow-sm active:scale-95 transition">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-orange-50 text-orange-500 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                        </path>
                    </svg>
                </div>
                <span class="font-bold text-gray-700 text-sm">Ganti Password</span>
            </div>
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </a>

        {{-- Menu: Logout --}}
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="w-full flex items-center justify-between bg-white p-4 rounded-2xl border border-red-100 shadow-sm active:scale-95 transition group">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-red-50 text-red-500 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                            </path>
                        </svg>
                    </div>
                    <span class="font-bold text-red-600 text-sm group-hover:text-red-700">Logout Aplikasi</span>
                </div>
            </button>
        </form>

        <div class="text-center mt-8">
            <p class="text-[10px] text-gray-400">Versi Aplikasi v1.0.0</p>
        </div>

    </div>
@endsection
