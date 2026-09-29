@php
    $roles = [
        [
            'key' => 'mahasiswa',
            'tab' => 'Mahasiswa',
            'icon' => 'school',
            'heading' => 'Portal Mandiri Mahasiswa',
            'mockup' => 'template/mahasiswa.png',
            'mockup_alt' => 'Ilustrasi dashboard mahasiswa SIPKL: status pendaftaran, ringkasan modul, dan daftar aktivitas',
            'text' => 'Mahasiswa memantau status pendaftaran PKL, mengajukan jadwal bimbingan, mengunggah laporan sesuai format resmi, dan melihat hasil penilaian kapan saja dari dashboardnya.',
            'points' => [
                'Status pendaftaran dan verifikasi admin dapat dipantau',
                'Pengajuan jadwal bimbingan langsung ke dosen pembimbing',
                'Unduh format laporan dan unggah berkas dalam satu tempat',
            ],
        ],
        [
            'key' => 'admin',
            'tab' => 'Admin',
            'icon' => 'admin_panel_settings',
            'heading' => 'Pusat Kendali Admin Jurusan',
            'mockup' => 'images/mockup-admin.svg',
            'mockup_alt' => 'Ilustrasi dashboard admin SIPKL: kartu statistik, grafik, dan daftar antrean verifikasi',
            'text' => 'Admin mengelola data mahasiswa, dosen, dan perusahaan mitra, memverifikasi pendaftaran serta laporan, mengunggah format laporan resmi, dan memantau statistik PKL dalam satu dashboard.',
            'points' => [
                'Kelola data mahasiswa dan dosen, manual maupun impor Excel',
                'Verifikasi pendaftaran PKL dan laporan mahasiswa',
                'Statistik peserta, dosen, dan perusahaan mitra',
            ],
        ],
        [
            'key' => 'dosen',
            'tab' => 'Dosen Pembimbing',
            'icon' => 'supervisor_account',
            'heading' => 'Ruang Kerja Dosen Pembimbing',
            'mockup' => 'images/mockup-dosen.svg',
            'mockup_alt' => 'Ilustrasi dashboard dosen SIPKL: daftar mahasiswa bimbingan, progres sesi, dan form nilai',
            'text' => 'Dosen melihat seluruh mahasiswa bimbingannya, mengonfirmasi jadwal bimbingan, dan memasukkan nilai PKL setelah sistem memastikan jumlah sesi bimbingan mencukupi.',
            'points' => [
                'Daftar mahasiswa bimbingan beserta progres bimbingan',
                'Konfirmasi jadwal bimbingan yang diajukan mahasiswa',
                'Input nilai PKL setelah minimal empat sesi disetujui',
            ],
        ],
    ];
@endphp

<section id="peran" class="bg-surface py-16 lg:py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-12">
        <div class="mx-auto mb-10 max-w-2xl text-center">
            <span class="section-eyebrow">Ruang Kerja Personal</span>
            <h2 class="mb-3 mt-3 font-headline-lg text-headline-lg font-bold text-on-surface">
                Satu Sistem, Tiga Peran Spesifik
            </h2>
            <p class="font-body-md text-body-md text-on-surface-variant">
                Setiap peran mendapat dashboard dan menu yang menyesuaikan kewenangannya.
            </p>
        </div>

        {{-- Switcher peran: satu indikator biru meluncur di belakang tab aktif --}}
        <div id="role-tablist" data-role-tablist
            class="relative mx-auto mb-10 flex w-full max-w-xl flex-wrap items-center justify-center gap-2 rounded-2xl bg-surface-container-low p-1.5 shadow-inner"
            role="tablist" aria-label="Pilih peran pengguna">
            {{-- Indikator geser; posisi & ukuran diatur JS dari tab aktif --}}
            <span id="role-tab-indicator" aria-hidden="true"
                class="pointer-events-none absolute left-0 top-0 z-0 h-px w-px rounded-xl bg-primary opacity-0 shadow-sm transition-all duration-300 ease-out"></span>
            @foreach ($roles as $index => $role)
                <button type="button" id="tab-btn-{{ $role['key'] }}" data-role-tab="{{ $role['key'] }}"
                    role="tab" aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                    tabindex="{{ $index === 0 ? '0' : '-1' }}"
                    aria-controls="role-panel-{{ $role['key'] }}"
                    @class([
                        'role-tab-btn relative z-10 flex-1 rounded-xl px-3 py-2.5 text-center font-title-sm text-title-sm transition-colors duration-200',
                        'bg-primary text-on-primary shadow-sm' => $index === 0,
                        'text-on-surface-variant hover:text-on-surface' => $index !== 0,
                    ])>
                    {{ $role['tab'] }}
                </button>
            @endforeach
        </div>

        @foreach ($roles as $index => $role)
            <div id="role-panel-{{ $role['key'] }}" role="tabpanel"
                aria-labelledby="tab-btn-{{ $role['key'] }}"
                @class([
                    'role-panel grid grid-cols-1 items-center gap-8 rounded-3xl bg-surface-container-lowest p-6 shadow-md sm:p-8 lg:grid-cols-12 lg:p-12',
                    'hidden' => $index !== 0,
                ])>
                <div class="flex flex-col gap-4 lg:col-span-6">
                    <div
                        class="inline-flex items-center gap-2 font-label-md text-label-md font-semibold text-primary">
                        <x-icon :name="$role['icon']" class="text-lg" />
                        {{ $role['heading'] }}
                    </div>
                    <h3 class="font-headline-md text-headline-md font-bold text-on-surface">
                        {{ $role['tab'] }} punya alur kerja sendiri
                    </h3>
                    <p class="font-body-md text-body-md leading-relaxed text-on-surface-variant">{{ $role['text'] }}</p>
                    <ul class="mt-2 flex flex-col gap-2.5 font-body-sm text-body-sm text-on-surface">
                        @foreach ($role['points'] as $point)
                            <li class="flex items-start gap-2">
                                <x-icon name="check_circle" class="mt-0.5 shrink-0 text-base text-tertiary" />
                                <span>{{ $point }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="lg:col-span-6">
                    <img src="{{ asset($role['mockup']) }}" alt="{{ $role['mockup_alt'] }}"
                        class="h-64 w-full rounded-2xl border border-surface-variant object-cover object-top shadow-sm sm:h-80"
                        width="800" height="500" loading="lazy" decoding="async">
                </div>
            </div>
        @endforeach
    </div>
</section>
