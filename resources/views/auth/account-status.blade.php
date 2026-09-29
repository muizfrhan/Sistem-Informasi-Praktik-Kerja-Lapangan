@php
    $pending = $user->status === 'ditunda';
@endphp

<x-guest-layout title="Status Akun — SIPKL">
    <div class="w-full max-w-[480px] mx-auto">

        <a href="{{ route('landing') }}"
            class="inline-flex items-center gap-1.5 font-label-md text-outline hover:text-primary transition-colors">
            <span class="material-symbols-outlined text-[16px]" aria-hidden="true">arrow_back</span>
            Kembali ke beranda
        </a>

        {{-- ============ Kartu status ============ --}}
        <div class="mt-4 card overflow-hidden">
            <div
                class="px-5 sm:px-6 py-8 text-center border-b {{ $pending ? 'bg-primary-fixed border-[#BFDBFE]' : 'bg-error-container border-[#FECACA]' }}">
                <span
                    class="mx-auto w-14 h-14 rounded-lg flex items-center justify-center {{ $pending ? 'bg-primary' : 'bg-error' }} shadow-level-2">
                    <span class="material-symbols-outlined text-white text-[26px]" aria-hidden="true">
                        {{ $pending ? 'hourglass_top' : 'block' }}
                    </span>
                </span>

                <span class="mt-4 inline-block {{ $pending ? 'badge-warning' : 'badge-danger' }}">
                    {{ $pending ? 'Menunggu persetujuan' : 'Akun nonaktif' }}
                </span>

                <h1 class="mt-3 text-headline-sm font-bold text-on-surface tracking-tight text-balance">
                    {{ $pending ? 'Pendaftaran sedang diproses' : 'Akses Anda dinonaktifkan' }}
                </h1>

                <p class="mt-2 text-body-md text-on-surface-variant max-w-sm mx-auto leading-relaxed">
                    {{ $alasan }}
                </p>
            </div>

            {{-- Ringkasan akun --}}
            <div class="p-5 sm:p-6 space-y-6">
                <dl class="grid grid-cols-2 gap-x-4 gap-y-3">
                    @foreach ([
    'Nama' => $user->name,
    'Peran' => $user->labelRole(),
    'NIM' => $user->mahasiswa?->nim ?? '—',
    'Terdaftar' => $user->created_at?->translatedFormat('d M Y') ?? '—',
] as $label => $value)
                        <div class="min-w-0">
                            <dt class="font-label-sm text-[11px] uppercase tracking-wide text-outline">{{ $label }}
                            </dt>
                            <dd class="mt-0.5 text-body-sm text-[13px] text-on-surface truncate" title="{{ $value }}">
                                {{ $value }}
                            </dd>
                        </div>
                    @endforeach
                </dl>

                <div class="h-px bg-surface-container" aria-hidden="true"></div>

                {{-- Langkah selanjutnya --}}
                <ol class="space-y-3">
                    @foreach ([
    ['Kirim konfirmasi ke operator', 'Sebutkan NIM Anda kepada admin PKL sekolah.'],
    ['Tunggu verifikasi data akademik', 'Pemeriksaan Dapodik memerlukan 1×2 hari kerja.'],
    ['Masuk otomatis setelah disetujui', 'Tidak perlu mendaftar ulang.'],
] as $i => [$judul, $ket])
                        <li class="flex items-start gap-3">
                            <span
                                class="w-6 h-6 shrink-0 rounded-full bg-primary-fixed text-primary font-label-md flex items-center justify-center">
                                {{ $i + 1 }}
                            </span>
                            <span class="min-w-0">
                                <span class="block font-title-sm text-[13px] text-on-surface leading-tight">
                                    {{ $judul }}</span>
                                <span class="block text-body-sm text-[12px] text-on-surface-variant leading-snug">
                                    {{ $ket }}</span>
                            </span>
                        </li>
                    @endforeach
                </ol>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    <a href="https://wa.me/6285895859312?text={{ rawurlencode('Halo Admin SIPKL, saya terdaftar dengan NIM ' . ($user->mahasiswa?->nim ?? '-') . ' (' . $user->name . ') dan ingin menanyakan status persetujuan akun saya.') }}"
                        target="_blank" rel="noopener noreferrer" class="btn-primary btn-md">
                        <span class="material-symbols-outlined text-[17px]" aria-hidden="true">chat</span>
                        Hubungi admin
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn-secondary btn-md w-full">
                            <span class="material-symbols-outlined text-[17px]" aria-hidden="true">logout</span>
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
