@props([
    'tone' => 'info',           // info | success | warning | error
    'icon' => null,
    'title' => null,
    'role' => 'alert',
])

@php
    $tones = [
        'info' => ['wrap' => 'bg-surface-container border-outline-variant text-on-surface-variant', 'icon' => 'info'],
        'success' => ['wrap' => 'bg-tertiary-container border-[#BBF7D0] text-on-tertiary-container', 'icon' => 'check_circle'],
        'warning' => ['wrap' => 'bg-secondary-container border-[#FDE68A] text-on-secondary-container', 'icon' => 'hourglass_top'],
        'error' => ['wrap' => 'bg-error-container border-[#FECACA] text-on-error-container', 'icon' => 'error'],
    ];
    $t = $tones[$tone] ?? $tones['info'];
@endphp

<div {{ $attributes->merge(['class' => 'flex items-start gap-2.5 p-3 rounded-lg border ' . $t['wrap']]) }}
    role="{{ $role }}">
    <span class="material-symbols-outlined text-[18px] shrink-0 mt-px" aria-hidden="true">{{ $icon ?? $t['icon'] }}</span>
    <div class="min-w-0 flex-1">
        @if ($title)
            <p class="font-title-sm text-[13px] leading-none">{{ $title }}</p>
        @endif
        <div class="text-body-sm text-[12px] leading-relaxed {{ $title ? 'mt-1' : '' }}">
            {{ $slot }}
        </div>
    </div>
</div>
