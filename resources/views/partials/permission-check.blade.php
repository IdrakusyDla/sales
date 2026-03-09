@php
    $requireLocation = $requireLocation ?? true;
@endphp

<div id="permission-guard" class="fixed inset-0 z-[100] bg-white hidden flex-col items-center justify-center p-6 text-center">
    <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mb-6">
        <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
        </svg>
    </div>
    <h2 class="text-2xl font-bold mb-3">Izin Diperlukan</h2>
    <p class="text-gray-600 mb-8 max-w-sm">
        Aplikasi membutuhkan izin Kamera {{ $requireLocation ? 'dan Lokasi (GPS)' : '' }} agar fitur ini bisa digunakan. Silakan berikan izin pada browser Anda.
    </p>
    
    <button onclick="requestPermissions()" class="w-full max-w-sm bg-blue-600 text-white font-bold py-4 rounded-xl shadow-lg mb-4">
        Minta Izin / Muat Ulang
    </button>

    <div id="permission-hint" class="text-sm text-gray-500 max-w-sm bg-gray-50 p-4 rounded-xl border border-gray-200 text-left">
        <strong>Jika tombol di atas tidak berfungsi:</strong><br>
        1. Buka pengaturan browser (ikon gembok/pengaturan di URL).<br>
        2. Izinkan Kamera {{ $requireLocation ? 'dan Lokasi' : '' }}.<br>
        3. Klik tombol Muat Ulang di atas.
    </div>
</div>

<script>
    function showPermissionGuard(type) {
        document.getElementById('permission-guard').classList.remove('hidden');
        document.getElementById('permission-guard').classList.add('flex');
        document.getElementById('permission-hint').classList.remove('hidden');
    }

    function requestPermissions() {
        @if($requireLocation)
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(stream => {
                    stream.getTracks().forEach(t => t.stop());
                    navigator.geolocation.getCurrentPosition(
                        () => window.location.reload(),
                        (err) => showPermissionGuard('location')
                    );
                })
                .catch(err => {
                    showPermissionGuard('camera');
                });
        @else
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(stream => {
                    stream.getTracks().forEach(t => t.stop());
                    window.location.reload();
                })
                .catch(err => {
                    showPermissionGuard('camera');
                });
        @endif
    }
</script>
