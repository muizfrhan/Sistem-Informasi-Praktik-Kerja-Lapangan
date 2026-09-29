<x-app-layout>
    <x-ui.page-header title="Detail Sertifikat" :subtitle="$sertifikat->nomor"
        :back="route('mahasiswa.sertifikat.index')">
        <x-slot:actions>
            @if ($sertifikat->file_pdf)
                <a href="{{ route('mahasiswa.sertifikat.download', $sertifikat) }}"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2.5 font-title-sm text-title-sm font-semibold text-white hover:bg-blue-700">
                    <x-icon name="download" class="h-4 w-4" /> Unduh PDF
                </a>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    <div class="rounded-2xl border-2 border-emerald-200 bg-gradient-to-br from-emerald-50 to-white p-8 to-transparent">
        <div class="text-center">
            <p class="font-label-sm text-label-sm uppercase tracking-[0.3em] text-emerald-700">
                Sertifikat
            </p>
            <h2 class="mt-2 font-headline-lg text-headline-lg font-extrabold tracking-tight text-gray-900">
                Praktik Kerja Lapangan
            </h2>
            <p class="mt-1 font-body-md text-body-md text-gray-600">
                Periode {{ $sertifikat->periode->nama }}
            </p>

            <div class="mx-auto mt-8 max-w-md border-y border-emerald-200 py-6">
                <p class="font-body-sm text-body-sm text-gray-500">Diberikan kepada</p>
                <p class="mt-1 font-headline-md text-headline-md font-bold text-gray-900">
                    {{ $sertifikat->mahasiswa->nama }}
                </p>
                <p class="font-body-sm text-body-sm text-gray-600">
                    NIM {{ $sertifikat->mahasiswa->nim }}
                </p>
            </div>

            <div class="mx-auto mt-6 grid max-w-2xl grid-cols-2 gap-4 text-left sm:grid-cols-4">
                <div>
                    <p class="font-label-sm text-label-sm uppercase text-gray-500">Nilai</p>
                    <p class="font-title-sm text-title-sm font-bold text-gray-900">{{ $sertifikat->nilai_akhir }}</p>
                </div>
                <div>
                    <p class="font-label-sm text-label-sm uppercase text-gray-500">Predikat</p>
                    <p class="font-title-sm text-title-sm font-bold text-gray-900">{{ $sertifikat->predikat }}</p>
                </div>
                <div>
                    <p class="font-label-sm text-label-sm uppercase text-gray-500">Durasi</p>
                    <p class="font-title-sm text-title-sm font-bold text-gray-900">{{ $sertifikat->durasi_hari }} hari</p>
                </div>
                <div>
                    <p class="font-label-sm text-label-sm uppercase text-gray-500">Terbit</p>
                    <p class="font-title-sm text-title-sm font-bold text-gray-900">
                        {{ $sertifikat->tanggal_terbit->format('d M Y') }}
                    </p>
                </div>
            </div>

            <div class="mt-8 flex flex-col items-center gap-3">
                <p class="font-label-sm text-label-sm text-gray-500">Nomor: {{ $sertifikat->nomor }}</p>
                <a href="{{ route('certificate.verify', $sertifikat->kode_verifikasi) }}"
                    target="_blank" rel="noopener noreferrer"
                    class="inline-flex items-center gap-2 rounded-xl border border-emerald-300 bg-white px-4 py-2 font-body-sm text-body-sm font-semibold text-emerald-700 hover:bg-emerald-50">
                    <x-icon name="qr_code_2" class="h-5 w-5" />
                    Kode verifikasi: {{ $sertifikat->kode_verifikasi }}
                </a>
            </div>
        </div>
    </div>
</x-app-layout>