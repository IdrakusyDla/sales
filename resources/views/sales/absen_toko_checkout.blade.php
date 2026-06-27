@extends('layout')
@section('content')
    <div class="px-5 md:px-8 py-6 md:py-8 md:bg-slate-50/50 md:min-h-screen">

        {{-- HEADER --}}
        <div class="mb-6">
            <div class="flex items-center gap-2 text-xs text-green-600 font-bold mb-1">
                <span>{{ $visitObj->client_name }}</span>
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                <span class="text-green-700">Check-out (Pulang)</span>
            </div>
            <h1 class="text-2xl md:text-xl font-bold md:font-extrabold text-gray-800 md:tracking-tight">Foto Saat Pulang dari Toko</h1>
            <p class="text-sm text-gray-600 md:text-gray-500 mt-1">Selesaikan kunjungan dengan foto pulang + laporan hasil.</p>
        </div>

        <div class="md:grid md:grid-cols-12 md:gap-6">

            {{-- KOLOM KIRI: FORM --}}
            <div class="md:col-span-9">
                <div class="md:bg-white md:rounded-[2rem] md:shadow-sm md:border md:border-gray-100 md:overflow-hidden md:p-8">

                    {{-- INFO TOKO --}}
                    <div class="bg-green-50 border border-green-100 rounded-2xl p-4 mb-6 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-green-600 text-white flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs text-green-600 font-bold">Check-out dari</p>
                            <h2 class="text-lg font-extrabold text-gray-800">{{ $visitObj->client_name }}</h2>
                            <p class="text-xs text-gray-500 mt-0.5">
                                Check-in pukul {{ \Carbon\Carbon::parse($visitObj->arrival_time)->format('H:i') }}
                            </p>
                        </div>
                    </div>

                    {{-- PREVIEW FOTO SAMPAI --}}
                    <div class="mb-6">
                        <p class="text-sm font-bold text-gray-700 mb-2 flex items-center gap-2">
                            <span class="inline-flex items-center gap-1 text-blue-700 bg-blue-100 px-2 py-0.5 rounded-full text-[10px] font-bold">1. SAMPAI</span>
                            <span class="text-gray-500 font-normal">Foto saat tadi check-in</span>
                        </p>
                        <button type="button" onclick="openImageModal('{{ route('files.visit.photo', [$visitObj->id, 'arrival']) }}')"
                            class="block w-full p-0 bg-transparent border-0 focus:outline-none md:mx-auto md:max-w-[569px]">
                            <img src="{{ route('files.visit.photo', [$visitObj->id, 'arrival']) }}" alt="Foto Sampai"
                                class="w-full h-64 md:h-80 rounded-2xl object-cover">
                        </button>
                        <p class="text-xs text-gray-400 mt-1">Klik foto untuk memperbesar.</p>
                    </div>

                    <form action="{{ route('sales.absen.toko.checkout.store', $visitObj->id) }}" method="POST" id="form-checkout" class="card-form">
                        @csrf
                        <input type="hidden" name="lat" id="lat">
                        <input type="hidden" name="long" id="long">

                        {{-- FOTO PULANG --}}
                        <div class="mb-2 flex items-center gap-2">
                            <span class="inline-flex items-center gap-1 text-green-700 bg-green-100 px-2 py-0.5 rounded-full text-[10px] font-bold">2. PULANG</span>
                        </div>
                        <x-camera-capture name="photo" label="Foto Saat Pulang *" accent="green" defaultFacing="user" />

                        {{-- STATUS HASIL --}}
                        <div class="mb-6">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Hasil Kunjungan *</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="flex items-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-green-500 has-[:checked]:border-green-500 has-[:checked]:bg-green-50 transition">
                                    <input type="radio" name="status" value="completed" required class="mr-3 status-radio">
                                    <span class="font-bold text-sm">Berhasil</span>
                                </label>
                                <label class="flex items-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-red-500 has-[:checked]:border-red-500 has-[:checked]:bg-red-50 transition">
                                    <input type="radio" name="status" value="failed" required class="mr-3 status-radio">
                                    <span class="font-bold text-sm">Gagal</span>
                                </label>
                            </div>
                        </div>

                        {{-- KETERANGAN (Berhasil) --}}
                        <div class="mb-6 hidden" id="notes-field">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Keterangan</label>
                            <textarea name="notes" id="notes" rows="3" placeholder="Catatan kunjungan..."
                                class="w-full border border-gray-300 rounded-xl p-3 text-sm"></textarea>
                        </div>

                        {{-- ALASAN (Gagal) --}}
                        <div class="mb-6 hidden" id="reason-field">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Alasan Gagal *</label>
                            <textarea name="reason" id="reason" rows="2" placeholder="Mengapa kunjungan gagal?"
                                class="w-full border border-gray-300 rounded-xl p-3 text-sm"></textarea>
                        </div>

                        <div class="flex gap-3 mb-24">
                            <a href="{{ route('dashboard') }}" class="flex-1 bg-gray-200 text-gray-700 py-4 rounded-xl font-bold text-sm text-center">Batal</a>
                            <button type="submit" id="btn-submit"
                                class="flex-[2] bg-green-600 text-white py-4 rounded-xl font-bold text-sm shadow-lg disabled:bg-gray-400 disabled:cursor-not-allowed"
                                disabled>Selesaikan Kunjungan</button>
                        </div>
                    </form>

                </div>
            </div>

            {{-- KOLOM KANAN: INFO (desktop) --}}
            <div class="hidden md:block md:col-span-3 md:space-y-6">
                <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Selanjutnya
                    </h3>
                    <div class="bg-green-50 rounded-xl p-4">
                        @if($pendingVisits > 0)
                            <p class="text-xs text-green-700">Masih ada <strong>{{ $pendingVisits }} toko</strong> lain yang belum dikunjungi. Setelah check-out, lanjutkan ke toko berikutnya.</p>
                        @else
                            <p class="text-xs text-green-700">Ini toko terakhir. Setelah check-out, Anda bisa <strong>Absen Keluar</strong>.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('partials.permission-check', ['requireLocation' => true])

    @section('scripts')
    <script>
        async function initGPS() {
            if (navigator.permissions && navigator.permissions.query) {
                try {
                    const p = await navigator.permissions.query({ name: 'geolocation' });
                    if (p.state === 'denied') { if(typeof showPermissionGuard==='function') showPermissionGuard('location'); return; }
                } catch(e){}
            }
            navigator.geolocation.getCurrentPosition(p => {
                const lat = document.getElementById('lat'); const lng = document.getElementById('long');
                if (lat) lat.value = p.coords.latitude; if (lng) lng.value = p.coords.longitude;
                checkSubmit();
            }, () => { if(typeof showPermissionGuard==='function') showPermissionGuard('location'); });
        }

        let __photoTaken = false;
        window.__onCameraUpdate = function(name, taken) { __photoTaken = taken; checkSubmit(); };

        function checkSubmit() {
            const btn = document.getElementById('btn-submit');
            if (!btn) return;
            const lat = document.getElementById('lat');
            const statusChecked = document.querySelector('input[name="status"]:checked');
            btn.disabled = !(__photoTaken && lat && lat.value && statusChecked);
        }

        // Toggle notes/reason
        document.querySelectorAll('.status-radio').forEach(r => r.addEventListener('change', function() {
            const reasonField = document.getElementById('reason-field');
            const reasonInput = document.getElementById('reason');
            const notesField = document.getElementById('notes-field');
            const notesInput = document.getElementById('notes');
            if (this.value === 'failed') {
                reasonField.classList.remove('hidden'); reasonInput.setAttribute('required','required');
                notesField.classList.add('hidden'); notesInput.value=''; notesInput.removeAttribute('required');
            } else {
                reasonField.classList.add('hidden'); reasonInput.removeAttribute('required'); reasonInput.value='';
                notesField.classList.remove('hidden'); notesInput.removeAttribute('required');
            }
            checkSubmit();
        }));

        const form = document.getElementById('form-checkout');
        const submitBtn = document.getElementById('btn-submit');
        let isSubmitting = false;
        form.addEventListener('submit', function(e) {
            if (isSubmitting) { e.preventDefault(); return false; }
            isSubmitting = true;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<svg class="w-5 h-5 inline animate-spin mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Sedang diproses...';
            submitBtn.classList.add('opacity-75','cursor-not-allowed');
        });

        initGPS();
    </script>
    @endsection
@endsection
