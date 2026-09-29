<x-guest-layout title="Kata Sandi Baru — SIPKL">
    <div class="w-full max-w-[420px] mx-auto" x-data="{ submitting: false, show: false }">

        {{-- ============ Navigasi ============ --}}
        <x-auth.switcher current="login" />

        {{-- ============ Judul ============ --}}
        <div class="mt-8 space-y-2">
            <h1 class="text-headline-lg-mobile md:text-headline-md font-bold text-on-surface tracking-tight">
                Kata sandi baru
            </h1>
            <p class="text-body-md text-on-surface-variant">
                Buat kata sandi baru untuk akun <span class="font-medium text-on-surface">{{ $request->email }}</span>.
            </p>
        </div>

        {{-- ============ Error ============ --}}
        @if ($errors->any())
            <x-auth.alert tone="error" class="mt-5">
                <ul class="space-y-0.5">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </x-auth.alert>
        @endif

        {{-- ============ Formulir ============ --}}
        <form method="POST" action="{{ route('password.store') }}" class="mt-6 space-y-4" x-on:submit="submitting = true">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <x-auth.field label="Email terdaftar" name="email" type="email" icon="mail" required maxlength="120"
                autocomplete="email" :value="$request->email" />

            <x-auth.field label="Kata sandi baru" name="password" type="password" icon="lock" required padded
                autocomplete="new-password" placeholder="Minimal 8 karakter" x-model="password"
                x-bind:type="show ? 'text' : 'password'">
                <button type="button" x-on:click="show = !show"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-outline hover:text-on-surface transition-colors"
                    :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'">
                    <span class="material-symbols-outlined text-[18px]" x-show="!show" aria-hidden="true">visibility</span>
                    <span class="material-symbols-outlined text-[18px]" x-cloak x-show="show"
                        aria-hidden="true">visibility_off</span>
                </button>
            </x-auth.field>

            <x-auth.field label="Ulangi kata sandi baru" name="password_confirmation" type="password" icon="lock"
                required padded autocomplete="new-password" placeholder="Ketik ulang kata sandi" />

            <button type="submit" x-bind:disabled="submitting" class="btn-primary btn-lg w-full">
                <span x-show="!submitting" class="inline-flex items-center gap-2">
                    Simpan kata sandi
                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_forward</span>
                </span>
                <span x-cloak x-show="submitting" class="inline-flex items-center gap-2">
                    <span class="btn-spinner" aria-hidden="true"></span>
                    Menyimpan&hellip;
                </span>
            </button>
        </form>

        <p class="mt-6 text-center text-body-sm text-[12px] text-on-surface-variant">
            Tautan tidak berlaku atau salah?
            <a href="{{ route('password.request') }}" class="font-title-sm text-primary hover:underline">Minta tautan
                baru</a>
        </p>
    </div>
</x-guest-layout>
