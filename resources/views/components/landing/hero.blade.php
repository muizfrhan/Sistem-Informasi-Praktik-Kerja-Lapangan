{{-- Gambar latar hero.
     Sumber  : public/template/hero.jpg (7952x5304) — foto asli siswa SMK di workshop.
     optimise: tools/optimize_hero_bg.py -> images/hero-bg.jpg + .webp (1920x1200).

     object-cover TANPA scale-* : gambar mengikuti ukuran section apa adanya,
     tidak ada zoom paksa. Scrim hanya protects sisi kiri tempat blok teks
     berada, jadi sisi kanan (foto + mockup) tetap terang. --}}
@php
    $heroWebp = asset('images/hero-bg.webp');
    $heroJpg = asset('images/hero-bg.jpg');
@endphp

<section
    class="relative isolate overflow-hidden bg-surface-container-low pb-16 pt-28 sm:pb-20 sm:pt-32 lg:pb-28 lg:pt-36">

    {{-- Foto latar: blur ringan (blur-sm ~8px) supaya detail foto berubah jadi
         bokeh dan tidak berebut perhatian dengan teks. Bukan blur berat —
         foto harus tetap terbaca sebagai foto, bukan Laluan kabut. --}}
    <div aria-hidden="true" class="pointer-events-none absolute inset-0 -z-10 overflow-hidden">
        <picture class="block h-full w-full">
            <source srcset="{{ $heroWebp }}" type="image/webp">
            <img src="{{ $heroJpg }}" alt=""
                class="h-full w-full object-cover object-[62%_center] blur-sm scale-[1.06]"
                width="1920" height="1200" loading="eager" fetchpriority="high" decoding="async">
        </picture>
    </div>

    {{-- Scrim: satu-satunya lapisan yang menjaga keterbacaan teks.
         Ramping kiri->kanan (tebal di kolom teks, tembus di sisi foto) plus
        Kabut tipis seragam. Kontras teks tetap aman, foto tetap terlihat. --}}
    <div aria-hidden="true"
        class="pointer-events-none absolute inset-0 -z-10 bg-surface/35 lg:bg-surface/20"></div>
    <div
        class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-r from-surface via-surface/75 to-surface/5 lg:from-surface/90 lg:via-surface/50 lg:to-transparent"
        aria-hidden="true"></div>
    <div
        class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-b from-surface/60 via-transparent to-surface-container-low/30 lg:from-transparent lg:to-transparent"
        aria-hidden="true"></div>

    {{-- Ambient blur (statis, tanpa animasi berat) --}}
    <div class="pointer-events-none absolute -left-32 -top-32 h-96 w-96 rounded-full bg-primary-fixed/30 blur-3xl"
        aria-hidden="true"></div>
    <div
        class="pointer-events-none absolute -right-32 top-1/2 h-[32rem] w-[32rem] rounded-full bg-primary-fixed/20 blur-3xl"
        aria-hidden="true"></div>

    <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-12">
        <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-12 lg:gap-8">

            {{-- Copy utama --}}
            <div class="flex flex-col items-start text-left lg:col-span-7">
                <div
                    class="mb-6 inline-flex items-center gap-2.5 rounded-full bg-surface-container-lowest px-3.5 py-1.5 shadow-sm">
                    <span class="h-2 w-2 animate-pulse rounded-full bg-primary" aria-hidden="true"></span>
                    <span
                        class="font-label-md text-label-md font-semibold tracking-wide text-primary">Sistem Informasi
                        Praktik Kerja Lapangan</span>
                </div>

                <h1
                    class="mb-5 font-display text-display font-extrabold tracking-tight text-on-surface sm:mb-6 lg:text-[46px] lg:leading-[54px]">
                    Kelola Praktik Kerja Lapangan Lebih
                    <span
                        class="text-primary underline decoration-primary/25 decoration-wavy underline-offset-8">Mudah
                        &amp; Terstruktur</span>
                </h1>

                <p class="mb-8 max-w-2xl font-body-lg text-body-lg leading-relaxed text-on-surface-variant">
                    Kelola seluruh proses PKL dalam satu platform terintegrasi — mulai dari pengajuan, penempatan,
                    jurnal, absensi, monitoring, hingga penilaian.
                </p>

                <div class="mb-10 flex w-full flex-col gap-3 sm:w-auto sm:flex-row sm:flex-wrap sm:items-center sm:gap-4">
                    <a href="{{ auth()->check() ? route('redirect') : route('login') }}"
                        class="group inline-flex items-center justify-center gap-2 rounded-xl bg-primary-container px-7 py-3.5 font-title-sm text-title-sm text-on-primary shadow-md transition-all duration-200 hover:bg-primary">
                        <span>{{ auth()->check() ? 'Buka Dashboard' : 'Masuk Sistem' }}</span>
                        <x-icon name="arrow_forward" class="text-lg transition-transform duration-200 group-hover:translate-x-1" />
                    </a>
                    <a href="#fitur"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-surface-container-lowest px-6 py-3.5 font-title-sm text-title-sm text-on-surface shadow-sm transition-all duration-200 hover:bg-surface-container-high">
                        <x-icon name="play_circle" class="text-lg text-primary" />
                        <span>Pelajari Fitur</span>
                    </a>
                </div>

                {{-- Trust badges --}}
                <div
                    class="flex flex-col gap-y-3 gap-x-6 border-t border-surface-variant/60 pt-6 font-label-md text-label-md text-on-surface-variant sm:flex-row sm:flex-wrap sm:items-center">
                    <div class="flex items-center gap-1.5">
                        <x-icon name="verified" class="text-base text-tertiary" filled />
                        <span>3 Peran Akses Terintegrasi</span>
                    </div>
                    <span class="hidden text-outline-variant sm:inline" aria-hidden="true">&bull;</span>
                    <div class="flex items-center gap-1.5">
                        <x-icon name="description" class="text-base text-primary" filled />
                        <span>Format Laporan Resmi</span>
                    </div>
                    <span class="hidden text-outline-variant sm:inline" aria-hidden="true">&bull;</span>
                    <div class="flex items-center gap-1.5">
                        <x-icon name="history" class="text-base text-tertiary" filled />
                        <span>Riwayat Aktivitas Tercatat</span>
                    </div>
                </div>
            </div>

            {{-- Mockup dashboard --}}
            <div class="relative lg:col-span-5">
                <div class="absolute inset-0 scale-95 rotate-1 rounded-3xl bg-primary/10 blur-2xl" aria-hidden="true"></div>

                <div
                    class="absolute -left-2 -top-12 z-20 flex items-center gap-3 rounded-2xl bg-surface-container-lowest/95 px-4 py-2.5 shadow-lg backdrop-blur-md sm:-left-4 lg:-top-16">
                    <div
                        class="flex h-8 w-8 items-center justify-center rounded-full bg-tertiary-container/15 text-tertiary">
                        <x-icon name="task_alt" class="text-base" />
                    </div>
                    <div>
                        <p class="font-label-sm text-label-sm text-on-surface-variant">Status Pendaftaran</p>
                        <p class="font-title-sm text-title-sm text-on-surface">Terverifikasi Admin</p>
                    </div>
                </div>

                <div
                    class="absolute -bottom-12 -right-2 z-20 flex items-center gap-3 rounded-2xl bg-surface-container-lowest/95 px-4 py-2.5 shadow-lg backdrop-blur-md sm:-right-4 lg:-bottom-16">
                    <div
                        class="flex h-8 w-8 items-center justify-center rounded-full bg-primary-container/15 text-primary">
                        <x-icon name="apartment" class="text-base" />
                    </div>
                    <div>
                        <p class="font-label-sm text-label-sm text-on-surface-variant">Perusahaan Mitra</p>
                        <p class="font-title-sm text-title-sm text-on-surface">Penempatan Terjaga</p>
                    </div>
                </div>

                <div class="relative z-10 overflow-hidden rounded-2xl bg-surface-container-lowest p-5 shadow-xl sm:p-6">
                    <div
                        class="-mx-6 -mt-6 mb-4 flex items-center justify-between bg-surface-container-low/50 px-6 pb-4 pt-5">
                        <div class="flex items-center gap-2">
                            <span class="h-3 w-3 rounded-full bg-error/70"></span>
                            <span class="h-3 w-3 rounded-full bg-secondary-fixed"></span>
                            <span class="h-3 w-3 rounded-full bg-tertiary/70"></span>
                            <span
                                class="ml-2 font-label-sm text-label-sm font-medium text-on-surface-variant">SIPKL
                                &bull; Dashboard Mahasiswa</span>
                        </div>
                        <span
                            class="rounded-full bg-tertiary-container/15 px-2.5 py-0.5 font-label-sm text-label-sm font-semibold text-tertiary">Berjalan</span>
                    </div>

                    <div class="mb-4 flex items-center gap-4 rounded-xl bg-surface-container-low p-3.5">
                        <img src="{{ asset('images/avatar-mahasiswa.svg') }}" alt="Avatar mahasiswa contoh"
                            class="h-12 w-12 rounded-xl object-cover" width="96" height="96" loading="lazy"
                            decoding="async">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <h4 class="truncate font-title-sm text-title-sm font-semibold text-on-surface">
                                    Ahmad Fauzi Ramadhan</h4>
                                <span
                                    class="shrink-0 font-label-sm text-label-sm font-bold text-primary">Semester 5</span>
                            </div>
                            <p class="truncate font-body-sm text-body-sm text-on-surface-variant">
                                Mitra Perusahaan PKL &bull; Jurusan Teknik Informatika
                            </p>
                        </div>
                    </div>

                    <div class="mb-4 rounded-xl bg-surface-container-lowest p-4 shadow-sm">
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <span class="font-label-md text-label-md font-semibold text-on-surface">Progres Bimbingan
                                PKL</span>
                            <span class="font-title-sm text-title-sm font-bold text-primary">4 dari 6 Sesi</span>
                        </div>
                        <div class="h-3 w-full overflow-hidden rounded-full bg-surface-container-high p-0.5">
                            <div class="h-full w-2/3 rounded-full bg-gradient-to-r from-primary to-primary-container"></div>
                        </div>
                        <div class="mt-2 flex items-center justify-between font-label-sm text-label-sm text-on-surface-variant">
                            <span>Minimal 4 sesi disetujui untuk input nilai</span>
                            <span>67%</span>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2.5">
                        <span
                            class="font-label-sm text-label-sm font-semibold uppercase tracking-wider text-on-surface-variant">Aktivitas
                            Terkini</span>
                        <div class="flex items-start gap-3 rounded-lg bg-surface-container-low/60">
                            <x-icon name="upload_file" class="mt-0.5 text-lg text-primary" />
                            <div class="min-w-0 flex-1">
                                <p class="font-title-sm text-title-sm leading-tight text-on-surface">Laporan PKL
                                    Diunggah</p>
                                <p class="truncate font-label-sm text-label-sm text-on-surface-variant">BAB 2 &mdash;
                                    Analisis dan Perancangan Sistem</p>
                            </div>
                            <span class="shrink-0 font-label-sm text-label-sm text-secondary">10m lalu</span>
                        </div>
                        <div class="flex items-start gap-3 rounded-lg bg-surface-container-low/60">
                            <x-icon name="event_available" class="mt-0.5 text-lg text-tertiary" />
                            <div class="min-w-0 flex-1">
                                <p class="font-title-sm text-title-sm leading-tight text-on-surface">Jadwal Bimbingan
                                    Disetujui</p>
                                <p class="font-label-sm text-label-sm text-on-surface-variant">Sesi 3 &middot; Dosen
                                    Pembimbing</p>
                            </div>
                            <span class="shrink-0 font-label-sm text-label-sm text-secondary">07:55</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
