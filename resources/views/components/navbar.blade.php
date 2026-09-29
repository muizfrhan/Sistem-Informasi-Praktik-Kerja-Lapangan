{{-- App bar: tinggi tetap 68px sesuai DESIGN.md "Layout Model". --}}
<header
    class="fixed top-0 inset-x-0 z-50 h-app-bar bg-white/90 backdrop-blur-xl border-b border-outline-variant">
    <div class="h-full px-4 sm:px-6 flex items-center justify-between gap-3">

        {{-- Kiri: tombol menu (mobile/tablet) + logo --}}
        <div class="flex items-center gap-2 sm:gap-3 min-w-0">
            <button type="button" x-on:click="sidebarOpen = !sidebarOpen" aria-controls="app-sidebar"
                aria-label="Buka menu navigasi"
                class="lg:hidden p-2 -ml-2 rounded-md text-on-surface-variant hover:bg-surface-container hover:text-on-surface transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <a href="{{ route('redirect') }}" class="flex items-center gap-2.5 min-w-0 group">
                <img src="{{ asset('images/sipkl-mark.svg') }}" alt="Logo SIPKL"
                    class="w-9 h-9 rounded-md shrink-0" width="64" height="64">
                <span class="min-w-0">
                    <span
                        class="block font-title-md text-[15px] font-bold text-on-surface leading-none tracking-tight">
                        SIPKL
                    </span>
                    <span
                        class="hidden sm:block font-label-sm text-[10px] text-outline leading-none mt-1 truncate">
                        Sistem Informasi Praktik Kerja Lapangan
                    </span>
                </span>
            </a>
        </div>

        {{-- Kanan: menu pengguna --}}
        <div class="flex items-center gap-1 sm:gap-2 shrink-0">

            {{-- Menu pengguna --}}
            <div class="relative" x-data="{ open: false }" @keydown.escape="open = false" @click.outside="open = false">
                <button type="button" @click="open = !open" :aria-expanded="open"
                    class="flex items-center gap-2 h-10 pl-1 pr-2 rounded-md hover:bg-surface-container transition-colors">
                    <span
                        class="w-8 h-8 shrink-0 rounded-full overflow-hidden bg-primary text-white text-xs font-semibold flex items-center justify-center">
                        @if ($foto = Auth::user()?->foto_url)
                            <img src="{{ $foto }}" alt="Foto {{ Auth::user()->name }}" class="w-full h-full object-cover"
                                width="32" height="32" loading="lazy">
                        @else
                            {{ Auth::user()?->inisial }}
                        @endif
                    </span>
                    <span class="hidden md:block text-left min-w-0">
                        <span
                            class="block font-title-sm text-[13px] text-on-surface leading-tight truncate max-w-[9rem]">
                            {{ Auth::user()?->name }}
                        </span>
                        <span
                            class="block font-label-sm text-[10px] text-outline leading-tight mt-0.5">
                            {{ Auth::user()?->labelRole() }}
                        </span>
                    </span>
                    <svg class="w-4 h-4 text-outline shrink-0 transition-transform duration-200"
                        :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                {{-- Dropdown: elevation level 3 --}}
                <div x-cloak x-show="open" x-transition.origin.top.right
                    class="absolute right-0 mt-2 w-60 rounded-lg border border-outline-variant bg-white shadow-level-3 overflow-hidden z-50">
                    <div class="px-4 py-3 border-b border-surface-container flex items-center gap-3">
                        <span
                            class="w-10 h-10 shrink-0 rounded-full overflow-hidden bg-primary text-white text-[13px] font-semibold flex items-center justify-center">
                            @if ($foto = Auth::user()?->foto_url)
                                <img src="{{ $foto }}" alt="" class="w-full h-full object-cover" width="40" height="40"
                                    loading="lazy">
                            @else
                                {{ Auth::user()?->inisial }}
                            @endif
                        </span>
                        <div class="min-w-0">
                            <p class="font-title-sm text-[13px] text-on-surface truncate">
                                {{ Auth::user()?->name }}</p>
                            <p class="font-body-sm text-[12px] text-outline truncate">
                                {{ Auth::user()?->email }}</p>
                        </div>
                    </div>

                    <a href="{{ route('profile.edit') }}"
                        class="flex items-center gap-2.5 px-4 h-10 text-body-md text-on-surface hover:bg-surface-container transition-colors">
                        <svg class="w-[18px] h-[18px] shrink-0 text-outline" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Profil saya
                    </a>

                    {{-- Perangkat Saya: daftar perangkat + pemindai QR Login --}}
                    <a href="{{ route('perangkat') }}"
                        class="flex items-center gap-2.5 px-4 h-10 text-body-md text-on-surface hover:bg-surface-container transition-colors">
                        <svg class="w-[18px] h-[18px] shrink-0 text-outline" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 18h.01M12 14h.01M12 10h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        Perangkat Saya
                        <span class="ml-auto font-label-sm text-[10px] text-outline">QR Login</span>
                    </a>

                    <form method="POST" action="{{ route('logout') }}" class="border-t border-surface-container">
                        @csrf
                        <button type="submit"
                            class="w-full flex items-center gap-2.5 px-4 h-10 text-left text-body-md text-error hover:bg-error-container transition-colors">
                            <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
