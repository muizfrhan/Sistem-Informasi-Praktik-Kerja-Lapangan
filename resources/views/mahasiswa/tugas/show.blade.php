<x-app-layout>
    <x-ui.page-header :title="$tugas->judul" :subtitle="'Dari ' . ($tugas->dosen?->nama ?? 'Pembimbing')"
        :back="route('mahasiswa.tugas.index')" />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-6">
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="font-label-sm text-label-sm uppercase tracking-wide text-gray-500">
                            Deadline
                        </dt>
                        <dd class="font-title-sm text-title-sm font-semibold {{ $tugas->sudahLewat() ? 'text-rose-600' : 'text-gray-900' }}">
                            {{ $tugas->deadline->format('d F Y H:i') }}
                            <span class="block font-body-sm text-body-sm font-normal text-gray-500">
                                {{ $tugas->deadline->diffForHumans() }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="font-label-sm text-label-sm uppercase tracking-wide text-gray-500">
                            Status
                        </dt>
                        <dd class="mt-1">
                            <x-ui.badge
                                :label="match($submission?->status) { 'completed' => 'Selesai', 'reviewed' => 'Direview', 'revision' => 'Perlu Revisi', 'submitted' => 'Menunggu Review', default => 'Belum Dikumpulkan' }"
                                :tone="match($submission?->status) { 'completed' => 'emerald', 'reviewed' => 'blue', 'revision' => 'rose', 'submitted' => 'amber', default => 'slate' }" />
                        </dd>
                    </div>
                </dl>

                <h2 class="mt-5 font-body-sm text-body-sm font-semibold uppercase tracking-wide text-gray-500">
                    Deskripsi Tugas
                </h2>
                <p class="mt-1 whitespace-pre-line font-body-md text-body-md leading-relaxed text-gray-700">
                    {{ $tugas->deskripsi }}
                </p>

                @if ($tugas->attachment)
                    <h2 class="mt-5 font-body-sm text-body-sm font-semibold uppercase tracking-wide text-gray-500">
                        Lampiran dari pembimbing
                    </h2>
                    <ul class="mt-2 space-y-2">
                        @foreach ($tugas->attachment as $path)
                            <li>
                                <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($path) }}"
                                    target="_blank" rel="noopener noreferrer"
                                    class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 font-body-sm text-body-sm text-blue-600 hover:bg-blue-50">
                                    <x-icon name="attach_file" class="h-4 w-4" /> {{ basename($path) }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            @if ($submission?->feedback)
                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6">
                    <h2 class="flex items-center gap-2 font-title-md text-title-md font-bold text-amber-800">
                        <x-icon name="rate_review" class="text-lg" /> Feedback Pembimbing
                    </h2>
                    <p class="mt-2 whitespace-pre-line font-body-sm text-body-sm text-amber-800">
                        {{ $submission->feedback }}
                    </p>
                    @if ($submission->nilai)
                        <p class="mt-3 font-headline-sm text-headline-sm font-bold text-amber-900">
                            Nilai: {{ $submission->nilai }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <div class="rounded-2xl border border-gray-200 bg-white p-6">
                <h2 class="font-title-md text-title-md font-bold text-gray-900">Kumpulkan Tugas</h2>

                @if ($submission && in_array($submission->status, ['completed', 'reviewed'], true))
                    <p class="mt-3 font-body-sm text-body-sm text-gray-600">
                        Tugas ini sudah dinilai dan tidak dapat dikirim ulang.
                    </p>
                @else
                    <form method="POST" action="{{ route('mahasiswa.tugas.submit', $tugas) }}"
                        enctype="multipart/form-data" class="mt-4 space-y-4">
                        @csrf

                        <div>
                            <label for="file"
                                class="mb-1.5 block font-body-sm text-body-sm font-semibold text-gray-700">
                                Berkas Hasil
                            </label>
                            <input id="file" type="file" name="file[]" multiple
                                accept=".pdf,.doc,.docx,.zip,.jpg,.png"
                                class="block w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-blue-600 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-white">
                            <p class="mt-1 font-label-sm text-label-sm text-gray-500">
                                PDF/DOC/DOCX/ZIP, maks. 5 berkas.
                            </p>
                        </div>

                        <div>
                            <label for="catatan"
                                class="mb-1.5 block font-body-sm text-body-sm font-semibold text-gray-700">
                                Catatan
                            </label>
                            <textarea id="catatan" name="catatan" rows="3"
                                class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Ceritakan singkat hasil pekerjaan Anda...">{{ old('catatan', $submission?->catatan) }}</textarea>
                        </div>

                        @error('file')
                            <p class="font-body-sm text-body-sm text-rose-600">{{ $message }}</p>
                        @enderror
                        @error('file.*')
                            <p class="font-body-sm text-body-sm text-rose-600">{{ $message }}</p>
                        @enderror
                        @error('catatan')
                            <p class="font-body-sm text-body-sm text-rose-600">{{ $message }}</p>
                        @enderror

                        <button type="submit"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-blue-500 to-cyan-500 px-5 py-2.5 font-title-sm text-title-sm font-semibold text-white shadow-sm hover:shadow-lg">
                            <x-icon name="upload" class="h-4 w-4" />
                            {{ $submission ? 'Kirim Ulang' : 'Kirim Tugas' }}
                        </button>
                    </form>
                @endif

                @if ($submission?->file)
                    <div class="mt-5 border-t border-gray-200 pt-4">
                        <p class="mb-2 font-label-sm text-label-sm font-semibold uppercase tracking-wide text-gray-500">
                            Berkas terkirim
                        </p>
                        <ul class="space-y-1.5">
                            @foreach ($submission->file as $path)
                                <li>
                                    <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($path) }}"
                                        target="_blank" rel="noopener noreferrer"
                                        class="inline-flex items-center gap-1.5 font-body-sm text-body-sm text-blue-600 hover:underline">
                                        <x-icon name="attach_file" class="h-3.5 w-3.5" /> {{ basename($path) }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>