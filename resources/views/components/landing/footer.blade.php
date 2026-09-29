@php
    $navLinks = [
        ['label' => 'Beranda', 'href' => route('landing')],
        ['label' => 'Tentang', 'href' => '#tentang'],
        ['label' => 'Fitur', 'href' => '#fitur'],
        ['label' => 'Alur PKL', 'href' => '#alur-pkl'],
        ['label' => 'FAQ', 'href' => '#faq'],
        ['label' => 'Kontak', 'href' => '#kontak'],
    ];

    $year = now()->year;
@endphp

<footer class="w-full bg-surface-container-lowest pb-8 pt-12">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-12">
        <div class="grid grid-cols-1 gap-8 pb-10 md:grid-cols-4">
            <div class="flex flex-col gap-3 md:col-span-2">
                <x-landing.brand size="h-9" :show-text="true" tag="div" />
                <p class="max-w-md font-body-md text-body-md text-on-surface-variant">
                    Platform terintegrasi untuk mengelola pengajuan, perusahaan mitra, bimbingan, laporan, dan
                    penilaian Praktik Kerja Lapangan.
                </p>
            </div>

            <nav class="flex flex-col gap-2" aria-label="Navigasi footer">
                <span
                    class="mb-2 font-title-sm text-title-sm font-semibold uppercase tracking-wider text-on-surface">Navigasi</span>
                @foreach ($navLinks as $link)
                    <a href="{{ $link['href'] }}"
                        class="w-fit font-body-sm text-body-sm text-on-surface-variant transition-colors hover:text-on-surface">
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="flex flex-col gap-2">
                <span
                    class="mb-2 font-title-sm text-title-sm font-semibold uppercase tracking-wider text-on-surface">Informasi</span>
                <a href="{{ auth()->check() ? route('redirect') : route('login') }}"
                    class="w-fit font-body-sm text-body-sm text-on-surface-variant transition-colors hover:text-on-surface">
                    {{ auth()->check() ? 'Buka Dashboard' : 'Masuk Sistem' }}
                </a>
                <a href="#alur-pkl"
                    class="w-fit font-body-sm text-body-sm text-on-surface-variant transition-colors hover:text-on-surface">
                    Alur PKL
                </a>
                <a href="#peran"
                    class="w-fit font-body-sm text-body-sm text-on-surface-variant transition-colors hover:text-on-surface">
                    Peran Pengguna
                </a>
                <a href="#faq"
                    class="w-fit font-body-sm text-body-sm text-on-surface-variant transition-colors hover:text-on-surface">
                    Tanya Jawab
                </a>
            </div>
        </div>

        <div
            class="flex flex-col items-center justify-between gap-4 border-t border-surface-variant/60 pt-6 font-body-sm text-body-sm text-on-surface-variant sm:flex-row">
            <p>&copy; {{ $year }} SIPKL. Seluruh Hak Cipta Dilindungi.</p>
            <p>Sistem Informasi Praktik Kerja Lapangan</p>
        </div>
    </div>
</footer>
