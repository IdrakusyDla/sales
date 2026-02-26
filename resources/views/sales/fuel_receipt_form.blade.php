@extends('layout')
@section('content')
    <div class="px-5 md:px-8 py-6 md:py-8">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('sales.history.detail', $expense->daily_log_id) }}" class="text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold">Lampirkan Struk Bahan Bakar</h1>
                <p class="text-sm text-gray-600">Tanggal: {{ \Carbon\Carbon::parse($expense->dailyLog->date)->format('d M Y') }}</p>
            </div>
        </div>
        
        {{-- INFO EXPENSE --}}
        <div class="bg-blue-50 rounded-xl p-4 mb-6 border border-blue-200">
            <div class="flex justify-between items-center mb-2">
                <p class="text-sm font-bold text-gray-700"><svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Bahan Bakar (Auto)</p>
                <p class="text-lg font-bold text-blue-600">Rp {{ number_format($expense->amount, 0, ',', '.') }}</p>
            </div>
            @if($expense->km_total)
                <p class="text-xs text-gray-600">Total KM: {{ number_format($expense->km_total, 2) }} KM</p>
            @endif
        </div>

        <form action="{{ route('sales.fuel.receipt.store', $expense->id) }}" method="POST" id="form-fuel-receipt">
            @csrf
            <input type="hidden" name="photo_receipt" id="photo_receipt_data">

            {{-- FOTO STRUK --}}
            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-700 mb-2">Foto Struk Bahan Bakar *</label>
                <div class="relative w-full h-64 bg-black rounded-2xl overflow-hidden">
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
            <div class="flex gap-3 mb-24">
                <a href="{{ route('sales.history.detail', $expense->daily_log_id) }}" class="flex-1 bg-gray-200 text-gray-700 py-4 rounded-xl font-bold text-sm text-center">Batal</a>
                <button type="submit" id="btn-submit"
                    class="flex-[2] bg-blue-600 text-white py-4 rounded-xl font-bold text-sm shadow-lg disabled:bg-gray-400 disabled:cursor-not-allowed"
                    disabled>Simpan Struk</button>
            </div>
        </form>
    </div>

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
                console.error('Error accessing camera:', err);
                document.getElementById('photo-status').textContent = '<svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg> Error: Tidak dapat mengakses kamera';
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
            document.getElementById('photo-status').textContent = '<svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Foto berhasil diambil';
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

        initCamera();
    </script>
    @endsection
@endsection

