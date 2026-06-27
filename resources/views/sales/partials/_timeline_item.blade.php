@php
    // Partial: satu item pada timeline aktivitas
    // Variabel: $bg (class lingkaran), $icon, $title, $time (datetime|null), $subTime (string|null), $sub (text), $badge, $badgeColor
    $iconSvg = [
        'login'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>',
        'logout' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>',
        'check'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>',
        'x'      => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>',
        'clock'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
    ][$icon] ?? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>';

    $badgeClasses = [
        'green' => 'bg-green-100 text-green-700',
        'red'   => 'bg-red-100 text-red-700',
        'amber' => 'bg-amber-100 text-amber-700',
        'gray'  => 'bg-gray-100 text-gray-500',
    ][$badgeColor ?? 'gray'] ?? 'bg-gray-100 text-gray-500';
@endphp

<div class="relative flex items-start gap-3 md:gap-6 group">
    <div class="w-10 h-10 md:w-14 md:h-14 rounded-full md:rounded-2xl flex items-center justify-center shrink-0 border-4 border-white shadow-sm z-10 relative transition-transform md:group-hover:scale-110 {{ $bg }}">
        <svg class="w-5 h-5 md:w-7 md:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $iconSvg !!}</svg>
    </div>
    <div class="flex-1 bg-gray-50 md:bg-white rounded-2xl p-3 md:p-5 border border-gray-100 md:shadow-sm md:hover:shadow-md transition min-w-0">
        <div class="flex justify-between items-start gap-2">
            <h3 class="font-bold text-gray-800 text-sm md:text-lg truncate">{{ $title }}</h3>
            @if(!empty($time))
                <span class="text-xs md:text-sm font-mono text-gray-500 bg-white md:bg-gray-50 px-2 py-1 rounded-md border border-gray-100 shrink-0">{{ \Carbon\Carbon::parse($time)->format('H:i') }}</span>
            @elseif(!empty($subTime))
                <span class="text-xs md:text-sm font-mono text-gray-500 bg-white md:bg-gray-50 px-2 py-1 rounded-md border border-gray-100 shrink-0">{{ $subTime }}</span>
            @endif
        </div>
        @if(!empty($badge))
            <span class="inline-block mt-1.5 md:mt-2 {{ $badgeClasses }} text-[10px] md:text-xs font-bold px-2 md:px-3 py-0.5 md:py-1 rounded-full uppercase tracking-wider">{{ $badge }}</span>
        @endif
        @if(!empty($sub))
            <p class="text-xs md:text-sm text-gray-600 mt-1 md:mt-2 break-words">{{ $sub }}</p>
        @endif
    </div>
</div>
