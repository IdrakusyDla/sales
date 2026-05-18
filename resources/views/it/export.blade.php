@extends('layout')
@section('content')

{{-- ========================================== --}}
{{-- TAMPILAN MOBILE (< 768px)                  --}}
{{-- ========================================== --}}
<div class="md:hidden">
    {{-- Header --}}
    <div class="bg-blue-600 text-white p-6 rounded-b-3xl shadow-lg mb-6 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-10 rounded-full -mr-10 -mt-10 pointer-events-none"></div>
        <div class="relative z-10 flex items-center gap-3">
            <a href="{{ route('it.dashboard') }}" class="text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h1 class="text-xl font-bold">Pusat Laporan</h1>
                <p class="text-blue-100 text-xs mt-1">Unduh rekap data sistem ke Excel (.xlsx)</p>
            </div>
        </div>
    </div>

    <div class="px-5">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <form action="{{ route('it.export.excel') }}" method="GET">
                {{-- Ilustrasi --}}
                <div class="flex justify-center mb-6">
                    <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center text-blue-600 border border-blue-100">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                </div>

                {{-- 1. PILIH JENIS LAPORAN --}}
                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Jenis Laporan</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" name="report_type" value="activity" class="peer sr-only">
                            <div class="text-center p-3 border rounded-xl peer-checked:bg-blue-50 peer-checked:border-blue-500 peer-checked:text-blue-600 transition">
                                <span class="block text-lg"><svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg></span>
                                <span class="text-xs font-bold">Aktivitas</span>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="report_type" value="combined" class="peer sr-only" checked>
                            <div class="text-center p-3 border rounded-xl peer-checked:bg-purple-50 peer-checked:border-purple-500 peer-checked:text-purple-600 transition">
                                <span class="block text-lg"><svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg></span>
                                <span class="text-xs font-bold">Reimburse</span>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- 2. INPUT TANGGAL --}}
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Dari Tanggal</label>
                        <input type="date" name="start_date" value="{{ date('Y-m-01') }}" required class="w-full border border-gray-300 rounded-xl p-3 text-sm focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Sampai Tanggal</label>
                        <input type="date" name="end_date" value="{{ date('Y-m-d') }}" required class="w-full border border-gray-300 rounded-xl p-3 text-sm focus:ring-blue-500">
                    </div>
                </div>

                {{-- Filter Karyawan --}}
                <div class="mb-6">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Filter Karyawan (Opsional)</label>
                    <select name="user_id" class="w-full border border-gray-300 rounded-xl p-3 text-sm focus:ring-blue-500 bg-white">
                        <option value="">-- Download Semua --</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} ({{ ucfirst($u->role) }})</option>
                        @endforeach
                    </select>
                </div>

                {{-- Tombol Download --}}
                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white py-4 rounded-xl font-bold shadow-lg flex justify-center items-center gap-2 transition transform active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Download Laporan
                </button>
            </form>
        </div>

        <p class="mt-6 text-center text-gray-400 text-[10px] px-8 pb-8">
            File akan terunduh dalam format <b>.XLSX</b>. <br>Kompatibel dengan Microsoft Excel & Google Sheets.
        </p>
    </div>
</div>

