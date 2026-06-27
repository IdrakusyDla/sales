<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login Absensi Sales</title>
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    <style>
        * {
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: white;
            color: #1f2937;
            min-height: 100vh;
            display: flex;
        }

        /* ===== Layout split-screen (desktop) ===== */
        .login-wrapper {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        .login-form-panel {
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 2rem 1.25rem;
        }

        .login-form-inner {
            width: 100%;
            max-width: 24rem;
            margin-left: auto;
            margin-right: auto;
        }

        /* Branding kiri — hanya tampil di desktop */
        .login-hero {
            display: none;
            position: relative;
            overflow: hidden;
            color: #fff;
            flex-direction: column;
            justify-content: space-between;
            padding: 3rem;
            background-image:
                radial-gradient(circle at 18% 18%, rgba(255, 255, 255, 0.10) 0, transparent 42%),
                linear-gradient(135deg, #2563eb 0%, #1e40af 55%, #1e3a8a 100%);
        }

        .hero-blob {
            position: absolute;
            border-radius: 9999px;
            filter: blur(46px);
            opacity: .35;
            pointer-events: none;
        }

        /* ===== Chip logo perusahaan ===== */
        .brand-logos {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .brand-logo-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            border: 1px solid rgba(0, 0, 0, 0.06);
            border-radius: 0.625rem;
            padding: 0.3rem;
            height: 2.75rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
        }

        .brand-logo-chip img {
            display: block;
            height: 100%;
            width: auto;
            max-width: 5.5rem;
            object-fit: contain;
        }

        @media (min-width: 768px) {
            .brand-logo-chip {
                height: 2.5rem;
            }
        }

        .brand-desktop {
            display: none;
        }

        @media (min-width: 768px) {
            .login-hero {
                display: flex;
                width: 50%;
            }

            .login-form-panel {
                width: 50%;
                padding: 2rem 3rem;
            }

            .login-form-inner {
                max-width: 22rem;
            }

            .brand-mobile {
                display: none;
            }

            .brand-desktop {
                display: block;
            }
        }

        /* ===== Fallback styles jika Tailwind belum load ===== */
        .text-center {
            text-align: center;
        }

        .mb-10 {
            margin-bottom: 2.5rem;
        }

        .mb-4 {
            margin-bottom: 1rem;
        }

        .mb-8 {
            margin-bottom: 2rem;
        }

        .mt-8 {
            margin-top: 2rem;
        }

        .w-20 {
            width: 5rem;
        }

        .h-20 {
            height: 5rem;
        }

        .w-5 {
            width: 1.25rem;
        }

        .h-5 {
            height: 1.25rem;
        }

        .w-3 {
            width: 0.75rem;
        }

        .h-3 {
            height: 0.75rem;
        }

        .w-full {
            width: 100%;
        }

        .bg-blue-600 {
            background-color: #2563eb;
        }

        .bg-red-500 {
            background-color: #ef4444;
        }

        .bg-red-50 {
            background-color: #fef2f2;
        }

        .border-red-600 {
            border-color: #dc2626;
        }

        .border-red-200 {
            border-color: #fecaca;
        }

        .text-white {
            color: white;
        }

        .text-red-600 {
            color: #dc2626;
        }

        .text-red-500 {
            color: #ef4444;
        }

        .text-gray-900 {
            color: #111827;
        }

        .text-gray-500 {
            color: #6b7280;
        }

        .text-gray-400 {
            color: #9ca3af;
        }

        .text-2xl {
            font-size: 1.5rem;
            line-height: 2rem;
        }

        .text-sm {
            font-size: 0.875rem;
            line-height: 1.25rem;
        }

        .text-xs {
            font-size: 0.75rem;
            line-height: 1rem;
        }

        .font-bold {
            font-weight: 700;
        }

        .rounded-xl {
            border-radius: 0.75rem;
        }

        .rounded-3xl {
            border-radius: 1.5rem;
        }

        .mx-auto {
            margin-left: auto;
            margin-right: auto;
        }

        .flex {
            display: flex;
        }

        .flex-col {
            flex-direction: column;
        }

        .flex-shrink-0 {
            flex-shrink: 0;
        }

        .items-center {
            align-items: center;
        }

        .justify-center {
            justify-content: center;
        }

        .relative {
            position: relative;
        }

        .absolute {
            position: absolute;
        }

        .inset-y-0 {
            top: 0;
            bottom: 0;
        }

        .left-0 {
            left: 0;
        }

        .pl-3 {
            padding-left: 0.75rem;
        }

        .pl-10 {
            padding-left: 2.5rem;
        }

        .p-3 {
            padding: 0.75rem;
        }

        .p-3\.5 {
            padding: 0.875rem;
        }

        .p-4 {
            padding: 1rem;
        }

        .px-5 {
            padding-left: 1.25rem;
            padding-right: 1.25rem;
        }

        .py-3 {
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
        }

        .py-4 {
            padding-top: 1rem;
            padding-bottom: 1rem;
        }

        .mb-6 {
            margin-bottom: 1.5rem;
        }

        .gap-2 {
            gap: 0.5rem;
        }

        .rounded-full {
            border-radius: 9999px;
        }

        .shadow-sm {
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        .block {
            display: block;
        }

        .border {
            border-width: 1px;
        }

        .border-gray-200 {
            border-color: #e5e7eb;
        }

        .bg-gray-50 {
            background-color: #f9fafb;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 0.875rem 0.875rem 0.875rem 2.75rem;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            font-size: 0.875rem;
        }

        .relative input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        button[type="submit"] {
            width: 100%;
            background-color: #2563eb;
            color: white;
            font-weight: 700;
            padding: 1rem 1.25rem;
            border-radius: 0.75rem;
            border: none;
            font-size: 0.875rem;
            cursor: pointer;
        }

        button[type="submit"]:hover {
            background-color: #1d4ed8;
        }
    </style>
    <script src="https://cdn.tailwindcss.com" async></script>
</head>

<body class="bg-white text-gray-800">

    <div class="login-wrapper">

        {{-- ===== Panel Branding (hanya desktop) ===== --}}
        <aside class="login-hero">
            {{-- Dekorasi --}}
            <div class="hero-blob" style="width:340px;height:340px;background:#60a5fa;top:-90px;right:-90px;"></div>
            <div class="hero-blob" style="width:280px;height:280px;background:#3b82f6;bottom:-70px;left:-70px;opacity:.45;"></div>

            {{-- Atas: Logo + Logo Perusahaan --}}
            <div class="relative flex items-center" style="gap:.85rem;z-index:10;">
                @if($companies->isNotEmpty())
                    <div class="brand-logos">
                        @foreach($companies as $company)
                            <span class="brand-logo-chip" title="{{ $company->name }}">
                                <img src="{{ $company->logoUrl() }}" alt="{{ $company->name }}" onerror="this.parentElement.style.display='none'">
                            </span>
                        @endforeach
                    </div>
                @else
                    <div class="flex items-center justify-center"
                        style="width:3rem;height:3rem;background:rgba(255,255,255,.15);border-radius:1rem;backdrop-filter:blur(4px);">
                        <svg style="width:1.6rem;height:1.6rem;" fill="none" stroke="#fff" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                @endif
                <span style="font-weight:700;font-size:1.15rem;letter-spacing:-.01em;">Absensi Sales</span>
            </div>

            {{-- Tengah: Heading + fitur --}}
            <div class="relative" style="z-index:10;">
                <h2 style="font-size:2rem;font-weight:800;line-height:1.2;margin:0 0 .75rem;">
                    Selamat Datang Kembali.
                </h2>
                <p style="font-size:1rem;line-height:1.6;opacity:.85;margin:0 0 2rem;max-width:26rem;">
                    Sistem absensi & pelaporan kunjungan toko untuk tim sales. Login untuk memulai aktivitas harian Anda.
                </p>

                <ul style="list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:1rem;">
                    <li class="flex items-center" style="gap:.85rem;">
                        <span class="flex items-center justify-center flex-shrink-0"
                            style="width:2.25rem;height:2.25rem;background:rgba(255,255,255,.15);border-radius:.625rem;">
                            <svg style="width:1.1rem;height:1.1rem;" fill="none" stroke="#fff" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                        </span>
                        <span style="font-size:.95rem;opacity:.92;">Absen masuk & keluar dengan lokasi real-time.</span>
                    </li>
                    <li class="flex items-center" style="gap:.85rem;">
                        <span class="flex items-center justify-center flex-shrink-0"
                            style="width:2.25rem;height:2.25rem;background:rgba(255,255,255,.15);border-radius:.625rem;">
                            <svg style="width:1.1rem;height:1.1rem;" fill="none" stroke="#fff" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 9a2 2 0 012-2h.93a2 2 0 001.66-.9l.82-1.2A2 2 0 0110.07 4h3.86a2 2 0 011.66.9l.82 1.2a2 2 0 001.66.9H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </span>
                        <span style="font-size:.95rem;opacity:.92;">Check-in & check-out per toko lengkap dengan foto.</span>
                    </li>
                    <li class="flex items-center" style="gap:.85rem;">
                        <span class="flex items-center justify-center flex-shrink-0"
                            style="width:2.25rem;height:2.25rem;background:rgba(255,255,255,.15);border-radius:.625rem;">
                            <svg style="width:1.1rem;height:1.1rem;" fill="none" stroke="#fff" viewBox="0 0 24 24">
                                <path d="M3 8C3 5.17157 3 3.75736 3.87868 2.87868C4.75736 2 6.17157 2 9 2H15C17.8284 2 19.2426 2 20.1213 2.87868C21 3.75736 21 5.17157 21 8V16C21 18.8284 21 20.2426 20.1213 21.1213C19.2426 22 17.8284 22 15 22H9C6.17157 22 4.75736 22 3.87868 21.1213C3 20.2426 3 18.8284 3 16V8Z" stroke="#ffffff" stroke-width="1.5"></path> <path d="M8 2.5V22" stroke="#ffffff" stroke-width="1.5" stroke-linecap="round"></path> <path d="M2 12H4" stroke="#ffffff" stroke-width="1.5" stroke-linecap="round"></path> <path d="M2 16H4" stroke="#ffffff" stroke-width="1.5" stroke-linecap="round"></path> <path d="M2 8H4" stroke="#ffffff" stroke-width="1.5" stroke-linecap="round"></path> <path d="M11.5 6.5H16.5" stroke="#ffffff" stroke-width="1.5" stroke-linecap="round"></path> <path d="M11.5 10H16.5" stroke="#ffffff" stroke-width="1.5" stroke-linecap="round"></path>
                            </svg>
                        </span>
                        <span style="font-size:.95rem;opacity:.92;">Laporan kunjungan harian otomatis & terpusat.</span>
                    </li>
                </ul>
            </div>

            {{-- Bawah: Copyright --}}
            <div class="relative" style="z-index:10;font-size:.8rem;opacity:.7;">
                &copy; {{ date('Y') }} Brother Food Indonesia.
            </div>
        </aside>

        {{-- ===== Panel Form ===== --}}
        <main class="login-form-panel">
            <div class="login-form-inner">

                {{-- Branding mobile --}}
                <div class="brand-mobile text-center mb-10">
                    @if($companies->isNotEmpty())
                        <div class="flex items-center justify-center" style="gap:.6rem;flex-wrap:wrap;margin-bottom:1rem;">
                            <div class="brand-logos">
                                @foreach($companies as $company)
                                    <span class="brand-logo-chip" title="{{ $company->name }}">
                                        <img src="{{ $company->logoUrl() }}" alt="{{ $company->name }}" onerror="this.parentElement.style.display='none'">
                                    </span>
                                @endforeach
                            </div>
                        </div>
                        <h1 class="text-2xl font-bold text-gray-900" style="margin:0;">Absensi Sales</h1>
                    @else
                        <div
                            class="w-20 h-20 bg-blue-600 rounded-3xl mx-auto flex items-center justify-center shadow-xl mb-4 transform rotate-3">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <h1 class="text-2xl font-bold text-gray-900">Absensi Sales</h1>
                    @endif
                    <p class="text-sm text-gray-500">Silakan login untuk mulai bekerja.</p>
                </div>

                {{-- Heading desktop --}}
                <div class="brand-desktop text-center mb-8">
                    <h1 class="text-2xl font-bold text-gray-900">Masuk ke Akun</h1>
                    <p class="text-sm text-gray-500">Silakan login untuk mulai bekerja.</p>
                </div>

                {{-- Form Login --}}
                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    @if ($errors->any())
                        <div class="flex justify-center mb-6">
                            <div
                                class="bg-red-50 text-red-600 text-sm font-bold px-5 py-3 rounded-full shadow-sm border border-red-200 flex items-center justify-center gap-2">
                                <svg class="w-5 h-5 flex-shrink-0 text-red-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                    </path>
                                </svg>
                                <span>Username atau Password salah.</span>
                            </div>
                        </div>
                    @endif

                    {{-- Input Username --}}
                    <div class="mb-4">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2 ml-1">Username</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <input type="text" name="username"
                                class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl block pl-10 pr-3 py-3"
                                placeholder="Contoh: budi" required autofocus>
                        </div>
                    </div>

                    {{-- Input Password --}}
                    <div class="mb-8">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2 ml-1">Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                    </path>
                                </svg>
                            </div>
                            <input type="password" name="password"
                                class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl block pl-10 pr-3 py-3"
                                placeholder="••••••••" required>
                        </div>
                    </div>

                    {{-- Tombol Login --}}
                    <button type="submit"
                        class="w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-bold rounded-xl text-sm px-5 py-4 text-center shadow-lg shadow-blue-500/30 transition transform active:scale-95">
                        MASUK SEKARANG
                    </button>
                </form>

                {{-- Footer --}}
                <div class="mt-8 text-center">
                    <p class="text-xs text-gray-400">Lupa password? Hubungi HRD.</p>
                </div>

            </div>
        </main>

    </div>

</body>

</html>
