{{--
  Widget "Mode Akses" di puncak sidebar — mengikuti template dashboard.html.
--}}
@php
    $user = Auth::user();
    $aktif = $user?->isAktif() ?? false;

    $ringkasan = match ($user?->role) {
        'admin' => 'Administrator Sistem',
        'dosen' => 'Dosen Pembimbing',
        'mahasiswa' => 'Mahasiswa Prakerin',
        'koordinator' => 'Koordinator PKL',
        'pimpinan' => 'Pimpinan Sekolah',
        'pembimbing_perusahaan' => 'Pembimbing Perusahaan',
        default => 'Pengguna SIPKL',
    };
@endphp

<div class="px-space-lg py-space-xs">
    <div class="p-space-sm rounded-xl bg-surface-container-low flex items-center justify-between gap-2">
        <div class="flex items-center gap-space-sm min-w-0">
            <span class="w-2.5 h-2.5 rounded-full {{ $aktif ? 'bg-tertiary' : 'bg-secondary' }} shrink-0"
                aria-hidden="true"></span>
            <div class="min-w-0">
                <span
                    class="block font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">Mode
                    Akses</span>
                <span class="block font-title-sm text-title-sm font-semibold text-on-surface truncate">
                    {{ $ringkasan }}
                </span>
            </div>
        </div>
        <span class="material-symbols-outlined text-title-md text-on-surface-variant shrink-0" aria-hidden="true">
            {{ $aktif ? 'verified_user' : 'pending' }}
        </span>
    </div>
</div>
