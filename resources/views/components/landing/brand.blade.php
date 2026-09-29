@props([
    'size' => 'h-8',       // tinggi logo di navbar
    'showText' => true,   // tampilkan wordmark "SIPKL"
    'tag' => 'a',
])

@php
    $brand = 'group flex shrink-0 items-center gap-2.5 sm:gap-3';
    $classes = trim($size . ' w-auto object-contain transition-transform duration-200 group-hover:scale-105');
@endphp

<{{ $tag }} @if($tag === 'a') href="{{ route('landing') }}" @endif
    class="{{ $brand }}"
    @if($tag === 'a') aria-label="SIPKL — Sistem Informasi Praktik Kerja Lapangan, beranda" @endif>
    <img src="{{ asset('images/sipkl-mark.svg') }}" alt="Logo SIPKL" class="{{ $classes }}"
        width="64" height="64" loading="eager" decoding="async">

    @if($showText)
        <span class="flex flex-col leading-none">
            <span
                class="font-headline-sm text-headline-sm font-extrabold tracking-tight text-primary">SIPKL</span>
            {{-- Subtitle hanya ditampilkan mulai layar kecil agar navbar tidak meluber --}}
            <span
                class="mt-0.5 hidden font-label-sm text-label-sm font-medium leading-tight text-on-surface-variant sm:block">Sistem
                Informasi Praktik Kerja Lapangan</span>
        </span>
    @endif
</{{ $tag }}>
