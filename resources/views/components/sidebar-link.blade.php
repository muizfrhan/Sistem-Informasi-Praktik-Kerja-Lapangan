@props(['route', 'label', 'icon' => null, 'collapsed' => false, 'badge' => null])

@php
$isActive = request()->routeIs($route);
@endphp

{{--
  Item navigasi sidebar — persis pola template:
  active = bg-primary-container + on-primary-container + font-semibold + shadow-sm.
--}}
<a href="{{ route($route) }}"
    aria-current="{{ $isActive ? 'page' : 'false' }}"
    title="{{ $label }}"
    class="flex items-center justify-between gap-3 px-space-sm py-2 rounded-lg transition-colors {{ $isActive ? 'bg-primary-container text-on-primary-container font-semibold shadow-sm' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface font-medium' }}">

    <span class="flex items-center gap-3 min-w-0">
        @if ($icon)
            <span
                class="shrink-0 w-5 h-5 {{ $isActive ? 'text-on-primary-container' : 'text-outline' }} transition-colors">
                {!! $icon !!}
            </span>
        @else
            <span
                class="shrink-0 w-5 h-5 {{ $isActive ? 'text-on-primary-container' : 'text-outline' }} transition-colors">
                <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </span>
        @endif

        <span class="truncate">{{ $label }}</span>
    </span>

    @if ($badge !== null)
        <span
            class="shrink-0 min-w-[1.25rem] h-5 px-1.5 inline-flex items-center justify-center rounded-full text-label-sm font-semibold {{ $isActive ? 'bg-on-primary-container/20 text-on-primary-container' : 'bg-primary-fixed text-primary' }}">
            {{ $badge }}
        </span>
    @endif
</a>
