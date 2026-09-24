@props([
    'variant' => 'dark', // 'dark' (for light background) or 'light' (for dark background)
    'size' => 'md',      // 'xs', 'sm', 'md', 'lg', 'xl'
    'withSubtitle' => false,
])

@php
    $imgHeight = match($size) {
        'xs' => 'h-6',
        'sm' => 'h-8',
        'lg' => 'h-12',
        'xl' => 'h-16 sm:h-20',
        default => 'h-10',
    };

    $containerClasses = $variant === 'light'
        ? 'bg-white rounded-xl px-2.5 py-1 shadow-sm ring-1 ring-white/30 hover:bg-white transition inline-flex items-center'
        : 'inline-flex items-center';
@endphp

<div {{ $attributes->merge(['class' => $containerClasses . ' select-none transition-transform group-hover:scale-[1.02]']) }}>
    <!-- Official CAN Travel Brand Logo (Aspect Ratio 2:1) -->
    <img src="{{ asset('images/logo/can-travel-logo.png') }}"
         alt="CAN Travel"
         class="{{ $imgHeight }} w-auto object-contain block flex-shrink-0"
         loading="eager"
         decoding="async">
</div>
