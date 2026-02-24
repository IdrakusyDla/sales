@extends('layout')
@section('content')
    <div class="px-5 py-6">
        <h1 class="text-2xl font-bold mb-2">Tambah Pengeluaran</h1>
        <p class="text-sm text-gray-600 mb-4">Catat pengeluaran untuk tanggal:
            {{ \Carbon\Carbon::parse($dailyLog->date)->format('d M Y') }}</p>

        @if($deadline)
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-3 mb-6">
                <p class="text-xs text-yellow-800">
                    <strong>Batas pengisian:</strong> {{ \Carbon\Carbon::parse($deadline)->format('d M Y') }}
                </p>
            </div>
        @endif

        {{-- LIST PENGELUARAN YANG SUDAH ADA --}}
        @if($expenses->count() > 0)
            <div class="mb-6">
                <p class="text-sm font-bold text-gray-700 mb-2">Pengeluaran yang sudah dicatat:</p>
                <div class="space-y-2">
                    @foreach($expenses as $expense)
                        <div class="bg-gray-50 rounded-xl p-3 flex justify-between items-center">
                            <div>
                                <p class="font-bold text-sm">{{ ucfirst($expense->type) }}</p>
                                @if($expense->note)
                                    <p class="text-xs text-gray-500">{{ $expense->note }}</p>
                                @endif
                            </div>
                            <p class="font-bold text-green-600">Rp {{ number_format($expense->amount, 0, ',', '.') }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <form action="{{ route('sales.reimburse.store', $dailyLog->id) }}" method="POST" id="form-reimburse">
            @csrf
            <input type="hidden" name="photo_receipt" id="photo_receipt_data">

            {{-- JENIS PENGELUARAN --}}
            <div class="mb-4">
                <label class="block text-sm font-bold text-gray-700 mb-2">Jenis Pengeluaran *</label>
                <select name="type" required class="w-full border border-gray-300 rounded-xl p-3 text-sm">
                    <option value="">-- Pilih Jenis --</option>
                    <option value="parking">🅿️ Parkir</option>
                    <option value="hotel">🏨 Hotel</option>
                    <option value="toll">🛣️ Tol</option>
                    <option value="transport">🚗 Transport Tambahan</option>
                    <option value="other">📝 Lainnya</option>
                </select>
            </div>

            {{-- OPSI AUTO RECEIPT (KHUSUS PARKIR) --}}
            <div id="auto-receipt-section" class="mb-6 hidden">
                <label class="flex items-center space-x-3 p-3 bg-blue-50 border border-blue-100 rounded-xl cursor-pointer">
                    <input type="checkbox" name="generate_receipt" id="generate_receipt" value="1" class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500">
                    <div>
                        <span class="block text-sm font-bold text-gray-700">Tidak ada struk?</span>
                        <span class="text-xs text-gray-500">Centang untuk buat struk otomatis</span>
                    </div>
                </label>
            </div>

            {{-- DETAIL PARKIR (AUTO RECEIPT) --}}
            <div id="parking-details-section" class="mb-6 hidden space-y-4">
                <div class="p-4 bg-gray-50 border border-gray-200 rounded-xl">
                    <h3 class="text-sm font-bold text-gray-800 mb-3 block border-b pb-2">Detail Untuk Struk</h3>
                    
                    <div class="mb-3">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">No. Kendaraan *</label>
                        <input type="text" name="license_plate" placeholder="Contoh: B 1234 ABC" 
                            class="w-full border border-gray-300 rounded-lg p-2 text-sm uppercase">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Lokasi Parkir *</label>
                        <input type="text" name="parking_location" placeholder="Contoh: Mall Grand Indonesia" 
                            class="w-full border border-gray-300 rounded-lg p-2 text-sm">
                    </div>
                </div>
            </div>

            {{-- NOMINAL --}}
            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-700 mb-2">Nominal (Rp) *</label>
                <input type="number" name="amount" step="0.01" min="0" required
                    class="w-full border border-gray-300 rounded-xl p-4 text-lg font-bold focus:ring-2 focus:ring-blue-500"
                    placeholder="Contoh: 50000">
            </div>

            {{-- CATATAN --}}
            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-700 mb-2">Catatan</label>
                <textarea name="note" rows="3" placeholder="Keterangan pengeluaran..."
                    class="w-full border border-gray-300 rounded-xl p-3 text-sm"></textarea>
            </div>

            {{-- FOTO STRUK --}}
            <div class="mb-6" id="photo-section">
                <label class="block text-sm font-bold text-gray-700 mb-2">Foto Struk/Bukti Pembayaran *</label>
                <div class="relative w-full h-64 bg-black rounded-2xl overflow-hidden">
                    <video id="video" autoplay playsinline class="w-full h-full object-cover"></video>
                    <canvas id="canvas" class="hidden w-full h-full object-cover"></canvas>
                    <button type="button" onclick="switchCamera()"
                        class="absolute top-4 right-4 bg-white/20 backdrop-blur p-2 rounded-full text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                            </path>
                        </svg>
                    </button>
                    <button type="button" onclick="takePicture()" id="btn-snap"
                        class="absolute bottom-4 left-1/2 transform -translate-x-1/2 w-16 h-16 bg-white rounded-full border-4 border-gray-300 shadow-lg flex items-center justify-center">
                        <div class="w-12 h-12 bg-blue-500 rounded-full"></div>
                    </button>
                </div>
                <p id="photo-status" class="text-xs text-gray-500 mt-2"></p>
            </div>

            {{-- TOMBOL SUBMIT --}}
            <div class="flex gap-3 mb-24">
                <a href="{{ route('sales.history') }}"
                    class="flex-1 bg-gray-200 text-gray-700 py-4 rounded-xl font-bold text-sm text-center">Batal</a>
                <button type="submit" id="btn-submit"
                    class="flex-[2] bg-blue-600 text-white py-4 rounded-xl font-bold text-sm shadow-lg disabled:bg-gray-400 disabled:cursor-not-allowed"
                    disabled>Simpan</button>
            </div>
        </form>
    </div>

    @section('scripts')
        <script>
            let stream;
            let photoTaken = false;
            let facingMode = 'environment';
            const typeSelect = document.querySelector('select[name="type"]');
            const autoReceiptSection = document.getElementById('auto-receipt-section');
            const parkingDetailsSection = document.getElementById('parking-details-section');
            const generateReceiptCheckbox = document.getElementById('generate_receipt');
            const photoSection = document.getElementById('photo-section');
            const btnSubmit = document.getElementById('btn-submit');
            const licenseInput = document.querySelector('input[name="license_plate"]');
            const locationInput = document.querySelector('input[name="parking_location"]');

            // Event Listener untuk Jenis Pengeluaran
            typeSelect.addEventListener('change', function () {
                if (this.value === 'parking') {
                    autoReceiptSection.classList.remove('hidden');
                } else {
                    autoReceiptSection.classList.add('hidden');
                    generateReceiptCheckbox.checked = false;
                    toggleReceiptMode();
                }
            });

            // Event Listener untuk Checkbox Auto Receipt
            generateReceiptCheckbox.addEventListener('change', toggleReceiptMode);

            function toggleReceiptMode() {
                const isAuto = generateReceiptCheckbox.checked;

                if (isAuto) {
                    // Mode Otomatis: Sembunyikan Foto, Tampilkan Detail Parkir
                    photoSection.classList.add('hidden');
                    parkingDetailsSection.classList.remove('hidden');

                    // Set requirements
                    licenseInput.required = true;
                    locationInput.required = true;

                    // Cek validasi form manual
                    checkSubmit();
                } else {
                    // Mode Foto: Tampilkan Foto, Sembunyikan Detail Parkir
                    photoSection.classList.remove('hidden');
                    parkingDetailsSection.classList.add('hidden');

                    // Unset requirements
                    licenseInput.required = false;
                    locationInput.required = false;

                    checkSubmit();
                }
            }

            function initCamera() {
                if (stream) stream.getTracks().forEach(t => t.stop());
                // Cek apakah element video visible (jika mode auto, video hidden, jangan init camera)
                if (photoSection.classList.contains('hidden')) return;

                navigator.mediaDevices.getUserMedia({
                    video: { facingMode: facingMode }
                }).then(s => {
                    stream = s;
                    document.getElementById('video').srcObject = stream;
                }).catch(err => {
                    console.log("Camera error: ", err);
                });
            }

            function switchCamera() {
                facingMode = facingMode === 'environment' ? 'user' : 'environment';
                initCamera();
            }

            function takePicture() {
                const video = document.getElementById('video');
                const canvas = document.getElementById('canvas');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0);
                ctx.font = "bold 20px sans-serif";
                ctx.fillStyle = "white";
                ctx.fillText(new Date().toLocaleString('id-ID'), 20, canvas.height - 30);

                document.getElementById('photo_receipt_data').value = canvas.toDataURL('image/png');
                video.classList.add('hidden');
                canvas.classList.remove('hidden');
                document.getElementById('btn-snap').classList.add('hidden');
                document.getElementById('photo-status').textContent = '✅ Foto berhasil diambil';
                photoTaken = true;
                checkSubmit();
            }

            function checkSubmit() {
                if (generateReceiptCheckbox.checked) {
                    // Jika mode auto, tombol submit aktif jika input sudah diisi (handled by browser validator actually, but we can enable btn)
                    btnSubmit.disabled = false;
                } else {
                    // Jika mode foto, harus sudah foto
                    btnSubmit.disabled = !photoTaken;
                    // Re-init camera jika visible dan belum ada foto
                    if (!photoSection.classList.contains('hidden') && !photoTaken) {
                        initCamera();
                    }
                }
            }

            // Init awal
            initCamera();
        </script>
    @endsection
@endsection