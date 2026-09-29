@props([
    'icon' => null,
    'title' => null,
    'subtitle' => null,
    'heading' => 'h2',
    'flush' => false,
])

{{--
  Panel dashboard — kartu putih `rounded-xl` dengan header berdivider,
  mengikuti pola section di template dashboard.html.
--}}
<section
    {{ $attributes->merge(['class' => 'rounded-xl bg-surface-container-lowest shadow-sm']) }}>
    @if ($title || $icon || isset($actions))
        <div
            class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-space-md pb-space-md border-b border-surface-container {{ $flush ? 'px-4 py-3' : 'px-space-lg py-space-md' }}">
            <div class="flex items-center gap-space-xs min-w-0">
                @if ($icon)
                    <span class="material-symbols-outlined text-primary text-title-md" aria-hidden="true">
                        {{ $icon }}
                    </span>
                @endif
                @if ($title)
                    <div class="min-w-0">
                        <{{ $heading }} class="font-headline-sm text-headline-sm font-bold text-on-surface">
                            {{ $title }}
                        </{{ $heading }}>
                        @if ($subtitle)
                            <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5 leading-snug">
                                {{ $subtitle }}
                            </p>
                        @endif
                    </div>
                @endif
            </div>

            @isset($actions)
                <div class="shrink-0 flex flex-wrap items-center gap-space-sm">{{ $actions }}</div>
            @endisset
        </div>
    @endif

    <div class="{{ $flush ? 'p-4' : 'p-space-lg' }}">
        {{ $slot }}
    </div>
</section>
