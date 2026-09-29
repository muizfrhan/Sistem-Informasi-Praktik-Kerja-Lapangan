@php
    /** Pendaftaran mandiri — hanya untuk role mahasiswa. $jurusanOptions dikirim controller. */
    $semesterOptions = collect(range(1, 8))->mapWithKeys(fn ($n) => [$n => 'Semester ' . $n]);
    $semesterDefault = (string) (intdiv((int) date('n') + 4, 6) * 2 - 1);
@endphp

<x-guest-layout title="Pendaftaran Mahasiswa — SIPKL">
    <div x-data="registerForm()" class="w-full max-w-[560px] mx-auto">

        {{-- ============ Navigasi ============ --}}
        <x-auth.switcher current="register" />

        {{-- ============ Judul ============ --}}
        <div class="mt-6 sm:mt-8 space-y-2">
            <h1 class="text-headline-lg-mobile md:text-headline-md font-bold text-on-surface tracking-tight">
                Registrasi akun mahasiswa
            </h1>
            <p class="text-body-sm sm:text-body-md text-on-surface-variant leading-relaxed">
                Lengkapi formulir berikut. Akun aktif setelah disetujui operator PKL sekolah.
            </p>
        </div>

        {{-- ============ Info alur persetujuan ============ --}}
        <x-auth.alert tone="warning" title="Menunggu persetujuan operator" class="mt-5">
            Verifikasi NIM dan data akademik biasanya selesai dalam 1&times;2 hari kerja. Anda tetap bisa masuk untuk
            memantau status akun.
        </x-auth.alert>

        {{-- ============ Error global ============ --}}
        @if ($errors->any())
            <x-auth.alert tone="error" title="Periksa kembali isian berikut" class="mt-4">
                <ul class="space-y-0.5">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </x-auth.alert>
        @endif

        {{-- ============ Formulir ============
                 w-full + mx-auto|max-w: kolom form selalu rata kiri dan
                 terpusat sebagai satu blok, tidak menumpangalign. --}}
        <form method="POST" action="{{ route('register') }}" class="w-full mt-6 space-y-5 text-left"
            x-on:submit="submitting = true">
            @csrf
            <input type="hidden" name="role" value="mahasiswa">

            {{-- ---------- 1. Data mahasiswa ---------- --}}
            <section class="form-section">
                <header class="form-section-head">
                    <span class="form-section-badge">1</span>
                    <div class="min-w-0">
                        <h2 class="form-section-title">Data mahasiswa</h2>
                        <p class="form-section-desc">Sesuai dokumen resmi sekolah.</p>
                    </div>
                </header>

                <x-auth.field label="Nama lengkap" name="name" icon="person" required autofocus maxlength="255"
                    autocomplete="name" placeholder="Sesuai dokumen resmi" />

                <div class="form-grid">
                    <x-auth.field label="NIM / NISN" name="nim" icon="badge" required inputmode="numeric" maxlength="20"
                        autocomplete="off" placeholder="2310631145"
                        x-on:input="$event.target.value = $event.target.value.replace(/[^0-9]/g, '')" />
                    <x-auth.field label="Nomor WhatsApp" name="no_hp" type="tel" icon="smartphone" required maxlength="25"
                        autocomplete="tel" inputmode="tel" placeholder="081234567890" />
                </div>

                <x-auth.field label="Email aktif" name="email" type="email" icon="mail" required maxlength="120"
                    autocomplete="email" placeholder="nama@sekolah.sch.id"
                    hint="Dipakai untuk pemberitahuan dan pemulihan kata sandi." />
            </section>

            {{-- ---------- 2. Data akademik ---------- --}}
            <section class="form-section">
                <header class="form-section-head">
                    <span class="form-section-badge">2</span>
                    <div class="min-w-0">
                        <h2 class="form-section-title">Data akademik</h2>
                        <p class="form-section-desc">Menentukan prodi dan masa praktik yang terdaftar.</p>
                    </div>
                </header>

                <div class="form-grid">
                    <x-auth.combobox label="Jurusan" name="jurusan" icon="domain" required maxlength="100"
                        :options="$jurusanOptions" placeholder="Pilih atau ketik jurusan"
                        hint="Pilih dari daftar atau ketik bila jurusan baru." />
                    <x-auth.combobox label="Program studi" name="program_studi" icon="menu_book" required maxlength="100"
                        :options="$prodiOptions" placeholder="Pilih atau ketik program studi" />
                </div>

                <div class="form-grid">
                    <x-auth.combobox label="Kelas" name="kelas" icon="groups" required maxlength="50"
                        :options="$kelasOptions" placeholder="Pilih atau ketik kelas" hint="Contoh: TI-5A." />
                    <x-auth.field label="Semester berjalan" name="semester" type="select" icon="calendar_month" required
                        :options="$semesterOptions" :value="$semesterDefault" empty-label="Pilih semester…" />
                </div>
            </section>

            {{-- ---------- 3. Keamanan akun ---------- --}}
            <section class="form-section">
                <header class="form-section-head">
                    <span class="form-section-badge">3</span>
                    <div class="min-w-0">
                        <h2 class="form-section-title">Keamanan akun</h2>
                        <p class="form-section-desc">Gunakan kombinasi huruf, angka, dan simbol.</p>
                    </div>
                </header>

                {{-- Wrapper column biasa. JANGAN h-full: di dalam section ber-height:auto
                     itu resolve menjadi tinggi raksasa dan membengkakkan
                     seluruh section. --}}
                <div class="flex flex-col min-w-0">
                    <x-auth.field label="Kata sandi" name="password" type="password" icon="lock" required padded
                        autocomplete="new-password" placeholder="Minimal 8 karakter" x-model="password"
                        x-bind:type="showPassword ? 'text' : 'password'"
                        x-on:input="strength = scorePassword($event.target.value)">
                        <button type="button" x-on:click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-outline hover:text-on-surface transition-colors"
                            :aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'">
                            <span class="material-symbols-outlined text-[18px]" x-show="!showPassword"
                                aria-hidden="true">visibility</span>
                            <span class="material-symbols-outlined text-[18px]" x-cloak x-show="showPassword"
                                aria-hidden="true">visibility_off</span>
                        </button>
                    </x-auth.field>

                    <div x-cloak x-show="password.length > 0" class="flex items-center gap-3 pl-1 pt-0.5 sm:pl-10">
                        <div class="strength-track">
                            <div class="strength-fill" :class="strengthColor"
                                :style="`width: ${strength * 25}%`"></div>
                        </div>
                        <span class="font-label-sm text-[11px] shrink-0 w-[74px] sm:w-[86px] text-right"
                            :class="strengthTextColor" x-text="strengthLabel"></span>
                    </div>
                </div>

                <div class="flex flex-col min-w-0">
                    <x-auth.field label="Ulangi kata sandi" name="password_confirmation" type="password" icon="lock"
                        required padded autocomplete="new-password" placeholder="Ketik ulang kata sandi"
                        x-model="passwordConfirmation" />
                    <p x-cloak x-show="passwordConfirmation.length > 0"
                        class="field-hint flex items-center gap-1.5 pl-1 pt-0.5 sm:pl-10"
                        :class="passwordConfirmation === password ? 'text-tertiary' : 'text-error'">
                        <span class="material-symbols-outlined text-[14px] shrink-0" aria-hidden="true"
                            x-text="passwordConfirmation === password ? 'check_circle' : 'error'"></span>
                        <span
                            x-text="passwordConfirmation === password ? 'Kata sandi cocok' : 'Kata sandi belum sama'"></span>
                    </p>
                </div>
            </section>

            {{-- ---------- Persetujuan ---------- --}}
            <label class="check-row">
                <input type="checkbox" name="setuju" value="1" x-model="setuju" required class="checkbox mt-0.5">
                <span class="min-w-0 flex-1 text-body-sm text-[13px] text-on-surface-variant leading-relaxed">
                    Saya menyatakan data di atas benar dan bersedia mengikuti ketentuan
                    <span class="font-title-sm text-on-surface">Praktik Kerja Lapangan SIPKL</span>.
                </span>
            </label>
            <p class="field-hint sm:hidden" x-show="!setuju" x-cloak>
                Centang persetujuan terlebih dahulu untuk mengaktifkan tombol kirim.
            </p>
            @if ($errors->has('setuju'))
                <p class="field-error-text flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[14px] shrink-0" aria-hidden="true">error</span>
                    {{ $errors->first('setuju') }}
                </p>
            @endif

            <button type="submit" x-bind:disabled="submitting || !setuju" class="btn-primary btn-lg w-full">
                <span x-show="!setuju" class="inline-flex items-center gap-2">
                    Setujui &amp; kirim pengajuan
                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_forward</span>
                </span>
                <span x-cloak x-show="setuju && !submitting" class="inline-flex items-center gap-2">
                    Kirim pengajuan
                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_forward</span>
                </span>
                <span x-cloak x-show="submitting" class="inline-flex items-center gap-2">
                    <span class="btn-spinner" aria-hidden="true"></span>
                    Menyimpan&hellip;
                </span>
            </button>
        </form>

        {{-- ============ Sudah punya akun ============ --}}
        <div
            class="mt-6 p-4 rounded-lg bg-surface-container-low border border-outline-variant flex flex-col sm:flex-row items-center justify-between gap-3 text-center sm:text-left">
            <div class="min-w-0">
                <p class="font-title-sm text-[13px] text-on-surface">Sudah punya akun?</p>
                <p class="text-body-sm text-[12px] text-on-surface-variant">Masuk langsung ke dashboard Anda.</p>
            </div>
            <a href="{{ route('login') }}" class="btn-secondary btn-sm w-full sm:w-auto shrink-0">Masuk ke akun</a>
        </div>

        <p class="mt-6 pt-5 border-t border-outline-variant text-center font-body-sm text-[11px] text-outline">
            Data Anda terenkripsi dan hanya dipakai untuk keperluan Praktik Kerja Lapangan.
        </p>
    </div>
</x-guest-layout>
