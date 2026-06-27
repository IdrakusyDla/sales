@props([
    'name' => 'photo',
    'label' => 'Foto Selfie *',
    'accent' => 'blue',        // blue | green | purple | amber
    'defaultFacing' => 'user', // user | environment
    'mirror' => true,          // flip horizontal (untuk selfie)
    'timestamp' => true,       // cetak timestamp di foto
    'hint' => null,            // teks bantuan di bawah
])

@php
    // Map accent ke class warna
    $ring = [
        'blue'   => 'bg-blue-500',
        'green'  => 'bg-green-500',
        'purple' => 'bg-purple-500',
        'amber'  => 'bg-amber-500',
    ][$accent] ?? 'bg-blue-500';

    $retakeBg = [
        'blue'   => 'bg-blue-600',
        'green'  => 'bg-green-600',
        'purple' => 'bg-purple-600',
        'amber'  => 'bg-amber-600',
    ][$accent] ?? 'bg-blue-600';

    $check = [
        'blue'   => 'text-blue-600',
        'green'  => 'text-green-600',
        'purple' => 'text-purple-600',
        'amber'  => 'text-amber-600',
    ][$accent] ?? 'text-blue-600';

    $uid = 'cam-' . uniqid();
@endphp

<div
    x-data="cameraCapture({
        uid: '{{ $uid }}',
        name: '{{ $name }}',
        defaultFacing: '{{ $defaultFacing }}',
        mirror: {{ $mirror ? 'true' : 'false' }},
        timestamp: {{ $timestamp ? 'true' : 'false' }}
    })"
    x-init="init()"
    data-camera="{{ $name }}"
    class="mb-6"
    @camera-updated.window="if ($event.detail.name === '{{ $name }}') { /* parent listens too */ }"
>

    {{-- Label --}}
    <label class="block text-sm font-bold text-gray-700 mb-2">{{ $label }}</label>

    {{-- Viewport --}}
    <div class="relative w-full h-64 md:h-80 bg-black rounded-2xl overflow-hidden md:mx-auto md:max-w-[569px]">
        <video x-ref="video" autoplay playsinline class="w-full h-full object-cover"></video>
        <canvas x-ref="canvas" class="hidden w-full h-full object-cover"></canvas>

        {{-- Switch camera --}}
        <button type="button" @click="switchCamera()" x-show="!taken"
            class="absolute top-4 right-4 bg-white/20 backdrop-blur p-2 rounded-full text-white">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
        </button>

        {{-- Snap button --}}
        <button type="button" @click="takePicture()" x-show="!taken"
            class="absolute bottom-4 left-1/2 transform -translate-x-1/2 w-16 h-16 bg-white rounded-full border-4 border-gray-300 shadow-lg flex items-center justify-center">
            <div class="w-12 h-12 {{ $ring }} rounded-full"></div>
        </button>

        {{-- Retake button --}}
        <button type="button" @click="retake()" x-show="taken"
            class="absolute bottom-4 left-1/2 transform -translate-x-1/2 {{ $retakeBg }} text-white px-6 py-3 rounded-xl font-bold text-sm shadow-lg flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            Ambil Ulang
        </button>
    </div>

    {{-- Status + hidden input --}}
    <p x-ref="status" class="text-xs text-gray-500 mt-2"></p>
    <input type="hidden" :name="name" x-ref="input">
</div>

@once
<script>
    window.cameraCapture = function (opts) {
        return {
            uid: opts.uid,
            name: opts.name,
            facing: opts.defaultFacing,
            mirror: opts.mirror,
            timestamp: opts.timestamp,
            stream: null,
            taken: false,

            init() {
                this.safeStart();
            },

            async safeStart() {
                if (navigator.permissions && navigator.permissions.query) {
                    try {
                        const p = await navigator.permissions.query({ name: 'camera' });
                        if (p.state === 'denied') {
                            if (typeof showPermissionGuard === 'function') showPermissionGuard('camera');
                            return;
                        }
                    } catch (e) {}
                }
                this.start();
            },

            start() {
                if (this.stream) this.stream.getTracks().forEach(t => t.stop());
                navigator.mediaDevices.getUserMedia({ video: { facingMode: this.facing } })
                    .then(s => {
                        this.stream = s;
                        this.$refs.video.srcObject = s;
                    })
                    .catch(() => {
                        if (typeof showPermissionGuard === 'function') showPermissionGuard('camera');
                    });
            },

            switchCamera() {
                this.facing = this.facing === 'user' ? 'environment' : 'user';
                this.start();
            },

            takePicture() {
                const video = this.$refs.video;
                const canvas = this.$refs.canvas;
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                const ctx = canvas.getContext('2d');

                if (this.mirror) {
                    ctx.translate(canvas.width, 0);
                    ctx.scale(-1, 1);
                }
                ctx.drawImage(video, 0, 0);
                ctx.setTransform(1, 0, 0, 1, 0, 0);

                if (this.timestamp) {
                    ctx.font = "bold 20px sans-serif";
                    ctx.fillStyle = "white";
                    ctx.fillText(new Date().toLocaleString('id-ID'), 20, canvas.height - 30);
                }

                this.$refs.input.value = canvas.toDataURL('image/png');
                video.classList.add('hidden');
                canvas.classList.remove('hidden');
                this.taken = true;
                this.$refs.status.innerHTML =
                    '<svg class="w-5 h-5 inline text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Foto berhasil diambil';

                if (this.stream) this.stream.getTracks().forEach(t => t.stop());

                this.notify(true);
            },

            retake() {
                this.taken = false;
                this.$refs.input.value = '';
                this.$refs.canvas.classList.add('hidden');
                this.$refs.video.classList.remove('hidden');
                this.$refs.status.textContent = '';
                this.start();
                this.notify(false);
            },

            notify(taken) {
                // Dispatch DOM event yang bubbles ke form (kompatibel dgn JS biasa & Alpine)
                this.$el.dispatchEvent(new CustomEvent('camera-updated', {
                    bubbles: true,
                    detail: { name: this.name, taken: taken }
                }));
                if (typeof window.__onCameraUpdate === 'function') {
                    window.__onCameraUpdate(this.name, taken);
                }
            }
        }
    }
</script>
@endonce

@if($hint)
    <p class="text-xs text-gray-500 -mt-4 mb-6">{{ $hint }}</p>
@endif
