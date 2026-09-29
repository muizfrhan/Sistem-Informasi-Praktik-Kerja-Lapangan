<x-app-layout>
    <div class="max-w-3xl">

        {{-- ============ Judul ============ --}}
        <div class="mb-6">
            <h1 class="text-headline-md font-bold text-on-surface tracking-tight">Perangkat Saya</h1>
            <p class="mt-1.5 text-body-md text-on-surface-variant">
                Kirim tautan login ke perangkat baru, pindai QR Login, atau keluarkan akses tertentu.
            </p>
        </div>


        {{-- ============ 1. QR milik akun ini (untuk perangkat baru) ============ --}}
        <section class="card card-pad"
            x-data="qrIssuer({ issue: @js(route('qr.issue')) }, {{ (int) config('qr-login.ttl') }})">

            <div class="flex items-start gap-3">
                <span
                    class="w-9 h-9 shrink-0 rounded-md bg-primary-fixed text-primary flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">qr_code_2</span>
                </span>
                <div class="min-w-0 flex-1">
                    <h2 class="font-title-sm text-[14px] text-on-surface leading-tight">QR Login untuk perangkat
                        baru</h2>
                    <p class="mt-1 text-body-sm text-[12px] text-on-surface-variant leading-relaxed">
                        Buat QR, lalu salin tautannya dan kirim ke perangkat yang ingin masuk. Di sana pilih
                        <strong class="font-title-sm">Scan QR</strong> (kamera) atau
                        <strong class="font-title-sm">Tempel kode QR</strong>. Anda akan menerima notifikasi untuk
                        mengizinkan.
                    </p>
                </div>
            </div>

            <div class="mt-4 flex flex-col sm:flex-row gap-4 items-start">
                {{-- QR --}}
                <div
                    class="relative w-full sm:w-[196px] shrink-0 aspect-square rounded-lg border border-outline-variant bg-white p-2 flex items-center justify-center">
                    <div x-show="status === 'idle' || status === 'loading'" class="text-center px-3">
                        <span class="material-symbols-outlined text-outline text-[28px] block" aria-hidden="true">qr_code_2</span>
                        <p class="mt-1.5 field-hint" x-text="status === 'loading' ? 'Menyiapkan…' : 'Belum ada QR'"></p>
                    </div>

                    {{-- x-html dipakai karena SVG dihasilkan pustaka QR di browser. --}}
                    <div x-show="svg !== ''" class="w-full h-full [&>svg]:w-full [&>svg]:h-full" x-html="svg"></div>

                    <div x-show="status === 'expired' || status === 'error'" x-cloak
                        class="absolute inset-0 grid place-items-center bg-white rounded-lg px-4 text-center">
                        <div>
                            <span class="material-symbols-outlined text-secondary text-[26px] block" aria-hidden="true"
                                x-text="status === 'error' ? 'error' : 'timer_off'"></span>
                            <p class="mt-1.5 field-hint" x-text="message || 'QR telah kedaluwarsa.'"></p>
                        </div>
                    </div>
                </div>

                {{-- Tautan + aksi --}}
                <div class="min-w-0 flex-1 w-full">
                    <div class="flex items-center gap-2 px-3 h-[42px] rounded-lg border border-outline-variant bg-surface-container-low">
                        <span class="material-symbols-outlined text-outline text-[16px] shrink-0" aria-hidden="true">link</span>
                        <input type="text" readonly x-ref="link" :value="url" @focus="$el.select()"
                            class="min-w-0 flex-1 bg-transparent font-mono text-[11px] text-on-surface-variant outline-none"
                            aria-label="Tautan QR" placeholder="Buat QR dulu untuk melihat tautan">
                        <button type="button" x-on:click="copyableCopy($event)" x-bind:disabled="url === ''"
                            class="btn-secondary btn-sm shrink-0 gap-1.5 disabled:opacity-50">Salin</button>
                    </div>

                    <div class="mt-3 flex items-center justify-between gap-3">
                        <p class="field-hint min-w-0" aria-live="polite"
                            x-text="svg === '' ? 'Kirim tautan ini ke perangkat baru.' : 'Menunggu perangkat baru membuka tautan…'"></p>
                        <span x-show="svg !== ''" x-cloak
                            class="font-label-sm text-[11px] text-outline tabular-nums shrink-0" x-text="countdown"></span>
                    </div>

                    <button type="button" x-on:click="create()" x-bind:disabled="loading"
                        class="btn-primary btn-md w-full mt-3">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">qr_code_2</span>
                        <span x-text="svg === '' ? 'Buat QR' : 'Buat QR baru'"></span>
                    </button>
                </div>
            </div>
        </section>

        {{-- ============ 2. Pemindai QR ============ --}}
        <section class="card card-pad" x-data="qrScanner()">
            <div class="flex items-start gap-3">
                <span
                    class="w-9 h-9 shrink-0 rounded-md bg-primary-fixed text-primary flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">qr_code_scanner</span>
                </span>
                <div class="min-w-0 flex-1">
                    <h2 class="font-title-sm text-[14px] text-on-surface leading-tight">Pindai QR Login</h2>
                    <p class="mt-1 text-body-sm text-[12px] text-on-surface-variant leading-relaxed">
                        Arahkan kamera ke QR di perangkat yang ingin masuk. Permintaan yang masuk juga muncul
                        otomatis sebagai notifikasi di atas halaman ini, lengkap dengan tombol Izinkan / Tolak.
                    </p>
                </div>
            </div>

            {{---area kamera--}}
            <div class="mt-4 relative w-full aspect-[4/3] rounded-lg overflow-hidden bg-secondary">
                <video x-ref="video" x-show="status === 'scanning'" playsinline muted
                    class="w-full h-full object-cover"></video>
                <canvas x-ref="canvas" class="hidden"></canvas>

                {{-- Kosong / tidak aktif --}}
                <div x-show="status === 'idle' || status === 'starting'"
                    class="absolute inset-0 grid place-items-center text-center px-6">
                    <div>
                        <span class="material-symbols-outlined text-white/40 text-[34px] block" aria-hidden="true">photo_camera</span>
                        <p class="mt-2 text-body-sm text-[12px] text-white/70" x-text="status === 'starting' ? 'Mengaktifkan kamera…' : 'Kamera belum aktif'"></p>
                    </div>
                </div>

                {{-- Error --}}
                <div x-show="status === 'denied' || status === 'error'" x-cloak
                    class="absolute inset-0 grid place-items-center text-center px-6">
                    <div>
                        <span class="material-symbols-outlined text-secondary text-[30px] block" aria-hidden="true">no_photography</span>
                        <p class="mt-2 text-body-sm text-[12px] text-white/80 leading-relaxed" x-text="error"></p>
                    </div>
                </div>

                {{-- QR terbaca --}}
                <div x-show="status === 'found'" x-cloak class="absolute inset-0 grid place-items-center">
                    <div class="text-center">
                        <span class="material-symbols-outlined text-tertiary-fixed text-[30px] block" aria-hidden="true">check_circle</span>
                        <p class="mt-2 font-title-sm text-[13px] text-white">QR terbaca, membuka konfirmasi…</p>
                    </div>
                </div>
            </div>

            <div class="mt-4 flex flex-wrap items-center gap-2">
                <button type="button" x-show="status !== 'scanning'" x-on:click="start()" class="btn-primary btn-md">
                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">photo_camera</span>
                    Aktifkan kamera
                </button>
                <button type="button" x-show="status === 'scanning'" x-on:click="stop(); status = 'idle'" class="btn-secondary btn-md">
                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">stop</span>
                    Matikan kamera
                </button>
                <a href="{{ route('perangkat') }}" class="btn-secondary btn-md">Muat ulang</a>
            </div>

            <p x-show="! supported" x-cloak class="mt-3 text-body-sm text-[12px] text-error leading-relaxed">
                Browser ini tidak mendukung akses kamera. Gunakan HTTPS atau localhost. Alternatives: salin tautan QR
                di halaman masuk, lalu tempel di kolom <strong class="font-title-sm">Tempel tautan</strong> pada tab
                QR Code — Anda akan diarahkan ke halaman konfirmasi.
            </p>
        </section>

        {{-- ============ Daftar perangkat ============ --}}
        <section class="card card-pad mt-5">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="font-title-sm text-[14px] text-on-surface leading-tight">Perangkat yang sedang login</h2>
                    <p class="mt-1 text-body-sm text-[12px] text-on-surface-variant">
                        @if ($driver === 'database')
                            Sesi yang masih hidup dalam {{ (int) config('session.lifetime', 120) }} menit terakhir.
                        @else
                            Penyimpanan sesi saat ini ({{ $driver }}) tidak mendukung daftar perangkat.
                        @endif
                    </p>
                </div>
                @if ($driver === 'database' && count($devices) > 0)
                    <form method="POST" action="{{ route('perangkat.logout-all') }}"
                        x-data="confirmForm({ title: 'Keluar dari semua perangkat?', text: 'Semua perangkat akan dikeluarkan, termasuk perangkat yang sedang kamu pakai. Kamu harus masuk lagi.', confirmText: 'Ya, keluarkan', cancelText: 'Batal', danger: true })"
                        x-on:submit="submit($event)">
                        @csrf
                        <button type="submit" class="btn-secondary btn-sm text-error">
                            <span class="material-symbols-outlined text-[16px]" aria-hidden="true">logout</span>
                            Keluar dari semua perangkat
                        </button>
                    </form>
                @endif
            </div>

            @if ($driver !== 'database')
                <x-auth.alert tone="info" class="mt-4" icon="info">
                    Daftar perangkat butuh driver sesi <code class="font-mono">database</code>. Atur
                    <code class="font-mono">SESSION_DRIVER=database</code> untuk mengaktifkannya.
                </x-auth.alert>
            @elseif (count($devices) === 0)
                <x-auth.alert tone="info" class="mt-4" icon="info">
                    Tidak ada perangkat lain yang sedang login.
                </x-auth.alert>
            @else
                <ul class="mt-4 divide-y divide-surface-container-low border-t border-surface-container-low">
                    @foreach ($devices as $d)
                        <li class="flex flex-wrap items-center gap-3 py-3">
                            <span
                                class="w-9 h-9 shrink-0 rounded-md flex items-center justify-center {{ $d['current'] ? 'bg-tertiary-container text-on-tertiary-container' : 'bg-surface-container text-outline' }}">
                                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">
                                    {{ $d['platform'] === 'Android' || $d['platform'] === 'iPhone' || $d['platform'] === 'iPad' ? 'smartphone' : 'desktop_windows' }}
                                </span>
                            </span>

                            <div class="min-w-0 flex-1">
                                <p class="font-title-sm text-[13px] text-on-surface leading-tight truncate">
                                    {{ $d['device_name'] }}
                                    @if ($d['current'])
                                        <span class="badge-success ml-1 align-middle">Perangkat ini</span>
                                    @endif
                                </p>
                                <p class="mt-0.5 text-body-sm text-[12px] text-outline leading-tight">
                                    {{ $d['lokasi'] }} &middot;
                                    @if ($d['current'])
                                        Aktif sekarang
                                    @else
                                        Terakhir aktif {{ $d['last_active']->diffForHumans() }}
                                    @endif
                                </p>
                            </div>

                            @unless ($d['current'])
                                <form method="POST" action="{{ route('perangkat.logout', ['device' => $d['id']]) }}"
                                    class="shrink-0">
                                    @csrf
                                    <button type="submit" class="btn-secondary btn-sm text-error">
                                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">logout</span>
                                        Keluar dari perangkat
                                    </button>
                                </form>
                            @endunless
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        {{-- ============ Info akun ============ --}}
        <section class="card card-pad mt-5">
            <h2 class="font-title-sm text-[14px] text-on-surface leading-tight">Akun yang sedang digunakan</h2>
            <dl class="mt-3 divide-y divide-surface-container-low">
                @foreach ([
                    ['Nama', auth()->user()->name],
                    ['Peran', auth()->user()->labelRole()],
                    ['Email', auth()->user()->email],
                    ['Login terakhir', auth()->user()->last_login_at?->diffForHumans() ?? 'Baru saja'],
                ] as [$label, $value])
                    <div class="flex items-center justify-between gap-4 py-2.5">
                        <dt class="font-label-sm text-[11px] text-outline shrink-0">{{ $label }}</dt>
                        <dd class="font-title-sm text-[13px] text-on-surface text-right min-w-0 truncate">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>
    </div>
</x-app-layout>
