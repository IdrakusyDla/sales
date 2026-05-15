@extends('layout')
@section('content')
    <div class="px-5 md:px-8 py-6 md:py-8">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ Auth::user()->isIt() ? route('it.dashboard') : route('hrd.dashboard') }}" class="text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <h1 class="text-2xl font-bold">Setting Bahan Bakar</h1>
        </div>

        {{-- INFO PRIORITAS --}}
        <div class="bg-gray-50 border border-gray-200 rounded-2xl p-4 mb-6">
            <p class="text-xs text-gray-600 text-center">
                <span class="font-bold">Prioritas:</span> Individual (per karyawan) > Role (per jabatan) > General (semua)
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- KOLOM KIRI: General + Role --}}
            <div class="space-y-6">
                {{-- SETTING GENERAL --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="font-bold text-base mb-4 flex items-center gap-2">
                        <span class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c-.94 1.543.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 002.573-1.066c.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </span>
                        Setting General
                    </h2>
                    <p class="text-xs text-gray-500 mb-4">Berlaku untuk semua karyawan jika tidak ada setting khusus</p>

                    @if($generalSetting)
                        <div class="bg-blue-50 rounded-xl p-3 mb-4 flex gap-6">
                            <div class="flex-1 text-center">
                                <p class="text-xs text-gray-600">KM/Liter</p>
                                <p class="text-lg font-bold text-blue-600">{{ number_format($generalSetting->km_per_liter, 2) }}</p>
                            </div>
                            <div class="w-px bg-blue-200"></div>
                            <div class="flex-1 text-center">
                                <p class="text-xs text-gray-600">Harga/Liter</p>
                                <p class="text-lg font-bold text-blue-600">Rp {{ number_format($generalSetting->fuel_price, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    @endif

                    <form action="{{ Auth::user()->isIt() ? route('it.fuel_settings.store.general') : route('fuel_settings.store.general') }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-2 gap-3 mb-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">KM per Liter *</label>
                                <input type="number" name="km_per_liter" step="0.01" min="0.01" required
                                    value="{{ $generalSetting->km_per_liter ?? '10' }}"
                                    class="w-full border border-gray-300 rounded-xl p-2.5 text-sm"
                                    placeholder="10">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Harga/Liter (Rp) *</label>
                                <input type="number" name="fuel_price" step="0.01" min="0" required
                                    value="{{ $generalSetting->fuel_price ?? '' }}"
                                    class="w-full border border-gray-300 rounded-xl p-2.5 text-sm"
                                    placeholder="15000">
                            </div>
                        </div>
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 transition text-white py-2.5 rounded-xl font-bold text-sm">
                            {{ $generalSetting ? 'Update' : 'Simpan' }} Setting General
                        </button>
                    </form>
                </div>

                {{-- SETTING ROLE --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="font-bold text-base mb-4 flex items-center gap-2">
                        <span class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </span>
                        Setting Per Role
                    </h2>
                    <p class="text-xs text-gray-500 mb-4">Override setting general berdasarkan jabatan</p>

                    <form action="{{ Auth::user()->isIt() ? route('it.fuel_settings.store.role') : route('fuel_settings.store.role') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="block text-xs font-bold text-gray-700 mb-1">Pilih Role *</label>
                            <select name="role" required class="w-full border border-gray-300 rounded-xl p-2.5 text-sm">
                                <option value="">-- Pilih Role --</option>
                                <option value="sales">Sales</option>
                                <option value="supervisor">Supervisor</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-3 mb-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">KM per Liter *</label>
                                <input type="number" name="km_per_liter" step="0.01" min="0.01" required
                                    class="w-full border border-gray-300 rounded-xl p-2.5 text-sm"
                                    placeholder="10">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Harga/Liter (Rp) *</label>
                                <input type="number" name="fuel_price" step="0.01" min="0" required
                                    class="w-full border border-gray-300 rounded-xl p-2.5 text-sm"
                                    placeholder="15000">
                            </div>
                        </div>
                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 transition text-white py-2.5 rounded-xl font-bold text-sm">
                            Simpan Setting Role
                        </button>
                    </form>

                    @if(isset($roleSettings) && $roleSettings->count() > 0)
                        <div class="mt-5 pt-5 border-t border-gray-100">
                            <h3 class="font-bold text-sm mb-3 text-gray-700">Setting Role Aktif</h3>
                            <div class="space-y-2">
                                @foreach($roleSettings as $rs)
                                    <div class="bg-indigo-50 rounded-xl p-3 flex justify-between items-center">
                                        <div class="flex items-center gap-3">
                                            <span class="w-7 h-7 bg-indigo-200 rounded-full flex items-center justify-center text-indigo-700 text-xs font-bold uppercase shrink-0">{{ substr($rs->role, 0, 2) }}</span>
                                            <div>
                                                <p class="font-bold text-sm text-indigo-900 capitalize">{{ $rs->role }}</p>
                                                <p class="text-xs text-indigo-600">
                                                    {{ number_format($rs->km_per_liter, 2) }} KM/L &times; Rp {{ number_format($rs->fuel_price, 0, ',', '.') }}/L
                                                </p>
                                            </div>
                                        </div>
                                        <form action="{{ Auth::user()->isIt() ? route('it.fuel_settings.deactivate', $rs->id) : route('fuel_settings.deactivate', $rs->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" onclick="return confirm('Nonaktifkan setting untuk role {{ $rs->role }}?')"
                                                class="text-red-500 hover:text-red-700 text-xs font-bold transition">Nonaktifkan</button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- KOLOM KANAN: Individual --}}
            <div class="space-y-6">
                {{-- FORM SETTING INDIVIDUAL --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="font-bold text-base mb-4 flex items-center gap-2">
                        <span class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </span>
                        Setting Individual
                    </h2>
                    <p class="text-xs text-gray-500 mb-4">Override setting per karyawan tertentu</p>

                    <form action="{{ Auth::user()->isIt() ? route('it.fuel_settings.store.individual') : route('fuel_settings.store.individual') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="block text-xs font-bold text-gray-700 mb-1">Pilih Karyawan *</label>
                            <select name="user_id" required class="w-full border border-gray-300 rounded-xl p-2.5 text-sm">
                                <option value="">-- Pilih Karyawan --</option>
                                @foreach($sales as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }} ({{ ucfirst($s->role) }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-3 mb-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">KM per Liter *</label>
                                <input type="number" name="km_per_liter" step="0.01" min="0.01" required
                                    class="w-full border border-gray-300 rounded-xl p-2.5 text-sm"
                                    placeholder="10">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Harga/Liter (Rp) *</label>
                                <input type="number" name="fuel_price" step="0.01" min="0" required
                                    class="w-full border border-gray-300 rounded-xl p-2.5 text-sm"
                                    placeholder="15000">
                            </div>
                        </div>
                        <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 transition text-white py-2.5 rounded-xl font-bold text-sm">
                            Simpan Setting Individual
                        </button>
                    </form>
                </div>

                {{-- LIST SETTING INDIVIDUAL --}}
                @if($individualSettings->count() > 0)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h2 class="font-bold text-base mb-4 flex items-center gap-2">
                            <span class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            </span>
                            Setting Individual Aktif
                        </h2>
                        <div class="space-y-2">
                            @foreach($individualSettings as $setting)
                                <div class="bg-gray-50 rounded-xl p-3 flex justify-between items-center">
                                    <div class="flex items-center gap-3">
                                        <span class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center text-purple-700 text-xs font-bold shrink-0">{{ substr($setting->user->name, 0, 1) }}</span>
                                        <div>
                                            <p class="font-bold text-sm">{{ $setting->user->name }}</p>
                                            <p class="text-xs text-gray-500">
                                                {{ number_format($setting->km_per_liter, 2) }} KM/L &times; Rp {{ number_format($setting->fuel_price, 0, ',', '.') }}/L
                                            </p>
                                        </div>
                                    </div>
                                    <form action="{{ Auth::user()->isIt() ? route('it.fuel_settings.deactivate', $setting->id) : route('fuel_settings.deactivate', $setting->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" onclick="return confirm('Nonaktifkan setting untuk {{ $setting->user->name }}?')"
                                            class="text-red-500 hover:text-red-700 text-xs font-bold transition">Nonaktifkan</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
