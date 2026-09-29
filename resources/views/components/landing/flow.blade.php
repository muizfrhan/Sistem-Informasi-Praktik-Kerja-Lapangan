@php
    $steps = [
        ['no' => '01', 'icon' => 'person_add', 'title' => 'Pendataan Peserta', 'text' => 'Admin membuat akun serta mengimpor data mahasiswa dan dosen pembimbing.'],
        ['no' => '02', 'icon' => 'assignment', 'title' => 'Pendaftaran PKL', 'text' => 'Mahasiswa mengajukan PKL beserta bidang dan perusahaan yang dituju.'],
        ['no' => '03', 'icon' => 'verified_user', 'title' => 'Verifikasi Pendaftaran', 'text' => 'Admin memeriksa data dan menyetujui atau menolak pengajuan.'],
        ['no' => '04', 'icon' => 'send', 'title' => 'Pengajuan Bimbingan', 'text' => 'Mahasiswa mengajukan jadwal bimbingan kepada dosen pembimbing.'],
        ['no' => '05', 'icon' => 'event_available', 'title' => 'Konfirmasi Bimbingan', 'text' => 'Dosen mengonfirmasi jadwal sehingga tercatat pada riwayat mahasiswa.'],
        ['no' => '06', 'icon' => 'download', 'title' => 'Unduh Format', 'text' => 'Mahasiswa mengunduh format laporan resmi yang diunggah admin.'],
        ['no' => '07', 'icon' => 'description', 'title' => 'Unggah Laporan', 'text' => 'Mahasiswa mengunggah laporan PKL sesuai format tersebut.'],
        ['no' => '08', 'icon' => 'grading', 'title' => 'Penilaian Akhir', 'text' => 'Admin memverifikasi laporan, lalu dosen memasukkan nilai PKL.'],
    ];
@endphp

<section id="alur-pkl" class="bg-surface-container-low/70 py-16 lg:py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-12">
        <div class="mb-12 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <span class="section-eyebrow">Alur Terstandarisasi</span>
                <h2 class="mb-2 mt-3 font-headline-lg text-headline-lg font-bold text-on-surface">
                    Delapan Tahapan Pelaksanaan PKL
                </h2>
            </div>
            <p class="mt-2 max-w-md font-body-md text-body-md text-on-surface-variant md:mt-0">
                Urutan kerja yang sama untuk semua peserta, dari pendataan peserta sampai penilaian akhir.
            </p>
        </div>

        <ol class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($steps as $step)
                <li class="step-card">
                    <div class="mb-3 flex items-center justify-between gap-2">
                        <span
                            class="font-headline-sm text-headline-sm font-black text-primary">{{ $step['no'] }}</span>
                        <x-icon :name="$step['icon']" class="text-base text-primary" />
                    </div>
                    <h3 class="mb-1 font-title-sm text-title-sm font-bold leading-snug text-on-surface">
                        {{ $step['title'] }}
                    </h3>
                    <p class="font-label-sm text-label-sm text-on-surface-variant">{{ $step['text'] }}</p>
                </li>
            @endforeach
        </ol>
    </div>
</section>
