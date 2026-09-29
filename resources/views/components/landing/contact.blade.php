@php
    $adminWa = 'https://wa.me/6285895859312?text=Halo%20Admin%2C%20saya%20memerlukan%20bantuan%20terkait%20SIPKL.%20Mohon%20informasinya%2C%20terima%20kasih.';
@endphp

<section id="kontak" class="bg-surface-container-lowest py-14 shadow-[0_-1px_8px_rgba(0,0,0,0.02)] lg:py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-12">
        <div class="grid grid-cols-1 items-center gap-8 lg:grid-cols-12">
            <div class="lg:col-span-7">
                <span class="section-eyebrow">Kontak</span>
                <h2 class="mb-3 mt-3 font-headline-lg text-headline-lg font-bold text-on-surface">
                    Butuh Bantuan atau Akses Akun?
                </h2>
                <p class="max-w-2xl font-body-md text-body-md leading-relaxed text-on-surface-variant">
                    Akun SIPKL dibuat oleh administrator. Jika Anda mahasiswa atau dosen pembimbing yang belum
                    memiliki akun, atau membutuhkan bantuan teknis, hubungi administrator melalui WhatsApp.
                </p>
            </div>

            <div class="lg:col-span-5">
                <div
                    class="flex flex-col gap-4 rounded-2xl border border-surface-variant bg-surface-container-low p-6 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-primary-container text-on-primary">
                            <x-icon name="support_agent" class="text-2xl" />
                        </div>
                        <div>
                            <p class="font-title-sm text-title-sm font-semibold text-on-surface">Administrator
                                Jurusan</p>
                            <p class="font-label-sm text-label-sm text-on-surface-variant">Pendampingan akun &
                                bantuan teknis</p>
                        </div>
                    </div>
                    <a href="{{ $adminWa }}" target="_blank" rel="noopener noreferrer"
                        class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-primary px-5 py-3 font-title-sm text-title-sm text-on-primary shadow-sm transition-colors hover:bg-primary-container">
                        <x-icon name="chat" class="text-lg" />
                        <span>Hubungi Admin</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
