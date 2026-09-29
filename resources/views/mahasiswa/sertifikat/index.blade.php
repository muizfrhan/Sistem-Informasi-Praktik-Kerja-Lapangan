<x-app-layout>
    <x-ui.page-header title="Sertifikat PKL" subtitle="Sertifikat yang telah diterbitkan untuk Anda" />

    @if ($sertifikat->isEmpty())
        <div class="rounded-2xl border border-gray-200 bg-white">
            <x-ui.empty-state title="Belum ada sertifikat"
                message="Sertifikat diterbitkan setelah seluruh proses PKL Anda selesai dan dinilai." icon="workspace_premium" />
        </div>
    @else
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            @foreach ($sertifikat as $s)
                <div
                    class="rounded-2xl border p-6 {{ $s->status === 'terbit' ? 'border-emerald-200 bg-gradient-to-br from-emerald-50 to-white to-transparent' : 'border-gray-200' }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-500 text-white">
                                <x-icon name="workspace_premium" class="text-2xl" />
                            </div>
                            <div>
                                <p class="font-title-sm text-title-sm font-bold text-gray-900">
                                    {{ $s->nomor }}
                                </p>
                                <p class="font-label-sm text-label-sm text-gray-500">
                                    {{ $s->periode->nama }}
                                </p>
                            </div>
                        </div>
                        <x-ui.badge :label="ucfirst($s->status)" :tone="$s->status === 'terbit' ? 'emerald' : 'slate'" />
                    </div>

                    <dl class="mt-5 space-y-2 font-body-sm text-body-sm">
                        <div class="flex justify-between gap-3">
                            <dt class="text-gray-500">Tanggal Terbit</dt>
                            <dd class="font-medium text-gray-900">{{ $s->tanggal_terbit->format('d M Y') }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-gray-500">Nilai</dt>
                            <dd class="font-medium text-gray-900">{{ $s->nilai_akhir }} ({{ $s->predikat }})</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-gray-500">Durasi</dt>
                            <dd class="font-medium text-gray-900">{{ $s->durasi_hari }} hari</dd>
                        </div>
                    </dl>

                    <div class="mt-5 flex flex-wrap gap-2">
                        <a href="{{ route('mahasiswa.sertifikat.show', $s) }}"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2 font-title-sm text-title-sm font-semibold text-white hover:bg-blue-700">
                            <x-icon name="visibility" class="h-4 w-4" /> Lihat
                        </a>
                        @if ($s->file_pdf)
                            <a href="{{ route('mahasiswa.sertifikat.download', $s) }}"
                                class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 px-4 py-2 font-title-sm text-title-sm font-semibold text-gray-700 hover:bg-gray-50">
                                <x-icon name="download" class="h-4 w-4" /> Unduh PDF
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-app-layout>