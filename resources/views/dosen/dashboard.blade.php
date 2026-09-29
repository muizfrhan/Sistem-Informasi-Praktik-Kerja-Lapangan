@php
    $dosen = Auth::user()->dosen;

    // Data statistik dikirimkan oleh Dosen\DashboardController — tidak diubah.
    $persentaseDisetujui = ($totalMahasiswa + $bimbinganDisetujui + $bimbinganPending) > 0
        ? ($bimbinganDisetujui / max(1, $bimbinganDisetujui + $bimbinganPending)) * 100
        : 0;
@endphp

<x-app-layout>
    <div class="space-y-space-lg">

        {{-- ================= HERO ================= --}}
        <x-dash.hero :name="Auth::user()->name"
            subtitle="Kelola persetujuan bimbingan, verifikasi laporan, dan penilaian PKL mahasiswa bimbingan Anda.">

            <x-slot:badge>
                <span
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-surface-container text-on-surface font-label-sm text-[11px]">
                    <span class="material-symbols-outlined text-[14px] text-primary" aria-hidden="true">verified_user</span>
                    <span>Portal Dosen Pembimbing &bull; {{ $totalMahasiswa }} Mahasiswa Bimbingan</span>
                </span>
            </x-slot:badge>

            <x-slot:meta>
                <div class="flex flex-wrap items-center gap-2">
                    <span
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-surface-container-low text-on-surface">
                        <span class="material-symbols-outlined text-[15px] text-primary" aria-hidden="true">
                            calendar_month</span>
                        <span class="font-body-sm text-[13px]">{{ \Carbon\Carbon::now()->translatedFormat('l, j F Y') }}</span>
                    </span>

                    <span
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-surface-container-low text-on-surface">
                        <span class="material-symbols-outlined text-[15px] text-primary" aria-hidden="true">schedule</span>
                        <span id="clock" class="font-body-sm text-[13px] tabular-nums">--:--:--</span>
                        <span class="font-body-sm text-[13px] text-on-surface-variant">WITA</span>
                    </span>

                    @if ($dosen?->nip)
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-surface-container-low text-on-surface">
                            <span class="material-symbols-outlined text-[15px] text-secondary" aria-hidden="true">pin</span>
                            <span class="font-body-sm text-[13px]">{{ $dosen->nip }}</span>
                        </span>
                    @endif
                </div>
            </x-slot:meta>

            <x-slot:actions>
                <a href="{{ route('dosen.mahasiswa.bimbingan') }}" class="btn-primary btn-md w-full">
                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">groups</span>
                    Mahasiswa Bimbingan
                </a>

                <a href="{{ route('dosen.bimbingan') }}" class="btn-secondary btn-md w-full">
                    <span class="material-symbols-outlined text-[18px] text-primary" aria-hidden="true">event_available</span>
                    Jadwal Bimbingan
                </a>

                <a href="{{ route('dosen.nilai') }}" class="btn-ghostish btn-md w-full">
                    <span class="material-symbols-outlined text-[16px]" aria-hidden="true">workspace_premium</span>
                    Input Nilai PKL
                </a>
            </x-slot:actions>
        </x-dash.hero>

        {{-- ================= BARIS METRIK ================= --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-space-md">

            {{-- Total mahasiswa bimbingan --}}
            <x-dash.stat label="Total Mahasiswa Bimbingan" icon="groups" tone="primary" :value="$totalMahasiswa"
                suffix="orang">
                <p class="font-body-sm text-on-surface-variant">Sebanyak {{ $totalMahasiswa }} mahasiswa dalam batas
                    bimbingan Anda.</p>
            </x-dash.stat>

            {{-- Bimbingan disetujui --}}
            <x-dash.stat label="Bimbingan Disetujui" icon="check_circle" tone="tertiary"
                :value="$bimbinganDisetujui" suffix="sesi">
                <x-dash.progress :value="$persentaseDisetujui" tone="tertiary" />
                <p class="font-body-sm text-on-surface-variant mt-2">
                    <span class="font-semibold text-tertiary">{{ number_format($persentaseDisetujui, 1) }}%</span> dari
                    total permintaan bimbingan.
                </p>
            </x-dash.stat>

            {{-- Bimbingan pending --}}
            <x-dash.stat label="Menunggu Persetujuan" icon="pending_actions" tone="warning" :value="$bimbinganPending"
                suffix="permintaan">
                <x-slot:badge>
                    <x-dash.pill :tone="$bimbinganPending > 0 ? 'warning' : 'success'" size="sm">
                        {{ $bimbinganPending > 0 ? 'Perlu ditinjau' : 'Semua beres' }}
                    </x-dash.pill>
                </x-slot:badge>

                <p class="font-body-sm text-on-surface-variant">
                    @if ($bimbinganPending > 0)
                        Ada {{ $bimbinganPending }} permintaan yang menunggu tindakan Anda.
                    @else
                        Tidak ada permintaan bimbingan yang menunggu.
                    @endif
                </p>
            </x-dash.stat>
        </div>

        {{-- ================= WORKSPACE 2 KOLOM ================= --}}
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-space-lg">

            {{-- ---------- KOLOM KIRI (8) ---------- --}}
            <div class="xl:col-span-8 space-y-space-lg">

                {{-- Tentang SIPKL --}}
                <x-dash.panel icon="info" title="Apa itu SIPKL?"
                    subtitle="Platform digital untuk Praktik Kerja Lapangan">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-space-lg items-center">
                        <div class="order-2 lg:order-1 flex justify-center">
                            <div class="relative">
                                <div
                                    class="absolute -inset-4 bg-primary-fixed rounded-full blur-2xl opacity-80 pointer-events-none"
                                    aria-hidden="true"></div>
                                <img src="{{ asset('Mascot.png') }}" alt="SIPKL Mascot"
                                    class="relative w-[220px] h-[220px] sm:w-[260px] sm:h-[260px] object-contain">
                            </div>
                        </div>

                        <div class="order-1 lg:order-2 space-y-3">
                            <p class="font-body-md text-on-surface-variant leading-relaxed">
                                <span class="font-semibold text-primary">Sistem Informasi Praktik Kerja Lapangan</span>
                                adalah platform digital yang memfasilitasi mahasiswa dalam mengelola seluruh proses PKL
                                dengan mudah, melalui fitur Pendaftaran Online, Pengajuan Bimbingan Online, dan
                                Pelaporan Digital.
                            </p>
                            <p class="font-body-md text-on-surface-variant leading-relaxed">
                                Selanjutnya, Anda dapat melihat ringkasan informasi Mahasiswa PKL pada bagian di bawah
                                ini.
                            </p>
                        </div>
                    </div>
                </x-dash.panel>

                {{-- Tips & Pengumuman --}}
                <x-dash.panel icon="campaign" title="Tips &amp; Pengumuman"
                    subtitle="Panduan singkat untuk pembimbing PKL">
                    {{-- Kedua kartu pakai tone PALE (latar terang, teks gelap) supaya kontrasnya
         konsisten dan lolos WCAG AA. Versi lama memakai bg-tertiary-container
         (#007f36, hijau tua) dengan text-on-surface-variant (#434655, abu tua) —
         rasio kontras hanya 1.82:1, teks praktis tak terbaca.
         Warna aksen hanya lewat ikon + chip kecil, bukan seluruh latar card. --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-space-md">
                        <div class="p-space-md rounded-lg bg-surface-container border border-surface-container-high">
                            <h3
                                class="font-title-sm text-[13px] text-on-surface font-semibold flex items-center gap-1.5">
                                <span
                                    class="inline-flex items-center justify-center w-6 h-6 shrink-0 rounded-md bg-primary-fixed text-on-primary-fixed-variant"
                                    aria-hidden="true">
                                    <span class="material-symbols-outlined text-[15px]">lightbulb</span>
                                </span>
                                Tips Bimbingan
                            </h3>
                            <p class="font-body-sm text-on-surface-variant mt-2 leading-relaxed">
                                Pastikan untuk memeriksa Jadwal Bimbingan mahasiswa secara berkala dan memberikan
                                feedback agar proses PKL berjalan optimal.
                            </p>
                        </div>

                        <div class="p-space-md rounded-lg bg-surface-container border border-surface-container-high">
                            <h3
                                class="font-title-sm text-[13px] text-on-surface font-semibold flex items-center gap-1.5">
                                <span
                                    class="inline-flex items-center justify-center w-6 h-6 shrink-0 rounded-md bg-tertiary-container/15 text-tertiary"
                                    aria-hidden="true">
                                    <span class="material-symbols-outlined text-[15px]">campaign</span>
                                </span>
                                Pengumuman
                            </h3>
                            <p class="font-body-sm text-on-surface-variant mt-2 leading-relaxed">
                                Silakan input nilai PKL mahasiswa maksimal 1 minggu setelah laporan akhir
                                dikumpulkan.
                            </p>
                        </div>
                    </div>
                </x-dash.panel>
            </div>

            {{-- ---------- KOLOM KANAN (4) ---------- --}}
            <div class="xl:col-span-4 space-y-space-lg">

                {{-- Antrean tugas --}}
                <x-dash.panel icon="fact_check" title="Antrean Tugas Anda" subtitle="Hal yang perlu ditinjau lebih dulu">
                    <div class="space-y-3">
                        @foreach ([
    ['Permintaan bimbingan', $bimbinganPending, 'warning', 'dosen.bimbingan', 'event_available'],
    ['Mahasiswa dalam bimbingan', $totalMahasiswa, 'primary', 'dosen.mahasiswa.bimbingan', 'groups'],
    ['Input nilai PKL', null, 'secondary', 'dosen.nilai', 'workspace_premium'],
] as [$judul, $jumlah, $tone, $rute, $ikon])
                            <a href="{{ route($rute) }}"
                                class="flex items-center gap-3 p-3 rounded-lg bg-surface-container-low hover:bg-surface-container transition-colors">
                                <span
                                    class="w-9 h-9 shrink-0 rounded-lg flex items-center justify-center {{ match ($tone) { 'warning' => 'bg-secondary-container text-secondary', 'primary' => 'bg-primary-fixed text-primary', default => 'bg-surface-container-high text-secondary',
} }}">
                                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">{{ $ikon }}</span>
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block font-title-sm text-[13px] font-semibold text-on-surface truncate">
                                        {{ $judul }}
                                    </span>
                                    <span class="block font-body-sm text-on-surface-variant">
                                        @if ($jumlah === null)
                                            Buka modul penilaian
                                        @else
                                            {{ $jumlah }} item
                                        @endif
                                    </span>
                                </span>
                                <span class="material-symbols-outlined text-[16px] text-outline shrink-0"
                                    aria-hidden="true">chevron_right</span>
                            </a>
                        @endforeach
                    </div>
                </x-dash.panel>

                {{-- Pintasan modul --}}
                <x-dash.panel icon="apps" title="Pintasan Modul" subtitle="Navigasi cepat">
                    <div class="space-y-1">
                        @foreach ([
    ['dosen.mahasiswa.bimbingan', 'Mahasiswa Bimbingan', 'groups'],
    ['dosen.bimbingan', 'Jadwal Bimbingan', 'calendar_month'],
    ['dosen.nilai', 'Input Nilai PKL', 'workspace_premium'],
    ['profile.edit', 'Profil Saya', 'account_circle'],
] as [$rute, $judul, $ikon])
                            <a href="{{ route($rute) }}"
                                class="flex items-center justify-between gap-3 p-2.5 rounded-lg bg-surface-container-low hover:bg-surface-container transition-colors">
                                <span class="flex items-center gap-2.5 min-w-0">
                                    <span class="material-symbols-outlined text-[18px] text-primary shrink-0"
                                        aria-hidden="true">{{ $ikon }}</span>
                                    <span class="font-title-sm text-[13px] font-semibold text-on-surface truncate">
                                        {{ $judul }}
                                    </span>
                                </span>
                                <span class="material-symbols-outlined text-[16px] text-outline shrink-0"
                                    aria-hidden="true">chevron_right</span>
                            </a>
                        @endforeach
                    </div>
                </x-dash.panel>
            </div>
        </div>
    </div>

    <script>
        function updateClock() {
            const now = new Date();
            // Zona WITA = UTC+8
            const utc = now.getTime() + (now.getTimezoneOffset() * 60000);
            const wita = new Date(utc + (8 * 60 * 60 * 1000));
            const h = String(wita.getHours()).padStart(2, '0');
            const m = String(wita.getMinutes()).padStart(2, '0');
            const s = String(wita.getSeconds()).padStart(2, '0');
            const el = document.getElementById('clock');
            if (el) el.textContent = `${h}:${m}:${s}`;
        }
        updateClock();
        setInterval(updateClock, 1000);
    </script>
</x-app-layout>
