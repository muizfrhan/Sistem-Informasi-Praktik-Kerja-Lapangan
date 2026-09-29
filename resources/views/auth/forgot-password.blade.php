@php
    // MAIL_MAILER=log -> tautan reset hanya tersimpan di storage/logs/laravel.log
    $linkDiLog = config('mail.default') === 'log';
@endphp

<x-guest-layout title="Lupa Kata Sandi — SIPKL">
    <div class="w-full max-w-[420px] mx-auto" x-data="{ submitting: false }">

        {{-- ============ Navigasi ============ --}}
        <x-auth.switcher current="login" />

        {{-- ============ Judul ============ --}}
        <div class="mt-8 space-y-2">
            <h1 class="text-headline-lg-mobile md:text-headline-md font-bold text-on-surface tracking-tight">
                Atur ulang kata sandi
            </h1>
            <p class="text-body-md text-on-surface-variant">
                Masukkan email terdaftar. Kami kirim tautan untuk membuat kata sandi baru.
            </p>
        </div>

        {{-- ============ Pesan ============ --}}

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
        <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4" x-on:submit="submitting = true">
            @csrf

            <x-auth.field label="Email terdaftar" name="email" type="email" icon="mail" required autofocus
                maxlength="120" autocomplete="email" placeholder="nama@sekolah.sch.id" />

            <x-auth.alert tone="info" icon="info">
                Akun Dosen, Admin, dan Koordinator dibuat operator. Hubungi administrator bila email tidak ditemukan.
            </x-auth.alert>

            @if ($linkDiLog)
                <x-auth.alert tone="warning" icon="science" title="Tautan hanya tersimpan di log">
                    Mailer lokal masih <code class="font-mono">log</code>. Tautan reset dicetak ke
                    <code class="font-mono">storage/logs/laravel.log</code>, bukan dikirim via email.
                </x-auth.alert>
            @endif

            <button type="submit" x-bind:disabled="submitting" class="btn-primary btn-lg w-full">
                <span x-show="!submitting" class="inline-flex items-center gap-2">
                    Kirim tautan reset
                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_forward</span>
                </span>
                <span x-cloak x-show="submitting" class="inline-flex items-center gap-2">
                    <span class="btn-spinner" aria-hidden="true"></span>
                    Mengirim&hellip;
                </span>
            </button>
        </form>

        <p class="mt-6 text-center text-body-sm text-[12px] text-on-surface-variant">
            Ingat kata sandinya?
            <a href="{{ route('login') }}" class="font-title-sm text-primary hover:underline">Kembali ke halaman
                masuk</a>
        </p>
    </div>
</x-guest-layout>
