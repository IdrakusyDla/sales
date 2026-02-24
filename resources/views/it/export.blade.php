@extends('layout')
@section('content')
    <div class="px-5 py-6">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('it.dashboard') }}" class="text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
            </a>
            <h1 class="text-2xl font-bold">Export Laporan</h1>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <form action="{{ route('it.export.excel') }}" method="GET">
                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Jenis Laporan</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" name="report_type" value="activity" class="peer sr-only">
                            <div
                                class="text-center p-3 border rounded-xl peer-checked:bg-indigo-50 peer-checked:border-indigo-500 peer-checked:text-indigo-600 transition">
                                <span class="block text-lg">📍</span>
                                <span class="text-xs font-bold">Aktivitas</span>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="report_type" value="combined" class="peer sr-only" checked>
                            <div
                                class="text-center p-3 border rounded-xl peer-checked:bg-purple-50 peer-checked:border-purple-500 peer-checked:text-purple-600 transition">
                                <span class="block text-lg">📊</span>
                                <span class="text-xs font-bold">Reimburse</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Dari Tanggal</label>
                        <input type="date" name="start_date" value="{{ date('Y-m-01') }}" required
                            class="w-full border border-gray-300 rounded-xl p-3 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Sampai Tanggal</label>
                        <input type="date" name="end_date" value="{{ date('Y-m-d') }}" required
                            class="w-full border border-gray-300 rounded-xl p-3 text-sm">
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Filter Karyawan (Opsional)</label>
                    <select name="user_id" class="w-full border border-gray-300 rounded-xl p-3 text-sm">
                        <option value="">-- Semua Karyawan --</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} ({{ ucfirst($u->role) }})</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-4 rounded-xl font-bold shadow-lg flex justify-center items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Download Laporan
                </button>
            </form>
        </div>
    </div>
@endsection