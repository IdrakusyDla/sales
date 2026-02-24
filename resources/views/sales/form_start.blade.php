@extends('layout')
@section('content')
    {{-- 1. KAMERA VIEWPORT (Bisa Switch Depan/Belakang) --}}
    <div class="relative w-full h-[50vh] bg-black overflow-hidden rounded-b-3xl shadow-xl group">
        <video id="video" autoplay playsinline class="w-full h-full object-cover"></video>
        <canvas id="canvas" class="hidden w-full h-full object-cover"></canvas>

        {{-- Tombol Switch Kamera --}}
        <button type="button" onclick="switchCamera()"
            class="absolute top-4 right-4 bg-white/20 backdrop-blur p-2 rounded-full text-white shadow">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                </path>
            </svg>
        </button>

        {{-- Tombol Jepret --}}
        <div class="absolute bottom-6 w-full flex justify-center z-20">
            <button id="btn-snap" onclick="takePicture()"
                class="w-16 h-16 bg-white rounded-full border-4 border-gray-300 shadow-lg flex items-center justify-center active:scale-90 transition">
                <div class="w-12 h-12 bg-blue-500 rounded-full"></div>
            </button>
        </div>
    </div>

    {{-- 2. FORM RENCANA --}}
    <div class="px-5 py-6 -mt-4 relative z-10">
        <form action="{{ route('store.start') }}" method="POST" id="form-start">
            @csrf
            <input type="hidden" name="lat" id="lat">
            <input type="hidden" name="long" id="long">
            <input type="hidden" name="photo" id="photo_data">

            <h2 class="font-bold text-lg mb-1">Absen Masuk</h2>
            <p class="text-xs text-gray-500 mb-4">Ambil foto berangkat & isi rencana kunjungan.</p>

            {{-- Container Input Dinamis --}}
            <div id="destinations-container" class="space-y-3 mb-4">
                {{-- Input Pertama (Wajib) --}}
                <div class="flex gap-2">
                    <input type="text" name="destinations[]" placeholder="Tujuan 1 (Contoh: Toko A)"
                        class="flex-1 border border-gray-300 rounded-xl p-3 text-sm focus:ring-blue-500" required>
                </div>
            </div>

            {{-- Tombol Tambah Tujuan --}}
            <button type="button" onclick="addDestination()"
                class="text-blue-600 text-sm font-bold flex items-center gap-1 mb-6">
                <span class="text-lg">+</span> Tambah Tujuan Lain
            </button>

            <div class="flex gap-3">
                <button type="button" onclick="resetCamera()"
                    class="flex-1 bg-gray-200 py-3 rounded-xl font-bold text-sm">Ulang Foto</button>
                <button type="submit" id="btn-submit"
                    class="flex-[2] bg-blue-600 text-white py-3 rounded-xl font-bold text-sm shadow-lg disabled:bg-gray-400"
                    disabled>Mulai Kerja</button>
            </div>
        </form>
    </div>

    {{-- SCRIPT --}}
    <script>
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const btnSnap = document.getElementById('btn-snap');
        let currentStream;
        let facingMode = 'user'; // Default kamera depan

        function initCamera() {
            if (currentStream) currentStream.getTracks().forEach(t => t.stop());

            navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: facingMode
                }
            }).then(stream => {
                currentStream = stream;
                video.srcObject = stream;
            });
        }

        function switchCamera() {
            facingMode = facingMode === 'user' ? 'environment' : 'user';
            initCamera();
        }

        function takePicture() {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');

            // Mirror jika kamera depan
            if (facingMode === 'user') {
                ctx.translate(canvas.width, 0);
                ctx.scale(-1, 1);
            }
            ctx.drawImage(video, 0, 0);

            // Watermark
            ctx.setTransform(1, 0, 0, 1, 0, 0);
            ctx.font = "bold 24px sans-serif";
            ctx.fillStyle = "white";
            ctx.fillText(new Date().toLocaleString('id-ID'), 30, canvas.height - 50);

            document.getElementById('photo_data').value = canvas.toDataURL('image/png');

            video.classList.add('hidden');
            canvas.classList.remove('hidden');
            btnSnap.classList.add('hidden');
            document.getElementById('btn-submit').disabled = false;
        }

        function addDestination() {
            const container = document.getElementById('destinations-container');
            const count = container.children.length + 1;
            const div = document.createElement('div');
            div.className = 'flex gap-2';
            div.innerHTML = `
            <input type="text" name="destinations[]" placeholder="Tujuan ${count}" class="flex-1 border border-gray-300 rounded-xl p-3 text-sm focus:ring-blue-500" required>
            <button type="button" onclick="this.parentElement.remove()" class="text-red-500 font-bold px-2">✕</button>
        `;
            container.appendChild(div);
        }

        // GPS Init
        navigator.geolocation.getCurrentPosition(p => {
            document.getElementById('lat').value = p.coords.latitude;
            document.getElementById('long').value = p.coords.longitude;
        });

        initCamera();
    </script>
@endsection
