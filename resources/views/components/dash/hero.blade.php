@props([
    'name' => null,
    'subtitle' => null,
    'greeting' => null,
])

@php
    // Sapaan berbasis waktu WITA (UTC+8).
    $jam = (int) \Carbon\Carbon::now('Asia/Makassar')->format('G');
    $sapaan = $jam < 11 ? 'Selamat Pagi' : ($jam < 15 ? 'Selamat Siang' : ($jam < 19 ? 'Selamat Sore' : 'Selamat Malam'));
@endphp

{{--
  Hero dashboard — kartu forgo `rounded-xl` dengan ambient glow, sapaan,
  chip metadata, dan deretan aksi di kanan (mengikuti template dashboard.html).

  Slot: `badge` (status pill), `meta` (chip), `extra`, `actions`.
--}}
<div
    {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-xl bg-surface-container-lowest shadow-sm p-space-lg']) }}>

    {{-- Ambient glow: kedalaman tanpa drop shadow --}}
    <div class="absolute -right-16 -top-16 w-80 h-80 rounded-full bg-primary-fixed/50 blur-3xl pointer-events-none"
        aria-hidden="true"></div>
    <div
        class="absolute right-40 -bottom-20 w-64 h-64 rounded-full bg-secondary-fixed/40 blur-2xl pointer-events-none"
        aria-hidden="true"></div>

    <div class="relative z-10 flex flex-col xl:flex-row xl:items-center justify-between gap-space-lg">
        <div class="space-y-space-md max-w-3xl min-w-0">
            @isset($badge)
                <div class="flex flex-wrap items-center gap-space-sm">{{ $badge }}</div>
            @endisset

            <div>
                <h1 class="font-headline-lg text-headline-lg text-on-surface font-bold tracking-tight">
                    {{ $greeting ?? $sapaan }}@if ($name), <span
                        class="text-primary">{{ $name }}</span>@endif !
                </h1>

                @if ($subtitle)
                    <p class="font-body-md text-body-md text-on-surface-variant mt-1">{{ $subtitle }}</p>
                @endif

                @isset($meta)
                    <div class="pt-1">{{ $meta }}</div>
                @endisset
            </div>

            @isset($extra)
                <div class="pt-2">{{ $extra }}</div>
            @endisset
        </div>

        @isset($actions)
            <div class="flex flex-col sm:flex-row xl:flex-col gap-space-sm shrink-0 xl:w-72">
                {{ $actions }}
            </div>
        @endisset
    </div>
</div>
