@php
    $isCheckIn = $activity['type'] === 'check_in';
    $isCheckOut = $activity['type'] === 'check_out';
    $isVisit = $activity['type'] === 'visit';
    $user = $activity['user'];
    $meta = $activity['meta'];

    $bgColor = $isCheckIn ? 'bg-blue-50 border-blue-100' : ($isCheckOut ? 'bg-green-50 border-green-100' : 'bg-purple-50 border-purple-100');
    $iconColor = $isCheckIn ? 'text-blue-600' : ($isCheckOut ? 'text-green-600' : 'text-purple-600');
    $labelBg = $isCheckIn ? 'bg-blue-100 text-blue-700' : ($isCheckOut ? 'bg-green-100 text-green-700' : 'bg-purple-100 text-purple-700');
    $label = $isCheckIn ? 'Masuk' : ($isCheckOut ? 'Keluar' : 'Kunjungan');

    $timeDisplay = $activity['time'] ? \Carbon\Carbon::parse($activity['time'])->format('H:i') : '-';
    $dateDisplay = $activity['date'] ? \Carbon\Carbon::parse($activity['date'])->translatedFormat('d M') : '-';

    $photoUrl = null;
    if ($isCheckIn && $meta->start_photo) {
        $photoUrl = url('/files/daily/' . $meta->id . '/start_photo');
    } elseif ($isCheckOut && $meta->end_photo) {
        $photoUrl = url('/files/daily/' . $meta->id . '/end_photo');
    } elseif ($isVisit && $meta->photo_path) {
        $photoUrl = url('/files/visit/' . $meta->id);
    }
@endphp

<div class="activity-item" data-date="{{ $activity['date'] ? \Carbon\Carbon::parse($activity['date'])->format('Y-m-d') : '' }}" data-type="{{ $isCheckIn ? 'check_in' : ($isCheckOut ? 'check_out' : 'visit') }}">
    <div class="{{ $bgColor }} border rounded-xl overflow-hidden">
        {{-- HEADER: avatar + info di dalam card --}}
        <div class="p-3 flex items-center gap-3">
            <div class="w-9 h-9 {{ $isCheckIn ? 'bg-blue-100' : ($isCheckOut ? 'bg-green-100' : 'bg-purple-100') }} rounded-full flex items-center justify-center text-xs font-bold {{ $iconColor }} shrink-0">
                {{ substr($user->name, 0, 1) }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold {{ $labelBg }} px-2 py-0.5 rounded-md">{{ $label }}</span>
                    <span class="text-[10px] text-gray-400">{{ $dateDisplay }} &bull; {{ $timeDisplay }}</span>
                </div>
                <p class="text-sm font-bold text-gray-800 truncate">{{ $user->name }}</p>
            </div>
        </div>

        {{-- DETAIL --}}
        @if($isVisit)
            <div class="px-3 pb-2">
                <p class="text-xs text-gray-600 truncate">{{ $meta->client_name }}</p>
                @if($meta->status === 'completed')
                    <span class="inline-block mt-0.5 text-[10px] font-bold bg-green-100 text-green-700 px-2 py-0.5 rounded-md">Selesai</span>
                @elseif($meta->status === 'pending')
                    <span class="inline-block mt-0.5 text-[10px] font-bold bg-amber-100 text-amber-700 px-2 py-0.5 rounded-md">Pending</span>
                @elseif($meta->status === 'failed')
                    <span class="inline-block mt-0.5 text-[10px] font-bold bg-red-100 text-red-700 px-2 py-0.5 rounded-md">Gagal</span>
                @endif
            </div>
        @else
            @php $log = $meta; @endphp
            @if(($isCheckIn && $log->start_odo_value) || ($isCheckOut && $log->end_odo_value) || $log->system_calculated_distance)
                <div class="px-3 pb-2 flex gap-3">
                    @if($isCheckIn && $log->start_odo_value)
                        <span class="text-[10px] text-gray-500">Odo: {{ number_format($log->start_odo_value, 0) }} km</span>
                    @endif
                    @if($isCheckOut && $log->end_odo_value)
                        <span class="text-[10px] text-gray-500">Odo: {{ number_format($log->end_odo_value, 0) }} km</span>
                        @if($log->start_odo_value)
                            <span class="text-[10px] text-gray-500">Jarak: {{ number_format($log->total_km, 1) }} km</span>
                        @endif
                    @endif
                    @if($log->system_calculated_distance)
                        <span class="text-[10px] text-gray-500">{{ number_format($log->system_calculated_distance, 1) }} km</span>
                    @endif
                </div>
            @endif
        @endif

        {{-- FOTO SELFIE (klik untuk zoom) --}}
        @if($photoUrl)
            <div class="border-t {{ $isCheckIn ? 'border-blue-100' : ($isCheckOut ? 'border-green-100' : 'border-purple-100') }}">
                <button type="button" onclick="openImageModal('{{ $photoUrl }}')" class="w-full block bg-transparent border-0 p-0">
                    <img src="{{ $photoUrl }}" alt="Foto" class="w-full aspect-[4/3] object-cover">
                </button>
            </div>
        @endif
    </div>
</div>

