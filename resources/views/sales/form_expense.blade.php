@extends('layout')
@section('content')
    <div class="px-5 py-6 pb-24">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('dashboard') }}" class="bg-gray-100 p-2 rounded-full text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-bold">Catat Pengeluaran</h1>
                <p class="text-xs text-gray-500">Isi form & lampirkan bukti foto.</p>
            </div>
        </div>

        <form action="{{ route('expense.store') }}" method="POST" id="expenseForm">
            @csrf
            <input type="hidden" name="photo_receipt" id="photo_receipt_data">
            <input type="hidden" name="photo_km" id="photo_km_data">

            {{-- 1. PILIH JENIS (WAJIB) --}}
            <div class="mb-5">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Jenis Pengeluaran <span
                        class="text-red-500">*</span></label>
                <div class="grid grid-cols-3 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="gas" class="peer sr-only" onchange="handleTypeChange('gas')">
                        <div
                            class="flex flex-col items-center justify-center p-3 border rounded-xl peer-checked:bg-orange-50 peer-checked:border-orange-500 peer-checked:text-orange-600 transition">
                            <span class="text-xl mb-1">⛽</span>
                            <span class="text-xs font-bold">Bensin</span>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="parking" class="peer sr-only"
                            onchange="handleTypeChange('parking')">
                        <div
                            class="flex flex-col items-center justify-center p-3 border rounded-xl peer-checked:bg-orange-50 peer-checked:border-orange-500 peer-checked:text-orange-600 transition">
                            <svg class="w-5 h-5 mb-1" version="1.1" id="_x32_" xmlns="http://www.w3.org/2000/svg"
                                xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="-20 -20 552 552" xml:space="preserve"
                                fill="currentColor">
                                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                <g id="SVGRepo_iconCarrier">
                                    <g>
                                        <path
                                            d="M404.751,54.102C371.523,20.771,324.986-0.026,274.178,0h-90.85h-8.682H53.16v512h130.167V369.324h90.85 c50.808,0.026,97.333-20.771,130.573-54.074c33.331-33.229,54.115-79.78,54.089-130.575 C458.866,133.854,438.082,87.329,404.751,54.102z M321.923,232.394c-12.408,12.305-28.919,19.754-47.745,19.779h-90.85V117.15 h90.85c18.826,0.026,35.338,7.474,47.732,19.779c12.318,12.408,19.754,28.906,19.779,47.745 C341.664,203.488,334.228,219.988,321.923,232.394z">
                                        </path>
                                    </g>
                                </g>
                            </svg>
                            <span class="text-xs font-bold">Parkir</span>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="toll" class="peer sr-only"
                            onchange="handleTypeChange('toll')">
                        <div
                            class="flex flex-col items-center justify-center p-3 border rounded-xl peer-checked:bg-green-50 peer-checked:border-green-500 peer-checked:text-green-600 transition">
                            <svg class="w-5 h-5 mb-1" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg"
                                xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="-20 -20 440 440" xml:space="preserve"
                                fill="currentColor">
                                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                <g id="SVGRepo_iconCarrier">
                                    <g>
                                        <g>
                                            <path
                                                d="M295.225,142.697c-9.9-44.668-19.801-89.336-29.707-134.003c-16.718,0-33.435,0-50.15,0 c2.389,44.668,4.781,89.336,7.172,134.003H295.225z">
                                            </path>
                                            <path
                                                d="M226.354,214.003c3.145,58.703,6.286,117.404,9.426,176.107c38.094,0,76.188,0,114.281,0 c-13.014-58.702-26.021-117.404-39.029-176.107H226.354z">
                                            </path>
                                            <path
                                                d="M183.435,8.694c-16.716,0-33.434,0-50.149,0c-9.902,44.667-19.798,89.335-29.698,134.003h72.682 C178.656,98.029,181.043,53.361,183.435,8.694z">
                                            </path>
                                            <path
                                                d="M48.742,390.11c38.096,0,76.188,0,114.281,0c3.152-58.702,6.293-117.404,9.43-176.107H87.785 C74.775,272.706,61.763,331.409,48.742,390.11z">
                                            </path>
                                            <path
                                                d="M394.176,161.212H4.628c-2.556,0-4.628,2.072-4.628,4.628v25.02c0,2.556,2.072,4.628,4.628,4.628h25.048v37.476 c0,2.556,2.071,4.629,4.627,4.629h24.996c2.117,0,3.964-1.438,4.484-3.488l9.818-38.615h251.602l9.816,38.615 c0.52,2.052,2.369,3.488,4.486,3.488h24.992c2.559,0,4.629-2.073,4.629-4.629v-37.476h25.049c2.557,0,4.629-2.072,4.629-4.628 v-25.02C398.805,163.284,396.732,161.212,394.176,161.212z">
                                            </path>
                                        </g>
                                    </g>
                                </g>
                            </svg>
                            <span class="text-xs font-bold">E-Toll</span>
                        </div>
                    </label>
                </div>
            </div>

            {{-- 2. NOMINAL (WAJIB) --}}
            <div class="mb-5">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Nominal (Rp) <span
                        class="text-red-500">*</span></label>
                <input type="number" name="amount" id="input_amount" oninput="validateForm()"
                    class="w-full border border-gray-300 rounded-xl p-3 text-lg font-bold focus:ring-blue-500"
                    placeholder="0" required>
            </div>

            {{-- 3. CATATAN (OPSIONAL) --}}
            <div class="mb-5">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Catatan Tambahan</label>
                <input type="text" name="note" class="w-full border border-gray-300 rounded-xl p-3 text-sm"
                    placeholder="Keterangan singkat...">
            </div>

            {{-- 4. KAMERA STRUK (WAJIB SEMUA TIPE) --}}
            <div class="mb-5">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Foto Struk / Bukti <span
                        class="text-red-500">*</span></label>

                {{-- Area Klik Kamera --}}
                <div onclick="openCamera('receipt')"
                    class="w-full h-32 border-2 border-dashed border-gray-300 rounded-xl flex flex-col items-center justify-center text-gray-400 cursor-pointer hover:bg-gray-50 transition"
                    id="box_receipt">
                    <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z">
                        </path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span class="text-xs font-bold">Ambil Foto Struk</span>
                </div>

                {{-- Preview Hasil Foto --}}
                <div id="preview_box_receipt" class="relative hidden">
                    <img id="img_receipt" class="w-full h-48 object-cover rounded-xl border border-gray-200 shadow-sm">
                    <button type="button" onclick="openCamera('receipt')"
                        class="absolute bottom-2 right-2 bg-black/60 text-white text-xs px-3 py-1 rounded-full font-bold backdrop-blur-sm">
                        Ulang Foto
                    </button>
                </div>
            </div>

            {{-- 5. KAMERA KM (KHUSUS BENSIN) --}}
            <div id="section_km" class="mb-8 hidden">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Foto Odometer (KM) <span
                        class="text-red-500">*</span></label>

                <div onclick="openCamera('km')"
                    class="w-full h-32 border-2 border-dashed border-gray-300 rounded-xl flex flex-col items-center justify-center text-gray-400 cursor-pointer hover:bg-gray-50 transition"
                    id="box_km">
                    <span class="text-2xl mb-1">🚗</span>
                    <span class="text-xs font-bold">Ambil Foto KM</span>
                </div>

                <div id="preview_box_km" class="relative hidden">
                    <img id="img_km" class="w-full h-48 object-cover rounded-xl border border-gray-200 shadow-sm">
                    <button type="button" onclick="openCamera('km')"
                        class="absolute bottom-2 right-2 bg-black/60 text-white text-xs px-3 py-1 rounded-full font-bold backdrop-blur-sm">
                        Ulang Foto
                    </button>
                </div>
            </div>

            {{-- TOMBOL SIMPAN (Disabled Awal) --}}
            <button type="submit" id="btn-submit" disabled
                class="w-full bg-gray-300 text-gray-500 py-3 rounded-xl font-bold shadow-none transition-all duration-300 disabled:cursor-not-allowed">
                Lengkapi Data Dulu...
            </button>
        </form>
    </div>

    {{-- MODAL KAMERA FULLSCREEN --}}
    <div id="cameraModal" class="fixed inset-0 z-[100] bg-black hidden flex flex-col">
        {{-- Viewport Video --}}
        <div class="relative flex-1 bg-black overflow-hidden">
            <video id="video" autoplay playsinline class="w-full h-full object-cover"></video>

            {{-- Tombol Switch Kamera --}}
            <button type="button" onclick="switchCamera()"
                class="absolute top-4 right-4 bg-white/20 backdrop-blur p-2 rounded-full text-white shadow-lg border border-white/30 z-20">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                    </path>
                </svg>
            </button>
        </div>

        {{-- Controls --}}
        <div class="h-32 bg-black flex justify-between items-center px-8 pb-4">
            <button type="button" onclick="closeCamera()"
                class="text-white font-bold text-sm bg-white/10 px-4 py-2 rounded-lg">Batal</button>

            {{-- Tombol Jepret --}}
            <button type="button" onclick="takePicture()"
                class="w-16 h-16 bg-white rounded-full border-4 border-gray-400 active:scale-90 transition"></button>

            <div class="w-16"></div> {{-- Spacer --}}
        </div>
    </div>
    <canvas id="canvas" class="hidden"></canvas>

    <script>
        let activeType = ''; // 'receipt' atau 'km'
        let currentStream;
        let facingMode = 'environment'; // Default kamera belakang
        let selectedType = ''; // 'gas', 'parking', 'toll'

        // --- 1. LOGIKA VALIDASI FORM ---
        function handleTypeChange(type) {
            selectedType = type;
            const kmSection = document.getElementById('section_km');

            if (type === 'gas') {
                kmSection.classList.remove('hidden');
            } else {
                kmSection.classList.add('hidden');
                // Reset Foto KM jika pindah tipe
                document.getElementById('photo_km_data').value = '';
                document.getElementById('preview_box_km').classList.add('hidden');
                document.getElementById('box_km').classList.remove('hidden');
            }
            validateForm();
        }

        function validateForm() {
            const amount = document.getElementById('input_amount').value;
            const receipt = document.getElementById('photo_receipt_data').value;
            const km = document.getElementById('photo_km_data').value;
            const btn = document.getElementById('btn-submit');

            let isValid = false;

            // Syarat 1: Tipe harus dipilih
            if (selectedType !== '') {
                // Syarat 2: Nominal harus diisi
                if (amount && amount > 0) {
                    // Syarat 3: Struk harus ada
                    if (receipt !== '') {
                        // Syarat 4: Jika Bensin, KM harus ada. Jika bukan, KM bebas.
                        if (selectedType === 'gas') {
                            if (km !== '') isValid = true;
                        } else {
                            isValid = true;
                        }
                    }
                }
            }

            // Update Tombol UI
            if (isValid) {
                btn.disabled = false;
                btn.innerHTML = "Simpan Pengeluaran";
                btn.className =
                    "w-full bg-blue-600 text-white py-3 rounded-xl font-bold shadow-lg transition-all duration-300 hover:bg-blue-700";
            } else {
                btn.disabled = true;
                btn.innerHTML = "Lengkapi Data Dulu...";
                btn.className =
                    "w-full bg-gray-300 text-gray-500 py-3 rounded-xl font-bold shadow-none transition-all duration-300 cursor-not-allowed";
            }
        }

        // --- 2. LOGIKA KAMERA (SWITCH & CAPTURE) ---
        const modal = document.getElementById('cameraModal');
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');

        function openCamera(type) {
            activeType = type;
            modal.classList.remove('hidden');
            startStream();
        }

        function startStream() {
            if (currentStream) currentStream.getTracks().forEach(t => t.stop());

            navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: facingMode
                }
            })
                .then(stream => {
                    currentStream = stream;
                    video.srcObject = stream;
                })
                .catch(e => alert("Error Kamera: " + e));
        }

        function switchCamera() {
            facingMode = facingMode === 'user' ? 'environment' : 'user';
            startStream();
        }

        function closeCamera() {
            modal.classList.add('hidden');
            if (currentStream) currentStream.getTracks().forEach(t => t.stop());
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

            const dataUrl = canvas.toDataURL('image/jpeg', 0.8); // Kompresi 0.8 biar ringan

            // Simpan Data & Tampilkan Preview
            document.getElementById('photo_' + activeType + '_data').value = dataUrl;
            document.getElementById('img_' + activeType).src = dataUrl;

            document.getElementById('preview_box_' + activeType).classList.remove('hidden');
            document.getElementById('box_' + activeType).classList.add('hidden');

            validateForm(); // Cek validasi setelah foto diambil
            closeCamera();
        }
    </script>
@endsection