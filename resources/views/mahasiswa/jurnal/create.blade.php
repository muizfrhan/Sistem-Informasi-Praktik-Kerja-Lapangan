<x-app-layout>
    <x-ui.page-header title="Tulis Jurnal Harian"
        :subtitle="'Periode ' . ($periode?->nama ?? '-')" :back="route('mahasiswa.jurnal.index')" />

    @if (! $periode || ! $periode->sedangBerjalan())
        <x-ui.alert tone="warning" title="Di luar masa pelaksanaan">
            Jurnal hanya dapat dibuat pada rentang tanggal periode PKL ({{ $periode?->tanggal_mulai->format('d M Y') ?? '-' }}
            s/d. {{ $periode?->tanggal_selesai->format('d M Y') ?? '-' }}).
        </x-ui.alert>
    @endif

    @if ($errors->any())
        <x-ui.alert tone="error" title="Periksa kembali isian Anda">
            <ul class="mt-1 list-disc space-y-0.5 pl-5">
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </x-ui.alert>
    @endif

    <form method="POST" action="{{ route('mahasiswa.jurnal.store') }}" enctype="multipart/form-data"
        class="rounded-2xl border border-gray-200 bg-white p-6"
        x-data="{ dragging: false }">
        @csrf

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            <x-ui.form-field label="Tanggal" name="tanggal" type="date" required
                :value="old('tanggal', now()->format('Y-m-d'))" :max="now()->format('Y-m-d')" />
            <x-ui.form-field label="Judul Kegiatan" name="judul" required :value="old('judul')"
                placeholder="Contoh: Membuat modul autentikasi" />
            <x-ui.form-field label="Jam Mulai" name="jam_mulai" type="time" :value="old('jam_mulai')" />
            <x-ui.form-field label="Jam Selesai" name="jam_selesai" type="time" :value="old('jam_selesai')" />
        </div>

        <div class="mt-5 space-y-5">
            <x-ui.form-field label="Deskripsi Kegiatan" name="deskripsi" type="textarea" required :rows="5"
                :value="old('deskripsi')"
                placeholder="Jelaskan kegiatan yang dilakukan hari ini secara kronologis..." />
            <x-ui.form-field label="Output / Hasil Kerja" name="output" type="textarea" :rows="3"
                :value="old('output')" hint="Misalnya: 1 modul selesai, 5 endpoint baru." />
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <x-ui.form-field label="Kendala" name="kendala" type="textarea" :rows="3" :value="old('kendala')" />
                <x-ui.form-field label="Solusi" name="solusi" type="textarea" :rows="3" :value="old('solusi')" />
            </div>
        </div>

        <div class="mt-5">
            <label for="dokumentasi"
                class="mb-1.5 block font-body-sm text-body-sm font-semibold text-gray-700">
                Dokumentasi (opsional, maks. 5 berkas)
            </label>
            <label for="dokumentasi"
                @dragover.prevent="dragging = true" @dragleave="dragging = false"
                @drop.prevent="dragging = false"
                class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed px-6 py-8 text-center transition-colors"
                :class="dragging ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:border-blue-400'">
                <x-icon name="upload_file" class="text-3xl text-gray-400" />
                <span class="font-body-sm text-body-sm text-gray-600">
                    Seret berkas ke sini atau klik untuk memilih
                </span>
                <span class="font-label-sm text-label-sm text-gray-400">
                    PDF, JPG, PNG · maks. 5 MB per berkas
                </span>
            </label>
            <input id="dokumentasi" type="file" name="dokumentasi[]" multiple
                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                class="sr-only">
        </div>

        <x-ui.form-actions class="mt-6" :cancel="route('mahasiswa.jurnal.index')" submit-label="Kirim Jurnal" />
    </form>
</x-app-layout>