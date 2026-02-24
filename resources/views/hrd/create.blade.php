@extends('layout')
@section('content')
    <div class="bg-white p-6 min-h-screen">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('dashboard') }}" class="text-gray-500">← Kembali</a>
            <h1 class="text-xl font-bold">Tambah Karyawan</h1>
        </div>

        <form action="{{ route('hrd.store') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Lengkap</label>
                <input type="text" name="name"
                    class="w-full border border-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-indigo-500" required>
            </div>

            <div class="mb-4">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Username Login</label>
                <input type="text" name="username"
                    class="w-full border border-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-indigo-500"
                    placeholder="Contoh: susi" required>
            </div>

            <div class="mb-6">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Password Awal</label>
                <input type="text" name="password" value="sales123"
                    class="w-full border border-gray-200 bg-gray-50 rounded-xl p-3 text-gray-500" readonly>
                <p class="text-[10px] text-gray-400 mt-1">*Password default: sales123</p>
            </div>

            <button type="submit"
                class="w-full bg-indigo-600 text-white py-3 rounded-xl font-bold shadow-lg hover:bg-indigo-700">
                Simpan Data
            </button>
        </form>
    </div>
@endsection
