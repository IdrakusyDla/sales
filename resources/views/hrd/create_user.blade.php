@extends('layout')
@section('content')
    <div class="px-5 md:px-8 py-6 md:py-8">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('hrd.dashboard') }}" class="text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <h1 class="text-2xl font-bold">Tambah Akun Baru</h1>
        </div>

        <form action="{{ route('hrd.users.store') }}" method="POST" class="space-y-6">
            @csrf

            @if ($errors->any())
                <div class="bg-red-50 text-red-700 p-4 rounded-xl text-sm mb-4 border border-red-200">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 mb-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4 border-b border-gray-100 pb-2">Data Karyawan</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Nama Lengkap *</label>
                        <input type="text" name="name" required value="{{ old('name') }}"
                            class="w-full border border-gray-300 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500"
                            placeholder="Contoh: Budi Santoso">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Username *</label>
                        <input type="text" name="username" required value="{{ old('username') }}"
                            class="w-full border border-gray-300 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500"
                            placeholder="Contoh: budi.santoso">
                        <p class="text-xs text-gray-500 mt-1">Username harus unik dan tidak boleh ada spasi</p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Role *</label>
                        <select name="role" required class="w-full border border-gray-300 rounded-xl p-3 text-sm bg-gray-50 focs:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih Role --</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->slug }}" {{ old('role') == $role->slug ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Hak akses aplikasi akan disesuaikan dengan Role terpilih</p>
                    </div>
                </div>
            </div>

            <!-- Optional Field: Supervisor Setup (Usually for Sales) -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 mb-6" id="supervisor-section">
                <h2 class="text-lg font-bold text-gray-800 mb-4 border-b border-gray-100 pb-2">Penugasan Supervisor</h2>
                <p class="text-xs text-blue-600 mb-4 bg-blue-50 p-2 rounded">Khusus untuk akun Sales, kamu bisa langsung menugaskannya ke Supervisor.</p>
                
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Supervisor (Opsional)</label>
                    <select name="supervisor_id" class="w-full border border-gray-300 rounded-xl p-3 text-sm">
                        <option value="">-- Pilih Supervisor --</option>
                        @foreach($supervisors as $supervisor)
                            <option value="{{ $supervisor->id }}" {{ old('supervisor_id') == $supervisor->id ? 'selected' : '' }}>
                                {{ $supervisor->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                <p class="text-xs text-blue-800">
                    <strong>Informasi Password:</strong> Password akan otomatis di-generate berdasarkan nama role (contoh: <code>sales123</code> atau <code>finance123</code>). Karyawan akan diminta mengubah password saat login pertama kali.
                </p>
            </div>

            <div class="flex gap-3 mb-10 mt-6">
                <button type="submit" class="flex-[2] bg-blue-600 text-white py-4 rounded-xl font-bold text-sm shadow-lg hover:bg-blue-700 transition">
                    Buat Akun
                </button>
                <a href="{{ route('hrd.dashboard') }}" class="flex-1 bg-gray-200 text-gray-700 py-4 rounded-xl font-bold text-sm text-center hover:bg-gray-300 transition">Batal</a>
            </div>
        </form>
    </div>

    <!-- Script to auto toggle supervisor section based on role dropdown -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleSelect = document.querySelector('select[name="role"]');
            const supervisorSection = document.getElementById('supervisor-section');
            
            function toggleSupervisor() {
                if(roleSelect.value === 'sales') {
                    supervisorSection.style.display = 'block';
                } else {
                    supervisorSection.style.display = 'none';
                    // Reset value
                    document.querySelector('select[name="supervisor_id"]').value = "";
                }
            }
            
            // Initial check
            toggleSupervisor();
            
            // On change
            roleSelect.addEventListener('change', toggleSupervisor);
        });
    </script>
@endsection
