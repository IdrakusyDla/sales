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

        /* Sidebar nav link active indicator */
        .sidebar-link {
            transition: all 0.15s ease;
        }

        .sidebar-link:hover {
            background-color: rgba(59, 130, 246, 0.08);
        }

        .sidebar-link.active {
            background-color: rgba(59, 130, 246, 0.1);
            color: #2563eb;
            border-right: 3px solid #2563eb;
        }

        /* Desktop scrollbar styling */
        @media (min-width: 768px) {
            .desktop-main::-webkit-scrollbar {
                width: 6px;
            }

            .desktop-main::-webkit-scrollbar-track {
                background: transparent;
            }

            .desktop-main::-webkit-scrollbar-thumb {
                background-color: rgba(0, 0, 0, 0.15);
                border-radius: 3px;
            }

            .desktop-main::-webkit-scrollbar-thumb:hover {
                background-color: rgba(0, 0, 0, 0.25);
            }

            /* === GLOBAL DESKTOP RESPONSIVE RULES === */

            /* Wider padding for all content pages on desktop */
            .desktop-main>.px-5 {
                padding-left: 2rem;
                padding-right: 2rem;
            }

            /* Remove excessive bottom padding meant for mobile bottom nav */
            .desktop-main .pb-24 {
                padding-bottom: 2rem;
            }

            .desktop-main .mb-24 {
                margin-bottom: 2rem;
            }

            /* Camera/video containers: center and enforce mobile-like portrait aspect ratio */
            .desktop-main .h-\[50vh\],
            .desktop-main .h-\[60vh\] {
                height: 600px !important;
                width: 450px !important;
                max-width: 100%;
                margin-left: auto;
                margin-right: auto;
                border-radius: 24px !important;
            }

            .desktop-main video {
                object-fit: cover;
                border-radius: 24px !important;
            }

            /* Forms: readable max width on desktop */
            .desktop-main form {
                max-width: 720px;
            }

            /* Images in detail views: constrain to readable size */
            .desktop-main img[alt="Foto Absen Masuk"],
            .desktop-main img[alt="Foto Absen Keluar"],
            .desktop-main img[alt="Foto Odometer Awal"],
            .desktop-main img[alt="Foto Odometer Akhir"],
            .desktop-main img[alt="Foto Kunjungan"],
            .desktop-main img[alt="Foto Bon"] {
                max-width: 400px;
            }

            /* Page headers: larger on desktop */
            .desktop-main h1 {
                font-size: 1.75rem;
            }
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

    {{-- ============================================ --}}
    {{-- LAYOUT UTAMA (MOBILE & DESKTOP DIGABUNG) --}}
    {{-- ============================================ --}}
    <div class="flex min-h-screen w-full bg-gray-100 md:bg-gray-50 justify-center md:justify-start">

        {{-- MAIN CONTENT WRAPPER --}}
        <main
            class="flex-1 min-h-screen flex flex-col relative md:ml-64 w-full max-w-[480px] md:max-w-none bg-white md:bg-transparent shadow-2xl md:shadow-none overflow-hidden order-2 md:order-none">

            {{-- KONTEN UTAMA --}}
            <div
                class="flex-1 overflow-y-auto no-scrollbar pb-24 md:pb-8 desktop-main relative w-full md:bg-gray-50 bg-white">
                @if (session('success'))
                    <div class="bg-green-500 text-white p-3 text-center text-sm font-bold sticky top-0 z-50">
                        <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg> {{ session('success') }}
                    </div>
                @endif

                @yield('content')
            </div>

            {{-- BOTTOM NAVIGATION (Hanya Mobile) --}}
            <nav class="md:hidden fixed left-1/2 transform -translate-x-1/2 w-full max-w-[480px] bg-white border-t border-gray-200 px-6 py-2 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]"
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
        </main>

        {{-- ============================================ --}}
        {{-- SIDEBAR DESKTOP (Sembunyi di Mobile) --}}
        {{-- ============================================ --}}
        <aside
            class="hidden md:flex w-64 bg-white border-r border-gray-200 fixed top-0 left-0 h-screen flex-col shadow-sm z-40">

            {{-- Logo / App Name --}}
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('logo.png?v=2') }}" alt="Logo" class="w-10 h-10 rounded-xl">
                    <div>
                        <h1 class="text-lg font-bold text-gray-800">Sales App</h1>
                        <p class="text-xs text-gray-500">{{ ucfirst(Auth::user()->role) }}</p>
                    </div>
                </div>
            </div>

            {{-- Navigation Links --}}
            <nav class="flex-1 py-4 overflow-y-auto">

                @if (in_array(Auth::user()->role, ['hrd', 'it', 'finance']))
                    {{-- HRD / IT / Finance sidebar menu --}}
                    <div class="px-3 mb-2">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider px-3 mb-2">Menu Utama</p>
                    </div>

                    <a href="{{ route('dashboard') }}"
                        class="sidebar-link flex items-center gap-3 px-6 py-3 text-sm font-medium {{ request()->routeIs('dashboard') || request()->routeIs('hrd.*') && !request()->routeIs('hrd.export.page') ? 'active' : 'text-gray-600' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                        Tim Sales
                    </a>

                    <a href="{{ Auth::user()->role == 'it' ? route('it.export.page') : (Auth::user()->role == 'finance' ? route('finance.export.page') : route('hrd.export.page')) }}"
                        class="sidebar-link flex items-center gap-3 px-6 py-3 text-sm font-medium {{ request()->routeIs('hrd.export.page') || request()->routeIs('it.export.page') || request()->routeIs('finance.export.page') ? 'active' : 'text-gray-600' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        Laporan
                    </a>

                    <a href="{{ route('profile.show') }}"
                        class="sidebar-link flex items-center gap-3 px-6 py-3 text-sm font-medium {{ request()->routeIs('profile.show') ? 'active' : 'text-gray-600' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Profil
                    </a>

                @elseif (in_array(Auth::user()->role, ['sales', 'supervisor']))
                    {{-- Sales / Supervisor sidebar menu --}}
                    <div class="px-3 mb-2">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider px-3 mb-2">Menu Utama</p>
                    </div>

                    <a href="{{ route('dashboard') }}"
                        class="sidebar-link flex items-center gap-3 px-6 py-3 text-sm font-medium {{ request()->routeIs('dashboard') ? 'active' : 'text-gray-600' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                            </path>
                        </svg>
                        Home
                    </a>

                    <a href="{{ route('sales.history') }}"
                        class="sidebar-link flex items-center gap-3 px-6 py-3 text-sm font-medium {{ request()->routeIs('sales.history') || request()->routeIs('sales.history.*') ? 'active' : 'text-gray-600' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                            </path>
                        </svg>
                        Riwayat
                    </a>

                    @php
                        $user = Auth::user();
                        $todayLogDesktop = \App\Models\DailyLog::where('user_id', $user->id)
                            ->where('date', \Carbon\Carbon::today())
                            ->orderBy('created_at', 'desc')
                            ->first();

                        if (!$todayLogDesktop) {
                            $cameraRouteDesktop = route('sales.absen.masuk');
                        } elseif (!$todayLogDesktop->hasEnded()) {
                            $cameraRouteDesktop = route('sales.absen.toko');
                        } else {
                            $cameraRouteDesktop = route('sales.absen.masuk');
                        }
                    @endphp
                    <a href="{{ $cameraRouteDesktop }}"
                        class="sidebar-link flex items-center gap-3 px-6 py-3 text-sm font-medium {{ request()->routeIs('sales.absen.*') ? 'active' : 'text-gray-600' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z">
                            </path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Absensi
                    </a>

                    @if(Auth::user()->role == 'supervisor')
                        <a href="{{ route('supervisor.dashboard') }}"
                            class="sidebar-link flex items-center gap-3 px-6 py-3 text-sm font-medium {{ request()->routeIs('supervisor.dashboard') || request()->routeIs('supervisor.*') ? 'active' : 'text-gray-600' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                </path>
                            </svg>
                            Tim Saya
                        </a>
                    @endif

                    <a href="{{ route('profile.show') }}"
                        class="sidebar-link flex items-center gap-3 px-6 py-3 text-sm font-medium {{ request()->routeIs('profile.show') || request()->routeIs('password.edit') ? 'active' : 'text-gray-600' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Profil
                    </a>
                @endif
            </nav>

            {{-- User Info Bottom --}}
            <div class="p-4 border-t border-gray-100">
                <div class="flex items-center gap-3">
                    <div
                        class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-sm">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-gray-800 truncate">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ Auth::user()->username }}</p>
                    </div>
                </div>
            </div>
        </aside>
    </div>

    @yield('scripts')

    {{-- Global image modal for viewing photos inline --}}
    <div id="global-image-modal"
        class="fixed inset-0 z-[120] bg-black bg-opacity-60 hidden flex items-center justify-center">
        <div class="max-w-4xl w-full mx-4">
            <div class="bg-white rounded-xl overflow-hidden">
                <div class="flex justify-end p-2">
                    <button onclick="closeGlobalImageModal()" class="text-gray-600 px-3 py-1">Tutup <svg
                            class="w-4 h-4 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg></button>
                </div>
                <div class="p-4" style="max-height:calc(100vh - 180px); overflow:auto;">
                    <img id="global-image-modal-img" src="" alt="Foto"
                        style="max-height:calc(100vh - 220px); max-width:100%; object-fit:contain; display:block; margin:0 auto;" />
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

        document.addEventListener('keydown', function (e) {
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