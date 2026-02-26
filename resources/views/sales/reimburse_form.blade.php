@extends('layout')
@section('content')
    <div class="px-5 md:px-8 py-6 md:py-8">
        <h1 class="text-2xl font-bold mb-2">Tambah Pengeluaran</h1>
        <p class="text-sm text-gray-600 mb-4">Catat pengeluaran untuk tanggal:
            {{ \Carbon\Carbon::parse($dailyLog->date)->format('d M Y') }}
        </p>

        @if($deadline)
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-3 mb-6">
                <p class="text-xs text-yellow-800">
                    <strong>Batas pengisian:</strong> {{ \Carbon\Carbon::parse($deadline)->format('d M Y') }}
                </p>
            </div>
        @endif

        {{-- LIST PENGELUARAN YANG SUDAH ADA --}}
        @if($expenses->count() > 0)
            <div class="mb-6">
                <p class="text-sm font-bold text-gray-700 mb-2">Pengeluaran yang sudah dicatat:</p>
                <div class="space-y-2">
                    @foreach($expenses as $expense)
                        <div class="bg-gray-50 rounded-xl p-3 flex justify-between items-center">
                            <div class="flex items-center gap-2">
                                @if($expense->type == 'fuel')
                                    <span class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                        </svg>
                                    </span>
                                @elseif($expense->type == 'parking')
                                    <span class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4 text-blue-500" version="1.1" id="_x32_" xmlns="http://www.w3.org/2000/svg"
                                            xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="-40 -40 592 592" xml:space="preserve"
                                            fill="currentColor">
                                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                            <g id="SVGRepo_iconCarrier">
                                                <g>
                                                    <path
                                                        d="M404.751,54.102C371.523,20.771,324.986-0.026,274.178,0h-90.85h-8.682H53.16v512h130.167V369.324h90.85 c50.808,0.026,97.333-20.771,130.573-54.074c33.331-33.229,54.115-79.78,54.089-130.575 C458.866,133.854,438.082,87.329,404.751,54.102z M321.923,232.394c-12.408,12.305-28.919,19.754-47.745,19.779h-90.85V117.15 h90.85c18.826,0.026,35.338,7.474,47.732,19.779c12.318,12.408,19.754,28.906,19.779,47.745 C341.664,203.488,334.228,219.988,321.923,232.394z">
                                                    </path>
                                                </g>
                                            </g>
                                        </svg>
                                    </span>
                                @elseif($expense->type == 'hotel')
                                    <span class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                            </path>
                                        </svg>
                                    </span>
                                @elseif($expense->type == 'toll')
                                    <span class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4 text-green-500" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="-20 -20 440 440" xml:space="preserve" fill="currentColor"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <g> <path d="M295.225,142.697c-9.9-44.668-19.801-89.336-29.707-134.003c-16.718,0-33.435,0-50.15,0 c2.389,44.668,4.781,89.336,7.172,134.003H295.225z"></path> <path d="M226.354,214.003c3.145,58.703,6.286,117.404,9.426,176.107c38.094,0,76.188,0,114.281,0 c-13.014-58.702-26.021-117.404-39.029-176.107H226.354z"></path> <path d="M183.435,8.694c-16.716,0-33.434,0-50.149,0c-9.902,44.667-19.798,89.335-29.698,134.003h72.682 C178.656,98.029,181.043,53.361,183.435,8.694z"></path> <path d="M48.742,390.11c38.096,0,76.188,0,114.281,0c3.152-58.702,6.293-117.404,9.43-176.107H87.785 C74.775,272.706,61.763,331.409,48.742,390.11z"></path> <path d="M394.176,161.212H4.628c-2.556,0-4.628,2.072-4.628,4.628v25.02c0,2.556,2.072,4.628,4.628,4.628h25.048v37.476 c0,2.556,2.071,4.629,4.627,4.629h24.996c2.117,0,3.964-1.438,4.484-3.488l9.818-38.615h251.602l9.816,38.615 c0.52,2.052,2.369,3.488,4.486,3.488h24.992c2.559,0,4.629-2.073,4.629-4.629v-37.476h25.049c2.557,0,4.629-2.072,4.629-4.628 v-25.02C398.805,163.284,396.732,161.212,394.176,161.212z"></path> </g> </g> </g></svg>
                                    </span>
                                @elseif($expense->type == 'transport')
                                    <span class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5 text-orange-500" fill="currentColor" viewBox="0 -3.6 30.859 30.859" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path id="Path_1" data-name="Path 1" d="M141.314,136.63l1.055-.085a.568.568,0,0,0,.52-.61l-.129-1.58a.565.565,0,0,0-.609-.519l-2.354,0-2.549-5.724a2.074,2.074,0,0,0-2.032-1.116h-15a2.084,2.084,0,0,0-2.036,1.116l-2.546,5.724-2.354,0a.568.568,0,0,0-.61.519l-.127,1.58a.567.567,0,0,0,.519.61l1.055.085a10.131,10.131,0,0,0-1.833,5.852l.238,3.025a1.649,1.649,0,0,0,.9,1.355v1.6c.1,2.185.788,2.185,2.319,2.185s2.32,0,2.423-2.185v-1.417l9.551,0,9.468,0v1.415c.1,2.185.787,2.185,2.319,2.185s2.32,0,2.422-2.185v-1.6a1.734,1.734,0,0,0,.978-1.355l.242-3.025A10.131,10.131,0,0,0,141.314,136.63ZM122.257,143.5a.568.568,0,0,1-.566.567h-5.577a.567.567,0,0,1-.568-.567v-2.04a.565.565,0,0,1,.568-.567l5.577.453a.568.568,0,0,1,.566.566Zm-4.9-7.98,2.742-6.307h15.232l2.741,6.307H117.359Zm22.53,7.98a.567.567,0,0,1-.567.567h-5.577a.568.568,0,0,1-.567-.567v-1.588a.569.569,0,0,1,.567-.566l5.577-.453a.565.565,0,0,1,.567.567Z" transform="translate(-112.289 -126.994)"></path> </g></svg>
                                    </span>
                                @else
                                    <span class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z">
                                            </path>
                                        </svg>
                                    </span>
                                @endif
                                <p class="font-bold text-sm">{{ ucfirst($expense->type) }}</p>
                            </div>
                            <p class="font-bold text-green-600">Rp {{ number_format($expense->amount, 0, ',', '.') }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <form action="{{ route('sales.reimburse.store', $dailyLog->id) }}" method="POST" id="form-reimburse">
            @csrf
            <input type="hidden" name="photo_receipt" id="photo_receipt_data">

            {{-- JENIS PENGELUARAN --}}
            <div class="mb-4">
                <label class="block text-sm font-bold text-gray-700 mb-2">Jenis Pengeluaran *</label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="fuel" required class="peer sr-only">
                        <div
                            class="border-2 border-gray-200 rounded-xl p-3 peer-checked:border-blue-500 peer-checked:bg-blue-50 transition">
                            <div class="flex items-center gap-2">
                                <span class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </span>
                                <span class="text-sm font-medium">Bahan Bakar</span>
                            </div>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="parking" class="peer sr-only">
                        <div
                            class="border-2 border-gray-200 rounded-xl p-3 peer-checked:border-blue-500 peer-checked:bg-blue-50 transition">
                            <div class="flex items-center gap-2">
                                <span class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4 text-blue-500" version="1.1" id="_x32_"
                                        xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                        viewBox="-40 -40 592 592" xml:space="preserve" fill="currentColor">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                        <g id="SVGRepo_iconCarrier">
                                            <g>
                                                <path
                                                    d="M404.751,54.102C371.523,20.771,324.986-0.026,274.178,0h-90.85h-8.682H53.16v512h130.167V369.324h90.85 c50.808,0.026,97.333-20.771,130.573-54.074c33.331-33.229,54.115-79.78,54.089-130.575 C458.866,133.854,438.082,87.329,404.751,54.102z M321.923,232.394c-12.408,12.305-28.919,19.754-47.745,19.779h-90.85V117.15 h90.85c18.826,0.026,35.338,7.474,47.732,19.779c12.318,12.408,19.754,28.906,19.779,47.745 C341.664,203.488,334.228,219.988,321.923,232.394z">
                                                </path>
                                            </g>
                                        </g>
                                    </svg>
                                </span>
                                <span class="text-sm font-medium">Parkir</span>
                            </div>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="hotel" class="peer sr-only">
                        <div
                            class="border-2 border-gray-200 rounded-xl p-3 peer-checked:border-purple-500 peer-checked:bg-purple-50 transition">
                            <div class="flex items-center gap-2">
                                <span class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                        </path>
                                    </svg>
                                </span>
                                <span class="text-sm font-medium">Hotel</span>
                            </div>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="toll" class="peer sr-only">
                        <div
                            class="border-2 border-gray-200 rounded-xl p-3 peer-checked:border-green-500 peer-checked:bg-green-50 transition">
                            <div class="flex items-center gap-2">
                                <span class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4 text-green-500" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="-20 -20 440 440" xml:space="preserve" fill="currentColor"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <g> <path d="M295.225,142.697c-9.9-44.668-19.801-89.336-29.707-134.003c-16.718,0-33.435,0-50.15,0 c2.389,44.668,4.781,89.336,7.172,134.003H295.225z"></path> <path d="M226.354,214.003c3.145,58.703,6.286,117.404,9.426,176.107c38.094,0,76.188,0,114.281,0 c-13.014-58.702-26.021-117.404-39.029-176.107H226.354z"></path> <path d="M183.435,8.694c-16.716,0-33.434,0-50.149,0c-9.902,44.667-19.798,89.335-29.698,134.003h72.682 C178.656,98.029,181.043,53.361,183.435,8.694z"></path> <path d="M48.742,390.11c38.096,0,76.188,0,114.281,0c3.152-58.702,6.293-117.404,9.43-176.107H87.785 C74.775,272.706,61.763,331.409,48.742,390.11z"></path> <path d="M394.176,161.212H4.628c-2.556,0-4.628,2.072-4.628,4.628v25.02c0,2.556,2.072,4.628,4.628,4.628h25.048v37.476 c0,2.556,2.071,4.629,4.627,4.629h24.996c2.117,0,3.964-1.438,4.484-3.488l9.818-38.615h251.602l9.816,38.615 c0.52,2.052,2.369,3.488,4.486,3.488h24.992c2.559,0,4.629-2.073,4.629-4.629v-37.476h25.049c2.557,0,4.629-2.072,4.629-4.628 v-25.02C398.805,163.284,396.732,161.212,394.176,161.212z"></path> </g> </g> </g></svg>
                                </span>
                                <span class="text-sm font-medium">Tol</span>
                            </div>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="transport" class="peer sr-only">
                        <div
                            class="border-2 border-gray-200 rounded-xl p-3 peer-checked:border-orange-500 peer-checked:bg-orange-50 transition">
                            <div class="flex items-center gap-2">
                                <span class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-orange-500" fill="currentColor" viewBox="0 -3.6 30.859 30.859" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path id="Path_1" data-name="Path 1" d="M141.314,136.63l1.055-.085a.568.568,0,0,0,.52-.61l-.129-1.58a.565.565,0,0,0-.609-.519l-2.354,0-2.549-5.724a2.074,2.074,0,0,0-2.032-1.116h-15a2.084,2.084,0,0,0-2.036,1.116l-2.546,5.724-2.354,0a.568.568,0,0,0-.61.519l-.127,1.58a.567.567,0,0,0,.519.61l1.055.085a10.131,10.131,0,0,0-1.833,5.852l.238,3.025a1.649,1.649,0,0,0,.9,1.355v1.6c.1,2.185.788,2.185,2.319,2.185s2.32,0,2.423-2.185v-1.417l9.551,0,9.468,0v1.415c.1,2.185.787,2.185,2.319,2.185s2.32,0,2.422-2.185v-1.6a1.734,1.734,0,0,0,.978-1.355l.242-3.025A10.131,10.131,0,0,0,141.314,136.63ZM122.257,143.5a.568.568,0,0,1-.566.567h-5.577a.567.567,0,0,1-.568-.567v-2.04a.565.565,0,0,1,.568-.567l5.577.453a.568.568,0,0,1,.566.566Zm-4.9-7.98,2.742-6.307h15.232l2.741,6.307H117.359Zm22.53,7.98a.567.567,0,0,1-.567.567h-5.577a.568.568,0,0,1-.567-.567v-1.588a.569.569,0,0,1,.567-.566l5.577-.453a.565.565,0,0,1,.567.567Z" transform="translate(-112.289 -126.994)"></path> </g></svg>
                                </span>
                                <span class="text-sm font-medium">Transport</span>
                            </div>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="other" class="peer sr-only">
                        <div
                            class="border-2 border-gray-200 rounded-xl p-3 peer-checked:border-gray-500 peer-checked:bg-gray-50 transition">
                            <div class="flex items-center gap-2">
                                <span class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z">
                                        </path>
                                    </svg>
                                </span>
                                <span class="text-sm font-medium">Lainnya</span>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            {{-- OPSI AUTO RECEIPT (KHUSUS PARKIR) --}}
            <div id="auto-receipt-section" class="mb-6 hidden">
                <label class="flex items-center space-x-3 p-3 bg-blue-50 border border-blue-100 rounded-xl cursor-pointer">
                    <input type="checkbox" name="generate_receipt" id="generate_receipt" value="1"
                        class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500">
                    <div>
                        <span class="block text-sm font-bold text-gray-700">Tidak ada struk?</span>
                        <span class="text-xs text-gray-500">Centang untuk buat struk otomatis</span>
                    </div>
                </label>
            </div>

            {{-- DETAIL PARKIR (AUTO RECEIPT) --}}
            <div id="parking-details-section" class="mb-6 hidden space-y-4">
                <div class="p-4 bg-gray-50 border border-gray-200 rounded-xl">
                    <h3 class="text-sm font-bold text-gray-800 mb-3 block border-b pb-2">Detail Untuk Struk</h3>

                    <div class="mb-3">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">No. Kendaraan *</label>
                        <input type="text" name="license_plate" placeholder="Contoh: B 1234 ABC"
                            class="w-full border border-gray-300 rounded-lg p-2 text-sm uppercase">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Lokasi Parkir *</label>
                        <input type="text" name="parking_location" placeholder="Contoh: Mall Grand Indonesia"
                            class="w-full border border-gray-300 rounded-lg p-2 text-sm">
                    </div>
                </div>
            </div>

            {{-- NOMINAL --}}
            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-700 mb-2">Nominal (Rp) *</label>
                <input type="number" name="amount" step="0.01" min="0" required
                    class="w-full border border-gray-300 rounded-xl p-4 text-lg font-bold focus:ring-2 focus:ring-blue-500"
                    placeholder="Contoh: 50000">
            </div>

            {{-- CATATAN --}}
            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-700 mb-2">Catatan</label>
                <textarea name="note" rows="3" placeholder="Keterangan pengeluaran..."
                    class="w-full border border-gray-300 rounded-xl p-3 text-sm"></textarea>
            </div>

            {{-- FOTO STRUK --}}
            <div class="mb-6" id="photo-section">
                <label class="block text-sm font-bold text-gray-700 mb-2">Foto Struk/Bukti Pembayaran *</label>
                <div class="relative w-full h-64 bg-black rounded-2xl overflow-hidden">
                    <video id="video" autoplay playsinline class="w-full h-full object-cover"></video>
                    <canvas id="canvas" class="hidden w-full h-full object-cover"></canvas>
                    <button type="button" onclick="switchCamera()"
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
                </div>
                <p id="photo-status" class="text-xs text-gray-500 mt-2"></p>
            </div>

            {{-- TOMBOL SUBMIT --}}
            <div class="flex gap-3 mb-24">
                <a href="{{ route('sales.history') }}"
                    class="flex-1 bg-gray-200 text-gray-700 py-4 rounded-xl font-bold text-sm text-center">Batal</a>
                <button type="submit" id="btn-submit"
                    class="flex-[2] bg-blue-600 text-white py-4 rounded-xl font-bold text-sm shadow-lg disabled:bg-gray-400 disabled:cursor-not-allowed"
                    disabled>Simpan</button>
            </div>
        </form>
    </div>

    @section('scripts')
        <script>
            let stream;
            let photoTaken = false;
            let facingMode = 'environment';
            const typeSelect = document.querySelector('select[name="type"]');
            const autoReceiptSection = document.getElementById('auto-receipt-section');
            const parkingDetailsSection = document.getElementById('parking-details-section');
            const generateReceiptCheckbox = document.getElementById('generate_receipt');
            const photoSection = document.getElementById('photo-section');
            const btnSubmit = document.getElementById('btn-submit');
            const licenseInput = document.querySelector('input[name="license_plate"]');
            const locationInput = document.querySelector('input[name="parking_location"]');

            // Event Listener untuk Jenis Pengeluaran
            typeSelect.addEventListener('change', function () {
                if (this.value === 'parking') {
                    autoReceiptSection.classList.remove('hidden');
                } else {
                    autoReceiptSection.classList.add('hidden');
                    generateReceiptCheckbox.checked = false;
                    toggleReceiptMode();
                }
            });

            // Event Listener untuk Checkbox Auto Receipt
            generateReceiptCheckbox.addEventListener('change', toggleReceiptMode);

            function toggleReceiptMode() {
                const isAuto = generateReceiptCheckbox.checked;

                if (isAuto) {
                    // Mode Otomatis: Sembunyikan Foto, Tampilkan Detail Parkir
                    photoSection.classList.add('hidden');
                    parkingDetailsSection.classList.remove('hidden');

                    // Set requirements
                    licenseInput.required = true;
                    locationInput.required = true;

                    // Cek validasi form manual
                    checkSubmit();
                } else {
                    // Mode Foto: Tampilkan Foto, Sembunyikan Detail Parkir
                    photoSection.classList.remove('hidden');
                    parkingDetailsSection.classList.add('hidden');

                    // Unset requirements
                    licenseInput.required = false;
                    locationInput.required = false;

                    checkSubmit();
                }
            }

            function initCamera() {
                if (stream) stream.getTracks().forEach(t => t.stop());
                // Cek apakah element video visible (jika mode auto, video hidden, jangan init camera)
                if (photoSection.classList.contains('hidden')) return;

                navigator.mediaDevices.getUserMedia({
                    video: { facingMode: facingMode }
                }).then(s => {
                    stream = s;
                    document.getElementById('video').srcObject = stream;
                }).catch(err => {
                    console.log("Camera error: ", err);
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
                document.getElementById('photo-status').innerHTML = '<svg class="w-5 h-5 inline text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Foto berhasil diambil';
                photoTaken = true;
                checkSubmit();
            }

            function checkSubmit() {
                if (generateReceiptCheckbox.checked) {
                    // Jika mode auto, tombol submit aktif jika input sudah diisi (handled by browser validator actually, but we can enable btn)
                    btnSubmit.disabled = false;
                } else {
                    // Jika mode foto, harus sudah foto
                    btnSubmit.disabled = !photoTaken;
                    // Re-init camera jika visible dan belum ada foto
                    if (!photoSection.classList.contains('hidden') && !photoTaken) {
                        initCamera();
                    }
                }
            }

            // Init awal
            initCamera();
        </script>
    @endsection
@endsection