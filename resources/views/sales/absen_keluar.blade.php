@extends('layout')
@section('content')
    <div class="px-5 md:px-8 py-6 md:py-8">
        <h1 class="text-2xl font-bold mb-2">Absen Keluar</h1>
        <p class="text-sm text-gray-600 mb-6">Setelah selesai kerja, ambil foto selfie dan foto odometer akhir</p>

        <form action="{{ route('sales.absen.keluar.store') }}" method="POST" id="form-absen-keluar">
            @csrf
            <input type="hidden" name="lat" id="lat">
            <input type="hidden" name="long" id="long">
            <input type="hidden" name="photo" id="photo_data">
            <input type="hidden" name="odometer_photo" id="odometer_photo_data">

            {{-- JENIS ABSEN KELUAR --}}
            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-700 mb-2">Jenis Absen Keluar *</label>
                <div class="space-y-3">
                    <label
                        class="flex items-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-blue-500">
                        <input type="radio" name="end_type" value="home" required class="mr-3">
                        <span class="flex-1">
                            <span class="font-bold text-sm">Pulang ke Rumah</span>
                            <p class="text-xs text-gray-500">Absen keluar dilakukan saat sampai di rumah</p>
                        </span>
                    </label>
                    <label
                        class="flex items-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-blue-500">
                        <input type="radio" name="end_type" value="last_store" required class="mr-3">
                        <span class="flex-1">
                            <span class="font-bold text-sm">Dari Toko Terakhir</span>
                            <p class="text-xs text-gray-500">Absen keluar langsung dari toko terakhir (ada agenda pribadi)
                            </p>
                        </span>
                    </label>
                    <label
                        class="flex items-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-blue-500">
                        <input type="radio" name="end_type" value="other" required class="mr-3">
                        <span class="flex-1">
                            <span class="font-bold text-sm">Lokasi Lain</span>
                            <p class="text-xs text-gray-500">Absen keluar dari lokasi lain</p>
                        </span>
                    </label>
                </div>
            </div>

            {{-- CATATAN (Jika Other) --}}
            <div class="mb-6 hidden" id="notes-field">
                <label class="block text-sm font-bold text-gray-700 mb-2">Catatan Lokasi</label>
                <textarea name="end_notes" id="end_notes" rows="2" placeholder="Jelaskan lokasi absen keluar..."
                    class="w-full border border-gray-300 rounded-xl p-3 text-sm"></textarea>
            </div>

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
                <label class="block text-sm font-bold text-gray-700 mb-2">Foto Odometer Akhir *</label>
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

            {{-- 3. INPUT NILAI ODOMETER AKHIR --}}
            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-700 mb-2">Nilai Odometer Akhir (KM) *</label>
                <input type="number" name="odometer_value" id="odometer_value" step="0.01" min="0" required
                    class="w-full border border-gray-300 rounded-xl p-4 text-lg font-bold focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Contoh: 12350.25">
                <p class="text-xs text-gray-500 mt-1">Odometer awal:
                    <strong>{{ number_format($todayLog->start_odo_value ?? 0, 2) }} KM</strong>
                </p>
                <p id="km-total" class="text-xs font-bold text-blue-600 mt-1"></p>
            </div>

            {{-- TOMBOL SUBMIT --}}
            <div class="flex gap-3 mb-24">
                <a href="{{ route('dashboard') }}"
                    class="flex-1 bg-gray-200 text-gray-700 py-4 rounded-xl font-bold text-sm text-center">Batal</a>
                <button type="submit" id="btn-submit"
                    class="flex-[2] bg-blue-600 text-white py-4 rounded-xl font-bold text-sm shadow-lg disabled:bg-gray-400 disabled:cursor-not-allowed"
                    disabled>Absen Keluar</button>
            </div>
        </form>
    </div>

    @section('scripts')
        <script>
            let selfieStream, odometerStream;
            let selfieTaken = false, odometerTaken = false;
            let selfieFacingMode = 'user'; // Kamera depan untuk selfie
            let odometerFacingMode = 'environment'; // Kamera belakang untuk odometer
            const startOdometer = {{ $todayLog->start_odo_value ?? 0 }};

            function initSelfieCamera() {
                if (selfieStream) selfieStream.getTracks().forEach(t => t.stop());
                navigator.mediaDevices.getUserMedia({
                    video: { facingMode: selfieFacingMode }
                }).then(stream => {
                    selfieStream = stream;
                    document.getElementById('video-selfie').srcObject = stream;
                });
            }

            function initOdometerCamera() {
                if (odometerStream) odometerStream.getTracks().forEach(t => t.stop());
                navigator.mediaDevices.getUserMedia({
                    video: { facingMode: odometerFacingMode }
                }).then(stream => {
                    odometerStream = stream;
                    document.getElementById('video-odometer').srcObject = stream;
                    document.getElementById('odometer-status').textContent = '';
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
                const endType = document.querySelector('input[name="end_type"]:checked');
                if (selfieTaken && odometerTaken && document.getElementById('odometer_value').value && endType) {
                    btn.disabled = false;
                }
            }

            // Handle end_type change untuk show/hide notes
            document.querySelectorAll('input[name="end_type"]').forEach(radio => {
                radio.addEventListener('change', function () {
                    const notesField = document.getElementById('notes-field');
                    if (this.value === 'other') {
                        notesField.classList.remove('hidden');
                    } else {
                        notesField.classList.add('hidden');
                    }
                });
            });

            // Calculate total KM
            document.getElementById('odometer_value').addEventListener('input', function () {
                const endValue = parseFloat(this.value) || 0;
                if (endValue > 0 && startOdometer > 0) {
                    const total = endValue - startOdometer;
                    document.getElementById('km-total').textContent = `Total KM hari ini: ${total.toFixed(2)} KM`;

                    // Validasi: end harus >= start
                    if (endValue < startOdometer) {
                        this.setCustomValidity('Nilai odometer akhir tidak boleh kurang dari odometer awal');
                    } else {
                        this.setCustomValidity('');
                    }
                }
                checkSubmit();
            });

            // GPS
            navigator.geolocation.getCurrentPosition(p => {
                document.getElementById('lat').value = p.coords.latitude;
                document.getElementById('long').value = p.coords.longitude;
            });

            // Prevent double submit
            const form = document.getElementById('form-absen-keluar');
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
        </script>
    @endsection
@endsection