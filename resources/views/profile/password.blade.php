@extends('layout')
@section('content')
    <div class="px-5 md:px-8 py-6 md:py-8">
        @if(!isset($forceChange) || !$forceChange)
            <div class="flex items-center gap-3 mb-6">
                <a href="{{ route('profile.show') }}" class="text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <h1 class="text-2xl font-bold">Ganti Password</h1>
            </div>
        @else
            <div class="mb-6">
                <h1 class="text-2xl font-bold">Ganti Password Default</h1>
                <p class="text-sm text-gray-600 mt-1">Anda harus mengganti password default sebelum melanjutkan</p>
            </div>
        @endif

        @if(session('warning'))
            <div class="bg-yellow-50 border-2 border-yellow-200 text-yellow-800 p-4 rounded-xl mb-6">
                <p class="text-sm font-bold"><svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg> {{ session('warning') }}</p>
            </div>
        @endif

        <form action="{{ route('password.update.custom') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Password Lama *</label>
                <input type="password" name="old_password" required
                    class="w-full border border-gray-300 rounded-xl p-4 text-sm focus:ring-2 focus:ring-blue-500"
                    placeholder="Masukkan password lama">
                @error('old_password')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Password Baru *</label>
                <input type="password" name="password" required minlength="6"
                    class="w-full border border-gray-300 rounded-xl p-4 text-sm focus:ring-2 focus:ring-blue-500"
                    placeholder="Minimal 6 karakter">
                @error('password')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Konfirmasi Password Baru *</label>
                <input type="password" name="password_confirmation" required minlength="6"
                    class="w-full border border-gray-300 rounded-xl p-4 text-sm focus:ring-2 focus:ring-blue-500"
                    placeholder="Ulangi password baru">
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                <p class="text-xs text-blue-800">
                    <strong>Tips:</strong> Gunakan password yang kuat dan mudah diingat. Jangan bagikan password Anda kepada siapapun.
                </p>
            </div>

            <div class="flex gap-3 mb-24">
                @if(!isset($forceChange) || !$forceChange)
                    <a href="{{ route('profile.show') }}" class="flex-1 bg-gray-200 text-gray-700 py-4 rounded-xl font-bold text-sm text-center">Batal</a>
                    <button type="submit" class="flex-[2] bg-blue-600 text-white py-4 rounded-xl font-bold text-sm shadow-lg">
                        Ganti Password
                    </button>
                @else
                    <button type="submit" class="w-full bg-blue-600 text-white py-4 rounded-xl font-bold text-sm shadow-lg">
                        Ganti Password
                    </button>
                @endif
            </div>
        </form>
    </div>
@endsection

