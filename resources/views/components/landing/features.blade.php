@php
    $features = [
        [
            'icon' => 'assignment',
            'title' => 'Pendaftaran PKL',
            'text' => 'Mahasiswa mengajukan PKL dan memantau status pendaftaran, bidang yang dituju, serta perusahaan mitranya.',
        ],
        [
            'icon' => 'verified_user',
            'title' => 'Verifikasi Admin',
            'text' => 'Admin memeriksa tiap pengajuan pendaftaran dan laporan, lalu memberi keputusan yang tercatat di sistem.',
        ],
        [
            'icon' => 'apartment',
            'title' => 'Perusahaan Mitra',
            'text' => 'Kelola daftar perusahaan mitra beserta alamat dan kontaknya, termasuk pengajuan referensi dari mahasiswa.',
        ],
        [
            'icon' => 'forum',
            'title' => 'Bimbingan PKL',
            'text' => 'Mahasiswa mengajukan jadwal bimbingan, dosen pembimbing mengonfirmasi, dan riwayat sesi tersimpan rapi.',
        ],
        [
            'icon' => 'description',
            'title' => 'Unggah Laporan',
            'text' => 'Mahasiswa mengunggah berkas laporan PKL sesuai format resmi, admin memverifikasi sebelum penilaian.',
        ],
        [
            'icon' => 'download',
            'title' => 'Format Laporan Resmi',
            'text' => 'Admin unggah berkas format laporan terbaru, mahasiswa mengunduhnya langsung dari dashboard.',
        ],
        [
            'icon' => 'grading',
            'title' => 'Input Nilai PKL',
            'text' => '                Dosen pembimbing memasukkan nilai setelah minimal empat sesi bimbingan disetujui oleh sistem.',
        ],
        [
            'icon' => 'upload_file',
            'title' => 'Impor Data Excel',
            'text' => 'Admin mengimpor data mahasiswa dan dosen sekaligus dari berkas Excel menggunakan templat bawaan.',
        ],
    ];
@endphp

<section id="fitur" class="bg-surface py-16 lg:py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-12">
        <div class="mb-12 flex max-w-3xl flex-col items-center text-center lg:mb-16">
            <span class="section-eyebrow">Kapabilitas Terpadu</span>
            <h2 class="mb-4 mt-3 font-headline-lg text-headline-lg font-bold text-on-surface">
                Fitur Utama Siklus PKL
            </h2>
            <p class="font-body-lg text-body-lg text-on-surface-variant">
                Sembilan modul kerja yang menutup alur PKL dari pendaftaran sampai penilaian, dengan hak akses
                yang berbeda untuk admin, dosen pembimbing, dan mahasiswa.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($features as $feature)
                <article class="feature-card">
                    <div class="feature-icon">
                        <x-icon :name="$feature['icon']" />
                    </div>
                    <h3 class="mb-2 font-title-md text-title-md font-bold text-on-surface">{{ $feature['title'] }}</h3>
                    <p class="font-body-sm text-body-sm leading-relaxed text-on-surface-variant">
                        {{ $feature['text'] }}
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</section>
