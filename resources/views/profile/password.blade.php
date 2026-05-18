@extends('layout')
@section('content')

{{-- ========================================== --}}
{{-- TAMPILAN MOBILE (< 768px)                  --}}
{{-- ========================================== --}}
<div class="md:hidden px-5 py-6">
    @if(!isset($forceChange) || !$forceChange)
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('profile.show') }}" class="text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h1 class="text-2xl font-bold">Ganti Password</h1>
        </div>
    @else
        <div class="mb-4">
            <h1 class="text-2xl font-bold">Ganti Password Default</h1>
        </div>
        @if(session('warning'))
            <div class="bg-amber-50 border border-amber-200 text-amber-800 p-4 rounded-2xl mb-6">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <p class="text-sm font-medium">{{ session('warning') }}</p>
                </div>
            </div>
        @endif
    @endif

    <form action="{{ route('password.update.custom') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">Password Lama *</label>
            <input type="password" name="old_password" required class="w-full border border-gray-300 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500" placeholder="Masukkan password lama">
            @error('old_password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">Password Baru *</label>
            <input type="password" name="password" required minlength="6" class="w-full border border-gray-300 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500" placeholder="Minimal 6 karakter">
            @error('password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">Konfirmasi Password Baru *</label>
            <input type="password" name="password_confirmation" required minlength="6" class="w-full border border-gray-300 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500" placeholder="Ulangi password baru">
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-xl p-3">
            <p class="text-xs text-blue-800"><strong>Tips:</strong> Gunakan password yang kuat dan mudah diingat. Jangan bagikan password Anda kepada siapapun.</p>
        </div>

        <div class="flex gap-3 mb-20">
            @if(!isset($forceChange) || !$forceChange)
                <a href="{{ route('profile.show') }}" class="flex-1 bg-gray-200 text-gray-700 py-4 rounded-xl font-bold text-sm text-center">Batal</a>
                <button type="submit" class="flex-[2] bg-blue-600 text-white py-4 rounded-xl font-bold text-sm shadow-lg">Ganti Password</button>
            @else
                <button type="submit" class="w-full bg-blue-600 text-white py-4 rounded-xl font-bold text-sm shadow-lg">Ganti Password</button>
            @endif
        </div>
    </form>
</div>

{{-- ========================================== --}}
{{-- TAMPILAN DESKTOP (>= 768px)                --}}
{{-- ========================================== --}}
<div class="hidden md:flex items-center justify-center min-h-screen bg-slate-50/50 px-8 py-12">
    <div class="w-full max-w-lg">
        {{-- CARD UTAMA --}}
        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">

            {{-- HEADER --}}
            <div class="px-10 pt-10 pb-6 text-center">
                <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-5">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                </div>
                <h1 class="text-2xl font-extrabold text-gray-800 tracking-tight">
                    @if(!isset($forceChange) || !$forceChange) Ganti Password
                    @else Ganti Password Default
                    @endif
                </h1>
                @if(!isset($forceChange) || !$forceChange)
                    <p class="text-sm text-gray-500 mt-2">Perbarui password Anda untuk keamanan akun</p>
                @endif
            </div>

            {{-- WARNING NOTIF (force change) --}}
            @if(isset($forceChange) && $forceChange && session('warning'))
                <div class="mx-8 mb-6 bg-amber-50 border border-amber-200 text-amber-800 p-4 rounded-2xl">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <p class="text-sm font-medium">{{ session('warning') }}</p>
                    </div>
                </div>
            @endif

            {{-- FORM --}}
            <form action="{{ route('password.update.custom') }}" method="POST">
                @csrf
                <div class="px-10 pb-10 space-y-5">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Password Lama *</label>
                        <input type="password" name="old_password" required class="w-full border-gray-200 bg-gray-50 text-gray-800 rounded-xl p-4 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow" placeholder="Masukkan password lama">
                        @error('old_password') <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Password Baru *</label>
                        <input type="password" name="password" required minlength="6" class="w-full border-gray-200 bg-gray-50 text-gray-800 rounded-xl p-4 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow" placeholder="Minimal 6 karakter">
                        @error('password') <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Konfirmasi Password Baru *</label>
                        <input type="password" name="password_confirmation" required minlength="6" class="w-full border-gray-200 bg-gray-50 text-gray-800 rounded-xl p-4 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow" placeholder="Ulangi password baru">
                    </div>

                    {{-- TIPS --}}
                    <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">
                        <div class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-blue-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <p class="text-xs text-blue-800">Gunakan password yang kuat dan mudah diingat. Jangan bagikan password Anda kepada siapapun.</p>
                        </div>
                    </div>

                    {{-- BUTTON --}}
                    <div class="pt-2">
                        @if(!isset($forceChange) || !$forceChange)
                            <div class="flex gap-3">
                                <a href="{{ route('profile.show') }}" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-4 rounded-xl font-bold text-sm text-center transition">Batal</a>
                                <button type="submit" class="flex-[2] bg-blue-600 hover:bg-blue-700 text-white py-4 rounded-xl font-bold text-sm shadow-lg shadow-blue-600/20 transition active:scale-[0.98]">Ganti Password</button>
                            </div>
                        @else
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-4 rounded-xl font-bold text-sm shadow-lg shadow-blue-600/20 transition active:scale-[0.98]">Ganti Password</button>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
