<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Sales App</title>
    <link rel="icon" href="{{ asset('logo.png?v=2') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('logo.png?v=2') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#2563eb">
    <style>
        /* Agar font mirip aplikasi */
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }

        /* Hilangkan scrollbar tapi tetap bisa scroll */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('SW registered!', reg))
                    .catch(err => console.log('SW registration failed', err));
            });
        }
    </script>
</head>

<body class="bg-gray-100 text-gray-800">

    {{-- CONTAINER HP (MAX WIDTH 480px) --}}
    <div class="max-w-[480px] mx-auto min-h-screen bg-white shadow-2xl relative overflow-hidden flex flex-col">

        {{-- KONTEN UTAMA (Scrollable) --}}
        <main class="flex-1 overflow-y-auto no-scrollbar pb-24 bg-gray-50">
            @if (session('success'))
                <div class="bg-green-500 text-white p-3 text-center text-sm font-bold sticky top-0 z-50">
                    <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> {{ session('success') }}
                </div>
            @endif

            @yield('content')
        </main>

        {{-- BOTTOM NAVIGATION (FIXED / STICKY) --}}
        <nav
            class="fixed left-1/2 transform -translate-x-1/2 w-full max-w-[480px] bg-white border-t border-gray-200 px-6 py-2 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]"
            style="bottom: 0; padding-bottom: calc(0.5rem + env(safe-area-inset-bottom, 0)); z-index: 50; pointer-events: auto;">

            {{-- MENU KHUSUS HRD & IT (3 TOMBOL: TIM - LAPORAN - PROFIL) --}}
            @if (in_array(Auth::user()->role, ['hrd', 'it', 'finance']))
                <div class="flex justify-between items-center px-6 py-1">

                    {{-- 1. Tombol Tim (Dashboard) --}}
                    <a href="{{ route('dashboard') }}"
                        class="flex flex-col items-center {{ request()->routeIs('dashboard') ? 'text-indigo-700' : 'text-gray-400' }} hover:text-indigo-700 transition">
                        <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                        <span class="text-[10px] font-bold">Tim Sales</span>
                    </a>

                    {{-- 2. Tombol Laporan --}}
                    <a href="{{ Auth::user()->role == 'it' ? route('it.export.page') : (Auth::user()->role == 'finance' ? route('finance.export.page') : route('hrd.export.page')) }}"
                        class="flex flex-col items-center {{ request()->routeIs('hrd.export.page') || request()->routeIs('it.export.page') || request()->routeIs('finance.export.page') ? 'text-indigo-700' : 'text-gray-400' }} hover:text-indigo-700 transition">
                        <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        <span class="text-[10px] font-bold">Laporan</span>
                    </a>

                    {{-- 3. Tombol Profil --}}
                    <a href="{{ route('profile.show') }}"
                        class="flex flex-col items-center {{ request()->routeIs('profile.show') ? 'text-indigo-700' : 'text-gray-400' }} hover:text-indigo-700 transition">
                        <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span class="text-[10px] font-bold">Profil</span>
                    </a>
                </div>
                {{-- B. MENU KHUSUS SALES & SUPERVISOR (5 TOMBOL) --}}
            @elseif (in_array(Auth::user()->role, ['sales', 'supervisor']))
                <div class="relative grid grid-cols-5 items-center justify-items-center">

                    {{-- 1. Home --}}
                    <a href="{{ route('dashboard') }}"
                        class="flex flex-col items-center group {{ request()->routeIs('dashboard') ? 'text-blue-600' : 'text-gray-400' }}">
                        <svg class="w-6 h-6 mb-1 group-active:scale-90 transition" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                            </path>
                        </svg>
                        <span class="text-[9px] font-bold">Home</span>
                    </a>

                    {{-- 2. Riwayat --}}
                    <a href="{{ route('sales.history') }}"
                        class="flex flex-col items-center group {{ request()->routeIs('sales.history') ? 'text-blue-600' : 'text-gray-400' }}">
                        <svg class="w-6 h-6 mb-1 group-active:scale-90 transition" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                            </path>
                        </svg>
                        <span class="text-[9px] font-bold">Riwayat</span>
                    </a>

                    {{-- 3. KAMERA (Floating Tengah) --}}
                    <div class="relative -top-8">
                        @php
                            $user = Auth::user();
                            // Ambil log TERAKHIR hari ini (untuk support multiple log/lembur)
                            $todayLog = \App\Models\DailyLog::where('user_id', $user->id)
                                ->where('date', \Carbon\Carbon::today())
                                ->orderBy('created_at', 'desc')
                                ->first();

                            if (!$todayLog) {
                                $cameraRoute = route('sales.absen.masuk');
                            } elseif (!$todayLog->hasEnded()) {
                                $cameraRoute = route('sales.absen.toko');
                            } else {
                                // Jika log terakhir sudah selesai, bisa absen masuk lagi (lembur)
                                $cameraRoute = route('sales.absen.masuk');
                            }
                        @endphp
                        <a href="{{ $cameraRoute }}"
                            class="flex items-center justify-center w-16 h-16 bg-blue-600 text-white rounded-full shadow-xl border-4 border-gray-50 transform active:scale-95 transition hover:bg-blue-700">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </a>
                    </div>

                    {{-- 4. TIM SALES (KHUSUS SUPERVISOR) --}}
                    @if(Auth::user()->role == 'supervisor')
                        <a href="{{ route('supervisor.dashboard') }}"
                            class="flex flex-col items-center group {{ request()->routeIs('supervisor.dashboard') ? 'text-blue-600' : 'text-gray-400' }}">
                            <svg class="w-6 h-6 mb-1 group-active:scale-90 transition" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                </path>
                            </svg>
                            <span class="text-[9px] font-bold">Tim Saya</span>
                        </a>
                    @else
                        <div></div>
                    @endif

                    {{-- 5. Profil / Password --}}
                    <a href="{{ route('profile.show') }}"
                        class="flex flex-col items-center group {{ request()->routeIs('profile.show') || request()->routeIs('password.edit') ? 'text-blue-600' : 'text-gray-400' }}">
                        <svg class="w-6 h-6 mb-1 group-active:scale-90 transition" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span class="text-[9px] font-bold">Profil</span>
                    </a>

                </div>
            @endif
        </nav>

        @yield('scripts')
        {{-- Global image modal for viewing photos inline --}}
        <div id="global-image-modal" class="fixed inset-0 z-[120] bg-black bg-opacity-60 hidden flex items-center justify-center">
            <div class="max-w-4xl w-full mx-4">
                <div class="bg-white rounded-xl overflow-hidden">
                    <div class="flex justify-end p-2">
                        <button onclick="closeGlobalImageModal()" class="text-gray-600 px-3 py-1">Tutup <svg class="w-4 h-4 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                    </div>
                    <div class="p-4" style="max-height:calc(100vh - 180px); overflow:auto;">
                        <img id="global-image-modal-img" src="" alt="Foto" style="max-height:calc(100vh - 220px); max-width:100%; object-fit:contain; display:block; margin:0 auto;" />
                    </div>
                </div>
            </div>
        </div>

        <script>
            function openImageModal(url) {
                var overlay = document.getElementById('global-image-modal');
                var img = document.getElementById('global-image-modal-img');
                img.src = url;
                overlay.classList.remove('hidden');
            }

            function closeGlobalImageModal() {
                var overlay = document.getElementById('global-image-modal');
                var img = document.getElementById('global-image-modal-img');
                img.src = '';
                overlay.classList.add('hidden');
            }

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    var overlay = document.getElementById('global-image-modal');
                    if (overlay && !overlay.classList.contains('hidden')) {
                        closeGlobalImageModal();
                    }
                }
            });
        </script>
</body>

</html>