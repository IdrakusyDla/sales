@extends('layout')
@section('content')
    <div class="px-5 md:px-8 py-6 md:py-8">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('hrd.dashboard') }}" class="text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <h1 class="text-2xl font-bold">Daftar Jabatan</h1>
        </div>

        <p class="text-sm text-gray-600 mb-6">Kelola daftar jabatan karyawan (misal: SMD, SPG). Saat membuat akun, tinggal pilih dari daftar ini.</p>

        {{-- FORM TAMBAH --}}
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 mb-6">
            <form action="{{ route('hrd.job_positions.store') }}" method="POST" class="flex gap-3">
                @csrf
                <input type="text" name="name" required placeholder="Nama Jabatan, misal: SMD" value="{{ old('name') }}"
                    class="flex-1 border border-gray-300 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500">
                <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-xl font-bold text-sm hover:bg-blue-700 transition">
                    Tambah Jabatan
                </button>
            </form>
        </div>

        @if(session('success'))
            <div class="bg-green-50 text-green-700 p-4 rounded-xl text-sm mb-4 border border-green-200 font-bold">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-50 text-red-700 p-4 rounded-xl text-sm mb-4 border border-red-200 font-bold">{{ session('error') }}</div>
        @endif

        {{-- LIST --}}
        <div class="space-y-3">
            @forelse($jobPositions as $jobPosition)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 flex-1">
                            <div class="w-10 h-10 bg-teal-100 rounded-full flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                @if($jobPosition->is_active)
                                    <h3 class="font-bold text-gray-800">{{ $jobPosition->name }}</h3>
                                @else
                                    <h3 class="font-bold text-gray-400 line-through">{{ $jobPosition->name }}</h3>
                                    <span class="text-[10px] bg-red-100 text-red-600 px-2 py-0.5 rounded-full font-bold">Nonaktif</span>
                                @endif
                                <p class="text-xs text-gray-500">{{ $jobPosition->users_count }} karyawan</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button" onclick="editJobPosition({{ $jobPosition->id }}, '{{ $jobPosition->name }}')"
                                class="text-blue-500 hover:text-blue-700 hover:bg-blue-50 transition px-3 py-2 rounded-lg text-xs font-bold">
                                Edit
                            </button>

                            <form action="{{ route('hrd.job_positions.toggle', $jobPosition->id) }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="{{ $jobPosition->is_active ? 'text-orange-500 hover:text-orange-700 hover:bg-orange-50' : 'text-green-500 hover:text-green-700 hover:bg-green-50' }} transition px-3 py-2 rounded-lg text-xs font-bold">
                                    {{ $jobPosition->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>

                            <form action="{{ route('hrd.job_positions.destroy', $jobPosition->id) }}" method="POST" onsubmit="return confirm('Hapus jabatan ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 hover:bg-red-50 transition px-3 py-2 rounded-lg text-xs font-bold">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-10 bg-gray-50 rounded-xl">
                    <p class="text-sm text-gray-500">Belum ada jabatan. Tambahkan di atas.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- MODAL EDIT --}}
    <div id="edit-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl p-6 w-full max-w-md">
            <h2 class="text-lg font-bold mb-4">Edit Jabatan</h2>
            <form id="edit-form" action="" method="POST">
                @csrf
                @method('PUT')
                <input type="text" name="name" id="edit-name" required
                    class="w-full border border-gray-300 rounded-xl p-3 text-sm mb-4 focus:ring-2 focus:ring-blue-500">
                <div class="flex gap-3">
                    <button type="button" onclick="closeModal()" class="flex-1 bg-gray-200 text-gray-700 py-3 rounded-xl font-bold text-sm">Batal</button>
                    <button type="submit" class="flex-1 bg-blue-600 text-white py-3 rounded-xl font-bold text-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editJobPosition(id, name) {
            document.getElementById('edit-form').action = '{{ route("hrd.job_positions.update", ":id") }}'.replace(':id', id);
            document.getElementById('edit-name').value = name;
            document.getElementById('edit-modal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('edit-modal').classList.add('hidden');
        }
    </script>
@endsection
