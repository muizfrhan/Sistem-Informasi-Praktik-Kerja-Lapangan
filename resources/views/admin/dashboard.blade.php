@php
    // Seluruh angka dikirimkan oleh Admin\DashboardController — tidak diubah.
    $approvalRate = ($laporanDiterima / max($totalLaporan, 1)) * 100;
    $totalPengguna = $totalMahasiswa + $totalDosen;
@endphp

<x-app-layout>
    <div class="space-y-space-lg">

        {{-- ================= HERO ================= --}}
        <x-dash.hero :name="auth()->user()->name"
            subtitle="Pantau dan kelola seluruh data Sistem Informasi Praktik Kerja Lapangan dari satu tempat.">

            <x-slot:badge>
                <span
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-surface-container text-on-surface font-label-sm text-[11px]">
                    <span class="material-symbols-outlined text-[14px] text-primary" aria-hidden="true">verified_user</span>
                    <span>Sistem berjalan normal &bull; {{ $totalPengguna }} pengguna aktif</span>
                </span>

                <x-dash.pill :tone="$laporanPending > 0 ? 'warning' : 'success'" size="sm">
                    {{ $laporanPending > 0 ? $laporanPending . ' laporan perlu ditinjau' : 'Tidak ada laporan pending' }}
                </x-dash.pill>
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
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-tertiary-container text-on-tertiary-container font-label-sm text-[11px]">
                        <span class="material-symbols-outlined text-[14px]" aria-hidden="true">speed</span>
                        <span>Approval Rate {{ number_format($approvalRate, 1) }}%</span>
                    </span>

                    <span
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-surface-container-low text-on-surface">
                        <span class="material-symbols-outlined text-[15px] text-secondary" aria-hidden="true">groups</span>
                        <span class="font-body-sm text-[13px]">
                            {{ $totalMahasiswa }} Mahasiswa &middot; {{ $totalDosen }} Dosen</span>
                    </span>
                </div>
            </x-slot:meta>

            <x-slot:actions>
                <a href="{{ route('admin.laporan.verifikasi') }}" class="btn-primary btn-md w-full">
                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">fact_check</span>
                    Verifikasi Laporan
                </a>

                <a href="{{ route('admin.pendaftaran.index') }}" class="btn-secondary btn-md w-full">
                    <span class="material-symbols-outlined text-[18px] text-primary" aria-hidden="true">how_to_reg</span>
                    Verifikasi Pendaftar
                </a>

                <a href="{{ route('admin.pengguna.index') }}" class="btn-ghostish btn-md w-full">
                    <span class="material-symbols-outlined text-[16px]" aria-hidden="true">manage_accounts</span>
                    Manajemen Akun
                </a>
            </x-slot:actions>
        </x-dash.hero>

        {{-- ================= BARIS METRIK UTAMA ================= --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-space-md">

            <x-dash.stat label="Total Mahasiswa" icon="groups" tone="primary" :value="$totalMahasiswa">
                <p class="font-body-sm text-on-surface-variant">Aktif</p>
            </x-dash.stat>

            <x-dash.stat label="Total Dosen" icon="school" tone="secondary" :value="$totalDosen">
                <p class="font-body-sm text-on-surface-variant">Pembimbing PKL</p>
            </x-dash.stat>

            <x-dash.stat label="Total Perusahaan" icon="apartment" tone="warning" :value="$totalPerusahaan">
                <p class="font-body-sm text-on-surface-variant">Mitra DUDI</p>
            </x-dash.stat>

            <x-dash.stat label="Total Laporan PKL" icon="description" tone="primary" :value="$totalLaporan">
                <x-dash.progress :value="$approvalRate" />
                <p class="font-body-sm text-on-surface-variant mt-2">
                    <span class="font-semibold text-tertiary">{{ number_format($approvalRate, 1) }}%</span> sudah
                    disetujui
                </p>
            </x-dash.stat>
        </div>

        {{-- ================= WORKSPACE 2 KOLOM ================= --}}
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-space-lg">

            {{-- ---------- KOLOM KIRI (8) ---------- --}}
            <div class="xl:col-span-8 space-y-space-lg">

                {{-- Distribusi laporan --}}
                <x-dash.panel icon="donut_large" title="Distribusi Status Laporan"
                    subtitle="Sebaran keputusan verifikasi terhadap seluruh laporan PKL">
                    <x-slot:actions>
                        <a href="{{ route('admin.laporan.verifikasi') }}"
                            class="font-title-sm text-[13px] text-primary font-semibold hover:underline inline-flex items-center gap-1">
                            Buka verifikasi
                            <span class="material-symbols-outlined text-[15px]" aria-hidden="true">arrow_forward</span>
                        </a>
                    </x-slot:actions>

                    @php
                        $total = max($totalLaporan, 1);
                        $baris = [
    ['Diterima', $laporanDiterima, 'tertiary', 'check_circle', $laporanDiterima / $total * 100],
    ['Pending', $laporanPending, 'warning', 'pending', $laporanPending / $total * 100],
    ['Ditolak', $laporanDitolak, 'danger', 'cancel', $laporanDitolak / $total * 100],
];
                    @endphp

                    <div class="space-y-4">
                        @foreach ($baris as [$label, $jumlah, $tone, $ikon, $persen])
                            <div>
                                <div class="flex items-center justify-between gap-3 mb-1.5">
                                    <span class="flex items-center gap-2 min-w-0">
                                        <span class="material-symbols-outlined text-[16px] shrink-0 {{ match ($tone) { 'tertiary' => 'text-tertiary', 'warning' => 'text-secondary', default => 'text-error',
} }}" aria-hidden="true">{{ $ikon }}</span>
                                        <span class="font-title-sm text-[13px] font-semibold text-on-surface">{{ $label }}</span>
                                    </span>
                                    <span class="shrink-0 text-right">
                                        <span class="font-title-sm text-[14px] font-bold text-on-surface">{{ $jumlah }}</span>
                                        <span class="font-body-sm text-[12px] text-outline ml-1.5">
                                            {{ number_format($persen, 1) }}%</span>
                                    </span>
                                </div>
                                <x-dash.progress :value="$persen" :tone="$tone === 'danger' ? 'danger' : $tone" />
                            </div>
                        @endforeach
                    </div>
                </x-dash.panel>

                {{-- Ringkasan sistem --}}
                <x-dash.panel icon="monitor_heart" title="Ringkasan Sistem"
                    subtitle="Kondisi keseluruhan implementasi SIPKL">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-space-md">
                        <div class="p-space-md rounded-lg bg-surface-container-low text-center">
                            <span class="font-label-sm text-[11px] uppercase tracking-wider text-outline block">
                                Approval Rate</span>
                            <span
                                class="block text-headline-sm font-bold text-tertiary mt-1.5 tabular-nums">{{ number_format($approvalRate, 1) }}%</span>
                        </div>
                        <div class="p-space-md rounded-lg bg-surface-container-low text-center">
                            <span class="font-label-sm text-[11px] uppercase tracking-wider text-outline block">
                                Butuh Review</span>
                            <span
                                class="block text-headline-sm font-bold text-secondary mt-1.5 tabular-nums">{{ $laporanPending }}</span>
                        </div>
                        <div class="p-space-md rounded-lg bg-surface-container-low text-center">
                            <span class="font-label-sm text-[11px] uppercase tracking-wider text-outline block">
                                Pengguna Aktif</span>
                            <span
                                class="block text-headline-sm font-bold text-primary mt-1.5 tabular-nums">{{ $totalPengguna }}</span>
                        </div>
                    </div>

                    <p class="font-body-sm text-on-surface-variant mt-4 leading-relaxed">
                        Sistem SIPKL berjalan normal dengan {{ $totalPengguna }} pengguna aktif
                        ({{ $totalMahasiswa }} mahasiswa dan {{ $totalDosen }} dosen) serta {{ $totalPerusahaan }} mitra
                        perusahaan.
                    </p>
                </x-dash.panel>
            </div>

            {{-- ---------- KOLOM KANAN (4) ---------- --}}
            <div class="xl:col-span-4 space-y-space-lg">

                {{-- Status laporan ringkas --}}
                <x-dash.panel icon="summarize" title="Ringkasan Laporan" subtitle="Status verifikasi laporan PKL">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between p-3 rounded-lg bg-tertiary-container">
                            <span class="flex items-center gap-2 min-w-0">
                                <span class="material-symbols-outlined text-[17px] text-on-tertiary-container shrink-0"
                                    aria-hidden="true">check_circle</span>
                                <span class="font-title-sm text-[13px] text-on-tertiary-container font-semibold truncate">
                                    Diterima</span>
                            </span>
                            <span
                                class="font-title-md font-bold text-on-tertiary-container tabular-nums">{{ $laporanDiterima }}</span>
                        </div>

                        <div class="flex items-center justify-between p-3 rounded-lg bg-secondary-container">
                            <span class="flex items-center gap-2 min-w-0">
                                <span class="material-symbols-outlined text-[17px] text-secondary shrink-0"
                                    aria-hidden="true">pending</span>
                                <span class="font-title-sm text-[13px] text-on-secondary-container font-semibold truncate">
                                    Pending</span>
                            </span>
                            <span
                                class="font-title-md font-bold text-on-secondary-container tabular-nums">{{ $laporanPending }}</span>
                        </div>

                        <div class="flex items-center justify-between p-3 rounded-lg bg-error-container">
                            <span class="flex items-center gap-2 min-w-0">
                                <span class="material-symbols-outlined text-[17px] text-error shrink-0"
                                    aria-hidden="true">cancel</span>
                                <span class="font-title-sm text-[13px] text-on-error-container font-semibold truncate">
                                    Ditolak</span>
                            </span>
                            <span
                                class="font-title-md font-bold text-on-error-container tabular-nums">{{ $laporanDitolak }}</span>
                        </div>
                    </div>
                </x-dash.panel>

                {{-- Pintasan modul --}}
                <x-dash.panel icon="apps" title="Pintasan Modul" subtitle="Navigasi cepat operasional">
                    <div class="space-y-1">
                        @foreach ([
    ['admin.mahasiswa.index', 'Kelola Mahasiswa', 'groups'],
    ['admin.dosen.index', 'Kelola Dosen', 'school'],
    ['admin.pengguna.index', 'Manajemen Akun', 'manage_accounts'],
    ['admin.perusahaan.index', 'Kelola Perusahaan', 'apartment'],
    ['admin.pendaftaran.index', 'Verifikasi Pendaftar', 'how_to_reg'],
    ['admin.laporan.verifikasi', 'Verifikasi Laporan', 'fact_check'],
    ['admin.format.index', 'Upload Format', 'upload_file'],
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
</x-app-layout>
