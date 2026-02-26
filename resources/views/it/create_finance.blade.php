@extends('layout')
@section('content')
    <div class="px-5 md:px-8 py-6 md:py-8">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('it.dashboard') }}" class="text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <h1 class="text-2xl font-bold">Tambah Finance</h1>
        </div>

        <form action="{{ route('it.finance.store') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Nama Lengkap *</label>
                <input type="text" name="name" required
                    class="w-full border border-gray-300 rounded-xl p-4 text-sm focus:ring-2 focus:ring-blue-500"
                    placeholder="Contoh: Budi Santoso">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Username *</label>
                <input type="text" name="username" required
                    class="w-full border border-gray-300 rounded-xl p-4 text-sm focus:ring-2 focus:ring-blue-500"
                    placeholder="Contoh: budi.santoso">
                <p class="text-xs text-gray-500 mt-1">Username harus unik dan tidak boleh ada spasi</p>
            </div>

            <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                <p class="text-xs text-green-800">
                    <strong>Password default:</strong> finance123<br>
                    Finance dapat mengganti password setelah login pertama kali.
                </p>
            </div>

            <div class="flex gap-3 mb-24">
                <a href="{{ route('it.dashboard') }}" class="flex-1 bg-gray-200 text-gray-700 py-4 rounded-xl font-bold text-sm text-center">Batal</a>
                <button type="submit" class="flex-[2] bg-green-600 text-white py-4 rounded-xl font-bold text-sm shadow-lg">
                    Buat Akun
                </button>
            </div>
        </form>
    </div>
@endsection
