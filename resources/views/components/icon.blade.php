@props([
    'name',
    'class' => 'text-2xl',
    'filled' => false,
])

<span class="material-symbols-outlined {{ $class }}"
    style="{{ $filled ? "font-variation-settings: 'FILL' 1, 'wght' 500;" : '' }}"
    aria-hidden="true">{{ $name }}</span>
