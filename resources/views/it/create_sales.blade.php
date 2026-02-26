@extends('layout')
@section('content')
    <div class="px-5 md:px-8 py-6 md:py-8">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('it.dashboard') }}" class="text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <h1 class="text-2xl font-bold">Tambah Sales</h1>
        </div>

        <form action="{{ route('it.sales.store') }}" method="POST" class="space-y-6">
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
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Supervisor (Opsional)</label>
                <select name="supervisor_id" class="w-full border border-gray-300 rounded-xl p-4 text-sm">
                    <option value="">-- Pilih Supervisor --</option>
                    @foreach($supervisors as $supervisor)
                        <option value="{{ $supervisor->id }}">{{ $supervisor->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                <p class="text-xs text-blue-800">
                    <strong>Password default:</strong> sales123
                </p>
            </div>
            <div class="flex gap-3 mb-24">
                <a href="{{ route('it.dashboard') }}" class="flex-1 bg-gray-200 text-gray-700 py-4 rounded-xl font-bold text-sm text-center">Batal</a>
                <button type="submit" class="flex-[2] bg-blue-600 text-white py-4 rounded-xl font-bold text-sm shadow-lg">Buat Akun</button>
            </div>
        </form>
    </div>
@endsection

