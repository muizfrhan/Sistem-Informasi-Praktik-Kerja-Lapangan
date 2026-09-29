<!DOCTYPE html>
<html lang="id" class="scroll-pt-20">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'SIPKL — Sistem Informasi Praktik Kerja Lapangan' }}</title>
    <meta name="description"
        content="{{ $description ?? 'Sistem informasi terintegrasi untuk mengelola Praktik Kerja Lapangan, mulai dari pengajuan, penempatan, monitoring, jurnal, absensi, hingga penilaian.' }}">
    <meta name="theme-color" content="#004ac6">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Branding / share metadata --}}
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/sipkl-mark.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="SIPKL">
    <meta property="og:locale" content="id_ID">
    <meta property="og:title" content="{{ $title ?? 'SIPKL — Sistem Informasi Praktik Kerja Lapangan' }}">
    <meta property="og:description"
        content="{{ $description ?? 'Sistem informasi terintegrasi untuk mengelola Praktik Kerja Lapangan, mulai dari pengajuan, penempatan, monitoring, jurnal, absensi, hingga penilaian.' }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('images/og-image.jpg') }}">
    <meta property="og:image:type" content="image/jpeg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="SIPKL — Sistem Informasi Praktik Kerja Lapangan">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title ?? 'SIPKL — Sistem Informasi Praktik Kerja Lapangan' }}">
    <meta name="twitter:description"
        content="{{ $description ?? 'Sistem informasi terintegrasi untuk mengelola Praktik Kerja Lapangan, mulai dari pengajuan, penempatan, monitoring, jurnal, absensi, hingga penilaian.' }}">
    <meta name="twitter:image" content="{{ asset('images/og-image.jpg') }}">

    {{-- Font: Plus Jakarta Sans + Material Symbols (dipakai sebagai ikon) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=block"
        rel="stylesheet">

    @vite(['resources/css/landing.css', 'resources/js/landing.js'])
</head>

<body class="min-h-screen bg-surface font-body-md text-body-md text-on-surface antialiased selection:bg-primary-container selection:text-on-primary">
    <a href="#konten-utama"
        class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[60] focus:rounded-lg focus:bg-primary focus:px-4 focus:py-2 focus:font-title-sm focus:text-on-primary">
        Lewati ke konten utama
    </a>

    <x-landing.navbar />

    <main id="konten-utama" class="w-full bg-surface">
        {{ $slot }}
    </main>

    <x-landing.footer />
</body>

</html>
