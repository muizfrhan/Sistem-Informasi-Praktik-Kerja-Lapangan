{{--
    Formulir informasiprofil: foto, nama, dan email.

    Foto profil memakai pratinjau langsung di browser (Alpine `photoPicker`)
    lewat object URL, sehingga pengguna bisa melihat hasilnya sebelum
    menyimpan. Validasi tetap diulang di server (ProfileUpdateRequest).
--}}
@php
    $fotoUrl = $user->foto_url;
    $maksKb = \App\Http\Requests\ProfileUpdateRequest::maksKb();
@endphp

<section x-data="photoPicker(@js($fotoUrl), @js($user->inisial), { maksKb: @js($maksKb) })">
    <header class="mb-6">
        <h2 class="font-headline-sm text-headline-sm font-bold text-on-surface tracking-tight">Informasi Profil</h2>
        <p class="mt-1.5 text-body-sm text-body-sm text-on-surface-variant leading-relaxed">
            Pasang foto profil, lalu perbarui nama dan alamat email akun Anda.
        </p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('patch')

        {{-- ===================== Foto profil ===================== --}}
        <div class="flex flex-col sm:flex-row sm:items-start gap-5">
            {{-- Pratinjau --}}
            <div class="shrink-0 mx-auto sm:mx-0">
                <div class="relative w-24 h-24">
                    <div
                        class="w-full h-full rounded-full overflow-hidden bg-primary-fixed border border-outline-variant grid place-items-center">
                        <img x-show="preview" x-bind:src="preview" alt="Pratinjau foto profil"
                            class="w-full h-full object-cover">

                        <span x-show="! preview"
                            class="font-headline-md text-headline-md font-bold text-on-primary-fixed-variant"
                            x-text="inisial"></span>
                    </div>

                    {{-- Spinner saat membaca berkas --}}
                    <div x-show="loading" x-cloak
                        class="absolute inset-0 rounded-full bg-surface-container/70 grid place-items-center">
                        <span class="btn-spinner text-primary" aria-hidden="true"></span>
                    </div>
                </div>
            </div>

            {{-- Kontrol --}}
            <div class="min-w-0 flex-1">
                <input type="file" name="foto" id="foto" class="sr-only" x-ref="input"
                    accept="image/jpeg,image/png,image/webp" x-on:change="pilih($event)">

                {{-- Area drop: bisa diklik maupun diseret --}}
                <div x-on:click="buka()" x-on:keydown.enter.prevent="buka()"
                    x-on:keydown.space.prevent="buka()" tabindex="0" role="button"
                    aria-label="Pilih atau seret foto profil ke sini"
                    class="w-full px-4 py-5 rounded-lg border-2 border-dashed cursor-pointer transition-colors duration-200"
                    :class="dragAktif
                        ? 'border-primary bg-primary-fixed/40'
                        : 'border-outline-variant bg-surface-container-low hover:border-primary hover:bg-surface-container'"
                    x-on:dragover.prevent="dragAktif = true" x-on:dragleave.prevent="dragAktif = false"
                    x-on:drop.prevent="tarik($event)">
                    <div class="flex flex-col items-center gap-1.5 text-center">
                        <span class="material-symbols-outlined text-[26px] text-outline" aria-hidden="true">
                            add_photo_alternate
                        </span>
                        <p class="text-body-sm text-body-sm text-on-surface">
                            <span class="font-title-sm">Klik untuk memilih</span> atau seret berkas ke sini
                        </p>
                        <p class="field-hint">
                            JPG, PNG, WEBP &middot; maks {{ number_format($maksKb / 1024, 0) }}MB
                        </p>
                    </div>
                </div>

                {{-- Berkas yang sedang dipilih --}}
                <div x-show="namaFile" x-cloak
                    class="mt-2.5 flex items-center gap-2 px-3 py-2 rounded-lg bg-surface-container border border-outline-variant">
                    <span class="material-symbols-outlined text-[18px] text-primary shrink-0" aria-hidden="true">
                        image
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block font-title-sm text-[12px] text-on-surface truncate" x-text="namaFile"></span>
                        <span class="block font-label-sm text-[10px] text-outline" x-text="ukuranFile"></span>
                    </span>
                    <button type="button" x-on:click.stop="bersihkan()" aria-label="Batalkan pilihan berkas"
                        class="shrink-0 p-1 -mr-1 rounded text-outline hover:text-on-surface transition-colors">
                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">close</span>
                    </button>
                </div>

                {{-- Foto lama + tombol hapus --}}
                <div x-show="punyaFoto && ! namaFile" x-cloak
                    class="mt-2.5 flex flex-wrap items-center gap-2">
                    <button type="button" x-on:click="hapusFoto()"
                        class="btn-secondary btn-sm text-error">
                        <span class="material-symbols-outlined text-[16px]" aria-hidden="true">delete</span>
                        Hapus foto
                    </button>
                    <p x-show="hapusDipilih" x-cloak class="font-body-sm text-[12px] text-error">
                        Foto akan dihapus setelah kamu tekan Simpan.
                    </p>
                </div>

                {{-- Galat validasi sisi klien --}}
                <p x-show="galat" x-cloak x-text="galat" class="mt-2 field-error-text" role="alert"></p>

                @error('foto')
                    <p class="mt-2 field-error-text">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Penanda "hapus foto" --}}
        <input type="hidden" name="hapus_foto" x-bind:value="hapusDipilih ? 1 : 0">

        <div class="pt-5 border-t border-outline-variant space-y-5">
            {{-- Nama --}}
            <div class="space-y-1.5">
                <x-input-label for="name" value="Nama lengkap" class="text-on-surface" />
                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                    autocomplete="name" placeholder="Masukkan nama lengkap"
                    class="field @error('name') field-error @enderror">
                @error('name')
                    <p class="field-error-text">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email --}}
            <div class="space-y-1.5">
                <x-input-label for="email" value="Email" class="text-on-surface" />
                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                    autocomplete="username" placeholder="nama@email.com"
                    class="field @error('email') field-error @enderror">
                @error('email')
                    <p class="field-error-text">{{ $message }}</p>
                @enderror
                <p class="field-hint">Email dipakai untuk masuk dan pemberitahuan sistem.</p>
            </div>
        </div>

        <div class="pt-5 border-t border-outline-variant flex justify-end">
            <button type="submit" class="btn-primary btn-md">
                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">save</span>
                Simpan perubahan
            </button>
        </div>
    </form>
</section>
