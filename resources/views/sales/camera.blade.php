@extends('layout')
@section('content')
    {{-- CAMERA VIEWPORT FULL --}}
    <div class="relative w-full h-[60vh] bg-black overflow-hidden rounded-b-3xl shadow-xl">
        <video id="video" autoplay playsinline class="w-full h-full object-cover"></video>
        <canvas id="canvas" class="hidden w-full h-full object-cover"></canvas>

        {{-- Loading GPS Badge --}}
        <div id="gps-badge"
            class="absolute top-4 left-4 bg-black/60 backdrop-blur text-white text-xs px-3 py-1.5 rounded-full flex items-center gap-2">
            <span class="animate-pulse">📡</span> Mencari GPS...
        </div>

        {{-- Tombol Jepret (Floating) --}}
        <div class="absolute bottom-6 left-0 w-full flex justify-center items-center z-20">
            <button id="btn-snap" onclick="takePicture()" disabled
                class="w-16 h-16 bg-white rounded-full border-4 border-gray-300 shadow-lg flex items-center justify-center disabled:opacity-50 active:scale-90 transition">
                <div class="w-12 h-12 bg-red-500 rounded-full"></div>
            </button>
        </div>
    </div>

    {{-- FORM INPUT --}}
    <div class="p-6 -mt-4 relative z-10">
        <form action="{{ route('store') }}" method="POST" id="form-absen">
            @csrf
            <input type="hidden" name="type" value="{{ $type }}">
            <input type="hidden" name="lat" id="lat">
            <input type="hidden" name="long" id="long">
            <input type="hidden" name="photo" id="photo_data">

            @if ($type == 'visit')
                <div class="mb-4">
                    <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Nama Klien / Toko</label>
                    <input type="text" name="client_name"
                        class="w-full bg-white border border-gray-200 rounded-xl p-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Ketik nama toko..." required>
                </div>

                <div class="mb-4">
                    <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Status</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" name="status" value="completed" class="peer sr-only" checked>
                            <div
                                class="text-center p-3 border rounded-xl peer-checked:bg-green-50 peer-checked:border-green-500 peer-checked:text-green-600 transition">
                                ✅ Berhasil
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="status" value="failed" class="peer sr-only">
                            <div
                                class="text-center p-3 border rounded-xl peer-checked:bg-red-50 peer-checked:border-red-500 peer-checked:text-red-600 transition">
                                ❌ Kendala
                            </div>
                        </label>
                    </div>
                </div>
            @else
                {{-- Jika Absen Masuk --}}
                <div class="mb-4">
                    <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Rencana Hari Ini</label>
                    <textarea name="notes"
                        class="w-full bg-white border border-gray-200 rounded-xl p-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Mau kemana hari ini?" required></textarea>
                </div>
            @endif

            <div class="flex gap-3">
                <button type="button" onclick="resetCamera()"
                    class="flex-1 bg-gray-200 text-gray-700 py-3 rounded-xl font-bold text-sm">Ulang</button>
                <button type="submit" id="btn-submit"
                    class="flex-[2] bg-blue-600 text-white py-3 rounded-xl font-bold text-sm shadow-lg disabled:bg-gray-400"
                    disabled>Simpan Data</button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const btnSnap = document.getElementById('btn-snap');
        const btnSubmit = document.getElementById('btn-submit');

        // Cek Tipe Kamera (Depan untuk Absen Masuk, Belakang untuk Visit)
        const facingMode = "{{ $type }}" == 'checkin' ? 'user' : 'environment';

        // 1. Init Kamera
        function startCamera() {
            navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: facingMode
                    }
                })
                .then(stream => {
                    video.srcObject = stream;
                })
                .catch(e => alert("Gagal akses kamera: " + e));

            video.classList.remove('hidden');
            canvas.classList.add('hidden');
            btnSnap.classList.remove('hidden');
            btnSubmit.disabled = true;
        }

        // 2. Init GPS
        if (navigator.geolocation) {
            navigator.geolocation.watchPosition(p => {
                document.getElementById('lat').value = p.coords.latitude;
                document.getElementById('long').value = p.coords.longitude;
                document.getElementById('gps-badge').innerHTML = "📍 Lokasi Akurat";
                document.getElementById('gps-badge').classList.replace('bg-black/60', 'bg-green-500');
                btnSnap.disabled = false; // Bisa foto kalau GPS sudah dapat
            });
        }

        // 3. Logic Foto
        function takePicture() {
            const ctx = canvas.getContext('2d');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;

            // Kalau kamera depan, harus dimirror
            if (facingMode == 'user') {
                ctx.translate(canvas.width, 0);
                ctx.scale(-1, 1);
            }

            ctx.drawImage(video, 0, 0);

            // Reset transform untuk watermark text
            ctx.setTransform(1, 0, 0, 1, 0, 0);

            // Watermark
            const date = new Date().toLocaleString('id-ID');
            ctx.font = "bold 24px sans-serif";
            ctx.fillStyle = "white";
            ctx.shadowColor = "black";
            ctx.shadowBlur = 4;
            ctx.fillText(date, 30, canvas.height - 60);

            // Simpan ke input
            document.getElementById('photo_data').value = canvas.toDataURL('image/png');

            // UI Change
            video.classList.add('hidden');
            canvas.classList.remove('hidden');
            btnSnap.classList.add('hidden'); // Sembunyikan tombol foto
            btnSubmit.disabled = false; // Aktifkan tombol simpan
        }

        function resetCamera() {
            startCamera();
            document.getElementById('photo_data').value = '';
        }

        startCamera();
    </script>
@endsection
