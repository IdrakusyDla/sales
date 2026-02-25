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

        @if ($errors->any())
            <div class="flex justify-center mb-6">
                <div
                    class="bg-red-50 text-red-600 text-sm font-bold px-5 py-3 rounded-full shadow-sm border border-red-200 flex items-center justify-center gap-2">
                    <svg class="w-5 h-5 flex-shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

</body>

</html>