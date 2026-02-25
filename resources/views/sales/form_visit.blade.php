@extends('layout')
@section('content')
    {{-- 1. KAMERA VIEWPORT (UI SERAGAM DENGAN ABSEN MASUK) --}}
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

        {{-- Overlay Loading GPS --}}
        <div id="gps-loader" class="absolute top-4 left-4 bg-black/50 text-white text-xs px-3 py-1 rounded-full">
            <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path></svg> Mencari GPS...
        </div>
    </div>

    {{-- 2. FORM INPUT --}}
    <div class="px-5 py-6 -mt-4 relative z-10">
        <form action="{{ route('store.visit') }}" method="POST" enctype="multipart/form-data" id="visitForm">
            @csrf
            <input type="hidden" name="lat" id="lat">
            <input type="hidden" name="long" id="long">
            <input type="hidden" name="photo" id="photo_data">

            <div class="flex justify-between items-end mb-4">
                <div>
                    <h1 class="text-xl font-bold text-gray-800">Lapor Kunjungan</h1>
                    <p class="text-xs text-gray-500">Isi data kunjungan dengan lengkap.</p>
                </div>
                <button type="button" onclick="resetCamera()"
                    class="text-xs text-blue-600 font-bold bg-blue-50 px-3 py-2 rounded-lg">
                    ↺ Ulang Foto
                </button>
            </div>

            {{-- A. PILIH LOKASI (Wajib) --}}
            <div class="mb-4">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Lokasi <span
                        class="text-red-500">*</span></label>
                <select name="visit_id" id="visit_select" onchange="checkNew(this.value); validateForm()"
                    class="w-full border border-gray-300 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="" disabled selected>-- Pilih Tujuan --</option>
                    @foreach ($plannedVisits as $plan)
                        <option value="{{ $plan->id }}">{{ $plan->client_name }} (Rencana)</option>
                    @endforeach
                    <option value="new">+ Kunjungan Dadakan Baru</option>
                </select>

                {{-- Input Dadakan --}}
                <div id="new_client_input" class="hidden mt-2">
                    <input type="text" name="new_client_name" id="new_client_text" oninput="validateForm()"
                        placeholder="Nama Toko/Klien..."
                        class="w-full border border-blue-300 bg-blue-50 rounded-xl p-3 text-sm">
                </div>
            </div>

            {{-- B. STATUS (Wajib) --}}
            <div class="mb-4">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Status <span
                        class="text-red-500">*</span></label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="status" value="completed" class="peer sr-only"
                            onchange="toggleReason(false); validateForm()">
                        <div
                            class="text-center p-3 border rounded-xl peer-checked:bg-green-50 peer-checked:border-green-500 peer-checked:text-green-600 transition text-sm font-bold opacity-50 peer-checked:opacity-100">
                            <svg class="w-5 h-5 inline mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Berhasil
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="status" value="failed" class="peer sr-only"
                            onchange="toggleReason(true); validateForm()">
                        <div
                            class="text-center p-3 border rounded-xl peer-checked:bg-red-50 peer-checked:border-red-500 peer-checked:text-red-600 transition text-sm font-bold opacity-50 peer-checked:opacity-100">
                            <svg class="w-5 h-5 inline mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            Gagal
                        </div>
                    </label>
                </div>
            </div>

            {{-- C. CATATAN / ALASAN (Wajib) --}}
            <div class="mb-6">
                <div id="notes_box" class="hidden">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Hasil Meeting <span
                            class="text-red-500">*</span></label>
                    <textarea name="notes" id="input_notes" oninput="validateForm()"
                        class="w-full border border-gray-200 rounded-xl p-3 text-sm" rows="2"
                        placeholder="Contoh: Order 5 dus, minta diskon..."></textarea>
                </div>

                <div id="reason_box" class="hidden">
                    <label class="block text-xs font-bold text-red-500 uppercase mb-2">Alasan Gagal <span
                            class="text-red-500">*</span></label>
                    <textarea name="reason" id="input_reason" oninput="validateForm()"
                        class="w-full border border-red-200 bg-red-50 rounded-xl p-3 text-sm" rows="2"
                        placeholder="Contoh: Toko tutup, Owner tidak ada..."></textarea>
                </div>
            </div>

            {{-- TOMBOL SUBMIT (Disabled by default) --}}
            <button type="submit" id="btn-submit" disabled
                class="w-full bg-blue-600 text-white py-4 rounded-xl font-bold shadow-lg disabled:bg-gray-300 disabled:text-gray-500 disabled:shadow-none transition-all">
                Lengkapi Data Dulu...
            </button>
        </form>
    </div>

    {{-- SCRIPT KAMERA & VALIDASI --}}
    <script>
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const btnSnap = document.getElementById('btn-snap');
        const photoInput = document.getElementById('photo_data');
        let currentStream;
        let facingMode = 'environment'; // Default kamera belakang

        // 1. KAMERA LOGIC (Sama dengan Absen Masuk)
        function initCamera() {
            if (currentStream) currentStream.getTracks().forEach(t => t.stop());

            navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: facingMode
                }
            }).then(stream => {
                currentStream = stream;
                video.srcObject = stream;
            }).catch(e => alert("Kamera Error: " + e));
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
            ctx.shadowColor = "black";
            ctx.shadowBlur = 4;
            ctx.fillText(new Date().toLocaleString('id-ID'), 30, canvas.height - 50);

            photoInput.value = canvas.toDataURL('image/png');

            video.classList.add('hidden');
            canvas.classList.remove('hidden');
            btnSnap.classList.add('hidden');

            validateForm(); // Cek validasi setelah foto
        }

        function resetCamera() {
            photoInput.value = '';
            video.classList.remove('hidden');
            canvas.classList.add('hidden');
            btnSnap.classList.remove('hidden');
            validateForm();
        }

        // 2. FORM UI LOGIC
        function checkNew(val) {
            const input = document.getElementById('new_client_input');
            if (val === 'new') input.classList.remove('hidden');
            else input.classList.add('hidden');
        }

        function toggleReason(isFailed) {
            if (isFailed) {
                document.getElementById('notes_box').classList.add('hidden');
                document.getElementById('reason_box').classList.remove('hidden');
                // Reset value biar validasi jalan benar
                document.getElementById('input_notes').value = '';
            } else {
                document.getElementById('notes_box').classList.remove('hidden');
                document.getElementById('reason_box').classList.add('hidden');
                document.getElementById('input_reason').value = '';
            }
        }

        // 3. VALIDASI FORM (WAJIB ISI SEMUA)
        function validateForm() {
            const visitId = document.getElementById('visit_select').value;
            const isNew = visitId === 'new';
            const newClientName = document.getElementById('new_client_text').value.trim();

            // Cek Status Radio
            const statusCompleted = document.querySelector('input[name="status"][value="completed"]').checked;
            const statusFailed = document.querySelector('input[name="status"][value="failed"]').checked;

            // Cek Textarea
            const notes = document.getElementById('input_notes').value.trim();
            const reason = document.getElementById('input_reason').value.trim();

            // Cek Foto
            const hasPhoto = photoInput.value !== '';

            let isValid = false;

            // Logika Validasi
            if (hasPhoto && visitId !== "") {
                // Jika pilih kunjungan dadakan, nama toko wajib isi
                if (isNew && newClientName === "") {
                    isValid = false;
                } else {
                    // Jika status completed, notes wajib isi
                    if (statusCompleted && notes !== "") isValid = true;
                    // Jika status failed, reason wajib isi
                    else if (statusFailed && reason !== "") isValid = true;
                }
            }

            // Update Tombol Submit
            const btn = document.getElementById('btn-submit');
            if (isValid) {
                btn.disabled = false;
                btn.innerText = "Kirim Laporan";
                btn.classList.add('bg-blue-600', 'text-white');
                btn.classList.remove('bg-gray-300', 'text-gray-500');
            } else {
                btn.disabled = true;
                btn.innerText = "Lengkapi Data Dulu...";
                btn.classList.add('bg-gray-300', 'text-gray-500');
                btn.classList.remove('bg-blue-600', 'text-white');
            }
        }

        // GPS Init
        navigator.geolocation.getCurrentPosition(p => {
            document.getElementById('lat').value = p.coords.latitude;
            document.getElementById('long').value = p.coords.longitude;
            document.getElementById('gps-loader').innerHTML = '<svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg> GPS Akurat';
            document.getElementById('gps-loader').classList.replace('bg-black/50', 'bg-green-500');
        });

        initCamera();
    </script>
@endsection
