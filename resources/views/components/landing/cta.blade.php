@php
    $ctaHref = auth()->check() ? route('redirect') : route('login');
    $ctaLabel = auth()->check() ? 'Buka Dashboard' : 'Masuk Sistem';
@endphp

<section class="bg-surface py-14 lg:py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-12">
        <div
            class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-primary to-primary-container p-8 text-on-primary shadow-xl sm:p-10 lg:p-14">
            <div class="pointer-events-none absolute -bottom-16 -right-16 h-80 w-80 rounded-full bg-white/10 blur-2xl"
                aria-hidden="true"></div>
            <div class="pointer-events-none absolute right-1/4 top-0 h-40 w-40 rounded-full bg-white/5 blur-xl"
                aria-hidden="true"></div>

            <div class="relative z-10 flex max-w-3xl flex-col items-start">
                <span
                    class="mb-6 inline-flex items-center gap-1.5 rounded-full bg-white/20 px-3.5 py-1 font-label-md text-label-md font-semibold text-white backdrop-blur-md">
                    <x-icon name="rocket_launch" class="text-base" />
                    Siapkan Praktik Kerja Lapangan Anda
                </span>
                <h2
                    class="mb-4 font-headline-lg text-headline-lg font-extrabold tracking-tight text-white lg:text-[38px] lg:leading-[46px]">
                    Mulai Kelola PKL Lebih Tertata dari Sekarang
                </h2>
                <p class="mb-8 max-w-2xl font-body-lg text-body-lg leading-relaxed text-white/90">
                    Bergabung dengan alur kerja yang sudah dipakai admin, dosen pembimbing, dan mahasiswa untuk
                    menjalankan Praktik Kerja Lapangan tanpa berkas tercecer.
                </p>
                <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row sm:flex-wrap sm:items-center sm:gap-4">
                    <a href="{{ $ctaHref }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-surface-container-lowest px-7 py-3.5 font-title-sm text-title-sm font-semibold text-primary shadow-md transition-colors hover:bg-surface-container-high">
                        <x-icon name="login" class="text-lg text-primary" />
                        <span>{{ $ctaLabel }}</span>
                    </a>
                    <a href="#alur-pkl"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-white/10 px-6 py-3.5 font-title-sm text-title-sm font-semibold text-white backdrop-blur-md transition-colors hover:bg-white/20">
                        <x-icon name="route" class="text-lg" />
                        <span>Lihat Alur PKL</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
