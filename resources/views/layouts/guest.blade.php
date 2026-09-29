<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Autentikasi — SIPKL' }}</title>
    <meta name="description"
        content="Portal autentikasi SIPKL — Sistem Informasi Praktik Kerja Lapangan. Logbook harian, presensi geofencing, dan verifikasi laporan magang.">
    <meta name="theme-color" content="#0F172A">

    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/sipkl-mark.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/apple-touch-icon.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=block"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

{{-- Canvas Base #F8FAFC — isolates the active card surface. --}}
<body class="min-h-screen bg-background font-sans text-body-md text-on-surface antialiased">
    <a href="#auth-panel"
        class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[60] focus:rounded-lg focus:bg-primary focus:px-4 focus:py-2 focus:font-title-sm focus:text-on-primary">
        Lewati ke formulir
    </a>

    {{-- Margin: mobile 12px · tablet 24px · desktop 32px.
         items-start di mobile supaya halaman panjang tidak ikut center vertikal
         (align-items:center + tinggi > viewport bikin konten atas terpotong). --}}
    <div
        class="w-full max-w-canvas mx-auto min-h-screen lg:min-h-0 flex items-start lg:items-center justify-center px-3 py-4 sm:px-4 sm:py-5 md:px-6 md:py-8 lg:px-8 lg:py-10">

        {{-- Top-level container: radius XL 24px, elevation level 3.
             w-full + max-w agar tidak pernah melebihi parent. --}}
        <div
            class="w-full max-w-full grid grid-cols-1 lg:grid-cols-12 overflow-hidden rounded-xl border border-outline-variant bg-white shadow-level-3">

            {{-- ============ PANEL KIRI: identitas & nilai produk ============ --}}
            {{-- Di mobile panel identitas disembunyikan agar formulir langsung masuk
                 viewport tanpa perlu scroll melewati konten promosi. --}}
            <div class="hidden lg:col-span-5 lg:block relative overflow-hidden bg-secondary text-inverse-on-surface">

                {{-- Ambient glow — Mendel, bukan dekorasi solidify. --}}
                <div
                    class="absolute -top-24 -left-24 w-80 h-80 rounded-full bg-primary/40 blur-[100px] pointer-events-none">
                </div>
                <div
                    class="absolute -bottom-24 -right-16 w-72 h-72 rounded-full bg-primary/20 blur-[110px] pointer-events-none">
                </div>

                <div class="relative z-10 p-6 sm:p-8 lg:p-12 flex flex-col justify-between gap-8 min-h-full">

                    {{-- Brand --}}
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <span
                                class="w-11 h-11 lg:w-12 lg:h-12 shrink-0 rounded-lg bg-primary flex items-center justify-center shadow-level-2 relative">
                                <img src="{{ asset('images/sipkl-mark.svg') }}" alt="Logo SIPKL"
                                    class="h-6 w-6 lg:h-7 lg:w-7 object-contain" width="64" height="64">
                                <span
                                    class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 rounded-full bg-tertiary ring-2 ring-secondary"></span>
                            </span>
                            <div class="min-w-0">
                                <p class="font-headline-sm text-[18px] lg:text-headline-sm text-white font-bold tracking-tight leading-none">
                                    SIPKL <span class="text-primary-fixed-dim">CONNECT</span>
                                </p>
                                <p class="mt-1 font-label-sm text-[10px] uppercase tracking-widest text-white/50">
                                    Praktik Kerja Lapangan
                                </p>
                            </div>
                        </div>

                        <span
                            class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/10 text-white/70 font-label-sm shrink-0">
                            <span class="w-1.5 h-1.5 rounded-full bg-tertiary-fixed"></span>
                            v1.0
                        </span>
                    </div>

                    {{-- Headline --}}
                    <div class="space-y-4">
                        <span
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-primary/20 text-primary-fixed-dim font-label-sm">
                            <span class="material-symbols-outlined text-[15px]" aria-hidden="true">verified</span>
                            Platform Vokasi Nasional
                        </span>

                        <h1
                            class="text-headline-lg-mobile md:text-headline-lg text-white font-bold tracking-tight leading-[1.15]">
                            Akselerasi Praktik Kerja, Bebas Hambatan Birokrasi.
                        </h1>

                        <p class="text-body-md text-white/60 leading-relaxed max-w-md">
                            Jembatan kolaborasi terverifikasi antara SMK/Politeknik, Guru Pembimbing, dan mitra
                            industri (DUDI) bereputasi tinggi.
                        </p>
                    </div>

                    {{-- Metrik --}}
                    <div class="grid grid-cols-2 gap-3">
                        @foreach ([
    ['fact_check', '99.8%', 'Presensi Geofencing', 'text-tertiary-fixed'],
    ['qr_code_scanner', 'Otomatis', 'E-Sertifikat QR', 'text-primary-fixed-dim'],
] as [$ikon, $angka, $judul, $warna])
                            <div class="p-4 rounded-lg bg-white/[0.06] border border-white/10">
                                <span class="material-symbols-outlined {{ $warna }} text-[22px]" aria-hidden="true">
                                    {{ $ikon }}
                                </span>
                                <p class="mt-2 font-title-md text-white text-[15px] leading-none">{{ $angka }}</p>
                                <p class="mt-1 font-body-sm text-white/50 text-[12px] leading-tight">{{ $judul }}</p>
                            </div>
                        @endforeach
                    </div>

                    {{-- Fitur --}}
                    <ul class="space-y-2.5">
                        @foreach ([
    'Sinkronisasi otomatis data siswa & akademik kampus',
    'Logbook harian & penilaian kompetensi real-time',
    'Penugasan berbasis Capaian Pembelajaran (CP)',
] as $fitur)
                            <li class="flex items-start gap-2.5">
                                <span
                                    class="mt-0.5 w-5 h-5 shrink-0 rounded-full bg-tertiary/20 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-tertiary-fixed text-[13px]"
                                        aria-hidden="true">check</span>
                                </span>
                                <span class="text-body-sm text-white/70 leading-snug">{{ $fitur }}</span>
                            </li>
                        @endforeach
                    </ul>

                    {{-- Kredibilitas --}}
                    <div class="pt-2 space-y-5">
                        <figure
                            class="p-4 rounded-lg bg-white/[0.06] border border-white/10 relative">
                            <span class="material-symbols-outlined text-white/15 absolute right-3 top-2 text-[34px]"
                                aria-hidden="true">format_quote</span>
                            <blockquote class="text-body-sm text-white/80 italic leading-relaxed pr-7">
                                “SIPKL memangkas birokrasi izin dan jurnal harian kami. Monitoring evaluasi siswa di
                                24 kota terselesaikan dalam satu dashboard terpadu.”
                            </blockquote>
                            <figcaption class="mt-3 flex items-center gap-3">
                                <span
                                    class="w-9 h-9 shrink-0 rounded-full bg-white/10 flex items-center justify-center font-title-sm text-primary-fixed-dim font-semibold">
                                    DR
                                </span>
                                <span class="min-w-0">
                                    <span class="block font-title-sm text-[13px] text-white">Drs. Rahmad Hidayat,
                                        M.Kom</span>
                                    <span class="block font-label-sm text-[11px] text-white/45">Koordinator Hubungan
                                        Industri</span>
                                </span>
                            </figcaption>
                        </figure>

                        <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-white/45 font-label-sm">
                            <span class="flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[15px]" aria-hidden="true">school</span>
                                Terakreditasi A
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[15px]" aria-hidden="true">lock</span>
                                SSL 256-bit
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[15px]" aria-hidden="true">encrypted</span>
                                TLS 1.3
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ============ PANEL KANAN: formulir ============ --}}
            {{-- min-w-0 wajib: kolom grid flex/grid tanpa ini bisa memaksa
                 lebar panel melebihi allotted track dan memicu overflow. --}}
            <div id="auth-panel"
                class="lg:col-span-7 min-w-0 bg-white p-4 sm:p-8 lg:p-12 flex flex-col justify-center">

                {{-- Header mobile — pengganti ringkasan panel kiri yang disembunyikan. --}}
                <div class="lg:hidden flex items-center justify-between gap-3 pb-4 sm:pb-5 mb-5 sm:mb-6 border-b border-outline-variant">
                    <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                        <span
                            class="w-10 h-10 sm:w-11 sm:h-11 shrink-0 rounded-lg bg-primary flex items-center justify-center shadow-level-2">
                            <img src="{{ asset('images/sipkl-mark.svg') }}" alt="Logo SIPKL"
                                class="h-5 w-5 object-contain" width="64" height="64">
                        </span>
                        <div class="min-w-0">
                            <p
                                class="font-title-md text-[16px] font-bold text-on-surface tracking-tight leading-none whitespace-nowrap">
                                SIPKL <span class="text-primary">CONNECT</span>
                            </p>
                            <p class="mt-1 font-label-sm text-[10px] uppercase tracking-widest text-outline truncate">
                                Praktik Kerja Lapangan
                            </p>
                        </div>
                    </div>
                    {{-- Hanya ikon di layar sangat sempit; teksnya sudah ada di
                         panel kiri / footer desktop. --}}
                    <span
                        class="hidden sm:shrink-0 sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-surface-container text-outline font-label-sm whitespace-nowrap">
                        <span class="material-symbols-outlined text-[14px]" aria-hidden="true">lock</span>
                        SSL 256
                    </span>
                </div>

                {{ $slot }}
            </div>
        </div>
    </div>

    {{-- Flash pesan (hasil login/register + ringkasan validasi) --}}
    <x-flash-messages />

    @stack('scripts')
</body>

</html>
