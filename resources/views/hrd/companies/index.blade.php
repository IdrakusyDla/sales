@extends('layout')
@section('content')
    <div class="px-5 md:px-8 py-6 md:py-8">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('hrd.dashboard') }}" class="text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <h1 class="text-2xl font-bold">Daftar Perusahaan</h1>
        </div>

        <p class="text-sm text-gray-600 mb-6">Kelola daftar perusahaan dalam group. Saat membuat akun karyawan, tinggal pilih dari daftar ini.</p>

        {{-- FORM TAMBAH --}}
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 mb-6">
            <form action="{{ route('hrd.companies.store') }}" method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row gap-3">
                @csrf
                <input type="text" name="name" required placeholder="Nama Perusahaan, misal: PT Maju Jaya" value="{{ old('name') }}"
                    class="flex-1 border border-gray-300 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500">
                <label class="flex items-center gap-2 border border-gray-300 rounded-xl px-3 py-3 text-sm text-gray-500 cursor-pointer hover:bg-gray-50">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span data-choose-text>Logo (opsional)</span>
                    <input type="file" name="logo" accept="image/png,image/jpeg,image/svg+xml,image/webp" class="hidden" onchange="syncFileName(this)">
                </label>
                <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-xl font-bold text-sm hover:bg-blue-700 transition">
                    Tambah Perusahaan
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
            @forelse($companies as $company)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 flex-1">
                            <div class="w-10 h-10 bg-cyan-100 rounded-full flex items-center justify-center shrink-0 overflow-hidden">
                                @if($company->logo_path)
                                    <img src="{{ route('files.company.logo', $company) }}" alt="{{ $company->name }}" class="w-full h-full object-contain" onerror="this.style.display='none'">
                                @else
                                    <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                @endif
                            </div>
                            <div class="flex-1">
                                @if($company->is_active)
                                    <h3 class="font-bold text-gray-800">{{ $company->name }}</h3>
                                @else
                                    <h3 class="font-bold text-gray-400 line-through">{{ $company->name }}</h3>
                                    <span class="text-[10px] bg-red-100 text-red-600 px-2 py-0.5 rounded-full font-bold">Nonaktif</span>
                                @endif
                                <p class="text-xs text-gray-500">{{ $company->users_count }} karyawan</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            {{-- Edit --}}
                            <button type="button" onclick="editCompany({{ $company->id }}, '{{ $company->name }}', '{{ $company->logo_path ? route('files.company.logo', $company) : '' }}')"
                                class="text-blue-500 hover:text-blue-700 hover:bg-blue-50 transition px-3 py-2 rounded-lg text-xs font-bold">
                                Edit
                            </button>

                            {{-- Toggle Status --}}
                            <form action="{{ route('hrd.companies.toggle', $company->id) }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="{{ $company->is_active ? 'text-orange-500 hover:text-orange-700 hover:bg-orange-50' : 'text-green-500 hover:text-green-700 hover:bg-green-50' }} transition px-3 py-2 rounded-lg text-xs font-bold">
                                    {{ $company->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>

                            {{-- Delete --}}
                            <form action="{{ route('hrd.companies.destroy', $company->id) }}" method="POST" onsubmit="return confirm('Hapus perusahaan ini?')">
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
                    <p class="text-sm text-gray-500">Belum ada perusahaan. Tambahkan di atas.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- MODAL EDIT --}}
    <div id="edit-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl p-6 w-full max-w-md">
            <h2 class="text-lg font-bold mb-4">Edit Perusahaan</h2>
            <form id="edit-form" action="" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="text" name="name" id="edit-name" required
                    class="w-full border border-gray-300 rounded-xl p-3 text-sm mb-4 focus:ring-2 focus:ring-blue-500">

                <label class="flex flex-col gap-2 mb-4">
                    <span class="text-xs font-bold text-gray-500 uppercase">Logo Perusahaan</span>
                    <div id="edit-logo-preview" class="hidden w-16 h-16 rounded-xl border border-gray-200 overflow-hidden bg-gray-50">
                        <img id="edit-logo-img" src="" alt="" class="w-full h-full object-contain">
                    </div>
                    <span class="flex items-center gap-2 border border-gray-300 rounded-xl px-3 py-2 text-sm text-gray-500 cursor-pointer hover:bg-gray-50 w-fit">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span id="edit-logo-label">Ganti logo (opsional)</span>
                        <input type="file" name="logo" id="edit-logo" accept="image/png,image/jpeg,image/svg+xml,image/webp" class="hidden">
                    </span>
                    <span class="text-xs text-gray-400">Kosongkan jika tidak ingin mengubah logo.</span>
                </label>

                <div class="flex gap-3">
                    <button type="button" onclick="closeModal()" class="flex-1 bg-gray-200 text-gray-700 py-3 rounded-xl font-bold text-sm">Batal</button>
                    <button type="submit" class="flex-1 bg-blue-600 text-white py-3 rounded-xl font-bold text-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editCompany(id, name, logoUrl) {
            document.getElementById('edit-form').action = '{{ route("hrd.companies.update", ":id") }}'.replace(':id', id);
            document.getElementById('edit-name').value = name;

            const preview = document.getElementById('edit-logo-preview');
            const img = document.getElementById('edit-logo-img');
            if (logoUrl) {
                img.src = logoUrl;
                preview.classList.remove('hidden');
            } else {
                img.src = '';
                preview.classList.add('hidden');
            }
            document.getElementById('edit-logo').value = '';
            document.getElementById('edit-logo-label').textContent = 'Ganti logo (opsional)';

            document.getElementById('edit-modal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('edit-modal').classList.add('hidden');
        }

        function syncFileName(input) {
            const span = input.closest('label').querySelector('[data-choose-text]');
            if (span) span.textContent = input.files.length ? input.files[0].name : 'Logo (opsional)';
        }

        document.getElementById('edit-logo').addEventListener('change', function () {
            document.getElementById('edit-logo-label').textContent = this.files.length ? this.files[0].name : 'Ganti logo (opsional)';
        });
    </script>
@endsection
