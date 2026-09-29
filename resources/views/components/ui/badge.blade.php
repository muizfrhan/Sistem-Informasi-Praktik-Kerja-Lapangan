@props([
    'label',
    'tone' => 'slate',   // slate | blue | emerald | amber | rose | violet | cyan
])

@php
    $tones = [
        'slate' => 'bg-slate-100 text-slate-700',
        'blue' => 'bg-blue-100 text-blue-700',
        'emerald' => 'bg-emerald-100 text-emerald-700',
        'amber' => 'bg-amber-100 text-amber-700',
        'rose' => 'bg-rose-100 text-rose-700',
        'violet' => 'bg-violet-100 text-violet-700',
        'cyan' => 'bg-cyan-100 text-cyan-700',
    ];
@endphp

<span
    class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 font-label-sm text-label-sm font-semibold {{ $tones[$tone] ?? $tones['slate'] }}">
    {{ $label }}
</span>