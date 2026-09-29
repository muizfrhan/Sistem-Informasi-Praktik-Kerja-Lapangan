@props([
    'value' => 0,
    'max' => 100,
    'tone' => 'blue',
    'label' => null,
    'showValue' => true,
    'size' => 'md',   // sm | md | lg
])

@php
    $persen = $max > 0 ? round(($value / $max) * 100, 1) : 0;
    $bars = ['sm' => 'h-1.5', 'md' => 'h-2.5', 'lg' => 'h-3.5'];
    $grad = [
        'blue' => 'from-blue-500 to-cyan-400',
        'emerald' => 'from-emerald-500 to-teal-400',
        'amber' => 'from-amber-500 to-orange-400',
        'rose' => 'from-rose-500 to-pink-400',
        'violet' => 'from-violet-500 to-purple-400',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'w-full']) }}>
    @if ($label || $showValue)
        <div class="mb-1.5 flex items-center justify-between gap-2">
            @if ($label)
                <span class="font-body-sm text-body-sm text-gray-600">{{ $label }}</span>
            @else
                <span></span>
            @endif
            @if ($showValue)
                <span class="font-title-sm text-title-sm font-semibold text-gray-900">{{ $persen }}%</span>
            @endif
        </div>
    @endif
    <div class="w-full overflow-hidden rounded-full bg-gray-200 bg-white/10 {{ $bars[$size] }}"
        role="progressbar" aria-valuenow="{{ $persen }}" aria-valuemin="0" aria-valuemax="100"
        aria-label="{{ $label ?? 'Progres' }}">
        <div class="h-full rounded-full bg-gradient-to-r {{ $grad[$tone] ?? $grad['blue'] }} transition-all duration-500"
            style="width: {{ min(100, $persen) }}%"></div>
    </div>
</div>