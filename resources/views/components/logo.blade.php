@props([
    'variant' => 'dark', // 'dark' (for light background) or 'light' (for dark background)
    'size' => 'md',      // 'sm', 'md', 'lg'
    'withSubtitle' => true,
])

@php
    $sizeClasses = match($size) {
        'sm' => ['icon' => 'w-8 h-8', 'text' => 'text-lg', 'sub' => 'text-[9px]'],
        'lg' => ['icon' => 'w-12 h-12', 'text' => 'text-2xl', 'sub' => 'text-xs'],
        default => ['icon' => 'w-10 h-10', 'text' => 'text-xl', 'sub' => 'text-[11px]'],
    };

    $textColor = $variant === 'light' ? 'text-white' : 'text-slate-900';
    $subColor = $variant === 'light' ? 'text-slate-300' : 'text-slate-500';
    $tagBg = $variant === 'light' ? 'bg-white/10 text-brand-300 border-white/20' : 'bg-brand-50 text-brand-600 border-brand-200';
@endphp

<div {{ $attributes->merge(['class' => 'inline-flex items-center space-x-3 select-none']) }}>
    <!-- Modern Aerodynamic Logo Mark -->
    <div class="{{ $sizeClasses['icon'] }} rounded-xl bg-gradient-to-tr from-brand-700 via-brand-600 to-sky-400 flex items-center justify-center text-white shadow-md shadow-brand-600/25 flex-shrink-0">
        <svg class="w-3/5 h-3/5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <!-- Modern stylized streamlined coach / path icon -->
            <path d="M4 16C4 16.5523 4.44772 17 5 17H6M18 17H19C19.5523 17 20 16.5523 20 16M4 16V9C4 6.79086 5.79086 5 8 5H16C18.2091 5 20 6.79086 20 9V16M4 16H20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            <path d="M4 11H20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            <circle cx="7.5" cy="14" r="1.5" fill="currentColor"/>
            <circle cx="16.5" cy="14" r="1.5" fill="currentColor"/>
            <path d="M10 5L12 8H14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </div>

    <!-- Wordmark -->
    <div class="leading-none">
        <div class="flex items-center space-x-1.5">
            <span class="{{ $sizeClasses['text'] }} font-black tracking-tight {{ $textColor }}">
                CAN
            </span>
            <span class="text-[10px] font-extrabold uppercase tracking-widest px-1.5 py-0.5 rounded border {{ $tagBg }}">
                TRAVEL
            </span>
        </div>
        @if($withSubtitle)
            <span class="{{ $sizeClasses['sub'] }} font-medium {{ $subColor }} block mt-0.5 tracking-tight">
                Comfort & Reliability
            </span>
        @endif
    </div>
</div>
