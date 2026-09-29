@php
    $mhs = Auth::user()->mahasiswa;

    // Seluruh query di bawah sudah ada sebelumnya — hanya dikelompokkan di satu
    // blok agar tampilan bisa disusun ulang tanpa mengubah logikanya.
    $pendaftaran = $mhs?->pendaftaranPkl()->latest()->first();
    $laporan = $mhs?->laporanPkl()->latest()->first();
    $totalBimbingan = $mhs?->bimbingan->count() ?? 0;
    $bimbinganSelesai = $mhs?->bimbingan()->where('status', 'disetujui')->count() ?? 0;

    $progress = 0;
    if ($pendaftaran && $pendaftaran->status === 'diterima') {
        $progress += 33;
    }
    if ($laporan && $laporan->status === 'diterima') {
        $progress += 33;
    }
    if ($bimbinganSelesai >= 4) {
        $progress += 34;
    }

    $statusPendaftaran = $pendaftaran?->status;
    $statusLaporan = $laporan?->status;

    $pillPendaftaran = match ($statusPendaftaran) {
        'diterima' => 'success',
        'ditolak' => 'danger',
        default => 'warning',
    };
@endphp

<x-app-layout>
    <div class="space-y-space-lg">

        {{-- ================= HERO ================= --}}
        <x-dash.hero :name="$mhs?->nama ?? Auth::user()->name"
            subtitle="Pantau proses Praktik Kerja Lapangan Anda mulai dari pendaftaran, bimbingan, hingga pelaporan digital.">

            {{-- Status pill --}}
            <x-slot:badge>
                <span
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-surface-container text-on-surface font-label-sm text-[11px]">
                    <span class="material-symbols-outlined text-[14px] text-primary" aria-hidden="true">verified_user</span>
                    <span>
                        @if ($statusPendaftaran === 'diterima')
                            Status Penempatan Aktif
                        @elseif ($statusPendaftaran === 'ditolak')
                            Pendaftaran PKL Ditolak
                        @elseif ($statusPendaftaran)
                            Pendaftaran Menunggu Verifikasi
                        @else
                            Belum Mendaftar PKL
                        @endif
                    </span>
                </span>
            </x-slot:badge>

            {{-- Chip metadata: tanggal, jam WITA, NIM, kelas --}}
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

                    @if ($mhs?->nim)
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-surface-container-low text-on-surface">
                            <span class="material-symbols-outlined text-[15px] text-secondary" aria-hidden="true">badge</span>
                            <span class="font-body-sm text-[13px]">{{ $mhs->nim }}</span>
                        </span>
                    @endif

                    @if ($mhs?->kelas)
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-surface-container-low text-on-surface">
                            <span class="material-symbols-outlined text-[15px] text-secondary" aria-hidden="true">groups</span>
                            <span class="font-body-sm text-[13px]">{{ $mhs->kelas }} &middot; Sem {{ $mhs->semester }}</span>
                        </span>
                    @endif
                </div>
            </x-slot:meta>

            {{-- Aksi utama --}}
            <x-slot:actions>
                <a href="{{ route('mahasiswa.pendaftaran') }}" class="btn-primary btn-md w-full">
                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">how_to_reg</span>
                    {{ $pendaftaran ? 'Kelola Pendaftaran' : 'Daftar PKL Sekarang' }}
                </a>

                <a href="{{ route('mahasiswa.laporan') }}" class="btn-secondary btn-md w-full">
                    <span class="material-symbols-outlined text-[18px] text-primary" aria-hidden="true">edit_square</span>
                    Upload Laporan
                </a>

                <a href="{{ route('mahasiswa.bimbingan') }}" class="btn-ghostish btn-md w-full">
                    <span class="material-symbols-outlined text-[16px]" aria-hidden="true">contact_support</span>
                    Ajukan Bimbingan
                </a>
            </x-slot:actions>
        </x-dash.hero>

        {{-- ================= BARIS METRIK ================= --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-space-md">

            {{-- Pendaftaran PKL --}}
            <x-dash.stat label="Status Pendaftaran" icon="how_to_reg" tone="primary"
                :value="$pendaftaran ? ucfirst($statusPendaftaran) : 'Belum'" value-class="text-headline-sm">
                <x-slot:badge>
                    <x-dash.pill :tone="$pillPendaftaran" size="sm">
                        {{ $statusPendaftaran ? ucfirst($statusPendaftaran) : 'Belum mendaftar' }}
                    </x-dash.pill>
                </x-slot:badge>

                <x-slot:footer>
                    @if ($pendaftaran)
                        <p class="font-body-sm text-on-surface-variant truncate" title="{{ $pendaftaran->bidang_pkl }}">
                            {{ $pendaftaran->bidang_pkl }}
                        </p>
                        <p class="font-body-sm text-outline mt-0.5">Periode: {{ $pendaftaran->periode }}</p>
                    @else
                        <p class="font-body-sm text-on-surface-variant">Belum mendaftar.</p>
                    @endif
                </x-slot:footer>
            </x-dash.stat>

            {{-- Laporan PKL --}}
            <x-dash.stat label="Laporan PKL" icon="description" tone="secondary"
                :value="$laporan ? ucfirst($statusLaporan) : 'Belum'" value-class="text-headline-sm">
                <x-slot:footer>
                    @if ($laporan)
                        <x-dash.pill :tone="$statusLaporan === 'diterima' ? 'success' : ($statusLaporan === 'ditolak' ? 'danger' : 'warning')" size="sm">
                            {{ $statusLaporan === 'diterima' ? 'Diterima' : ($statusLaporan === 'ditolak' ? 'Ditolak' : 'Menunggu') }}
                        </x-dash.pill>
                        <a href="{{ asset('storage/' . $laporan->file) }}" target="_blank" rel="noopener"
                            class="mt-2 inline-flex items-center gap-1 font-title-sm text-[13px] text-primary hover:underline">
                            <span class="material-symbols-outlined text-[15px]" aria-hidden="true">open_in_new</span>
                            Lihat Laporan
                        </a>
                    @else
                        <p class="font-body-sm text-on-surface-variant">Belum upload laporan.</p>
                    @endif
                </x-slot:footer>
            </x-dash.stat>

            {{-- Total Bimbingan --}}
            <x-dash.stat label="Total Bimbingan" icon="forum" tone="tertiary" :value="$totalBimbingan"
                suffix="sesi">
                <x-dash.progress :value="$totalBimbingan > 0 ? ($bimbinganSelesai / $totalBimbingan) * 100 : 0"
                    tone="tertiary" />
                <p class="font-body-sm text-on-surface-variant mt-2">
                    <span class="font-semibold text-tertiary">{{ $bimbinganSelesai }}</span> bimbingan disetujui
                </p>
            </x-dash.stat>

            {{-- Progress PKL --}}
            <x-dash.stat label="Progress PKL" icon="trending_up" tone="warning" :value="$progress" suffix="%">
                <x-slot:badge>
                    <x-dash.pill :tone="$progress >= 100 ? 'success' : 'warning'" size="sm">
                        {{ $progress >= 100 ? 'Tuntas' : 'Berjalan' }}
                    </x-dash.pill>
                </x-slot:badge>

                <x-dash.progress :value="$progress" tone="warning" />
                <p class="font-body-sm text-on-surface-variant mt-2">{{ $progress }}% telah dilaksanakan.</p>
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
                                Selanjutnya, Anda dapat melihat ringkasan informasi PKL pada bagian di bawah ini.
                            </p>

                            <div class="flex flex-wrap gap-2 pt-1">
                                <a href="{{ route('mahasiswa.pendaftaran') }}"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-surface-container-low text-on-surface font-title-sm text-[12px] hover:bg-surface-container transition-colors">
                                    <span class="material-symbols-outlined text-[15px] text-primary" aria-hidden="true">
                                        how_to_reg</span>
                                    Pendaftaran Online
                                </a>
                                <a href="{{ route('mahasiswa.bimbingan') }}"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-surface-container-low text-on-surface font-title-sm text-[12px] hover:bg-surface-container transition-colors">
                                    <span class="material-symbols-outlined text-[15px] text-primary" aria-hidden="true">forum</span>
                                    Bimbingan Online
                                </a>
                                <a href="{{ route('mahasiswa.laporan') }}"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-surface-container-low text-on-surface font-title-sm text-[12px] hover:bg-surface-container transition-colors">
                                    <span class="material-symbols-outlined text-[15px] text-primary" aria-hidden="true">
                                        upload_file</span>
                                    Pelaporan Digital
                                </a>
                            </div>
                        </div>
                    </div>
                </x-dash.panel>

                {{-- Tips & Pengumuman --}}
                <x-dash.panel icon="campaign" title="Tips &amp; Pengumuman"
                    subtitle="Panduan singkat untuk menjalankan PKL dengan lancar">
                    {{-- Kedua kartu pakai tone PALE (latar terang, teks gelap) supaya kontrasnya
         konsisten dan lolos WCAG AA. Versi lama memakai bg-tertiary-container
         (#007f36, hijau tua) dengan text-on-surface-variant (#434655, abu tua) —
         rasio kontras hanya 1.82:1, teks praktis tak terbaca.
         Warna aksen hanya lewat chip ikon, bukan seluruh latar card. --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-space-md">
                        <div class="p-space-md rounded-lg bg-surface-container border border-surface-container-high">
                            <h3
                                class="font-title-sm text-[13px] text-on-surface font-semibold flex items-center gap-1.5">
                                <span
                                    class="inline-flex items-center justify-center w-6 h-6 shrink-0 rounded-md bg-primary-fixed text-on-primary-fixed-variant"
                                    aria-hidden="true">
                                    <span class="material-symbols-outlined text-[15px]">lightbulb</span>
                                </span>
                                Tips PKL
                            </h3>
                            <p class="font-body-sm text-on-surface-variant mt-2 leading-relaxed">
                                Pastikan untuk mengisi logbook harian selama PKL dan berkomunikasi rutin dengan
                                pembimbing.
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
                                Batas akhir pengumpulan laporan PKL adalah 2 minggu setelah selesai PKL.
                            </p>
                        </div>
                    </div>
                </x-dash.panel>
            </div>

            {{-- ---------- KOLOM KANAN (4) ---------- --}}
            <div class="xl:col-span-4 space-y-space-lg">

                {{-- Ringkasan kemajuan --}}
                <x-dash.panel icon="checklist" title="Ringkasan Kemajuan"
                    subtitle="Pemenuhan syarat utama PKL">
                    <div class="space-y-3">
                        @foreach ([
    ['Pendaftaran PKL disetujui', $statusPendaftaran === 'diterima'],
    ['Laporan PKL disetujui', $statusLaporan === 'diterima'],
    ['Minimal 4 sesi bimbingan', $bimbinganSelesai >= 4],
] as [$syarat, $terpenuhi])
                            <div class="flex items-center gap-3 p-3 rounded-lg bg-surface-container-low">
                                <span
                                    class="w-8 h-8 shrink-0 rounded-lg flex items-center justify-center {{ $terpenuhi ? 'bg-tertiary-container text-tertiary' : 'bg-surface-container text-outline' }}">
                                    <span class="material-symbols-outlined text-[17px]" aria-hidden="true">
                                        {{ $terpenuhi ? 'check' : 'pending' }}
                                    </span>
                                </span>
                                <span class="font-body-sm text-on-surface min-w-0 flex-1">{{ $syarat }}</span>
                                <x-dash.pill :tone="$terpenuhi ? 'success' : 'neutral'" size="sm">
                                    {{ $terpenuhi ? 'Terpenuhi' : 'Belum' }}
                                </x-dash.pill>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-5 pt-4 border-t border-surface-container">
                        <div class="flex items-center justify-between gap-3 mb-2">
                            <span class="font-label-sm text-[11px] uppercase tracking-wider text-outline">
                                Progres Keseluruhan</span>
                            <span class="font-title-sm text-[13px] font-semibold text-on-surface">{{ $progress }}%</span>
                        </div>
                        <x-dash.progress :value="$progress" size="lg" />
                    </div>
                </x-dash.panel>

                {{-- Pintasan modul --}}
                <x-dash.panel icon="apps" title="Pintasan Modul" subtitle="Menu yang paling sering Anda gunakan">
                    <div class="space-y-1">
                        @foreach ([
    ['mahasiswa.pendaftaran', 'Pendaftaran PKL', 'how_to_reg'],
    ['mahasiswa.perusahaan', 'List Perusahaan', 'apartment'],
    ['mahasiswa.laporan', 'Upload Laporan PKL', 'description'],
    ['mahasiswa.format', 'Format Laporan', 'download_for_offline'],
    ['mahasiswa.bimbingan', 'Bimbingan', 'forum'],
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
