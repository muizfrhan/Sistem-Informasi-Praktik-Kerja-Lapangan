<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'SIPKL — Sistem Informasi Praktik Kerja Lapangan' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=block"
        rel="stylesheet">

    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/sipkl-mark.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/apple-touch-icon.png') }}">
    <meta name="theme-color" content="#2563EB">
</head>

{{-- sidebarOpen: drawer navigasi untuk tablet & mobile (< 1024px). --}}
<body data-sipkl-role="{{ auth()->user()?->labelRole() }}" x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false"
    class="bg-background min-h-screen font-sans">

    {{-- Notifikasi permintaan login QR untuk Device A. `display:contents` supaya
         layout tetap sama; dialog-nya sendiri dirender ke body oleh
         window.sipklAlert (resources/js/app.js). --}}
    <div class="contents" x-data="qrApprover({
            pending: @js(route('qr.pending')),
            decideBase: @js(url('/perangkat/qr')),
         })" x-init="start()">

    <x-navbar />

    <div class="pt-app-bar">
        {{-- Overlay saat drawer terbuka --}}
        <div x-cloak x-show="sidebarOpen" x-transition.opacity.duration.200ms x-on:click="sidebarOpen = false"
            class="fixed inset-0 top-app-bar z-30 bg-secondary/50 backdrop-blur-sm lg:hidden" aria-hidden="true"></div>

        {{-- Sidebar: 280px (ekspanded) · drawer overlay di bawah lg --}}
        <aside id="app-sidebar"
            class="fixed left-0 top-app-bar bottom-0 z-40 w-sidebar flex-shrink-0 overflow-y-auto overscroll-contain bg-white border-r border-outline-variant transition-transform duration-300 ease-out lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0 shadow-level-4' : '-translate-x-full'"
            aria-label="Navigasi utama">

            <div class="flex items-center justify-between gap-2 px-4 py-3 lg:hidden sticky top-0 bg-white border-b border-outline-variant z-10">
                <span class="font-title-sm text-on-surface">Menu</span>
                <button type="button" x-on:click="sidebarOpen = false" aria-label="Tutup menu"
                    class="p-1.5 -mr-1.5 rounded-md text-outline hover:bg-surface-container transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <nav class="p-3">
                @php $role = Auth::user()?->role; @endphp

                @if ($role === 'mahasiswa')
                    <x-sidebar-mahasiswa :collapsed="false" />
                @elseif ($role === 'dosen')
                    <x-sidebar-dosen :collapsed="false" />
                @elseif ($role === 'admin')
                    <x-sidebar-admin :collapsed="false" />
                @else
                    {{-- Peran tanpa sidebar khusus (pimpinan, koordinator, pembimbing perusahaan). --}}
                    <p class="px-3 pt-3 pb-2 font-label-sm text-[11px] uppercase tracking-wider text-outline">Menu</p>
                    <x-sidebar-link route="profile.edit" label="Profil saya" :collapsed="false"
                        icon='<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                          </svg>' />

                    <div class="mt-4 mx-1 p-3 rounded-lg bg-surface-container border border-outline-variant">
                        <p class="font-title-sm text-[13px] text-on-surface">{{ Auth::user()?->labelRole() }}</p>
                        <p class="mt-1 text-body-sm text-[12px] text-on-surface-variant leading-relaxed">
                            Dasbor khusus peran ini belum tersedia. Hubungi administrator bila ini keliru.
                        </p>
                    </div>
                @endif
            </nav>
        </aside>

        {{-- Content canvas: max 1600px · margin 16/24/32px --}}
        <main class="min-w-0 lg:ml-sidebar">
            <div class="mx-auto w-full max-w-canvas px-4 py-4 md:px-6 md:py-6 lg:px-8 lg:py-8">
                {{ $slot }}
            </div>
        </main>
    </div>

    @stack('modals')
    @stack('scripts')

    {{-- Flash pesan (hasil create/update/delete + ringkasan validasi).
         Ditampilkan sebagai toast oleh window.sipklAlert. --}}
    <x-flash-messages />
    </div>
</body>

</html>
