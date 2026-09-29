@props([
    'label',
    'value',
    'icon' => 'info',
    'tone' => 'blue',        // blue | cyan | emerald | amber | rose | violet | slate
    'hint' => null,
    'href' => null,
])

@php
    $tones = [
        'blue' => 'from-blue-500 to-cyan-500',
        'cyan' => 'from-cyan-500 to-teal-500',
        'emerald' => 'from-emerald-500 to-teal-500',
        'amber' => 'from-amber-500 to-orange-500',
        'rose' => 'from-rose-500 to-pink-500',
        'violet' => 'from-violet-500 to-purple-500',
        'slate' => 'from-slate-500 to-slate-600',
    ];
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }} @if($href) href="{{ $href }}" @endif
    class="group flex items-center gap-4 rounded-2xl border border-gray-200 border-white/10 bg-white bg-white/[0.03] p-5 transition-all duration-200 {{ $href ? 'hover:-translate-y-0.5 hover:shadow-lg' : '' }}">
    <div
        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br {{ $tones[$tone] ?? $tones['blue'] }} text-white shadow-sm">
        <x-icon :name="$icon" class="text-2xl" />
    </div>
    <div class="min-w-0">
        <p class="truncate font-body-sm text-body-sm font-medium text-gray-500">{{ $label }}</p>
        <p class="font-headline-sm text-headline-sm font-bold tracking-tight text-gray-900">{{ $value }}</p>
        @if ($hint)
            <p class="mt-0.5 truncate font-label-sm text-label-sm text-gray-400">{{ $hint }}</p>
        @endif
    </div>
</{{ $tag }}>