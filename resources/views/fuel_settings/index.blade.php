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

        {{-- SETTING GENERAL --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <h2 class="font-bold text-lg mb-4"><svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c-.94 1.543.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 002.573-1.066c.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg> Setting General (Untuk Semua Karyawan)</h2>
            
            @if($generalSetting)
                <div class="bg-blue-50 rounded-xl p-4 mb-4">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-bold text-gray-700">KM per Liter:</span>
                        <span class="text-sm font-bold text-blue-600">{{ number_format($generalSetting->km_per_liter, 2) }} KM/L</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-bold text-gray-700">Harga per Liter:</span>
                        <span class="text-sm font-bold text-blue-600">Rp {{ number_format($generalSetting->fuel_price, 0, ',', '.') }}</span>
                    </div>
                </div>
            @endif

            <form action="{{ Auth::user()->isIt() ? route('it.fuel_settings.store.general') : route('fuel_settings.store.general') }}" method="POST">
                @csrf
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-2">KM per Liter *</label>
                        <input type="number" name="km_per_liter" step="0.01" min="0.01" required
                            value="{{ $generalSetting->km_per_liter ?? '10' }}"
                            class="w-full border border-gray-300 rounded-xl p-3 text-sm"
                            placeholder="10">
                        <p class="text-xs text-gray-500 mt-1">Contoh: 10 = 1 liter untuk 10 KM</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-2">Harga per Liter (Rp) *</label>
                        <input type="number" name="fuel_price" step="0.01" min="0" required
                            value="{{ $generalSetting->fuel_price ?? '' }}"
                            class="w-full border border-gray-300 rounded-xl p-3 text-sm"
                            placeholder="15000">
                    </div>
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-xl font-bold text-sm">
                    {{ $generalSetting ? 'Update' : 'Simpan' }} Setting General
                </button>
            </form>
        </div>

        {{-- SETTING INDIVIDUAL --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <h2 class="font-bold text-lg mb-4"><svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg> Setting Individual (Per Karyawan)</h2>
            
            <form action="{{ Auth::user()->isIt() ? route('it.fuel_settings.store.individual') : route('fuel_settings.store.individual') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-700 mb-2">Pilih Sales *</label>
                    <select name="user_id" required class="w-full border border-gray-300 rounded-xl p-3 text-sm">
                        <option value="">-- Pilih Sales --</option>
                        @foreach($sales as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Setting individual akan override setting general</p>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-2">KM per Liter *</label>
                        <input type="number" name="km_per_liter" step="0.01" min="0.01" required
                            class="w-full border border-gray-300 rounded-xl p-3 text-sm"
                            placeholder="10">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-2">Harga per Liter (Rp) *</label>
                        <input type="number" name="fuel_price" step="0.01" min="0" required
                            class="w-full border border-gray-300 rounded-xl p-3 text-sm"
                            placeholder="15000">
                    </div>
                </div>
                <button type="submit" class="w-full bg-purple-600 text-white py-3 rounded-xl font-bold text-sm">
                    Simpan Setting Individual
                </button>
            </form>
        </div>

        {{-- LIST SETTING INDIVIDUAL YANG SUDAH ADA --}}
        @if($individualSettings->count() > 0)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="font-bold text-lg mb-4"><svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg> Setting Individual Aktif</h2>
                <div class="space-y-3">
                    @foreach($individualSettings as $setting)
                        <div class="bg-gray-50 rounded-xl p-4 flex justify-between items-center">
                            <div class="flex-1">
                                <p class="font-bold text-sm">{{ $setting->user->name }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ number_format($setting->km_per_liter, 2) }} KM/L × Rp {{ number_format($setting->fuel_price, 0, ',', '.') }}/L
                                </p>
                            </div>
                            <form action="{{ Auth::user()->isIt() ? route('it.fuel_settings.deactivate', $setting->id) : route('fuel_settings.deactivate', $setting->id) }}" method="POST">
                                @csrf
                                <button type="submit" onclick="return confirm('Nonaktifkan setting untuk {{ $setting->user->name }}?')"
                                    class="text-red-600 text-xs font-bold">Nonaktifkan</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection

