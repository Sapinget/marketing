@props([
    'fill1' => 'rgba(255,165,0,.08)',
    'fill2' => 'rgba(255,165,0,.14)',
    'height1' => 'h-40',
    'height2' => 'h-28',
    'class' => '',
])

<div class="pointer-events-none absolute inset-x-0 bottom-0 overflow-hidden {{ $class }}">
    <svg class="wave-back pointer-events-none absolute inset-x-0 bottom-0 w-full {{ $height1 }}" viewBox="0 0 1440 200" preserveAspectRatio="none" aria-hidden="true">
        <path fill="{{ $fill1 }}" d="M0,110 C240,170 480,50 720,90 C960,130 1200,30 1440,80 L1440,200 L0,200 Z" />
    </svg>
    <svg class="wave-front pointer-events-none absolute inset-x-0 bottom-0 w-full {{ $height2 }}" viewBox="0 0 1440 200" preserveAspectRatio="none" aria-hidden="true">
        <path fill="{{ $fill2 }}" d="M0,140 C260,90 500,190 760,130 C1020,70 1220,160 1440,110 L1440,200 L0,200 Z" />
    </svg>
</div>
