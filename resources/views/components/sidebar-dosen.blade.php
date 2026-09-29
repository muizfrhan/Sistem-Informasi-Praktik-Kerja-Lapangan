@props(['collapsed' => false])

<div>
    <x-sidebar-access-mode />

    <x-sidebar-link route="dosen.dashboard" label="Home" :collapsed="$collapsed"
        icon='<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1h3a1 1 0 001-1V10" />
          </svg>' />

    <x-sidebar-section label="Bimbingan" />

    <x-sidebar-link route="dosen.mahasiswa.bimbingan" label="Mahasiswa Bimbingan" :collapsed="$collapsed"
        icon='<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87M16 7a4 4 0 11-8 0 4 4 0 018 0zM21 7a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>' />

    <x-sidebar-link route="dosen.bimbingan" label="Jadwal Bimbingan" :collapsed="$collapsed"
        icon='<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M5 11h14M5 19h14M5 15h14M5 7h14" />
          </svg>' />

    <x-sidebar-section label="Penilaian" />

    <x-sidebar-link route="dosen.nilai" label="Input Nilai PKL" :collapsed="$collapsed"
        icon='<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a2 2 0 012-2h2a2 2 0 012 2v2M12 12a4 4 0 110-8 4 4 0 010 8zM4 20h16" />
          </svg>' />

    <x-sidebar-section label="Profil" />

    <x-sidebar-link route="profile.edit" label="Profil Saya" :collapsed="$collapsed"
        icon='<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>' />
</div>
