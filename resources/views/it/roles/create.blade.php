@extends('layout')
@section('content')
    <div class="px-5 md:px-8 py-6 md:py-8">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('it.roles.index') }}" class="text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h1 class="text-2xl font-bold">Buat Role Baru</h1>
        </div>

        @if($errors->any())
            <div class="bg-red-100 text-red-700 p-4 rounded-xl mb-6 text-sm font-bold">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('it.roles.store') }}" method="POST" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            @csrf
            
            <div class="mb-5">
                <label class="block text-sm font-bold text-gray-700 mb-2">Nama Role *</label>
                <input type="text" name="name" required placeholder="Contoh: Manager Area" value="{{ old('name') }}"
                    class="w-full border border-gray-300 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            <div class="mb-5">
                <label class="block text-sm font-bold text-gray-700 mb-2">Dashboard Redirect (Nama Route) *</label>
                <input type="text" name="dashboard_url" required placeholder="Contoh: supervisor.dashboard" value="{{ old('dashboard_url') }}"
                    class="w-full border border-gray-300 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                <p class="text-xs text-gray-500 mt-1">Saat user login, mereka akan diarahkan ke halaman rute ini secara bawaan.</p>
            </div>

            <div class="mb-8">
                <label class="block text-sm font-bold text-gray-700 mb-3">Hak Akses Menu (Permissions)</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 bg-gray-50 p-4 rounded-xl border border-gray-100">
                    @foreach($permissions as $code => $label)
                        <label class="flex items-center space-x-3 cursor-pointer p-2 hover:bg-white rounded-lg transition-colors">
                            <input type="checkbox" name="permissions[]" value="{{ $code }}" 
                                {{ (is_array(old('permissions')) && in_array($code, old('permissions'))) ? 'checked' : '' }}
                                class="w-5 h-5 text-blue-600 rounded bg-gray-100 border-gray-300">
                            <span class="text-sm text-gray-700 font-medium">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="mb-8 p-4 bg-purple-50 rounded-xl border border-purple-100">
                <label class="flex items-center space-x-3 cursor-pointer">
                    <input type="checkbox" name="hrd_can_create" value="1" 
                        {{ old('hrd_can_create') ? 'checked' : '' }}
                        class="w-5 h-5 text-purple-600 rounded bg-white border-purple-300">
                    <div>
                        <span class="text-sm font-bold text-purple-900 block">Izinkan HRD Membuat Akun Secara Dinamis</span>
                        <span class="text-xs text-purple-700 block mt-0.5">Jika dicentang, role ini akan muncul sebagai pilihan saat HRD mengakses menu Tambah Akun Baru.</span>
                    </div>
                </label>
            </div>

            <div class="pt-4 border-t border-gray-100 flex gap-3">
                <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-blue-700 shadow-sm">Simpan Role</button>
                <a href="{{ route('it.roles.index') }}" class="bg-gray-100 text-gray-700 px-6 py-3 rounded-xl font-bold hover:bg-gray-200">Batal</a>
            </div>
        </form>
    </div>
@endsection
