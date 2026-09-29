@props(['collapsed' => false])

<div>
    <x-sidebar-access-mode />

    <x-sidebar-link route="admin.dashboard" label="Dashboard" :collapsed="$collapsed"
        icon='<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1h3a1 1 0 001-1V10" />
          </svg>' />

    <x-sidebar-section label="Manajemen Pengguna" />

    <x-sidebar-link route="admin.mahasiswa.index" label="Kelola Mahasiswa" :collapsed="$collapsed"
        icon='<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87M16 7a4 4 0 11-8 0 4 4 0 018 0zM21 7a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>' />

    <x-sidebar-link route="admin.dosen.index" label="Kelola Dosen" :collapsed="$collapsed"
        icon='<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l4.16-2.31M12 14v7m0 0l-4.16-2.31M12 21l4.16-2.31M7.84 16.69L12 14m-4.16 2.31L12 21" />
          </svg>' />

    <x-sidebar-link route="admin.pengguna.index" label="Manajemen Akun" :collapsed="$collapsed"
        icon='<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>' />

    <x-sidebar-section label="Perusahaan" />

    <x-sidebar-link route="admin.perusahaan.index" label="Kelola Perusahaan" :collapsed="$collapsed"
        icon='<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
          </svg>' />

    <x-sidebar-section label="Pendaftar PKL" />

    <x-sidebar-link route="admin.pendaftaran.index" label="Verifikasi Pendaftar" :collapsed="$collapsed"
        icon='<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m1 10H6a2 2 0 01-2-2V6a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z" />
          </svg>' />

    <x-sidebar-section label="Laporan PKL" />

    <x-sidebar-link route="admin.laporan.verifikasi" label="Verifikasi Laporan" :collapsed="$collapsed"
        icon='<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M7 3h7l5 5v13a1 1 0 01-1 1H7a1 1 0 01-1-1V4a1 1 0 011-1z" />
          </svg>' />

    <x-sidebar-link route="admin.format.index" label="Upload Format" :collapsed="$collapsed"
        icon='<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M16 12l-4-4m0 0l-4 4m4-4v12" />
          </svg>' />

    <x-sidebar-section label="Profil" />

    <x-sidebar-link route="profile.edit" label="Profil Saya" :collapsed="$collapsed"
        icon='<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>' />
</div>