{{-- ========================================== --}}
{{-- TAMPILAN DESKTOP (>= 768px)                --}}
{{-- ========================================== --}}
<div class="hidden md:block px-8 py-8 min-h-screen bg-slate-50/50">
    {{-- HEADER --}}
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-gray-800 tracking-tight mb-2">Pusat Laporan</h1>
        <p class="text-gray-500 font-medium">Unduh rekap data sistem ke Excel (.xlsx)</p>
    </div>

    <div class="grid grid-cols-12 gap-8 items-start">
        {{-- KOLOM KIRI: FORM UTAMA --}}
        <div class="col-span-8">
            <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
                {{-- Card Header --}}
                <div class="px-8 py-6 border-b border-gray-100 bg-gray-50/50 flex items-center gap-4">
                    <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 border border-blue-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <div>
                        <h2 class="font-extrabold text-lg text-gray-800">Form Export Laporan</h2>
                        <p class="text-sm text-gray-500">Pilih jenis laporan, rentang tanggal, dan filter karyawan</p>
                    </div>
                </div>

                {{-- Form Content --}}
                <div class="p-8">
                    <form action="{{ route('it.export.excel') }}" method="GET">
                        {{-- 1. PILIH JENIS LAPORAN --}}
                        <div class="mb-6">
                            <label class="block text-sm font-bold text-gray-700 mb-3 uppercase tracking-wider">Jenis Laporan</label>
                            <div class="grid grid-cols-2 gap-4">
                                <label class="cursor-pointer group">
                                    <input type="radio" name="report_type" value="activity" class="peer sr-only">
                                    <div class="text-center p-5 border-2 border-gray-200 rounded-2xl peer-checked:bg-blue-50 peer-checked:border-blue-500 peer-checked:text-blue-600 transition hover:border-gray-300 group-hover:shadow-sm">
                                        <div class="w-12 h-12 mx-auto mb-3 bg-blue-50 rounded-xl flex items-center justify-center">
                                            <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                        </div>
                                        <span class="text-sm font-bold">Aktivitas</span>
                                        <p class="text-xs text-gray-400 mt-1">Rekap kunjungan & absensi</p>
                                    </div>
                                </label>
                                <label class="cursor-pointer group">
                                    <input type="radio" name="report_type" value="combined" class="peer sr-only" checked>
                                    <div class="text-center p-5 border-2 border-gray-200 rounded-2xl peer-checked:bg-purple-50 peer-checked:border-purple-500 peer-checked:text-purple-600 transition hover:border-gray-300 group-hover:shadow-sm">
                                        <div class="w-12 h-12 mx-auto mb-3 bg-purple-50 rounded-xl flex items-center justify-center">
                                            <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                        </div>
                                        <span class="text-sm font-bold">Reimburse</span>
                                        <p class="text-xs text-gray-400 mt-1">Rekap pengeluaran & klaim</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- 2. INPUT TANGGAL --}}
                        <div class="grid grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wider">Dari Tanggal</label>
                                <input type="date" name="start_date" value="{{ date('Y-m-01') }}" required class="w-full border-gray-200 bg-gray-50 text-gray-800 rounded-xl p-4 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wider">Sampai Tanggal</label>
                                <input type="date" name="end_date" value="{{ date('Y-m-d') }}" required class="w-full border-gray-200 bg-gray-50 text-gray-800 rounded-xl p-4 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow">
                            </div>
                        </div>

                        {{-- 3. FILTER KARYAWAN --}}
                        <div class="mb-8">
                            <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wider">Filter Karyawan (Opsional)</label>
                            <select name="user_id" class="w-full border-gray-200 bg-gray-50 text-gray-800 rounded-xl p-4 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow">
                                <option value="">-- Download Semua --</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }} ({{ ucfirst($u->role) }})</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Tombol Download --}}
                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white py-5 rounded-2xl font-bold shadow-lg shadow-green-600/20 flex justify-center items-center gap-3 transition transform active:scale-[0.98] text-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            Download Laporan
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: INFO PANEL --}}
        <div class="col-span-4 space-y-6 sticky top-8">
            {{-- Info Format --}}
            <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-8">
                <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 mb-4">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <h3 class="font-extrabold text-gray-800 text-lg mb-3">Format File</h3>
                <p class="text-sm text-gray-500 leading-relaxed">File akan terunduh dalam format <strong class="text-gray-700">.XLSX</strong> yang kompatibel dengan:</p>
                <ul class="mt-3 space-y-2">
                    <li class="flex items-center gap-2 text-sm text-gray-600"><svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Microsoft Excel</li>
                    <li class="flex items-center gap-2 text-sm text-gray-600"><svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Google Sheets</li>
                    <li class="flex items-center gap-2 text-sm text-gray-600"><svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> LibreOffice Calc</li>
                </ul>
            </div>

            {{-- Panduan --}}
            <div class="bg-blue-50 rounded-[2rem] border border-blue-100 p-8">
                <div class="w-14 h-14 bg-blue-100 rounded-2xl flex items-center justify-center text-blue-600 mb-4">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h3 class="font-extrabold text-blue-900 text-lg mb-3">Panduan</h3>
                <ul class="space-y-2 text-sm text-blue-700">
                    <li class="flex items-start gap-2"><span class="font-bold">1.</span> Pilih jenis laporan yang diinginkan</li>
                    <li class="flex items-start gap-2"><span class="font-bold">2.</span> Tentukan rentang tanggal</li>
                    <li class="flex items-start gap-2"><span class="font-bold">3.</span> Opsional: filter per karyawan</li>
                    <li class="flex items-start gap-2"><span class="font-bold">4.</span> Klik Download Laporan</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@endsection
