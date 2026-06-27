@php
    // Partial: satu step pada stepper progress
    // Variabel: $icon ('in'|'out'|'store'), $label, $time, $state ('done'|'active'|'locked'|'failed')
    $state = $state ?? 'locked';
    $isDone = in_array($state, ['done','failed']);
    $isActive = $state === 'active';

    $iconSvg = [
        'in' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>',
        'out' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>',
        'store' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>',
    ][$icon] ?? '';

    $circleClasses = match($state) {
        'done' => 'bg-blue-600 text-white',
        'active' => 'bg-amber-500 text-white ring-4 ring-amber-200',
        'failed' => 'bg-red-500 text-white',
        default => 'bg-gray-100 text-gray-400'
    };
@endphp

<div class="flex flex-col items-center shrink-0" style="min-width:64px;">
    <div class="w-10 h-10 md:w-12 md:h-12 rounded-full flex items-center justify-center shadow-sm transition-all {{ $circleClasses }}">
        @if($isDone && $icon === 'store' && $state === 'done')
            <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
        @elseif($state === 'failed')
            <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
        @elseif($isActive)
            <svg class="w-5 h-5 md:w-6 md:h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        @else
            <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $iconSvg !!}</svg>
        @endif
    </div>
    <p class="text-[9px] md:text-[11px] font-bold text-gray-700 mt-1.5 text-center leading-tight max-w-[72px] md:max-w-[90px] truncate" title="{{ $label }}">{{ $label }}</p>
    @if($time)
        <p class="text-[9px] md:text-[10px] text-gray-400 font-mono">{{ $time }}</p>
    @endif
</div>
