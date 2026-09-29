@props([
    'label',
    'value' => null,
    'icon' => 'insights',
    'tone' => 'primary',   // primary | tertiary | secondary | warning | danger
    'suffix' => null,
    'hint' => null,
    'valueClass' => null,   // kelas custom untuk nilai non-angka
])

@php
    // Warna ditulis sebagai string literal agar Tailwind JIT memindainya.
    $iconClass = match ($tone) {
        'primary' => 'bg-primary-fixed text-primary',
        'tertiary' => 'bg-tertiary-fixed text-tertiary',
        'secondary' => 'bg-secondary-fixed text-secondary',
        'warning' => 'bg-secondary-container text-secondary',
        'danger' => 'bg-error-container text-error',
        default => 'bg-primary-fixed text-primary',
    };

    $valueTone = match ($tone) {
        'primary' => 'text-primary',
        'tertiary' => 'text-tertiary',
        'secondary' => 'text-secondary',
        'warning' => 'text-secondary',
        'danger' => 'text-error',
        default => 'text-on-surface',
    };
@endphp

{{--
  Kartu metrik — pola template: label uppercase di kiri, ikon di kotak
  40px di kanan, angka besar, lalu slot footer (progress / mini-stat).
  Slot `badge` untuk pill di sebelah angka.
--}}
<div
    {{ $attributes->merge(['class' => 'rounded-xl bg-surface-container-lowest shadow-sm p-space-lg flex flex-col justify-between hover:shadow-level-2 transition-shadow duration-200']) }}>
    <div class="flex items-start justify-between">
        <div class="min-w-0">
            <span class="font-label-sm text-label-sm uppercase tracking-wider text-outline block">
                {{ $label }}
            </span>

            @if ($value !== null)
                <div class="flex flex-wrap items-baseline gap-2 mt-1">
                    <span
                        class="font-bold tracking-tight {{ $valueClass ?? $valueTone }} {{ $valueClass ? 'text-title-md' : 'text-display text-display' }}">{{ $value }}</span>
                    @if ($suffix)
                        <span class="font-title-sm text-title-sm text-on-surface-variant">{{ $suffix }}</span>
                    @endif
                    @isset($badge)
                        {{ $badge }}
                    @endisset
                </div>
            @endif
        </div>

        <div class="w-10 h-10 rounded-xl {{ $iconClass }} flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined text-headline-sm" aria-hidden="true">{{ $icon }}</span>
        </div>
    </div>

    @if ($hint || isset($footer) || ! $slot->isEmpty())
        <div class="mt-space-md space-y-1.5">
            {{ $slot }}
            @isset($footer)
                {{ $footer }}
            @endisset
            @if ($hint)
                <p class="font-body-sm text-body-sm text-on-surface-variant">{{ $hint }}</p>
            @endif
        </div>
    @endif
</div>
