<x-guest-layout title="Menunggu Persetujuan Login — SIPKL">
    {{--
        Perangkat baru (belum login) yang sudah membuka / qr-login / {token}.
        Halaman ini mem-poll status; begitu pemilik akun menyetujui, sesi login
        dibuat otomatis lewat form ke `qr.login.claim`.
    --}}
    <div class="w-full max-w-[420px] mx-auto"
        x-data="qrLoginPanel({ status: @js(route('qr.login.status')) }, {{ (int) config('qr-login.poll_interval') }})"
        x-init="start()">

        {{-- Form tersembunyi: submit = klaim sesi lalu masuk. --}}
        <form method="POST" action="{{ route('qr.login.claim') }}" x-ref="claimForm" x-show="false">@csrf</form>

        {{-- ============ Judul ============ --}}
        <div class="text-center space-y-2">
            <span
                class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-primary-fixed text-primary shrink-0 mx-auto">
                <span class="material-symbols-outlined text-[24px]" aria-hidden="true">qr_code_scanner</span>
            </span>
            <h1 class="text-headline-lg-mobile md:text-headline-md font-bold text-on-surface tracking-tight">
                @if ($record)
                    Menunggu Persetujuan
                @else
                    Login QR
                @endif
            </h1>
            <p class="text-body-md text-on-surface-variant">
                @if ($record)
                    QR sudah dibaca. Tunggu sampai pemilik akun menyetujui di perangkatnya.
                @else
                    {{ $message }}
                @endif
            </p>
        </div>

        @if ($record)
            {{-- ============ Status ============ --}}
            <div class="mt-6 flex flex-col items-center">
                <div class="relative w-full max-w-[240px] aspect-square rounded-lg border border-outline-variant bg-white p-4 flex items-center justify-center">
                    <div x-show="status === 'pending' || status === 'scanned' || status === 'awaiting_confirmation'"
                        class="text-center px-2">
                        <span class="btn-spinner text-primary" aria-hidden="true"></span>
                        <p class="mt-3 font-title-sm text-[13px] text-on-surface">Menunggu persetujuan…</p>
                        <p class="mt-1 field-hint">Pemilik akun akan melihat notifikasi Izinkan / Tolak di dashboardnya.</p>
                    </div>

                    <div x-show="status === 'approved'" x-cloak class="text-center">
                        <span class="material-symbols-outlined text-tertiary text-[34px] block" aria-hidden="true">check_circle</span>
                        <p class="mt-2 font-title-sm text-[13px] text-on-surface">Disetujui</p>
                        <p class="mt-0.5 field-hint">Mengautentikasi akun&hellip;</p>
                    </div>

                    <div x-show="status === 'rejected' || status === 'expired' || status === 'used' || status === 'error'"
                        x-cloak class="text-center px-2">
                        <span class="material-symbols-outlined text-secondary text-[32px] block" aria-hidden="true"
                            x-text="status === 'used' ? 'block' : (status === 'error' ? 'error' : 'timer_off')"></span>
                        <p class="mt-2 text-body-sm text-[12px] text-on-surface-variant leading-snug" x-text="message"></p>
                    </div>
                </div>

                <div class="mt-4 w-full">
                    <div class="flex items-center justify-between gap-3">
                        <p class="field-hint min-w-0" aria-live="polite"
                            x-text="message || 'Menunggu keputusan di perangkat yang sudah login…'"></p>
                        <span x-show="status !== 'none'" x-cloak
                            class="font-label-sm text-[11px] text-outline tabular-nums shrink-0" x-text="countdown"
                            aria-label="Sisa waktu"></span>
                    </div>
                </div>
            </div>

            {{-- ============ Info permintaan ============ --}}
            <div class="mt-5 rounded-lg border border-outline-variant bg-white overflow-hidden">
                <div class="px-4 py-3 bg-surface-container border-b border-outline-variant">
                    <p class="font-title-sm text-[13px] text-on-surface">Permintaan dari</p>
                </div>
                <dl class="divide-y divide-surface-container-low">
                    @foreach ([
                        ['Perangkat', $record->device_name ?: 'Tidak diketahui'],
                        ['Browser', $record->browser ?: '—'],
                        ['Platform', $record->platform ?: '—'],
                        ['Lokasi', $lokasi],
                    ] as [$label, $value])
                        <div class="flex items-center justify-between gap-4 px-4 py-2.5">
                            <dt class="font-label-sm text-[11px] text-outline shrink-0">{{ $label }}</dt>
                            <dd class="font-title-sm text-[13px] text-on-surface text-right min-w-0 truncate">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        @else
            {{-- ============ QR tidak bisa dipakai ============ --}}
            <div class="mt-6 flex flex-col items-center gap-4">
                <span class="material-symbols-outlined text-secondary text-[40px]" aria-hidden="true">timer_off</span>
                <p class="text-center field-hint">Minta QR baru kepada pemilik akun, lalu pindai lagi.</p>
                <a href="{{ route('login') }}" class="btn-primary btn-md w-full">Kembali ke halaman masuk</a>
            </div>
        @endif
    </div>
</x-guest-layout>
