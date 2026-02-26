@extends('layout')
@section('content')
    <div class="px-5 md:px-8 py-6 md:py-8">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('dashboard') }}" class="bg-gray-100 p-2 rounded-full text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
            </a>
            <h1 class="text-xl font-bold">Ganti Password</h1>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <form action="{{ route('sales.password.update') }}" method="POST">
                @csrf

                {{-- Error Message --}}
                @if ($errors->any())
                    <div class="bg-red-50 text-red-500 text-xs p-3 rounded-lg mb-4">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Password Lama</label>
                    <input type="password" name="old_password"
                        class="w-full border border-gray-300 rounded-xl p-3 text-sm focus:ring-blue-500" required>
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Password Baru</label>
                    <input type="password" name="password"
                        class="w-full border border-gray-300 rounded-xl p-3 text-sm focus:ring-blue-500" required>
                </div>

                <div class="mb-6">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation"
                        class="w-full border border-gray-300 rounded-xl p-3 text-sm focus:ring-blue-500" required>
                </div>

                <button type="submit"
                    class="w-full bg-blue-600 text-white py-3 rounded-xl font-bold shadow-lg hover:bg-blue-700 transition">
                    Simpan Password Baru
                </button>
            </form>
        </div>
    </div>
@endsection
