@php
    $isCheckIn = $activity['type'] === 'check_in';
    $isCheckOut = $activity['type'] === 'check_out';
    $isVisit = $activity['type'] === 'visit';
    $user = $activity['user'];
    $meta = $activity['meta'];

    $bgColor = $isCheckIn ? 'bg-blue-50/50 border-blue-100' : ($isCheckOut ? 'bg-green-50/50 border-green-100' : 'bg-purple-50/50 border-purple-100');
    $iconColor = $isCheckIn ? 'text-blue-600' : ($isCheckOut ? 'text-green-600' : 'text-purple-600');
    $labelBg = $isCheckIn ? 'bg-blue-100 text-blue-700' : ($isCheckOut ? 'bg-green-100 text-green-700' : 'bg-purple-100 text-purple-700');
    $label = $isCheckIn ? 'Masuk' : ($isCheckOut ? 'Keluar' : 'Kunjungan');

    $timeDisplay = $activity['time'] ? \Carbon\Carbon::parse($activity['time'])->format('H:i') : '-';
    $dateDisplay = $activity['date'] ? \Carbon\Carbon::parse($activity['date'])->translatedFormat('d M Y') : '-';
    $timeAgo = $activity['time'] ? \Carbon\Carbon::parse($activity['time'])->diffForHumans() : '';

    $photoUrl = null;
    if ($isCheckIn && $meta->start_photo) {
        $photoUrl = url('/files/daily/' . $meta->id . '/start_photo');
    } elseif ($isCheckOut && $meta->end_photo) {
        $photoUrl = url('/files/daily/' . $meta->id . '/end_photo');
    } elseif ($isVisit && $meta->photo_path) {
        $photoUrl = url('/files/visit/' . $meta->id);
    }

    $dataType = $activity['type']; // check_in, check_out, visit

    // Link detail user (bisa di-override oleh konteks pemanggil, mis. supervisor)
    $showUserRoute = $showUserRoute ?? route('hrd.show.user', $user->id);
@endphp

<div class="activity-item mb-3" data-date="{{ $activity['date'] ? \Carbon\Carbon::parse($activity['date'])->format('Y-m-d') : '' }}" data-type="{{ $dataType }}">
    <div class="{{ $bgColor }} border rounded-2xl overflow-hidden hover:shadow-sm transition">
        {{-- HEADER --}}
        <div class="p-4">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold {{ $labelBg }} px-2.5 py-1 rounded-lg">{{ $label }}</span>
                    <span class="text-xs text-gray-400">{{ $dateDisplay }}</span>
                </div>
                <span class="text-xs text-gray-400">{{ $timeDisplay }} &bull; {{ $timeAgo }}</span>
            </div>

            <div class="flex items-center gap-3">
                <div class="w-9 h-9 {{ $isCheckIn ? 'bg-blue-100' : ($isCheckOut ? 'bg-green-100' : 'bg-purple-100') }} rounded-full flex items-center justify-center text-sm font-bold {{ $iconColor }} shrink-0">
                    {{ substr($user->name, 0, 1) }}
                </div>
                <div class="flex-1 min-w-0">
                    <a href="{{ $showUserRoute }}" class="text-sm font-bold text-gray-800 hover:text-blue-600 transition truncate block">
                        {{ $user->name }}
                    </a>
                    <p class="text-xs text-gray-500 capitalize">{{ $user->role }}</p>
                </div>
            </div>
        </div>

        {{-- FOTO SELFIE --}}
        @if($photoUrl)
            <div class="border-t {{ $isCheckIn ? 'border-blue-100' : ($isCheckOut ? 'border-green-100' : 'border-purple-100') }}">
                <button type="button" onclick="openImageModal('{{ $photoUrl }}')" class="w-full block bg-transparent border-0 p-0 cursor-zoom-in">
                    <img src="{{ $photoUrl }}" alt="Foto selfie" class="w-full object-cover" style="max-height: 280px;">
                </button>
            </div>
        @endif

        {{-- DETAIL INFO --}}
        <div class="px-4 pb-4{{ !$photoUrl ? '' : ' pt-3' }}">
            @if($isVisit)
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <span class="text-sm text-gray-700 truncate">{{ $meta->client_name }}</span>
                </div>
                <div class="flex items-center gap-2 mt-1">
                    @if($meta->status === 'completed')
                        <span class="text-[10px] font-bold bg-green-100 text-green-700 px-2 py-0.5 rounded-md">Selesai</span>
                    @elseif($meta->status === 'pending')
                        <span class="text-[10px] font-bold bg-amber-100 text-amber-700 px-2 py-0.5 rounded-md">Pending</span>
                    @elseif($meta->status === 'failed')
                        <span class="text-[10px] font-bold bg-red-100 text-red-700 px-2 py-0.5 rounded-md">Gagal</span>
                    @endif
                    @if($meta->notes)
                        <span class="text-xs text-gray-400 truncate">{{ $meta->notes }}</span>
                    @endif
                </div>
            @else
                @php $log = $meta; @endphp
                <div class="flex gap-4">
                    @if($isCheckIn && $log->start_odo_value)
                        <div class="flex items-center gap-1">
                            <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                            <span class="text-xs text-gray-500">Odo: {{ number_format($log->start_odo_value, 0) }} km</span>
                        </div>
                    @endif
                    @if($isCheckOut && $log->end_odo_value)
                        <div class="flex items-center gap-1">
                            <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                            <span class="text-xs text-gray-500">Odo: {{ number_format($log->end_odo_value, 0) }} km</span>
                        </div>
                        @if($log->start_odo_value)
                            <div class="flex items-center gap-1">
                                <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                                <span class="text-xs text-gray-500">Jarak: {{ number_format($log->total_km, 1) }} km</span>
                            </div>
                        @endif
                    @endif
                    @if($log->system_calculated_distance)
                        <div class="flex items-center gap-1">
                            <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                            <span class="text-xs text-gray-500">{{ number_format($log->system_calculated_distance, 1) }} km</span>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
