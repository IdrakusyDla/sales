@extends('layout')
@section('content')
    <div class="px-5 md:px-8 py-6 md:py-8">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('it.dashboard') }}" class="text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold flex items-center gap-2">
                    {{ $user->name }}
                    @if(!$user->is_active)
                        <span
                            class="bg-red-100 text-red-600 text-xs px-2 py-1 rounded-full border border-red-200">Nonaktif</span>
                    @endif
                </h1>
                <p class="text-sm text-gray-500">
                    {{ $user->username }} • {{ ucfirst($user->role) }}
                    @if($user->last_activity_at)
                        • Aktif: {{ \Carbon\Carbon::parse($user->last_activity_at)->diffForHumans() }}
                    @endif
                </p>
            </div>
        </div>

        {{-- STATISTIK --}}
        <div class="grid grid-cols-3 gap-3 mb-6">
            <div class="bg-blue-50 rounded-xl p-3 text-center">
                <p class="text-xs text-gray-600">Absensi</p>
                <p class="text-xl font-bold text-blue-600">{{ $stats['total_absensi'] }}</p>
            </div>
            <div class="bg-green-50 rounded-xl p-3 text-center">
                <p class="text-xs text-gray-600">Kunjungan</p>
                <p class="text-xl font-bold text-green-600">{{ $stats['total_visits'] }}</p>
            </div>
            <div class="bg-orange-50 rounded-xl p-3 text-center">
                <p class="text-xs text-gray-600">Reimburse</p>
                <p class="text-xl font-bold text-orange-600">Rp {{ number_format($stats['total_expenses'] / 1000, 0) }}K</p>
            </div>
        </div>

        {{-- MAIN ACTION CONTAINER: Desktop Reordering --}}
        <div class="flex flex-col md:grid md:grid-cols-2 gap-6 mb-6 md:items-start">
            
            {{-- KOLOM KIRI: FILTER TANGGAL (Desktop Kiri, Mobile Bawah) --}}
            <div class="order-2 md:order-1">
                <div class="bg-blue-50/50 rounded-2xl p-5 border border-blue-100 h-full">
                    <h3 class="font-bold text-blue-900 mb-3 text-sm flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                        Filter Riwayat
                    </h3>
                    <form method="GET" action="{{ route('it.show.user', $user->id) }}">
                        <div class="space-y-3">
                            <div>
                                <label class="text-xs text-blue-800 font-bold mb-1 block">Dari Tanggal</label>
                                <input type="date" name="start_date" value="{{ request('start_date') }}"
                                    class="w-full border-0 ring-1 ring-inset ring-blue-200 focus:ring-2 focus:ring-blue-600 rounded-xl p-3 text-sm bg-white shadow-sm">
                            </div>
                            <div>
                                <label class="text-xs text-blue-800 font-bold mb-1 block">Sampai Tanggal</label>
                                <input type="date" name="end_date" value="{{ request('end_date') }}"
                                    class="w-full border-0 ring-1 ring-inset ring-blue-200 focus:ring-2 focus:ring-blue-600 rounded-xl p-3 text-sm bg-white shadow-sm">
                            </div>
                        </div>
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 transition text-white py-3 rounded-xl font-bold text-sm mt-4 shadow-sm">
                            Filter
                        </button>
                    </form>
                </div>
            </div>

            {{-- KOLOM KANAN: TOMBOL AKSI (Desktop Kanan, Mobile Atas) --}}
            <div class="order-1 md:order-2 space-y-4">
                {{-- TOMBOL RESET PASSWORD --}}
                <form action="{{ route('it.reset.password', $user->id) }}" method="POST">
                    @csrf
                    <button type="submit" onclick="return confirm('Reset password untuk {{ $user->name }}?')"
                        class="w-full bg-yellow-500 hover:bg-yellow-600 transition text-white py-3 rounded-xl font-bold text-sm flex items-center justify-center gap-2 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg> Reset Password
                    </button>
                </form>

                {{-- TOMBOL NONAKTIFKAN/AKTIFKAN --}}
                <form action="{{ route('it.user.toggle_status', $user->id) }}" method="POST">
                    @csrf
                    @if($user->is_active)
                        <button type="submit"
                            onclick="return confirm('Nonaktifkan akun {{ $user->name }}? User tidak akan bisa login.')"
                            class="w-full bg-red-600 hover:bg-red-700 transition text-white py-3 rounded-xl font-bold text-sm flex items-center justify-center gap-2 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg> Nonaktifkan Akun
                        </button>
                    @else
                        <button type="submit" onclick="return confirm('Aktifkan kembali akun {{ $user->name }}?')"
                            class="w-full bg-green-600 hover:bg-green-700 transition text-white py-3 rounded-xl font-bold text-sm flex items-center justify-center gap-2 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Aktifkan Akun
                        </button>
                    @endif
                </form>

                {{-- TOMBOL HAPUS AKUN (KHUSUS IT) --}}
                <form action="{{ route('it.user.delete', $user->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        onclick="return confirm('PERINGATAN: HAPUS AKUN {{ strtoupper($user->name) }}?\n\nTindakan ini TIDAK DAPAT dibatalkan!\nSemua data absensi, kunjungan, dan reimburse akan dihapus permanen.\n\nLanjutkan menghapus?')"
                        class="w-full bg-gray-800 hover:bg-gray-900 transition text-white py-3 rounded-xl font-bold text-sm border-2 border-gray-900 flex items-center justify-center gap-2 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg> Hapus Akun Permanen
                    </button>
                </form>
            </div>
        </div>

        {{-- LIST RIWAYAT --}}
        <div class="space-y-4 md:space-y-0 md:grid md:grid-cols-2 lg:grid-cols-3 md:gap-4">
            @forelse($dailyLogs as $log)
                <div class="relative bg-white rounded-2xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition group h-full flex flex-col justify-between">
                    <a href="{{ route('sales.history.detail', $log->id) }}" class="absolute inset-0 z-10 rounded-2xl"></a>
                    <div class="relative z-20 pointer-events-none">
                        <div class="pointer-events-auto">
                    <div class="mb-3 pb-3 border-b border-gray-100">
                        <div class="flex justify-between items-start mb-2">
                            <div class="flex-1">
                                <h3 class="font-bold text-gray-800 text-lg">{{ \Carbon\Carbon::parse($log->date)->format('d M Y') }}</h3>
                                <p class="text-xs text-gray-500 mt-1">
                                    @if($log->hasStarted())
                                        Masuk: {{ \Carbon\Carbon::parse($log->start_time)->format('H:i') }}
                                    @endif
                                    @if($log->hasEnded())
                                        | Keluar: {{ \Carbon\Carbon::parse($log->end_time)->format('H:i') }}
                                    @endif
                                </p>
                            </div>
                            <div class="ml-3">
                                @if($log->hasEnded())
                                    <span class="bg-green-100 text-green-700 text-xs font-bold px-3 py-1.5 rounded-full inline-block">Selesai</span>
                                @else
                                    <span class="bg-yellow-100 text-yellow-700 text-xs font-bold px-3 py-1.5 rounded-full inline-block">Berlangsung</span>
                                @endif
                            </div>
                        </div>
                        {{-- INFO DEADLINE DI HEADER --}}
                        @if($log->hasEnded())
                            @php
                                $deadline = \App\Models\Expense::calculateDeadline($log->date);
                                $isDeadlinePassed = \Carbon\Carbon::today()->gt($deadline);
                                $fuelExpense = $log->expenses->where('type', 'fuel')->where('is_auto_calculated', true)->first();
                                $hasIncompleteFuelReceipt = $fuelExpense && !$fuelExpense->photo_receipt;
                                $hasIncompleteExpenses = $log->expenses->whereNull('photo_receipt')->count() > 0;
                            @endphp
                            <div class="mt-2 {{ $isDeadlinePassed ? 'bg-red-50 border border-red-200' : ($hasIncompleteFuelReceipt || $hasIncompleteExpenses ? 'bg-orange-50 border border-orange-200' : 'bg-yellow-50 border border-yellow-200') }} rounded-lg p-2.5">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 {{ $isDeadlinePassed ? 'text-red-600' : 'text-yellow-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if($isDeadlinePassed)
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        @endif
                                    </svg>
                                    <div class="flex-1">
                                        <p class="text-sm font-bold {{ $isDeadlinePassed ? 'text-red-700' : ($hasIncompleteFuelReceipt || $hasIncompleteExpenses ? 'text-orange-700' : 'text-yellow-700') }}">
                                            @if($isDeadlinePassed)
                                                Batas Waktu Sudah Lewat
                                            @elseif($hasIncompleteFuelReceipt || $hasIncompleteExpenses)
                                                Batas Melengkapi Berkas • Belum Lengkap
                                            @else
                                                Batas Melengkapi Berkas
                                            @endif
                                        </p>
                                        <p class="text-sm {{ $isDeadlinePassed ? 'text-red-600' : ($hasIncompleteFuelReceipt || $hasIncompleteExpenses ? 'text-orange-600' : 'text-yellow-600') }} font-bold mt-0.5">
                                            Sampai: {{ \Carbon\Carbon::parse($deadline)->format('d M Y') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- INFO ODOMETER & KM --}}
                    @if($log->start_odo_value && $log->end_odo_value)
                        <div class="bg-blue-50 rounded-xl p-3 mb-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Odometer Awal:</span>
                                <span class="font-bold">{{ number_format($log->start_odo_value, 2) }} KM</span>
                            </div>
                            <div class="flex justify-between text-sm mt-1">
                                <span class="text-gray-600">Odometer Akhir:</span>
                                <span class="font-bold">{{ number_format($log->end_odo_value, 2) }} KM</span>
                            </div>
                            <div class="flex justify-between text-sm mt-2 pt-2 border-t border-blue-200">
                                <span class="text-gray-700 font-bold">Total KM:</span>
                                <span class="font-bold text-blue-600">{{ number_format($log->total_km, 2) }} KM</span>
                            </div>
                        </div>
                    @endif

                    {{-- LIST KUNJUNGAN --}}
                    <div class="space-y-2 mb-3">
                        @foreach($log->visits as $visit)
                            <div class="flex items-start gap-3 p-2 bg-gray-50 rounded-lg">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 text-sm
                                    {{ $visit->status == 'completed' ? 'bg-green-100' : ($visit->status == 'failed' ? 'bg-red-100' : 'bg-gray-200') }}">
                                    @if($visit->status == 'completed')
                                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    @elseif($visit->status == 'failed')
                                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    @else
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <p class="font-bold text-sm">{{ $visit->client_name }}</p>
                                    <p class="text-xs text-gray-500">
                                        {{ \Carbon\Carbon::parse($visit->time)->format('H:i') }}
                                        @if(!$visit->is_planned) <span class="text-orange-600">(Dadakan)</span> @endif
                                    </p>
                                    @if($visit->notes)
                                        <p class="text-xs text-gray-600 mt-1">{{ $visit->notes }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                        </div>
                    </div>
                    <div class="pointer-events-auto">
                        {{-- REIMBURSE --}}
                    @if($log->expenses->count() > 0)
                        <div class="mt-3 pt-3 border-t border-gray-100">
                            <p class="text-xs font-bold text-gray-600 mb-2">Reimburse:</p>
                            <div class="space-y-1">
                                @foreach($log->expenses as $expense)
                                    <div class="flex justify-between items-center text-sm mb-1">
                                        <span class="text-gray-600 flex items-center">
                                            @if($expense->type == 'fuel' && $expense->is_auto_calculated)
                                                <span class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center shrink-0 inline-flex mr-1"><svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg></span> Bahan Bakar (Auto)
                                            @elseif($expense->type == 'fuel')
                                                <span class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center shrink-0 inline-flex mr-1"><svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg></span> Bahan Bakar
                                            @elseif($expense->type == 'parking')
                                                <span class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center shrink-0 inline-flex mr-1"><svg class="w-3.5 h-3.5 text-blue-500" version="1.1" id="_x32_" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="-40 -40 592 592" xml:space="preserve" fill="currentColor"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <path d="M404.751,54.102C371.523,20.771,324.986-0.026,274.178,0h-90.85h-8.682H53.16v512h130.167V369.324h90.85 c50.808,0.026,97.333-20.771,130.573-54.074c33.331-33.229,54.115-79.78,54.089-130.575 C458.866,133.854,438.082,87.329,404.751,54.102z M321.923,232.394c-12.408,12.305-28.919,19.754-47.745,19.779h-90.85V117.15 h90.85c18.826,0.026,35.338,7.474,47.732,19.779c12.318,12.408,19.754,28.906,19.779,47.745 C341.664,203.488,334.228,219.988,321.923,232.394z"></path> </g> </g></svg></span> Parkir
                                            @elseif($expense->type == 'toll')
                                                <span class="w-6 h-6 rounded-full bg-green-100 flex items-center justify-center shrink-0 inline-flex mr-1"><svg class="w-3.5 h-3.5 text-green-500" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="-20 -20 440 440" xml:space="preserve" fill="currentColor"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <g> <path d="M295.225,142.697c-9.9-44.668-19.801-89.336-29.707-134.003c-16.718,0-33.435,0-50.15,0 c2.389,44.668,4.781,89.336,7.172,134.003H295.225z"></path> <path d="M226.354,214.003c3.145,58.703,6.286,117.404,9.426,176.107c38.094,0,76.188,0,114.281,0 c-13.014-58.702-26.021-117.404-39.029-176.107H226.354z"></path> <path d="M183.435,8.694c-16.716,0-33.434,0-50.149,0c-9.902,44.667-19.798,89.335-29.698,134.003h72.682 C178.656,98.029,181.043,53.361,183.435,8.694z"></path> <path d="M48.742,390.11c38.096,0,76.188,0,114.281,0c3.152-58.702,6.293-117.404,9.43-176.107H87.785 C74.775,272.706,61.763,331.409,48.742,390.11z"></path> <path d="M394.176,161.212H4.628c-2.556,0-4.628,2.072-4.628,4.628v25.02c0,2.556,2.072,4.628,4.628,4.628h25.048v37.476 c0,2.556,2.071,4.629,4.627,4.629h24.996c2.117,0,3.964-1.438,4.484-3.488l9.818-38.615h251.602l9.816,38.615 c0.52,2.052,2.369,3.488,4.486,3.488h24.992c2.559,0,4.629-2.073,4.629-4.629v-37.476h25.049c2.557,0,4.629-2.072,4.629-4.628 v-25.02C398.805,163.284,396.732,161.212,394.176,161.212z"></path> </g> </g> </g></svg></span> Tol
                                            @elseif($expense->type == 'transport')
                                                <span class="w-6 h-6 rounded-full bg-orange-100 flex items-center justify-center shrink-0 inline-flex mr-1"><svg class="w-3.5 h-3.5 text-orange-500" fill="currentColor" viewBox="0 -3.6 30.859 30.859" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path id="Path_1" data-name="Path 1" d="M141.314,136.63l1.055-.085a.568.568,0,0,0,.52-.61l-.129-1.58a.565.565,0,0,0-.609-.519l-2.354,0-2.549-5.724a2.074,2.074,0,0,0-2.032-1.116h-15a2.084,2.084,0,0,0-2.036,1.116l-2.546,5.724-2.354,0a.568.568,0,0,0-.61.519l-.127,1.58a.567.567,0,0,0,.519.61l1.055.085a10.131,10.131,0,0,0-1.833,5.852l.238,3.025a1.649,1.649,0,0,0,.9,1.355v1.6c.1,2.185.788,2.185,2.319,2.185s2.32,0,2.423-2.185v-1.417l9.551,0,9.468,0v1.415c.1,2.185.787,2.185,2.319,2.185s2.32,0,2.422-2.185v-1.6a1.734,1.734,0,0,0,.978-1.355l.242-3.025A10.131,10.131,0,0,0,141.314,136.63ZM122.257,143.5a.568.568,0,0,1-.566.567h-5.577a.567.567,0,0,1-.568-.567v-2.04a.565.565,0,0,1,.568-.567l5.577.453a.568.568,0,0,1,.566.566Zm-4.9-7.98,2.742-6.307h15.232l2.741,6.307H117.359Zm22.53,7.98a.567.567,0,0,1-.567.567h-5.577a.568.568,0,0,1-.567-.567v-1.588a.569.569,0,0,1,.567-.566l5.577-.453a.565.565,0,0,1,.567.567Z" transform="translate(-112.289 -126.994)"></path> </g></svg></span> Transport
                                            @elseif($expense->type == 'hotel')
                                                <span class="w-6 h-6 rounded-full bg-purple-100 flex items-center justify-center shrink-0 inline-flex mr-1"><svg class="w-3.5 h-3.5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg></span> Hotel
                                            @else
                                                <span class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center shrink-0 inline-flex mr-1"><svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"></path></svg></span> Lainnya
                                            @endif
                                            @if(!$expense->photo_receipt && $expense->isFuel())
                                                <span class="text-orange-600 text-[10px]">(Belum ada struk)</span>
                                            @endif
                                            @if($expense->status === 'needs_revision_sales')
                                                <span class="bg-orange-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full ml-1">REVISI</span>
                                            @endif
                                        </span>
                                        <span class="font-bold text-green-600">Rp {{ number_format($expense->amount, 0, ',', '.') }}</span>
                                    </div>
                                @endforeach
                                <div class="flex justify-between text-sm mt-2 pt-2 border-t border-gray-200">
                                    <span class="font-bold">Total:</span>
                                    <span class="font-bold text-green-600">Rp {{ number_format($log->expenses->sum('amount'), 0, ',', '.') }}</span>
                                </div>
                            </div>
                            
                            
                        </div>
                    @elseif($log->hasEnded())
                        
                    @endif
                </div>
                </div>
            @empty
                <div class="text-center py-10">
                    <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    <p class="text-sm text-gray-500">Belum ada riwayat aktivitas</p>
                </div>
            @endforelse
        </div>

        @if($dailyLogs->hasPages())
            <div class="mt-6">
                {{ $dailyLogs->links() }}
            </div>
        @endif
    </div>
@endsection