@props(['column', 'label', 'current' => null, 'direction' => 'asc'])

@php
    $isActive = $current === $column;
    $next = $isActive && $direction === 'asc' ? 'desc' : 'asc';
    $icon = $isActive
        ? ($direction === 'asc' ? 'arrow_upward' : 'arrow_downward')
        : 'unfold_more';
@endphp

<a href="{{ request()->fullUrlWithQuery(array_merge(request()->query(), ['sort' => $column, 'direction' => $next])) }}"
    class="inline-flex items-center gap-1 transition-colors hover:text-blue-600 {{ $isActive ? 'text-blue-600' : '' }}">
    {{ $label }}
    <x-icon :name="$icon" class="h-3.5 w-3.5" />
</a>