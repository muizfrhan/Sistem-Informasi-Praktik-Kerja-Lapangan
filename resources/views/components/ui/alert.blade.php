@props([
    'tone' => 'info',   // info | success | warning | error
    'title' => null,
    'dismissible' => true,
])

@php
    $tones = [
        'info' => ['wrap' => 'bg-blue-50 border-blue-200 text-blue-800', 'icon' => 'info'],
        'success' => ['wrap' => 'bg-emerald-50 border-emerald-200 text-emerald-800', 'icon' => 'task_alt'],
        'warning' => ['wrap' => 'bg-amber-50 border-amber-200 text-amber-800', 'icon' => 'warning'],
        'error' => ['wrap' => 'bg-rose-50 border-rose-200 text-rose-800', 'icon' => 'error'],
    ];
    $t = $tones[$tone] ?? $tones['info'];
@endphp

<div {{ $attributes->merge(['class' => 'mb-6 flex items-start gap-3 rounded-xl border px-4 py-3 text-sm ' . $t['wrap']]) }}
    @if ($dismissible) x-data="{ show: true }" x-show="show" @endif role="alert">
    <x-icon :name="$t['icon']" class="mt-0.5 h-5 w-5 shrink-0" />
    <div class="flex-1">
        @if ($title)
            <p class="font-semibold">{{ $title }}</p>
        @endif
        <div>{{ $slot }}</div>
    </div>
    @if ($dismissible)
        <button type="button" @click="show = false" aria-label="Tutup"
            class="shrink-0 opacity-60 transition-opacity hover:opacity-100">
            <x-icon name="close" class="h-4 w-4" />
        </button>
    @endif
</div>