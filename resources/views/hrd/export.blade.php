@extends('layout')
@section('content')
    {{-- Header --}}
    <div
        class="bg-indigo-700 text-white p-6 md:px-8 md:py-10 md:max-w-3xl md:mx-auto md:text-center rounded-b-3xl md:rounded-3xl shadow-lg mb-6 relative overflow-hidden md:mt-6">
        <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-10 rounded-full -mr-10 -mt-10 pointer-events-none">
        </div>
        <div class="relative z-10">
            <h1 class="text-xl md:text-3xl font-bold">Pusat Laporan</h1>
            <p class="text-indigo-200 text-xs md:text-sm mt-1">Unduh rekap absensi ke Excel (.xlsx)</p>
        </div>
    </div>

    <div class="px-5 md:max-w-3xl md:mx-auto">

        <div class="bg-white p-6 md:p-8 rounded-2xl md:rounded-[2rem] shadow-sm border border-gray-100">
            <form action="{{ route('hrd.export.excel') }}" method="GET">

                {{-- Ilustrasi --}}
                <div class="flex justify-center mb-6">
                    <div
                        class="w-20 h-20 bg-green-50 rounded-full flex items-center justify-center text-green-600 border border-green-100">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                    </div>
                </div>

                {{-- 1. PILIH JENIS LAPORAN (BARU) --}}
                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Jenis Laporan</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" name="report_type" value="activity" class="peer sr-only">
                            <div
                                class="text-center p-3 border rounded-xl peer-checked:bg-blue-50 peer-checked:border-blue-500 peer-checked:text-blue-600 transition">
                                <span class="block text-lg"><svg class="w-5 h-5 inline" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                        </path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg></span>
                                <span class="text-xs font-bold">Aktivitas</span>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="report_type" value="combined" class="peer sr-only" checked>
                            <div
                                class="text-center p-3 border rounded-xl peer-checked:bg-purple-50 peer-checked:border-purple-500 peer-checked:text-purple-600 transition">
                                <span class="block text-lg"><svg class="w-5 h-5 inline" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                        </path>
                                    </svg></span>
                                <span class="text-xs font-bold">Reimburse</span>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- 2. INPUT TANGGAL (Kode Lama, Geser ke Bawah sedikit) --}}
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Dari Tanggal</label>
                        <input type="date" name="start_date" value="{{ date('Y-m-01') }}"
                            class="w-full border border-gray-300 rounded-xl p-3 text-sm focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Sampai Tanggal</label>
                        <input type="date" name="end_date" value="{{ date('Y-m-d') }}"
                            class="w-full border border-gray-300 rounded-xl p-3 text-sm focus:ring-indigo-500">
                    </div>
                </div>

                {{-- Filter Sales --}}
                <div class="mb-6">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Filter Sales (Opsional)</label>
                    <select name="user_id"
                        class="w-full border border-gray-300 rounded-xl p-3 text-sm focus:ring-indigo-500 bg-white">
                        <option value="">-- Download Semua Sales --</option>
                        @foreach ($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Tombol Download --}}
                <button type="submit"
                    class="w-full bg-green-600 hover:bg-green-700 text-white py-4 rounded-xl font-bold shadow-lg flex justify-center items-center gap-2 transition transform active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Download Laporan
                </button>
            </form>
        </div>

        <p class="mt-6 text-center text-gray-400 text-[10px] px-8">
            File akan terunduh dalam format <b>.XLSX</b>. <br>Kompatibel dengan Microsoft Excel & Google Sheets.
        </p>

    </div>
@endsection