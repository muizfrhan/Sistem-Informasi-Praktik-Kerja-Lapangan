@props([
    'tone' => 'neutral',   // neutral | primary | success | warning | danger
    'icon' => null,
    'size' => 'md',        // sm | md
])

@php
    // String literal agar Tailwind JIT memindainya.
    $class = match ($tone) {
        'primary' => 'bg-primary-fixed text-on-primary-fixed-variant border-primary-fixed-dim',
        'success' => 'bg-tertiary-container text-on-tertiary-container border-tertiary-fixed-dim',
        'warning' => 'bg-secondary-container text-on-secondary-container border-outline-variant',
        'danger' => 'bg-error-container text-on-error-container border-error',
        default => 'bg-surface-container text-on-surface-variant border-outline-variant',
    };

    $textSize = $size === 'sm' ? 'text-[10px] px-2 py-0.5' : 'text-label-sm px-2.5 py-0.5';
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 rounded-full border font-semibold whitespace-nowrap ' . $textSize . ' ' . $class]) }}>
    @if ($icon)
        <span class="material-symbols-outlined {{ $size === 'sm' ? 'text-label-sm' : 'text-label-md' }}" aria-hidden="true">
            {{ $icon }}
        </span>
    @endif
    {{ $slot }}
</span>
