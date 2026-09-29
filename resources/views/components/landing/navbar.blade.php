@php
    $navLinks = [
        ['label' => 'Beranda', 'href' => route('landing')],
        ['label' => 'Tentang', 'href' => '#tentang'],
        ['label' => 'Fitur', 'href' => '#fitur'],
        ['label' => 'Alur PKL', 'href' => '#alur-pkl'],
        ['label' => 'FAQ', 'href' => '#faq'],
        ['label' => 'Kontak', 'href' => '#kontak'],
    ];

    $ctaHref = auth()->check() ? route('redirect') : route('login');
    $ctaLabel = auth()->check() ? 'Buka Dashboard' : 'Masuk Sistem';
@endphp

<header
    class="fixed inset-x-0 top-0 z-50 border-b border-surface-variant/60 bg-surface/85 shadow-[0_1px_8px_rgba(0,0,0,0.04)] backdrop-blur-xl">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-2 px-4 sm:gap-4 sm:px-6 lg:px-12">

        {{-- Logo + wordmark --}}
        <x-landing.brand size="h-9 sm:h-10" />

        {{-- Navigasi desktop --}}
        <nav class="hidden items-center gap-1 xl:flex" aria-label="Navigasi utama">
            @foreach ($navLinks as $link)
                @if ($link['href'] === route('landing'))
                    <a href="{{ $link['href'] }}" aria-current="page"
                        class="rounded-lg px-3 py-2 font-title-sm text-title-sm text-primary transition-colors hover:bg-surface-container-high">
                        {{ $link['label'] }}
                    </a>
                @else
                    <a href="{{ $link['href'] }}"
                        class="rounded-lg px-3 py-2 font-body-md text-body-md text-on-surface-variant transition-colors hover:bg-surface-container-high hover:text-on-surface">
                        {{ $link['label'] }}
                    </a>
                @endif
            @endforeach
        </nav>

        <div class="flex shrink-0 items-center gap-2 sm:gap-3">
            <a href="#kontak"
                class="hidden rounded-lg px-3 py-2 font-title-sm text-title-sm text-primary transition-colors hover:bg-surface-container-high lg:inline-flex lg:items-center lg:gap-1.5">
                <x-icon name="help" class="text-lg" />
                Bantuan
            </a>

            {{-- CTA ke route login / dashboard existing --}}
            <a href="{{ $ctaHref }}"
                class="inline-flex items-center justify-center gap-1.5 whitespace-nowrap rounded-lg bg-primary-container px-3 py-2 font-title-sm text-title-sm text-on-primary shadow-sm transition-opacity hover:opacity-95 sm:px-4">
                <x-icon name="login" class="text-lg" />
                {{ $ctaLabel }}
            </a>

            {{-- Tombol menu mobile --}}
            <button id="landing-menu-trigger" type="button" aria-expanded="false"
                aria-controls="landing-mobile-menu" aria-label="Buka menu navigasi"
                class="inline-flex h-10 w-10 items-center justify-center rounded-lg text-primary transition-colors hover:bg-surface-container-high xl:hidden">
                <x-icon name="menu" class="text-2xl" />
            </button>
        </div>
    </div>

    {{-- Navigasi mobile --}}
    <div id="landing-mobile-menu" class="hidden border-t border-surface-variant/60 bg-surface xl:hidden">
        <nav class="mx-auto max-w-7xl px-4 py-3 sm:px-6 lg:px-12" aria-label="Navigasi mobile">
            <ul class="grid grid-cols-1 gap-1 sm:grid-cols-2">
                @foreach ($navLinks as $link)
                    <li>
                        <a href="{{ $link['href'] }}"
                            class="block rounded-lg px-3 py-2.5 font-title-sm text-title-sm text-on-surface-variant transition-colors hover:bg-surface-container-low hover:text-on-surface">
                            {{ $link['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>
    </div>
</header>
