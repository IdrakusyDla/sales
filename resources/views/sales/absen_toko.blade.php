@extends('layout')
@section('content')
    <div class="px-5 py-6">
        <h1 class="text-2xl font-bold mb-2">Lapor Kunjungan Toko</h1>
        <p class="text-sm text-gray-600 mb-6">Ambil foto selfie saat berada di toko</p>

        <form action="{{ route('sales.absen.toko.store') }}" method="POST" id="form-absen-toko">
            @csrf
            <input type="hidden" name="lat" id="lat">
            <input type="hidden" name="long" id="long">
            <input type="hidden" name="photo" id="photo_data">
            <input type="hidden" name="visit_id" id="visit_id" value="">

            {{-- PILIH KUNJUNGAN (Dari Rencana atau Dadakan) --}}
            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-700 mb-2">Pilih Kunjungan</label>
                
                {{-- Radio: Dari Rencana --}}
                <div class="mb-3">
                    <label class="flex items-center p-3 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-blue-500">
                        <input type="radio" name="visit_type" value="planned" checked onchange="toggleVisitType()" class="mr-3">
                        <span class="flex-1">
                            <span class="font-bold text-sm">Dari Rencana</span>
                            <p class="text-xs text-gray-500">Pilih toko yang sudah direncanakan</p>
                        </span>
                    </label>
                </div>

                {{-- Radio: Kunjungan Dadakan --}}
                <div class="mb-3">
                    <label class="flex items-center p-3 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-blue-500">
                        <input type="radio" name="visit_type" value="new" onchange="toggleVisitType()" class="mr-3">
                        <span class="flex-1">
                            <span class="font-bold text-sm">Kunjungan Dadakan</span>
                            <p class="text-xs text-gray-500">Toko yang tidak ada di rencana</p>
                        </span>
                    </label>
                </div>

                {{-- Dropdown Rencana (Jika dari rencana) --}}
                <div id="planned-visits" class="mt-3">
                    <select name="planned_visit_id" id="planned_visit_id" class="w-full border border-gray-300 rounded-xl p-3 text-sm">
                        <option value="">-- Pilih Toko dari Rencana --</option>
                        @foreach($plannedVisits as $visit)
                            <option value="{{ $visit->id }}">{{ $visit->client_name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Input Nama Toko Baru (Jika dadakan) --}}
                <div id="new-visit" class="mt-3 hidden">
                    <input type="text" name="new_client_name" id="new_client_name" placeholder="Nama Toko/Client"
                        class="w-full border border-gray-300 rounded-xl p-3 text-sm">
                </div>
            </div>

            {{-- FOTO SELFIE --}}
            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-700 mb-2">Foto Selfie *</label>
                <div class="relative w-full h-64 bg-black rounded-2xl overflow-hidden">
                    <video id="video" autoplay playsinline class="w-full h-full object-cover"></video>
                    <canvas id="canvas" class="hidden w-full h-full object-cover"></canvas>
                    <button type="button" onclick="takePicture()" id="btn-snap"
                        class="absolute bottom-4 left-1/2 transform -translate-x-1/2 w-16 h-16 bg-white rounded-full border-4 border-gray-300 shadow-lg flex items-center justify-center">
                        <div class="w-12 h-12 bg-blue-500 rounded-full"></div>
                    </button>
                </div>
                <p id="photo-status" class="text-xs text-gray-500 mt-2"></p>
            </div>

            {{-- STATUS KUNJUNGAN --}}
            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-700 mb-2">Status Kunjungan *</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex items-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-green-500">
                        <input type="radio" name="status" value="completed" required class="mr-3">
                        <span class="font-bold text-sm">Berhasil</span>
                    </label>
                    <label class="flex items-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-red-500">
                        <input type="radio" name="status" value="failed" required class="mr-3">
                        <span class="font-bold text-sm">Gagal</span>
                    </label>
                </div>
            </div>

            {{-- KETERANGAN (Hanya untuk status Berhasil) --}}
            <div class="mb-6 hidden" id="notes-field">
                <label class="block text-sm font-bold text-gray-700 mb-2">Keterangan</label>
                <textarea name="notes" id="notes" rows="3" placeholder="Catatan kunjungan..."
                    class="w-full border border-gray-300 rounded-xl p-3 text-sm"></textarea>
            </div>

            {{-- ALASAN (Jika Gagal) --}}
            <div class="mb-6 hidden" id="reason-field">
                <label class="block text-sm font-bold text-gray-700 mb-2">Alasan Gagal *</label>
                <textarea name="reason" id="reason" rows="2" placeholder="Mengapa kunjungan gagal?"
                    class="w-full border border-gray-300 rounded-xl p-3 text-sm"></textarea>
            </div>

            {{-- TOMBOL SUBMIT --}}
            <div class="flex gap-3 mb-24">
                <a href="{{ route('dashboard') }}" class="flex-1 bg-gray-200 text-gray-700 py-4 rounded-xl font-bold text-sm text-center">Batal</a>
                <button type="submit" id="btn-submit"
                    class="flex-[2] bg-blue-600 text-white py-4 rounded-xl font-bold text-sm shadow-lg disabled:bg-gray-400 disabled:cursor-not-allowed"
                    disabled>Kirim Laporan</button>
            </div>
        </form>
    </div>

    @section('scripts')
    <script>
        let stream;
        let photoTaken = false;

        function initCamera() {
            if (stream) stream.getTracks().forEach(t => t.stop());
            navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user' }
            }).then(s => {
                stream = s;
                document.getElementById('video').srcObject = stream;
            });
        }

        function takePicture() {
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
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
            document.getElementById('btn-snap').classList.add('hidden');
            document.getElementById('photo-status').innerHTML = '<svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Foto berhasil diambil';
            photoTaken = true;
            checkSubmit();
        }

        function toggleVisitType() {
            const visitType = document.querySelector('input[name="visit_type"]:checked').value;
            const plannedDiv = document.getElementById('planned-visits');
            const newDiv = document.getElementById('new-visit');
            
            if (visitType === 'planned') {
                plannedDiv.classList.remove('hidden');
                newDiv.classList.add('hidden');
                document.getElementById('new_client_name').removeAttribute('required');
                document.getElementById('planned_visit_id').setAttribute('required', 'required');
            } else {
                plannedDiv.classList.add('hidden');
                newDiv.classList.remove('hidden');
                document.getElementById('planned_visit_id').removeAttribute('required');
                document.getElementById('new_client_name').setAttribute('required', 'required');
            }
        }

        function checkSubmit() {
            const btn = document.getElementById('btn-submit');
            const visitType = document.querySelector('input[name="visit_type"]:checked').value;
            let valid = photoTaken && document.querySelector('input[name="status"]:checked');
            
            if (visitType === 'planned') {
                valid = valid && document.getElementById('planned_visit_id').value;
            } else {
                valid = valid && document.getElementById('new_client_name').value;
            }
            
            btn.disabled = !valid;
        }

        // Handle status change untuk show/hide reason dan notes
        document.querySelectorAll('input[name="status"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const reasonField = document.getElementById('reason-field');
                const reasonInput = document.getElementById('reason');
                const notesField = document.getElementById('notes-field');
                const notesInput = document.getElementById('notes');
                
                if (this.value === 'failed') {
                    // Tampilkan alasan gagal, sembunyikan keterangan
                    reasonField.classList.remove('hidden');
                    reasonInput.setAttribute('required', 'required');
                    notesField.classList.add('hidden');
                    notesInput.value = ''; // Clear notes jika ada
                    notesInput.removeAttribute('required');
                } else {
                    // Tampilkan keterangan, sembunyikan alasan gagal
                    reasonField.classList.add('hidden');
                    reasonInput.removeAttribute('required');
                    reasonInput.value = ''; // Clear reason jika ada
                    notesField.classList.remove('hidden');
                    notesInput.removeAttribute('required');
                }
                checkSubmit();
            });
        });

        // Handle visit selection
        document.getElementById('planned_visit_id').addEventListener('change', function() {
            document.getElementById('visit_id').value = this.value || 'new';
            checkSubmit();
        });

        document.getElementById('new_client_name').addEventListener('input', checkSubmit);
        document.querySelectorAll('input[name="status"]').forEach(r => r.addEventListener('change', checkSubmit));

        // GPS
        navigator.geolocation.getCurrentPosition(p => {
            document.getElementById('lat').value = p.coords.latitude;
            document.getElementById('long').value = p.coords.longitude;
        });

        // Prevent double submit
        const form = document.getElementById('form-absen-toko');
        const submitBtn = document.getElementById('btn-submit');
        let isSubmitting = false;

        form.addEventListener('submit', function(e) {
            if (isSubmitting) {
                e.preventDefault();
                return false;
            }
            isSubmitting = true;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="inline-block animate-spin mr-2"><svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg></span> Sedang diproses...';
            submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
        });

        initCamera();
    </script>
    @endsection
@endsection

