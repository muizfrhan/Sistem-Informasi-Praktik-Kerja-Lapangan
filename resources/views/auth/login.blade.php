<x-guest-layout title="Masuk — SIPKL">
    <div x-data="authLogin(@js(old('login', '')), @js(request()->boolean('qr')))"
        class="w-full max-w-[420px] mx-auto">

        {{-- ============ Navigasi Masuk / Daftar ============ --}}
        <x-auth.switcher current="login" />

        {{-- ============ Judul ============ --}}
        <div class="mt-6 sm:mt-8 space-y-2">
            <h1 class="text-headline-lg-mobile md:text-headline-md font-bold text-on-surface tracking-tight">
                Selamat datang kembali
            </h1>
            <p class="text-body-sm sm:text-body-md text-on-surface-variant leading-relaxed">
                Masuk untuk mengakses logbook, presensi digital, dan verifikasi laporan PKL Anda.
            </p>
        </div>

        {{-- ============ Pesan ============ --}}

        @if ($errors->any())
            <x-auth.alert tone="error" title="Login belum berhasil" class="mt-5" x-show="method === 'password'">
                <ul class="space-y-0.5">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </x-auth.alert>
        @endif

        {{-- ============ Pilihan metode masuk ============ --}}
        <div class="mt-6 grid grid-cols-2 gap-1 p-1 rounded-lg bg-surface-container" role="tablist"
            aria-label="Metode masuk">
            <button type="button" role="tab" x-on:click="method = 'password'" :aria-selected="method === 'password'"
                :class="method === 'password' ? 'bg-white text-primary shadow-level-1' : 'text-on-surface-variant hover:text-on-surface'"
                class="h-9 inline-flex items-center justify-center gap-1.5 rounded-md font-title-sm transition-colors">
                <span class="material-symbols-outlined text-[16px]" aria-hidden="true">password</span>
                Kata Sandi
            </button>
            <button type="button" role="tab" x-on:click="method = 'qr'" :aria-selected="method === 'qr'"
                :class="method === 'qr' ? 'bg-white text-primary shadow-level-1' : 'text-on-surface-variant hover:text-on-surface'"
                class="h-9 inline-flex items-center justify-center gap-1.5 rounded-md font-title-sm transition-colors">
                <span class="material-symbols-outlined text-[16px]" aria-hidden="true">qr_code_scanner</span>
                QR Code
            </button>
        </div>

        {{-- ============ Metode 1: kata sandi ============ --}}
        <div x-show="method === 'password'" class="mt-4">
            <form method="POST" action="{{ route('login') }}" class="space-y-4" x-on:submit="submitting = true">
                @csrf

                {{-- Identitas: NIM / NIP / Email, satu kolom untuk semua peran --}}
                <x-auth.field label="Identitas" name="login" icon="badge" required autofocus autocomplete="username"
                    placeholder="NIM, NIP, atau email terdaftar" x-model="identifier"
                    hint="Sistem mengenali peran Anda otomatis dari identitas yang diisi." />

                {{-- Kata sandi --}}
                <x-auth.field label="Kata sandi" name="password" type="password" icon="lock" required padded
                    autocomplete="current-password" placeholder="Masukkan kata sandi" x-model="password"
                    :link="route('password.request')" link-label="Lupa kata sandi?">
                    <button type="button" x-on:click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-outline hover:text-on-surface transition-colors"
                        :aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'">
                        <span class="material-symbols-outlined text-[18px]" x-show="!showPassword"
                            aria-hidden="true">visibility</span>
                        <span class="material-symbols-outlined text-[18px]" x-cloak x-show="showPassword"
                            aria-hidden="true">visibility_off</span>
                    </button>
                </x-auth.field>

                {{-- Ingat saya --}}
                <label class="check-row">
                    <input type="checkbox" name="remember" value="1" x-model="remember" class="checkbox mt-0.5">
                    <span class="min-w-0 flex-1">
                        <span class="block font-title-sm text-[13px] text-on-surface leading-snug">Ingat saya di
                            perangkat ini</span>
                        <span class="block mt-0.5 field-hint">Sesi tetap aktif selama 30 hari pada perangkat ini.</span>
                    </span>
                    <span
                        class="hidden sm:inline-flex items-center gap-1 rounded-full bg-tertiary-container/15 px-2 py-0.5 font-label-sm text-tertiary shrink-0 mt-0.5">
                        <span class="material-symbols-outlined text-[13px]" aria-hidden="true">shield</span>
                        Terenkripsi
                    </span>
                </label>

                {{-- Submit --}}
                <button type="submit" x-bind:disabled="submitting" class="btn-primary btn-lg w-full">
                    <span x-show="!submitting" class="inline-flex items-center gap-2">
                        Masuk
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_forward</span>
                    </span>
                    <span x-cloak x-show="submitting" class="inline-flex items-center gap-2">
                        <span class="btn-spinner" aria-hidden="true"></span>
                        Memverifikasi&hellip;
                    </span>
                </button>
            </form>
        </div>

        {{-- ============ Metode 2: login QR (perangkat baru) ============ --}}
        {{-- Di sini hanya MEMBACA QR milik dashboard: pindai kamera atau tempel
             tautannya. QR-nya sendiri dibuat di dashboard (Perangkat Saya). --}}
        <div x-show="method === 'qr'" class="mt-4" x-data="qrScanner()">

            {{-- ============ Pindai dengan kamera ============ --}}
            <div class="relative w-full aspect-[4/3] rounded-lg overflow-hidden bg-secondary">
                <video x-ref="video" x-show="status === 'scanning'" playsinline muted
                    class="w-full h-full object-cover"></video>
                <canvas x-ref="canvas" class="hidden"></canvas>

                <div x-show="status === 'idle' || status === 'starting'" class="absolute inset-0 grid place-items-center text-center px-6">
                    <div>
                        <span class="material-symbols-outlined text-white/40 text-[32px] block" aria-hidden="true">photo_camera</span>
                        <p class="mt-2 text-body-sm text-[12px] text-white/70"
                            x-text="status === 'starting' ? 'Mengaktifkan kamera…' : 'Kamera belum aktif'"></p>
                    </div>
                </div>

                <div x-show="status === 'denied' || status === 'error'" x-cloak class="absolute inset-0 grid place-items-center text-center px-6">
                    <div>
                        <span class="material-symbols-outlined text-secondary text-[28px] block" aria-hidden="true">no_photography</span>
                        <p class="mt-2 text-body-sm text-[12px] text-white/80 leading-relaxed" x-text="error"></p>
                    </div>
                </div>

                <div x-show="status === 'found'" x-cloak class="absolute inset-0 grid place-items-center">
                    <div class="text-center">
                        <span class="material-symbols-outlined text-tertiary-fixed text-[28px] block" aria-hidden="true">check_circle</span>
                        <p class="mt-2 font-title-sm text-[13px] text-white">QR terbaca, membuka konfirmasi…</p>
                    </div>
                </div>
            </div>

            <div class="mt-3 flex flex-col sm:flex-row gap-2">
                <button type="button" x-show="status !== 'scanning'" x-on:click="start()" class="btn-primary btn-md flex-1">
                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">photo_camera</span>
                    Scan QR
                </button>
                <button type="button" x-show="status === 'scanning'" x-on:click="stop(); status = 'idle'"
                    class="btn-secondary btn-md flex-1">
                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">stop</span>
                    Matikan kamera
                </button>
            </div>

            <p x-show="! supported" x-cloak class="mt-2 field-error-text">
                Browser ini tidak mendukung akses kamera. Gunakan HTTPS atau localhost, atau tempel kode QR di bawah.
            </p>

            {{-- ============ Tempel kode QR ============ --}}
            <form class="mt-4 pt-4 border-t border-outline-variant" x-on:submit.prevent="pasteLink()">
                <label for="qr-paste" class="field-label">Tempel kode QR</label>
                <div class="mt-1.5 flex flex-col sm:flex-row gap-2">
                    <input type="text" id="qr-paste" x-model="paste" placeholder="Tempel tautan /qr-login/…"
                        class="field flex-1" autocomplete="off" spellcheck="false">
                    <button type="submit" class="btn-secondary btn-md shrink-0">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">login</span>
                        Buka
                    </button>
                </div>
                <p x-show="pasteError" x-cloak class="mt-1.5 field-error-text" x-text="pasteError"></p>
                <p class="mt-1.5 field-hint">
                    Ambil tautannya dari <strong class="font-title-sm text-on-surface">Perangkat Saya &rarr; QR
                    Login</strong> di perangkat yang sudah login.
                </p>
            </form>

            {{-- ============ Petunjuk ============ --}}
            <div class="mt-4 flex items-start gap-2.5 p-3 rounded-lg bg-surface-container border border-outline-variant text-left">
                <span class="material-symbols-outlined text-outline text-[18px] shrink-0 mt-px" aria-hidden="true">info</span>
                <p class="text-body-sm text-[12px] text-on-surface-variant leading-relaxed">
                    Setelah QR dibaca, Anda masuk ke halaman menunggu. Login otomatis terjadi setelah pemilik akun
                    menyetujui — role dan hak akses mengikuti akun tersebut.
                </p>
            </div>
        </div>
        {{-- ============ Ajakan mendaftar ============ --}}
        <div
            class="mt-6 p-4 rounded-lg bg-surface-container-low border border-outline-variant flex flex-col sm:flex-row items-center justify-between gap-3 text-center sm:text-left">
            <div class="min-w-0">
                <p class="font-title-sm text-[13px] text-on-surface">Belum punya akun?</p>
                <p class="text-body-sm text-[12px] text-on-surface-variant">Mahasiswa baru bisa mendaftar sendiri.</p>
            </div>
            <a href="{{ route('register') }}" class="btn-secondary btn-sm w-full sm:w-auto shrink-0">Daftar sekarang</a>
        </div>

        {{-- ============ Footer ============ --}}
        <div class="mt-6 pt-5 border-t border-outline-variant text-center space-y-2">
            <p class="text-body-sm text-[11px] text-outline flex items-center justify-center gap-1.5">
                <span class="material-symbols-outlined text-[14px] shrink-0" aria-hidden="true">verified_user</span>
                <span>Dilindungi enkripsi 256-bit SSL &amp; Kebijakan Privasi SIPKL.</span>
            </p>
            <nav class="flex flex-wrap items-center justify-center gap-x-3 gap-y-1 font-label-sm text-[11px]">
                <a href="{{ route('landing') }}" class="text-outline hover:text-primary transition-colors">Beranda</a>
                <span class="text-border-default" aria-hidden="true">&bull;</span>
                <a href="{{ route('certificate.verify') }}"
                    class="text-outline hover:text-primary transition-colors">Verifikasi Sertifikat</a>
                <span class="text-border-default" aria-hidden="true">&bull;</span>
                <a href="https://wa.me/6285895859312" target="_blank" rel="noopener noreferrer"
                    class="text-outline hover:text-primary transition-colors">Bantuan</a>
            </nav>
        </div>
    </div>
</x-guest-layout>
