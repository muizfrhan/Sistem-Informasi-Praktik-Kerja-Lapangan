@php
    $faqs = [
        [
            'q' => 'Siapa saja yang dapat masuk ke sistem SIPKL?',
            'a' => 'SIPKL memiliki tiga peran: Admin, Dosen Pembimbing, dan Mahasiswa. Admin mengelola data peserta serta memverifikasi pengajuan, Dosen Pembimbing menangani bimbingan dan input nilai, sedangkan Mahasiswa mendaftar PKL dan mengunggah laporan. Akun dibuat oleh admin, jadi hubungi administrator bila belum memiliki akun.',
        ],
        [
            'q' => 'Bagaimana mahasiswa mendaftar Praktik Kerja Lapangan?',
            'a' => 'Mahasiswa membuka menu Pendaftaran PKL pada dashboard, lalu mengisi data pendaftaran beserta bidang PKL dan perusahaan yang dituju. Pengajuan berstatus menunggu sampai admin memverifikasinya, dan status tersebut dapat dipantau langsung dari dashboard.',
        ],
        [
            'q' => 'Bagaimana cara kerja bimbingan PKL?',
            'a' => 'Mahasiswa mengajukan jadwal bimbingan melalui menu Bimbingan PKL. Dosen pembimbing mengonfirmasi jadwal tersebut sehingga setiap sesi yang disetujui tercatat pada riwayat mahasiswa. Input nilai PKL baru dapat dilakukan setelah minimal empat sesi bimbingan disetujui.',
        ],
        [
            'q' => 'Di mana mahasiswa mendapatkan format laporan PKL?',
            'a' => 'Admin mengunggah berkas format laporan resmi melalui menu Format Laporan. Mahasiswa mengunduhnya dari dashboard, lalu mengunggah laporan yang sudah disusun sesuai format tersebut pada menu Laporan PKL.',
        ],
        [
            'q' => 'Bisakah admin memasukkan data mahasiswa dan dosen sekaligus banyak?',
            'a' => 'Bisa. Menu impor data pada dashboard admin menyediakan berkas template Excel untuk mahasiswa dan dosen. Unduh templatnya, isi data sesuai kolom yang tersedia, lalu unggah kembali melalui halaman impor.',
        ],
        [
            'q' => 'Apakah data tiap peran saling terpisah?',
            'a' => 'Ya. Setiap peran hanya mengakses menu sesuai kewenangannya, sehingga data mahasiswa, dosen, dan admin dikelola terpisah dan aman sesuai peran masing-masing.',
        ],
    ];
@endphp

<section id="faq" class="bg-surface-container-low/50 py-16 lg:py-24">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-12">
        <div class="mb-12 text-center">
            <span class="section-eyebrow">Tanya Jawab Umum</span>
            <h2 class="mb-3 mt-3 font-headline-lg text-headline-lg font-bold text-on-surface">
                Pertanyaan yang Sering Diajukan
            </h2>
            <p class="font-body-md text-body-md text-on-surface-variant">
                Informasi seputar akses, alur PKL, dan pengelolaan data di dalam SIPKL.
            </p>
        </div>

        <div class="flex flex-col gap-4">
            @foreach ($faqs as $index => $faq)
                @php $id = 'faq-' . ($index + 1); @endphp
                <div class="overflow-hidden rounded-2xl bg-surface-container-lowest shadow-sm">
                    <h3>
                        <button type="button" id="faq-trigger-{{ $id }}" data-faq-trigger="{{ $id }}"
                            aria-expanded="false" aria-controls="faq-body-{{ $id }}"
                            class="flex w-full items-center justify-between gap-4 p-5 text-left font-title-md text-title-md font-bold text-on-surface">
                            <span>{{ $faq['q'] }}</span>
                            <x-icon name="expand_more" class="shrink-0 text-2xl text-on-surface-variant" />
                        </button>
                    </h3>
                    <div id="faq-body-{{ $id }}" role="region" aria-labelledby="faq-trigger-{{ $id }}"
                        class="hidden px-5 pb-5 font-body-sm text-body-sm leading-relaxed text-on-surface-variant">
                        {{ $faq['a'] }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
