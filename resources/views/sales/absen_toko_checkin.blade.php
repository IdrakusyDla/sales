@extends('layout')
@section('content')
    <div class="px-5 md:px-8 py-6 md:py-8 md:bg-slate-50/50 md:min-h-screen">

        {{-- HEADER (responsive tunggal) --}}
        <div class="mb-6">
            <div class="flex items-center gap-2 text-xs text-blue-600 font-bold mb-1">
                <span>Toko</span>
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                <span class="text-blue-700">Check-in (Sampai)</span>
            </div>
            <h1 class="text-2xl md:text-xl font-bold md:font-extrabold text-gray-800 md:tracking-tight">Foto Saat Sampai di Toko</h1>
            <p class="text-sm text-gray-600 md:text-gray-500 mt-1">Ambil foto sebagai bukti Anda telah tiba di lokasi toko.</p>
        </div>

        <div class="md:grid md:grid-cols-12 md:gap-6">

            {{-- KOLOM KIRI: FORM UTAMA --}}
            <div class="md:col-span-9">
                <div class="md:bg-white md:rounded-[2rem] md:shadow-sm md:border md:border-gray-100 md:overflow-hidden md:p-8">

                    @if($visitObj)
                        {{-- MODE: check-in toko spesifik --}}
                        <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4 mb-6 flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </div>
                            <div>
                                <p class="text-xs text-blue-600 font-bold">Check-in di</p>
                                <h2 class="text-lg font-extrabold text-gray-800">{{ $visitObj->client_name }}</h2>
                            </div>
                        </div>

                        <form action="{{ route('sales.absen.toko.checkin.store', $visitObj->id) }}" method="POST" id="form-checkin" class="card-form">
                            @csrf
                            <input type="hidden" name="lat" id="lat">
                            <input type="hidden" name="long" id="long">

                            <x-camera-capture name="photo" label="Foto Sampai di Toko *" accent="blue" defaultFacing="user" />

                            <div class="flex gap-3 mb-24">
                                <a href="{{ route('dashboard') }}" class="flex-1 bg-gray-200 text-gray-700 py-4 rounded-xl font-bold text-sm text-center">Batal</a>
                                <button type="submit" id="btn-submit"
                                    class="flex-[2] bg-blue-600 text-white py-4 rounded-xl font-bold text-sm shadow-lg disabled:bg-gray-400 disabled:cursor-not-allowed"
                                    disabled>Check-in Toko</button>
                            </div>
                        </form>

                    @else
                        {{-- MODE: pilih toko dari rencana ATAU dadakan --}}

                        {{-- PILIH DARI RENCANA --}}
                        @if($pendingVisits->count() > 0)
                            <div class="mb-6">
                                <label class="block text-sm font-bold text-gray-700 mb-3">Pilih Toko dari Rencana</label>
                                <div class="space-y-3">
                                    @foreach($pendingVisits as $v)
                                        <a href="{{ route('sales.absen.toko.checkin', $v->id) }}"
                                            class="flex items-center gap-4 p-4 border-2 border-gray-200 rounded-2xl hover:border-blue-500 hover:bg-blue-50/50 transition group">
                                            <div class="w-10 h-10 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center shrink-0 group-hover:bg-blue-600 group-hover:text-white transition">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" clip-rule="evenodd" d="M5 3C3.89543 3 3 3.89543 3 5V6.83772L1.49006 11.3675C1.10052 12.5362 1.8474 13.7393 3 13.963V20C3 21.1046 3.89543 22 5 22H9H10H14H15H19C20.1046 22 21 21.1046 21 20V13.963C22.1526 13.7393 22.8995 12.5362 22.5099 11.3675L21 6.83772V5C21 3.89543 20.1046 3 19 3H5ZM15 20H19V14H17.5H12H6.5H5V20H9V17C9 15.3431 10.3431 14 12 14C13.6569 14 15 15.3431 15 17V20ZM11 20H13V17C13 16.4477 12.5523 16 12 16C11.4477 16 11 16.4477 11 17V20ZM3.38743 12L4.72076 8H6.31954L5.65287 12H4H3.38743ZM7.68046 12L8.34713 8H11V12H7.68046ZM13 12V8H15.6529L16.3195 12H13ZM18.3471 12L17.6805 8H19.2792L20.6126 12H20H18.3471ZM19 5V6H16.5H12H7.5H5V5H19Z"></path></svg>
                                            </div>
                                            <span class="flex-1 font-bold text-sm text-gray-800">{{ $v->client_name }}</span>
                                            <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                        </a>
                                    @endforeach
                                </div>
                            </div>

                            <div class="flex items-center gap-2 my-8 opacity-60">
                                <div class="h-px bg-gray-300 flex-1"></div>
                                <span class="text-xs font-bold text-gray-500">ATAU</span>
                                <div class="h-px bg-gray-300 flex-1"></div>
                            </div>
                        @endif

                        {{-- KUNJUNGAN DADAKAN --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-3">Kunjungan Dadakan</label>
                            <form action="{{ route('sales.absen.toko.checkin.store') }}" method="POST" id="form-checkin-new" class="card-form">
                                @csrf
                                <input type="hidden" name="lat" id="lat">
                                <input type="hidden" name="long" id="long">

                                <div class="mb-6">
                                    <input type="text" name="new_client_name" id="new_client_name" placeholder="Nama Toko / Client"
                                        class="w-full border border-gray-300 rounded-xl p-4 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                <x-camera-capture name="photo" label="Foto Sampai di Toko *" accent="blue" defaultFacing="user" />

                                <div class="flex gap-3 mb-24">
                                    <a href="{{ route('dashboard') }}" class="flex-1 bg-gray-200 text-gray-700 py-4 rounded-xl font-bold text-sm text-center">Batal</a>
                                    <button type="submit" id="btn-submit"
                                        class="flex-[2] bg-blue-600 text-white py-4 rounded-xl font-bold text-sm shadow-lg disabled:bg-gray-400 disabled:cursor-not-allowed"
                                        disabled>Check-in Toko</button>
                                </div>
                            </form>
                        </div>
                    @endif

                </div>
            </div>

            {{-- KOLOM KANAN: INFO CARDS (desktop) --}}
            <div class="hidden md:block md:col-span-3 md:space-y-6">

                <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Alur 2 Foto per Toko
                    </h3>
                    <div class="bg-blue-50 rounded-xl p-4">
                        <ul class="text-xs text-blue-700 space-y-2">
                            <li class="flex items-start gap-2">
                                <span class="w-5 h-5 rounded-full bg-blue-600 text-white text-[10px] font-bold flex items-center justify-center shrink-0 mt-0.5">1</span>
                                <span><strong>Check-in</strong>: foto saat sampai di toko</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="w-5 h-5 rounded-full bg-blue-600 text-white text-[10px] font-bold flex items-center justify-center shrink-0 mt-0.5">2</span>
                                <span>Kerja / demo product di toko</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="w-5 h-5 rounded-full bg-green-600 text-white text-[10px] font-bold flex items-center justify-center shrink-0 mt-0.5">3</span>
                                <span><strong>Check-out</strong>: foto saat pulang + hasil</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Progress Hari Ini
                    </h3>
                    <div class="bg-green-50 rounded-xl p-4">
                        <div class="space-y-2 text-xs">
                            <div class="flex justify-between"><span class="text-green-600">Selesai:</span><span class="font-bold text-green-700">{{ $completedVisits }}</span></div>
                            @if($inProgressVisits > 0)
                            <div class="flex justify-between"><span class="text-amber-600">Di toko:</span><span class="font-bold text-amber-700">{{ $inProgressVisits }}</span></div>
                            @endif
                            @if($failedVisits > 0)
                            <div class="flex justify-between"><span class="text-red-600">Gagal:</span><span class="font-bold text-red-700">{{ $failedVisits }}</span></div>
                            @endif
                            <div class="flex justify-between pt-2 border-t border-green-200"><span class="text-gray-500">Total toko:</span><span class="font-bold text-gray-700">{{ $totalVisits }}</span></div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    @include('partials.permission-check', ['requireLocation' => true])

    @section('scripts')
    <script>
        // GPS
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

        // State kamera (dari komponen)
        let __photoTaken = false;
        window.__onCameraUpdate = function(name, taken) { __photoTaken = taken; checkSubmit(); };

        function checkSubmit() {
            const btn = document.getElementById('btn-submit');
            if (!btn) return;
            const lat = document.getElementById('lat');
            const newName = document.getElementById('new_client_name');
            let ok = __photoTaken && lat && lat.value;
            if (newName) ok = ok && newName.value.trim();
            btn.disabled = !ok;
        }

        const newNameEl = document.getElementById('new_client_name');
        if (newNameEl) newNameEl.addEventListener('input', checkSubmit);

        // Double submit guard
        const form = document.getElementById('form-checkin') || document.getElementById('form-checkin-new');
        const submitBtn = document.getElementById('btn-submit');
        let isSubmitting = false;
        if (form) {
            form.addEventListener('submit', function(e) {
                if (isSubmitting) { e.preventDefault(); return false; }
                isSubmitting = true;
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<svg class="w-5 h-5 inline animate-spin mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Sedang diproses...';
                    submitBtn.classList.add('opacity-75','cursor-not-allowed');
                }
            });
        }

        initGPS();
    </script>
    @endsection
@endsection
