@php
    $stats = [
        ['value' => '3', 'label' => 'Peran Akses', 'icon' => 'admin_panel_settings', 'tone' => 'primary'],
        ['value' => '9', 'label' => 'Modul Fitur Terintegrasi', 'icon' => 'widgets', 'tone' => 'secondary'],
        ['value' => '8', 'label' => 'Tahapan Alur PKL', 'icon' => 'route', 'tone' => 'tertiary'],
        ['value' => '24/7', 'label' => 'Akses daring kapan saja', 'icon' => 'schedule', 'tone' => 'primary'],
    ];

    $tones = [
        'primary' => 'bg-primary text-on-primary',
        'secondary' => 'bg-secondary text-on-secondary',
        'tertiary' => 'bg-tertiary-container text-on-tertiary',
    ];

    $highlights = [
        'Satu basis data untuk admin, dosen, dan mahasiswa',
        'Berkas laporan dan format resmi tersimpan rapi',
        'Status pendaftaran dan laporan dapat dipantau',
        'Akses berbasis peran, tiap peran dibatasi sesuai tugasnya',
    ];
@endphp

<section id="tentang" class="bg-surface-container-lowest py-16 shadow-sm lg:py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-12">
        <div class="grid grid-cols-1 items-start gap-10 lg:grid-cols-12 lg:gap-12">
            <div class="lg:col-span-5">
                <span class="section-eyebrow">Tentang SIPKL</span>
                <h2 class="mb-4 mt-3 font-headline-lg text-headline-lg font-bold text-on-surface">
                    Satu Platform untuk Seluruh Peserta PKL
                </h2>
            </div>
            <div class="lg:col-span-7">
                <p class="font-body-lg text-body-lg leading-relaxed text-on-surface-variant">
                    SIPKL menyatukan administrasi Praktik Kerja Lapangan dalam satu aplikasi: mengelola data
                    mahasiswa dan dosen, memverifikasi pendaftaran, mengelola perusahaan mitra, mengatur
                    jadwal bimbingan, hingga mengunggah dan menilai laporan.
                </p>
                <ul class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    @foreach ($highlights as $point)
                        <li class="flex items-start gap-2 font-body-sm text-body-sm text-on-surface">
                            <x-icon name="check_circle" class="mt-0.5 shrink-0 text-base text-tertiary" />
                            <span>{{ $point }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="mt-12 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($stats as $stat)
                <div class="stat-card">
                    <div
                        class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl shadow-sm {{ $tones[$stat['tone']] }}">
                        <x-icon :name="$stat['icon']" class="text-2xl" />
                    </div>
                    <div class="min-w-0">
                        <span
                            class="block font-headline-lg text-headline-lg font-bold tracking-tight text-on-surface">{{ $stat['value'] }}</span>
                        <p class="font-body-sm text-body-sm font-medium text-on-surface-variant">{{ $stat['label'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
