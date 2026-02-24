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

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: white;
            color: #1f2937;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 2rem;
        }

        /* Fallback styles jika Tailwind belum load */
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

        .w-full {
            width: 100%;
        }

        .bg-blue-600 {
            background-color: #2563eb;
        }

        .text-white {
            color: white;
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

        .px-5 {
            padding-left: 1.25rem;
            padding-right: 1.25rem;
        }

        .py-4 {
            padding-top: 1rem;
            padding-bottom: 1rem;
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
            padding: 0.875rem;
            padding-left: 2.5rem;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            font-size: 0.875rem;
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

<body class="bg-white text-gray-800 h-screen flex flex-col justify-center px-8 login-container">

    {{-- 1. Logo / Branding Area --}}
    <div class="text-center mb-10">
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
        <p class="text-sm text-gray-500">Silakan login untuk mulai bekerja</p>
    </div>

    {{-- 2. Form Login --}}
    <form method="POST" action="{{ route('login') }}">
        @csrf

        {{-- Error Message (Jika password salah) --}}
        @if ($errors->any())
            <div
                class="bg-red-100 text-red-700 text-sm font-bold p-4 rounded-xl mb-4 border-2 border-red-300 text-center shadow-sm">
                ⚠️ Username atau Password salah.
            </div>
        @endif

        {{-- Input Username --}}
        <div class="mb-4">
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Username</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="text-gray-400">👤</span> {{-- Ganti icon amplop jadi orang --}}
                </div>
                {{-- Name ganti jadi 'username', Type jadi 'text' --}}
                <input type="text" name="username"
                    class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block pl-10 p-3.5"
                    placeholder="Contoh: budi" required autofocus>
            </div>
        </div>

        {{-- Input Password --}}
        <div class="mb-8">
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Password</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="text-gray-400">🔒</span>
                </div>
                <input type="password" name="password"
                    class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block pl-10 p-3.5"
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

</body>

</html>