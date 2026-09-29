@php
    /**
     * Konfirmasi login QR (Device A).
     *
     * $record null  -> QR tidak valid / kedaluwarsa / sudah dipakai / ditolak.
     * $record ada   -> menunggu keputusan user: TOLAK atau IZINKAN.
     */
@endphp

<x-guest-layout title="Konfirmasi Login QR — SIPKL">
    <div class="w-full max-w-[420px] mx-auto">

        {{-- ============ Judul ============ --}}
        <div class="text-center space-y-2">
            <span
                class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-primary-fixed text-primary shrink-0 mx-auto">
                <span class="material-symbols-outlined text-[24px]" aria-hidden="true">qr_code_scanner</span>
            </span>
            <h1 class="text-headline-lg-mobile md:text-headline-md font-bold text-on-surface tracking-tight">
                @if ($record)
                    Konfirmasi Login
                @else
                    Login QR
                @endif
            </h1>
            <p class="text-body-md text-on-surface-variant">
                @if ($record)
                    Ada perangkat baru yang ingin masuk ke akun Anda.
                @else
                    {{ $message }}
                @endif
            </p>
        </div>

        @if (! $record)
            {{-- ============ QR tidak bisa dipakai ============ --}}
            <div class="mt-6 flex flex-col items-center gap-4">
                <span class="material-symbols-outlined text-secondary text-[40px]" aria-hidden="true">timer_off</span>

                <a href="{{ route('perangkat') }}" class="btn-primary btn-md w-full">
                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">refresh</span>
                    Buat QR baru di perangkat lain
                </a>
                <a href="{{ route('redirect') }}" class="btn-secondary btn-md w-full">Kembali ke dashboard</a>
            </div>
        @else
            {{-- ============ Ringkasan permintaan ============ --}}
            <div class="mt-6 rounded-lg border border-outline-variant bg-white overflow-hidden">
                <div class="px-4 py-3 bg-surface-container border-b border-outline-variant">
                    <p class="font-title-sm text-[13px] text-on-surface">Perangkat yang ingin masuk</p>
                </div>

                <dl class="divide-y divide-surface-container-low">
                    @foreach ([
                        ['Devices', $record->device_name ?: 'Tidak diketahui'],
                        ['Browser', $record->browser ?: '—'],
                        ['Platform', $record->platform ?: '—'],
                        ['Waktu', 'Sekarang'],
                        ['Lokasi', $lokasi],
                    ] as [$label, $value])
                        <div class="flex items-center justify-between gap-4 px-4 py-2.5">
                            <dt class="font-label-sm text-[11px] text-outline shrink-0">{{ $label }}</dt>
                            <dd class="font-title-sm text-[13px] text-on-surface text-right min-w-0 truncate">
                                {{ $value }}
                            </dd>
                        </div>
                    @endforeach
                </dl>
            </div>

            {{-- ============ Akun yang akan dipakai ============ --}}
            <div class="mt-4 rounded-lg border border-[#BFDBFE] bg-primary-fixed p-4">
                <div class="flex items-start gap-3">
                    <span class="w-9 h-9 shrink-0 rounded-md bg-primary text-white text-sm font-semibold flex items-center justify-center">
                        {{ strtoupper(substr(auth()->user()->name ?? '?', 0, 1)) }}
                    </span>
                    <div class="min-w-0">
                        <p class="font-title-sm text-[13px] text-on-surface leading-tight">
                            Masuk sebagai {{ auth()->user()->name }}
                        </p>
                        <p class="mt-0.5 text-body-sm text-[12px] text-on-surface-variant">
                            {{ auth()->user()->labelRole() }} &middot; {{ auth()->user()->email }}
                        </p>
                    </div>
                </div>
                <p class="mt-3 text-body-sm text-[12px] text-on-surface-variant leading-relaxed">
                    Jika diizinkan, perangkat tersebut masuk dengan hak akses
                    <strong class="font-title-sm text-on-surface">{{ auth()->user()->labelRole() }}</strong> — bukan
                    akun lain. Anda tetap bisa mencabutnya kapan saja lewat
                    <strong class="font-title-sm text-on-surface">Perangkat Saya</strong>.
                </p>
            </div>

            {{-- ============ Keputusan ============ --}}
            <div class="mt-5 grid grid-cols-2 gap-3">
                <form method="POST" action="{{ route('qr.login.reject', ['token' => $token]) }}">
                    @csrf
                    <button type="submit" class="btn-secondary btn-lg w-full">
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">close</span>
                        Tolak
                    </button>
                </form>

                <form method="POST" action="{{ route('qr.login.approve', ['token' => $token]) }}">
                    @csrf
                    <button type="submit" class="btn-primary btn-lg w-full">
                        Izinkan
                        <span class="material-symbols-outlined text-[18px]" aria-hidden="true">arrow_forward</span>
                    </button>
                </form>
            </div>

            <p class="mt-4 text-center field-hint">
                QR hanya berlaku sesaat. Jangan izinkan jika perangkat ini bukan milik Anda.
            </p>
        @endif
    </div>
</x-guest-layout>
