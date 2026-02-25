@extends('layout')
@section('content')
    <div class="px-5 py-6">
        <h1 class="text-2xl font-bold mb-2">Absen Masuk</h1>
        <p class="text-sm text-gray-600 mb-6">Sebelum berangkat kerja, ambil foto selfie dan foto odometer kendaraan</p>

        <form action="{{ route('sales.absen.masuk.store') }}" method="POST" id="form-absen-masuk">
            @csrf
            <input type="hidden" name="lat" id="lat">
            <input type="hidden" name="long" id="long">
            <input type="hidden" name="photo" id="photo_data">
            <input type="hidden" name="odometer_photo" id="odometer_photo_data">

            {{-- 1. FOTO SELFIE --}}
            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-700 mb-2">Foto Selfie *</label>
                <div class="relative w-full h-64 bg-black rounded-2xl overflow-hidden">
                    <video id="video-selfie" autoplay playsinline class="w-full h-full object-cover"></video>
                    <canvas id="canvas-selfie" class="hidden w-full h-full object-cover"></canvas>
                    {{-- Tombol switch kamera selfie --}}
                    <button type="button" onclick="switchSelfieCamera()" id="btn-switch-selfie"
                        class="absolute top-4 right-4 bg-white/20 backdrop-blur p-2 rounded-full text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                            </path>
                        </svg>
                    </button>
                    {{-- Tombol ambil foto --}}
                    <button type="button" onclick="takeSelfie()" id="btn-snap-selfie"
                        class="absolute bottom-4 left-1/2 transform -translate-x-1/2 w-16 h-16 bg-white rounded-full border-4 border-gray-300 shadow-lg flex items-center justify-center">
                        <div class="w-12 h-12 bg-blue-500 rounded-full"></div>
                    </button>
                    {{-- Tombol ambil ulang selfie --}}
                    <button type="button" onclick="retakeSelfie()" id="btn-retake-selfie"
                        class="hidden absolute bottom-4 left-1/2 transform -translate-x-1/2 bg-blue-600 text-white px-6 py-3 rounded-xl font-bold text-sm shadow-lg flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg> Ambil Ulang
                    </button>
                </div>
                <p id="selfie-status" class="text-xs text-gray-500 mt-2"></p>
            </div>

            {{-- 2. FOTO ODOMETER --}}
            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-700 mb-2">Foto Odometer Kendaraan *</label>
                <div class="relative w-full h-64 bg-black rounded-2xl overflow-hidden">
                    <video id="video-odometer" autoplay playsinline class="w-full h-full object-cover"></video>
                    <canvas id="canvas-odometer" class="hidden w-full h-full object-cover"></canvas>
                    {{-- Tombol switch kamera odometer --}}
                    <button type="button" onclick="switchOdometerCamera()" id="btn-switch-odometer"
                        class="absolute top-4 right-4 bg-white/20 backdrop-blur p-2 rounded-full text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                            </path>
                        </svg>
                    </button>
                    {{-- Tombol ambil foto --}}
                    <button type="button" onclick="takeOdometer()" id="btn-snap-odometer"
                        class="absolute bottom-4 left-1/2 transform -translate-x-1/2 w-16 h-16 bg-white rounded-full border-4 border-gray-300 shadow-lg flex items-center justify-center">
                        <div class="w-12 h-12 bg-green-500 rounded-full"></div>
                    </button>
                    {{-- Tombol ambil ulang odometer --}}
                    <button type="button" onclick="retakeOdometer()" id="btn-retake-odometer"
                        class="hidden absolute bottom-4 left-1/2 transform -translate-x-1/2 bg-green-600 text-white px-6 py-3 rounded-xl font-bold text-sm shadow-lg flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg> Ambil Ulang
                    </button>
                </div>
                <p id="odometer-status" class="text-xs text-gray-500 mt-2"></p>
            </div>

            {{-- 3. INPUT NILAI ODOMETER --}}
            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-700 mb-2">Nilai Odometer (KM) *</label>
                <input type="number" name="odometer_value" id="odometer_value" step="0.01" min="0" required
                    class="w-full border border-gray-300 rounded-xl p-4 text-lg font-bold focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Contoh: 12345.67">
                <p class="text-xs text-gray-500 mt-1">Masukkan nilai odometer yang terlihat di foto</p>
            </div>

            {{-- 4. RENCANA KUNJUNGAN --}}
            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-700 mb-2">Rencana Kunjungan Hari Ini *</label>
                <div id="destinations-container" class="space-y-3">
                    <div class="flex gap-2">
                        <input type="text" name="destinations[]" placeholder="Toko/Tujuan 1" required
                            class="flex-1 border border-gray-300 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <button type="button" onclick="addDestination()"
                    class="text-blue-600 text-sm font-bold flex items-center gap-1 mt-3">
                    <span class="text-lg">+</span> Tambah Tujuan Lain
                </button>
            </div>

            {{-- 5. TOMBOL SUBMIT --}}
            <div class="flex gap-3 mb-24">
                <button type="button" onclick="resetForm()"
                    class="flex-1 bg-gray-200 text-gray-700 py-4 rounded-xl font-bold text-sm">Reset</button>
                <button type="submit" id="btn-submit"
                    class="flex-[2] bg-blue-600 text-white py-4 rounded-xl font-bold text-sm shadow-lg disabled:bg-gray-400 disabled:cursor-not-allowed"
                    disabled>Absen Masuk</button>
            </div>
        </form>
    </div>

    @section('scripts')
        <script>
            let selfieStream, odometerStream;
            let selfieTaken = false, odometerTaken = false;
            let selfieFacingMode = 'user'; // Kamera depan untuk selfie
            let odometerFacingMode = 'environment'; // Kamera belakang untuk odometer

            // Init Selfie Camera
            function initSelfieCamera() {
                if (selfieStream) selfieStream.getTracks().forEach(t => t.stop());
                navigator.mediaDevices.getUserMedia({
                    video: { facingMode: selfieFacingMode }
                }).then(stream => {
                    selfieStream = stream;
                    document.getElementById('video-selfie').srcObject = stream;
                }).catch(err => {
                    alert('Akses kamera selfie ditolak. Pastikan izin kamera sudah diberikan.');
                });
            }

            // Init Odometer Camera
            function initOdometerCamera() {
                if (odometerStream) odometerStream.getTracks().forEach(t => t.stop());
                navigator.mediaDevices.getUserMedia({
                    video: { facingMode: odometerFacingMode }
                }).then(stream => {
                    odometerStream = stream;
                    document.getElementById('video-odometer').srcObject = stream;
                    document.getElementById('odometer-status').textContent = '';
                }).catch(err => {
                    alert('Akses kamera odometer ditolak. Pastikan izin kamera sudah diberikan.');
                });
            }

            function switchSelfieCamera() {
                selfieFacingMode = selfieFacingMode === 'user' ? 'environment' : 'user';
                initSelfieCamera();
            }

            function switchOdometerCamera() {
                odometerFacingMode = odometerFacingMode === 'environment' ? 'user' : 'environment';
                initOdometerCamera();
            }

            function takeSelfie() {
                const video = document.getElementById('video-selfie');
                const canvas = document.getElementById('canvas-selfie');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                const ctx = canvas.getContext('2d');
                ctx.translate(canvas.width, 0);
                ctx.scale(-1, 1);
                ctx.drawImage(video, 0, 0);
                ctx.setTransform(1, 0, 0, 1, 0, 0);
                ctx.font = "bold 20px sans-serif";
                ctx.fillStyle = "white";
                ctx.fillText(new Date().toLocaleString('id-ID'), 20, canvas.height - 30);

                document.getElementById('photo_data').value = canvas.toDataURL('image/png');
                video.classList.add('hidden');
                canvas.classList.remove('hidden');
                document.getElementById('btn-snap-selfie').classList.add('hidden');
                document.getElementById('btn-switch-selfie').classList.add('hidden');
                document.getElementById('btn-retake-selfie').classList.remove('hidden');
                document.getElementById('selfie-status').innerHTML = '<svg class="w-5 h-5 inline text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Foto selfie berhasil diambil';
                selfieTaken = true;

                // Stop selfie camera and start odometer camera
                if (selfieStream) selfieStream.getTracks().forEach(t => t.stop());
                initOdometerCamera();

                checkSubmit();
            }

            function retakeSelfie() {
                // Reset selfie state
                selfieTaken = false;
                document.getElementById('photo_data').value = '';
                document.getElementById('canvas-selfie').classList.add('hidden');
                document.getElementById('video-selfie').classList.remove('hidden');
                document.getElementById('btn-snap-selfie').classList.remove('hidden');
                document.getElementById('btn-switch-selfie').classList.remove('hidden');
                document.getElementById('btn-retake-selfie').classList.add('hidden');
                document.getElementById('selfie-status').textContent = '';

                // Stop odometer camera and restart selfie camera
                if (odometerStream) odometerStream.getTracks().forEach(t => t.stop());
                odometerTaken = false;
                document.getElementById('odometer_photo_data').value = '';
                document.getElementById('canvas-odometer').classList.add('hidden');
                document.getElementById('video-odometer').classList.remove('hidden');
                document.getElementById('btn-snap-odometer').classList.remove('hidden');
                document.getElementById('btn-switch-odometer').classList.remove('hidden');
                document.getElementById('btn-retake-odometer').classList.add('hidden');
                document.getElementById('odometer-status').innerHTML = '<svg class="w-5 h-5 inline text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg> Kamera odometer akan aktif setelah foto selfie diambil';

                initSelfieCamera();
                checkSubmit();
            }

            function takeOdometer() {
                const video = document.getElementById('video-odometer');
                const canvas = document.getElementById('canvas-odometer');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0);
                ctx.font = "bold 20px sans-serif";
                ctx.fillStyle = "white";
                ctx.fillText(new Date().toLocaleString('id-ID'), 20, canvas.height - 30);

                document.getElementById('odometer_photo_data').value = canvas.toDataURL('image/png');
                video.classList.add('hidden');
                canvas.classList.remove('hidden');
                document.getElementById('btn-snap-odometer').classList.add('hidden');
                document.getElementById('btn-switch-odometer').classList.add('hidden');
                document.getElementById('btn-retake-odometer').classList.remove('hidden');
                document.getElementById('odometer-status').innerHTML = '<svg class="w-5 h-5 inline text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Foto odometer berhasil diambil';
                odometerTaken = true;

                // Stop odometer camera after photo taken
                if (odometerStream) odometerStream.getTracks().forEach(t => t.stop());

                checkSubmit();
            }

            function retakeOdometer() {
                // Reset odometer state
                odometerTaken = false;
                document.getElementById('odometer_photo_data').value = '';
                document.getElementById('canvas-odometer').classList.add('hidden');
                document.getElementById('video-odometer').classList.remove('hidden');
                document.getElementById('btn-snap-odometer').classList.remove('hidden');
                document.getElementById('btn-switch-odometer').classList.remove('hidden');
                document.getElementById('btn-retake-odometer').classList.add('hidden');
                document.getElementById('odometer-status').textContent = '';

                initOdometerCamera();
                checkSubmit();
            }

            function checkSubmit() {
                const btn = document.getElementById('btn-submit');
                if (selfieTaken && odometerTaken && document.getElementById('odometer_value').value) {
                    btn.disabled = false;
                }
            }

            function addDestination() {
                const container = document.getElementById('destinations-container');
                const count = container.children.length + 1;
                const div = document.createElement('div');
                div.className = 'flex gap-2';
                div.innerHTML = `
                                        <input type="text" name="destinations[]" placeholder="Toko/Tujuan ${count}" required
                                            class="flex-1 border border-gray-300 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500">
                                        <button type="button" onclick="this.parentElement.remove()" class="text-red-500 font-bold px-3">✕</button>
                                    `;
                container.appendChild(div);
            }

            function resetForm() {
                location.reload();
            }

            // GPS
            navigator.geolocation.getCurrentPosition(p => {
                document.getElementById('lat').value = p.coords.latitude;
                document.getElementById('long').value = p.coords.longitude;
            }, err => {
                alert('Akses lokasi ditolak. Pastikan izin lokasi sudah diberikan.');
            });

            // Prevent double submit
            const form = document.getElementById('form-absen-masuk');
            const submitBtn = document.getElementById('btn-submit');
            let isSubmitting = false;

            form.addEventListener('submit', function(e) {
                if (isSubmitting) {
                    e.preventDefault();
                    return false;
                }
                isSubmitting = true;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<svg class="w-5 h-5 inline animate-spin mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Sedang diproses...';
                submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
            });

            // Init - hanya mulai kamera selfie, kamera odometer aktif setelah foto selfie diambil
            initSelfieCamera();
            document.getElementById('odometer-status').innerHTML = '<svg class="w-5 h-5 inline text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg> Kamera odometer akan aktif setelah foto selfie diambil';
            document.getElementById('odometer_value').addEventListener('input', checkSubmit);
        </script>
    @endsection
@endsection