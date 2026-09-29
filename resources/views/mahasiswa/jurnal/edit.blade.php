<x-app-layout>
    <x-ui.page-header title="Ubah Jurnal" :subtitle="$jurnal->judul"
        :back="route('mahasiswa.jurnal.index')" />

    @if ($errors->any())
        <x-ui.alert tone="error" title="Periksa kembali isian Anda">
            <ul class="mt-1 list-disc space-y-0.5 pl-5">
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </x-ui.alert>
    @endif

    <form method="POST" action="{{ route('mahasiswa.jurnal.update', $jurnal) }}" enctype="multipart/form-data"
        class="rounded-2xl border border-gray-200 bg-white p-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            <x-ui.form-field label="Tanggal" name="tanggal" type="date" required
                :value="old('tanggal', $jurnal->tanggal->format('Y-m-d'))" :max="now()->format('Y-m-d')" />
            <x-ui.form-field label="Judul Kegiatan" name="judul" required :value="old('judul', $jurnal->judul)" />
            <x-ui.form-field label="Jam Mulai" name="jam_mulai" type="time"
                :value="old('jam_mulai', $jurnal->jam_mulai ? substr($jurnal->jam_mulai, 0, 5) : '')" />
            <x-ui.form-field label="Jam Selesai" name="jam_selesai" type="time"
                :value="old('jam_selesai', $jurnal->jam_selesai ? substr($jurnal->jam_selesai, 0, 5) : '')" />
        </div>

        <div class="mt-5 space-y-5">
            <x-ui.form-field label="Deskripsi Kegiatan" name="deskripsi" type="textarea" required :rows="5"
                :value="old('deskripsi', $jurnal->deskripsi)" />
            <x-ui.form-field label="Output / Hasil Kerja" name="output" type="textarea" :rows="3"
                :value="old('output', $jurnal->output)" />
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <x-ui.form-field label="Kendala" name="kendala" type="textarea" :rows="3"
                    :value="old('kendala', $jurnal->kendala)" />
                <x-ui.form-field label="Solusi" name="solusi" type="textarea" :rows="3"
                    :value="old('solusi', $jurnal->solusi)" />
            </div>
        </div>

        @if ($jurnal->dokumentasi)
            <div class="mt-5">
                <p class="mb-2 font-body-sm text-body-sm font-semibold text-gray-700">
                    Dokumentasi saat ini
                </p>
                <ul class="flex flex-wrap gap-2">
                    @foreach ($jurnal->dokumentasi as $path)
                        <li>
                            <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($path) }}"
                                target="_blank" rel="noopener noreferrer"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-1.5 font-body-sm text-body-sm text-blue-600 hover:bg-blue-50">
                                <x-icon name="attach_file" class="h-4 w-4" />
                                {{ basename($path) }}
                            </a>
                        </li>
                    @endforeach
                </ul>
                <p class="mt-2 font-label-sm text-label-sm text-gray-500">
                    Mengunggah berkas baru akan mengganti dokumen yang ada.
                </p>
            </div>
        @endif

        <div class="mt-5">
            <x-ui.form-field label="Tambah Dokumentasi" name="dokumentasi" type="file"
                hint="PDF, JPG, PNG · maks. 5 berkas" />
        </div>

        <x-ui.form-actions class="mt-6" :cancel="route('mahasiswa.jurnal.index')" submit-label="Simpan Perubahan" />
    </form>
</x-app-layout>