@php
    $requireLocation = $requireLocation ?? true;
@endphp

{{-- PERMISSION GUARD: Halaman tutorial izin --}}
<div id="permission-guard" class="fixed inset-0 z-[100] bg-white hidden flex-col items-center justify-start overflow-y-auto">
    <div class="w-full max-w-md mx-auto px-6 py-10 text-center">

        {{-- ICON --}}
        <div class="w-20 h-20 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-5">
            <svg class="w-10 h-10 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
            </svg>
        </div>

        <h2 class="text-2xl font-extrabold mb-2">Izin Diperlukan</h2>
        <p class="text-gray-500 text-sm mb-8">Aktifkan Kamera{{ $requireLocation ? ' & Lokasi' : '' }} untuk melanjutkan</p>

        {{-- STATUS CHECK --}}
        <div id="perm-status-box" class="space-y-2 mb-6 text-left">
            {{-- Kamera --}}
            <div id="perm-camera-status" class="flex items-center gap-3 p-3 rounded-xl border border-gray-200">
                <div class="w-9 h-9 bg-gray-100 rounded-lg flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-bold text-gray-700">Kamera</p>
                    <p class="text-xs text-gray-400" id="perm-camera-label">Memeriksa...</p>
                </div>
                <span id="perm-camera-icon" class="text-gray-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>

            @if($requireLocation)
            {{-- Lokasi --}}
            <div id="perm-location-status" class="flex items-center gap-3 p-3 rounded-xl border border-gray-200">
                <div class="w-9 h-9 bg-gray-100 rounded-lg flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-bold text-gray-700">Lokasi (GPS)</p>
                    <p class="text-xs text-gray-400" id="perm-location-label">Memeriksa...</p>
                </div>
                <span id="perm-location-icon" class="text-gray-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            @endif
        </div>

        {{-- TUTORIAL CHROME MOBILE (hanya ditampilkan jika status denied) --}}
        <div id="perm-tutorial" class="hidden text-left bg-slate-50 rounded-2xl p-5 mb-6 border border-slate-200">
            <p class="font-bold text-sm text-slate-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-500 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>
                Izin Diblokir - Ubah Manual di Chrome
            </p>

            <ol class="space-y-4">
                <li class="flex gap-3">
                    <span class="w-7 h-7 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">1</span>
                    <div>
                        <p class="text-sm font-bold text-gray-800">Tap &#8942; (titik tiga) di pojok kanan atas</p>
                        <p class="text-xs text-gray-500 mt-0.5">Buka menu Chrome dengan tap ikon <strong>&#8942;</strong> di sudut kanan atas layar</p>
                    </div>
                </li>
                <li class="flex gap-3">
                    <span class="w-7 h-7 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">2</span>
                    <div>
                        <p class="text-sm font-bold text-gray-800">Pilih <em>"Pengaturan"</em></p>
                        <p class="text-xs text-gray-500 mt-0.5">Scroll ke bawah, tap menu <strong>Pengaturan</strong> (ikon gear &#9881;)</p>
                    </div>
                </li>
                <li class="flex gap-3">
                    <span class="w-7 h-7 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">3</span>
                    <div>
                        <p class="text-sm font-bold text-gray-800">Tap <em>"Setelan Situs"</em></p>
                        <p class="text-xs text-gray-500 mt-0.5">Di bagian <strong>Lanjutan</strong>, cari dan tap <strong>Setelan Situs</strong></p>
                    </div>
                </li>
                <li class="flex gap-3">
                    <span class="w-7 h-7 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">4</span>
                    <div>
                        <p class="text-sm font-bold text-gray-800">Aktifkan Kamera{{ $requireLocation ? ' & Lokasi' : '' }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">Tap <strong>Kamera</strong>{{ $requireLocation ? ' dan <strong>Lokasi</strong>' : '' }}, ubah ke <strong>"Izinkan"</strong></p>
                    </div>
                </li>
                <li class="flex gap-3">
                    <span class="w-7 h-7 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">5</span>
                    <div>
                        <p class="text-sm font-bold text-gray-800">Kembali & tap "Cek Ulang" di bawah</p>
                        <p class="text-xs text-gray-500 mt-0.5">Tekan tombol back, lalu tap <strong>Cek Ulang Izin</strong> untuk melanjutkan</p>
                    </div>
                </li>
            </ol>
        </div>

        {{-- FALLBACK: Jika tidak support Permissions API --}}
        <div id="perm-fallback" class="hidden text-left bg-amber-50 rounded-2xl p-5 mb-6 border border-amber-200">
            <p class="text-sm text-amber-800">
                <strong>Tidak bisa mendeteksi status izin.</strong><br>
                <span class="text-xs">Buka <strong>Pengaturan Chrome</strong> &rarr; <strong>Setelan Situs</strong> &rarr; aktifkan <strong>Kamera</strong>{{ $requireLocation ? ' dan <strong>Lokasi</strong>' : '' }} untuk website ini, lalu reload halaman.</span>
            </p>
        </div>

        {{-- BUTTON --}}
        <button onclick="recheckPermissions()" id="btn-recheck" class="w-full bg-blue-600 text-white font-bold py-4 rounded-xl shadow-lg active:scale-[0.98] transition mb-3">
            Cek Ulang Izin
        </button>

        <button onclick="window.location.reload()" class="w-full bg-gray-100 text-gray-600 font-bold py-3 rounded-xl active:scale-[0.98] transition">
            Muat Ulang Halaman
        </button>
    </div>
</div>

<script>
    // Track permission states
    let _permStates = { camera: null {{ $requireLocation ? ', location: null' : '' }} };

    function showPermissionGuard(type) {
        const guard = document.getElementById('permission-guard');
        guard.classList.remove('hidden');
        guard.classList.add('flex');
        checkPermissionStatus();
    }

    function setPermUI(name, state) {
        const label = document.getElementById('perm-' + name + '-label');
        const icon = document.getElementById('perm-' + name + '-icon');
        const box = document.getElementById('perm-' + name + '-status');

        if (!label) return;

        _permStates[name] = state;

        if (state === 'granted') {
            label.textContent = 'Sudah diizinkan';
            label.className = 'text-xs text-green-600 font-medium';
            icon.innerHTML = '<svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
            box.className = box.className.replace(/border-\w+-200/g, '').replace(/bg-\w+-50/g, '');
            box.classList.add('border-green-200', 'bg-green-50');
        } else if (state === 'denied') {
            label.textContent = 'Diblokir - ubah manual di pengaturan';
            label.className = 'text-xs text-red-500 font-medium';
            icon.innerHTML = '<svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
            box.className = box.className.replace(/border-\w+-200/g, '').replace(/bg-\w+-50/g, '');
            box.classList.add('border-red-200', 'bg-red-50');
        } else {
            label.textContent = 'Belum dipilih - tap tombol di bawah';
            label.className = 'text-xs text-amber-600 font-medium';
            icon.innerHTML = '<svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>';
            box.className = box.className.replace(/border-\w+-200/g, '').replace(/bg-\w+-50/g, '');
            box.classList.add('border-amber-200', 'bg-amber-50');
        }
    }

    function shouldShowTutorial() {
        return Object.values(_permStates).some(s => s === 'denied');
    }

    async function checkPermissionStatus() {
        if (!navigator.permissions || !navigator.permissions.query) {
            document.getElementById('perm-fallback').classList.remove('hidden');
            document.getElementById('perm-tutorial').classList.add('hidden');
            return;
        }

        try {
            const camPerm = await navigator.permissions.query({ name: 'camera' });
            setPermUI('camera', camPerm.state);

            @if($requireLocation)
            try {
                const locPerm = await navigator.permissions.query({ name: 'geolocation' });
                setPermUI('location', locPerm.state);
            } catch(e) {
                setPermUI('location', 'prompt');
            }
            @endif

            // Update tombol berdasarkan status
            const btn = document.getElementById('btn-recheck');
            const tutorial = document.getElementById('perm-tutorial');

            // Tampilkan tutorial hanya jika ada yang denied
            if (shouldShowTutorial()) {
                tutorial.classList.remove('hidden');
                btn.textContent = 'Cek Ulang Izin';
            } else {
                tutorial.classList.add('hidden');
            }

            // Cek apakah semua sudah granted
            const allGranted = Object.values(_permStates).every(s => s === 'granted');
            if (allGranted) {
                window.location.reload();
                return;
            }

            // Update teks tombol berdasarkan status
            const hasPrompt = Object.values(_permStates).some(s => s === 'prompt');
            if (hasPrompt) {
                btn.textContent = 'Izinkan Sekarang';
            } else {
                btn.textContent = 'Cek Ulang Izin';
            }

        } catch(e) {
            document.getElementById('perm-fallback').classList.remove('hidden');
        }
    }

    async function recheckPermissions() {
        const btn = document.getElementById('btn-recheck');
        btn.disabled = true;
        btn.textContent = 'Memeriksa...';

        // Cek ulang status dulu
        await checkPermissionStatus();

        // Jika belum auto-reload (belum semua granted), coba request yang statusnya "prompt"
        const allGranted = Object.values(_permStates).every(s => s === 'granted');
        if (!allGranted) {
            const hasPrompt = Object.values(_permStates).some(s => s === 'prompt');
            if (hasPrompt) {
                // Status "prompt" = browser masih bisa tampilkan popup izin
                await requestPromptPermissions();
            }
        }

        btn.disabled = false;
    }

    // Request izin untuk status "prompt" - browser akan tampilkan popup
    async function requestPromptPermissions() {
        const btn = document.getElementById('btn-recheck');
        btn.textContent = 'Meminta izin...';

        try {
            // Request kamera
            if (_permStates.camera === 'prompt') {
                const stream = await navigator.mediaDevices.getUserMedia({ video: true });
                stream.getTracks().forEach(t => t.stop());
            }

            @if($requireLocation)
            // Request lokasi
            if (_permStates.location === 'prompt') {
                await new Promise((resolve, reject) => {
                    navigator.geolocation.getCurrentPosition(resolve, reject);
                });
            }
            @endif

            // Jika berhasil semua, reload
            window.location.reload();
        } catch(err) {
            // User menolak atau error - refresh status
            await checkPermissionStatus();
        }
    }
</script>
