@extends('layout')
@section('content')
    <div class="px-5 md:px-8 py-6 md:py-8">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('it.dashboard') }}" class="text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h1 class="text-2xl font-bold">Buat Akun Baru</h1>
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

        <form action="{{ route('it.users.store') }}" method="POST" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            @csrf
            
            <div class="mb-5">
                <label class="block text-sm font-bold text-gray-700 mb-2">Pilih Role / Jabatan *</label>
                <select name="role" id="role-select" required onchange="toggleSupervisorField()"
                    class="w-full border border-gray-300 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">-- Pilih Role --</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->slug }}" {{ old('role') == $role->slug ? 'selected' : '' }}>{{ $role->name }}</option>
                    @endforeach
                </select>
                <p class="text-[10px] text-gray-500 mt-1">Role menentukan hak akses dan dashboard awal user.</p>
            </div>

            <div class="mb-5">
                <label class="block text-sm font-bold text-gray-700 mb-2">Nama Lengkap *</label>
                <input type="text" name="name" required placeholder="Contoh: Budi Santoso" value="{{ old('name') }}"
                    class="w-full border border-gray-300 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            <div class="mb-5">
                <label class="block text-sm font-bold text-gray-700 mb-2">Username *</label>
                <input type="text" name="username" required placeholder="Contoh: budi123" value="{{ old('username') }}"
                    class="w-full border border-gray-300 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            <div class="mb-5">
                <label class="block text-sm font-bold text-gray-700 mb-2">Password (Opsional)</label>
                <input type="text" name="password" placeholder="Kosongkan untuk bawaan sistem" value="{{ old('password') }}"
                    class="w-full border border-gray-300 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                <p class="text-[10px] text-gray-500 mt-1">Jika dikosongkan, password otomatis digenerate seperti [namarole]123</p>
            </div>

            {{-- Kolom Atasan / Supervisor, hanya muncul jika role == sales --}}
            <div id="supervisor-field" class="mb-8" style="display: none;">
                <label class="block text-sm font-bold text-gray-700 mb-2">Pilih Atasan/Supervisor (Opsional)</label>
                <select name="supervisor_id"
                    class="w-full border border-gray-300 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">-- Tidak ada atasan --</option>
                    @foreach($supervisors as $spv)
                        <option value="{{ $spv->id }}" {{ old('supervisor_id') == $spv->id ? 'selected' : '' }}>{{ $spv->name }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-xl font-bold hover:bg-blue-700 shadow-sm transition">
                Simpan Akun
            </button>
        </form>
    </div>

@endsection

@section('scripts')
<script>
    function toggleSupervisorField() {
        var roleSelect = document.getElementById('role-select');
        var spvField = document.getElementById('supervisor-field');
        if (roleSelect && roleSelect.value === 'sales') {
            spvField.style.display = 'block';
        } else {
            if(spvField) spvField.style.display = 'none';
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        toggleSupervisorField();
    });
</script>
@endsection
