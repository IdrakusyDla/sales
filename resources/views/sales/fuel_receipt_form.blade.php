@extends('layout')
@section('content')
    <div class="px-5 md:px-8 py-6 md:py-8">
        <div class="flex items-center gap-3 mb-6 md:mb-8">
            <a href="{{ route('sales.history.detail', $expense->daily_log_id) }}"
                class="text-gray-600 md:text-gray-500 md:hover:text-gray-700 md:transition shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl md:text-3xl font-bold md:font-extrabold text-gray-800 md:tracking-tight">Lampirkan Struk Bahan Bakar</h1>
                <p class="text-sm text-gray-600 md:text-gray-500 md:font-medium">Tanggal: {{ \Carbon\Carbon::parse($expense->dailyLog->date)->format('d M Y') }}</p>
            </div>
        </div>

        <div class="md:grid md:grid-cols-12 md:gap-6 md:items-start">

            {{-- INFO EXPENSE (DESKTOP: KANAN) --}}
            <div class="md:col-span-5 md:col-start-8 md:row-start-1 md:sticky md:top-8">
                <div class="bg-blue-50 rounded-xl p-4 mb-6 md:mb-0 border border-blue-200 md:rounded-[2rem] md:p-6 md:border-blue-100">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </span>
                        <p class="text-sm font-bold text-gray-700">Bahan Bakar (Auto)</p>
                    </div>
                    <p class="text-xs text-gray-500 font-medium uppercase tracking-wider mb-1">Nominal Reimburse</p>
                    <p class="text-2xl md:text-3xl font-extrabold text-blue-600 mb-3">Rp {{ number_format($expense->amount, 0, ',', '.') }}</p>
                    @if($expense->km_total)
                        <div class="flex items-center gap-2 pt-3 border-t border-blue-200/70">
                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1"></path>
                            </svg>
                            <p class="text-sm font-bold text-gray-700">Total KM: {{ number_format($expense->km_total, 2) }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- FORM / KAMERA (DESKTOP: KIRI) --}}
            <div class="md:col-span-7 md:col-start-1 md:row-start-1">
                <form action="{{ route('sales.fuel.receipt.store', $expense->id) }}" method="POST" id="form-fuel-receipt" class="card-form md:bg-white md:rounded-[2rem] md:shadow-sm md:border md:border-gray-100 md:p-6">
                    @csrf
                    <input type="hidden" name="photo_receipt" id="photo_receipt_data">

                    {{-- FOTO STRUK --}}
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Foto Struk Bahan Bakar *</label>
                        <div class="relative w-full h-64 md:h-[440px] bg-black rounded-2xl overflow-hidden">
                            <video id="video" autoplay playsinline class="w-full h-full object-cover"></video>
                            <canvas id="canvas" class="hidden w-full h-full object-cover"></canvas>
                            <button type="button" onclick="switchCamera()"
                                class="absolute top-4 right-4 bg-white/20 backdrop-blur p-2 rounded-full text-white">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
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
                    <div class="flex gap-3 mb-24 md:mb-0">
                        <a href="{{ route('sales.history.detail', $expense->daily_log_id) }}" class="flex-1 bg-gray-200 text-gray-700 py-4 rounded-xl font-bold text-sm text-center md:flex-none md:px-8 md:hover:bg-gray-300 md:transition">Batal</a>
                        <button type="submit" id="btn-submit"
                            class="flex-[2] bg-blue-600 text-white py-4 rounded-xl font-bold text-sm shadow-lg disabled:bg-gray-400 disabled:cursor-not-allowed md:flex-1 md:hover:bg-blue-700 md:transition"
                            disabled>Simpan Struk</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('partials.permission-check', ['requireLocation' => false])

    @section('scripts')
    <script>
        let stream;
        let photoTaken = false;
        let facingMode = 'environment';

        function initCamera() {
            if (stream) stream.getTracks().forEach(t => t.stop());
            navigator.mediaDevices.getUserMedia({
                video: { facingMode: facingMode }
            }).then(s => {
                stream = s;
                document.getElementById('video').srcObject = stream;
            }).catch(err => {
                if(typeof showPermissionGuard === 'function') showPermissionGuard('camera');
                else {
                    console.error('Error accessing camera:', err);
                    document.getElementById('photo-status').innerHTML = '<svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg> Error: Tidak dapat mengakses kamera';
                }
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
            document.getElementById('photo-status').innerHTML = '<svg class="w-5 h-5 inline text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Foto berhasil diambil';
            photoTaken = true;
            checkSubmit();
        }

        function checkSubmit() {
            document.getElementById('btn-submit').disabled = !photoTaken;
        }

        // Cleanup saat halaman ditutup
        window.addEventListener('beforeunload', () => {
            if (stream) stream.getTracks().forEach(t => t.stop());
        });

        // Camera init - request jika granted/prompt, tolak jika denied
        async function safeInitCamera() {
            if (navigator.permissions && navigator.permissions.query) {
                try {
                    const perm = await navigator.permissions.query({ name: 'camera' });
                    if (perm.state === 'denied') {
                        if(typeof showPermissionGuard === 'function') showPermissionGuard('camera');
                        return;
                    }
                } catch(e) {}
            }
            initCamera();
        }

        safeInitCamera();

        // Prevent double submit
        document.getElementById('form-fuel-receipt').addEventListener('submit', function() {
            document.getElementById('btn-submit').disabled = true;
            document.getElementById('btn-submit').textContent = 'Menyimpan...';
        });
    </script>
    @endsection
@endsection

