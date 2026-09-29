<x-app-layout>
    <x-ui.page-header title="Ajukan Izin" subtitle="Izin, sakit, atau keperluan keluarga"
        :back="route('mahasiswa.izin.index')" />

    @if ($errors->any())
        <x-ui.alert tone="error" title="Periksa kembali isian Anda">
            <ul class="mt-1 list-disc space-y-0.5 pl-5">
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </x-ui.alert>
    @endif

    <form method="POST" action="{{ route('mahasiswa.izin.store') }}" enctype="multipart/form-data"
        class="max-w-3xl rounded-2xl border border-gray-200 bg-white p-6">
        @csrf

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <x-ui.form-field label="Jenis Pengajuan" name="jenis" type="select" required
                :value="old('jenis', 'izin')"
                :options="['izin' => 'Izin', 'sakit' => 'Sakit', 'keluarga' => 'Keperluan Keluarga']" />
            <div></div>
            <x-ui.form-field label="Tanggal Mulai" name="tanggal_mulai" type="date" required
                :value="old('tanggal_mulai', now()->format('Y-m-d'))" :max="now()->format('Y-m-d')" />
            <x-ui.form-field label="Tanggal Selesai" name="tanggal_selesai" type="date" required
                :value="old('tanggal_selesai', now()->format('Y-m-d'))" :max="now()->format('Y-m-d')" />
        </div>

        <div class="mt-5">
            <x-ui.form-field label="Alasan" name="alasan" type="textarea" required :rows="4"
                :value="old('alasan')"
                placeholder="Jelaskan alasan pengajuan izin secara singkat dan jelas..." />
        </div>

        <div class="mt-5">
            <x-ui.form-field label="Lampiran Surat (opsional)" name="lampiran" type="file"
                hint="PDF / JPG / PNG, maksimal 5 MB. Surat dokter bila jenis pengajuan=Sakit." />
        </div>

        <x-ui.form-actions class="mt-6" :cancel="route('mahasiswa.izin.index')" submit-label="Kirim Pengajuan" />
    </form>
</x-app-layout>