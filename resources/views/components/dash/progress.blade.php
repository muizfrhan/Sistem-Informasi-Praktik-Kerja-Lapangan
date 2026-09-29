@props([
    'value' => 0,
    'tone' => 'primary',   // primary | tertiary | warning | danger
    'size' => 'md',        // sm | md | lg
])

@php
    $fill = match ($tone) {
        'tertiary' => 'bg-tertiary',
        'warning' => 'bg-secondary',
        'danger' => 'bg-error',
        default => 'bg-primary',
    };

    $track = match ($size) {
        'sm' => 'h-1',
        'lg' => 'h-2.5',
        default => 'h-2',
    };

    $persen = max(0, min(100, (float) $value));
@endphp

<div {{ $attributes->merge(['class' => 'w-full bg-surface-container rounded-full ' . $track . ' overflow-hidden']) }}>
    <div class="{{ $fill }} {{ $track }} rounded-full transition-all duration-700" style="width: {{ $persen }}%"></div>
</div>
