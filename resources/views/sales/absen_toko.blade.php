@extends('layout')
@section('content')
    <div class="px-5 md:px-8 py-6 md:py-8 md:bg-slate-50/50 md:min-h-screen">

        {{-- HEADER DESKTOP --}}
        <div class="hidden md:block mb-6">
            <h1 class="text-xl font-extrabold text-gray-800 tracking-tight mb-1">Lapor Kunjungan Toko</h1>
            <p class="text-sm text-gray-500">Ambil foto selfie saat berada di toko</p>
        </div>

        {{-- HEADER MOBILE --}}
        <div class="md:hidden mb-6">
            <h1 class="text-2xl font-bold mb-2">Lapor Kunjungan Toko</h1>
            <p class="text-sm text-gray-600">Ambil foto selfie saat berada di toko</p>
        </div>

        <div class="md:grid md:grid-cols-12 md:gap-6">

            {{-- ========================================== --}}
            {{-- KOLOM KIRI: FORM UTAMA (9/12)             --}}
            {{-- ========================================== --}}
            <div class="md:col-span-9">
                <div class="md:bg-white md:rounded-[2rem] md:shadow-sm md:border md:border-gray-100 md:overflow-hidden md:p-8">

        <form action="{{ route('sales.absen.toko.store') }}" method="POST" id="form-absen-toko" class="card-form">
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
                <div class="relative w-full h-64 md:h-80 bg-black rounded-2xl overflow-hidden md:mx-auto md:max-w-[569px]">
                    <video id="video" autoplay playsinline class="w-full h-full object-cover"></video>
                    <canvas id="canvas" class="hidden w-full h-full object-cover"></canvas>
                    <button type="button" onclick="switchCamera()" id="btn-switch"
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
                    {{-- Tombol ambil ulang foto --}}
                    <button type="button" onclick="retakePicture()" id="btn-retake"
                        class="hidden absolute bottom-4 left-1/2 transform -translate-x-1/2 bg-blue-600 text-white px-6 py-3 rounded-xl font-bold text-sm shadow-lg flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                            </path>
                        </svg> Ambil Ulang
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
            </div>

            {{-- ========================================== --}}
            {{-- KOLOM KANAN: INFORMATION CARDS (DESKTOP)  --}}
            {{-- ========================================== --}}
            <div class="hidden md:block md:col-span-3 md:space-y-6">

                {{-- Card 1: Tips Kunjungan --}}
                <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Tips Kunjungan
                    </h3>
                    <div class="bg-blue-50 rounded-xl p-4">
                        <ul class="text-xs text-blue-700 space-y-2">
                            <li class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span>Pastikan wajah & toko terlihat jelas</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span>Ambil foto saat sudah berada di dalam toko</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span>Lengkapi status & keterangan dengan benar</span>
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- Card 2: Kunjungan Tersisa --}}
                <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                        Kunjungan Tersisa
                    </h3>
                    <div class="bg-gray-50 rounded-xl p-4">
                        @if($plannedVisits->count() > 0)
                            <p class="text-xs text-gray-600 mb-2">Toko yang belum dikunjungi:</p>
                            <ul class="text-xs text-gray-700 space-y-1">
                                @foreach($plannedVisits as $visit)
                                    <li class="flex items-start gap-2">
                                        <span class="text-gray-400">•</span>
                                        <span>{{ $visit->client_name }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            @if($failedVisits > 0)
                                <p class="text-xs text-gray-600">
                                    <svg class="w-4 h-4 inline mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Semua toko sudah dikunjungi
                                </p>
                                <p class="text-xs text-red-600 mt-1 font-medium">{{ $failedVisits }} kunjungan gagal</p>
                            @else
                                <p class="text-xs text-gray-500">
                                    <svg class="w-4 h-4 inline mr-1 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Semua target tercapai!
                                </p>
                            @endif
                        @endif
                    </div>
                </div>

                {{-- Card 3: Progress Hari Ini --}}
                <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Progress Hari Ini
                    </h3>
                    <div class="bg-green-50 rounded-xl p-4">
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-green-600">Dikunjungi:</span>
                                <span class="font-bold text-green-700">{{ $completedVisits + $failedVisits }} / {{ $totalVisits }}</span>
                            </div>
                            <div class="pt-3 border-t border-green-200 space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-green-600">Berhasil:</span>
                                    <span class="text-xs font-bold text-green-700">{{ $completedVisits }}</span>
                                </div>
                                @if($failedVisits > 0)
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-red-600">Gagal:</span>
                                    <span class="text-xs font-bold text-red-600">{{ $failedVisits }}</span>
                                </div>
                                @endif
                                @if($plannedVisits->count() > 0)
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-gray-500">Belum dikunjungi:</span>
                                    <span class="text-xs font-bold text-gray-600">{{ $plannedVisits->count() }}</span>
                                </div>
                                @endif
                            </div>
                            @if($totalVisits > 0 && $completedVisits == $totalVisits)
                            <div class="pt-3 border-t border-green-200">
                                <p class="text-xs text-green-600">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Semua target tercapai!
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    @include('partials.permission-check', ['requireLocation' => true])

    @section('scripts')
    <script>
        let stream;
        let photoTaken = false;
        let facingMode = 'user';

        function initCamera() {
            if (stream) stream.getTracks().forEach(t => t.stop());
            navigator.mediaDevices.getUserMedia({
                video: { facingMode: facingMode }
            }).then(s => {
                stream = s;
                document.getElementById('video').srcObject = stream;
            }).catch(err => {
                if(typeof showPermissionGuard === 'function') showPermissionGuard('camera');
            });
        }

        function switchCamera() {
            facingMode = facingMode === 'user' ? 'environment' : 'user';
            initCamera();
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
            document.getElementById('btn-switch').classList.add('hidden');
            document.getElementById('btn-retake').classList.remove('hidden');
            document.getElementById('photo-status').innerHTML = '<svg class="w-5 h-5 inline text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Foto berhasil diambil';
            photoTaken = true;

            // Stop camera after photo taken
            if (stream) stream.getTracks().forEach(t => t.stop());

            checkSubmit();
        }

        function retakePicture() {
            // Reset photo state
            photoTaken = false;
            document.getElementById('photo_data').value = '';
            document.getElementById('canvas').classList.add('hidden');
            document.getElementById('video').classList.remove('hidden');
            document.getElementById('btn-snap').classList.remove('hidden');
            document.getElementById('btn-switch').classList.remove('hidden');
            document.getElementById('btn-retake').classList.add('hidden');
            document.getElementById('photo-status').textContent = '';

            initCamera();
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

        // GPS - request jika granted/prompt, tolak jika denied
        async function initGPS() {
            if (navigator.permissions && navigator.permissions.query) {
                try {
                    const perm = await navigator.permissions.query({ name: 'geolocation' });
                    if (perm.state === 'denied') {
                        if(typeof showPermissionGuard === 'function') showPermissionGuard('location');
                        return;
                    }
                } catch(e) {}
            }
            navigator.geolocation.getCurrentPosition(p => {
                document.getElementById('lat').value = p.coords.latitude;
                document.getElementById('long').value = p.coords.longitude;
            }, err => {
                if(typeof showPermissionGuard === 'function') showPermissionGuard('location');
            });
        }

        // Init camera - request jika granted/prompt, tolak jika denied
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
            submitBtn.innerHTML = '<svg class="w-5 h-5 inline animate-spin mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Sedang diproses...';
            submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
        });

        safeInitCamera();
        initGPS();
    </script>
    @endsection
@endsection
