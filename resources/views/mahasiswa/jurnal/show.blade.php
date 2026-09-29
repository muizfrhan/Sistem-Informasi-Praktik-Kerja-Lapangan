<x-app-layout>
    <x-ui.page-header :title="$jurnal->judul"
        :subtitle="$jurnal->tanggal->format('l, d F Y')" :back="route('mahasiswa.jurnal.index')">
        <x-slot:actions>
            @if (! in_array($jurnal->status, ['approved', 'reviewed'], true))
                <a href="{{ route('mahasiswa.jurnal.edit', $jurnal) }}"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 px-4 py-2.5 font-title-sm text-title-sm font-semibold text-gray-700 hover:bg-gray-50">
                    <x-icon name="edit" class="h-4 w-4" /> Ubah
                </a>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-6">
                <div class="mb-4 flex flex-wrap items-center gap-2">
                    <x-ui.badge :label="$jurnal->status_label" :tone="match($jurnal->status) {
                        'approved' => 'emerald', 'revision' => 'rose',
                        'submitted' => 'amber', 'reviewed' => 'blue', default => 'slate',
                    }" />
                    @if ($jurnal->revisi > 0)
                        <x-ui.badge label="Revisi ke-{{ $jurnal->revisi }}" tone="violet" />
                    @endif
                </div>

                <h2 class="font-body-sm text-body-sm font-semibold uppercase tracking-wide text-gray-500">
                    Deskripsi
                </h2>
                <p class="mt-1 whitespace-pre-line font-body-md text-body-md leading-relaxed text-gray-700">
                    {{ $jurnal->deskripsi }}
                </p>

                @if ($jurnal->jam_mulai)
                    <p class="mt-3 font-body-sm text-body-sm text-gray-500">
                        <x-icon name="schedule" class="mr-1 inline h-4 w-4" />
                        {{ substr($jurnal->jam_mulai, 0, 5) }} – {{ substr($jurnal->jam_selesai ?? $jurnal->jam_mulai, 0, 5) }}
                    </p>
                @endif
            </div>

            @foreach (['output' => ['Output / Hasil Kerja', 'task_alt'], 'kendala' => ['Kendala', 'warning'], 'solusi' => ['Solusi', 'lightbulb']] as $field => [$label, $icon])
                @if ($jurnal->{$field})
                    <div class="rounded-2xl border border-gray-200 bg-white p-6">
                        <h2 class="flex items-center gap-2 font-title-md text-title-md font-bold text-gray-900">
                            <x-icon :name="$icon" class="text-lg text-blue-500" /> {{ $label }}
                        </h2>
                        <p class="mt-2 whitespace-pre-line font-body-sm text-body-sm leading-relaxed text-gray-700">
                            {{ $jurnal->{$field} }}
                        </p>
                    </div>
                @endif
            @endforeach

            @if ($jurnal->catatan_reviewer)
                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6">
                    <h2 class="flex items-center gap-2 font-title-md text-title-md font-bold text-amber-800">
                        <x-icon name="rate_review" class="text-lg" /> Catatan Pembimbing
                    </h2>
                    <p class="mt-2 whitespace-pre-line font-body-sm text-body-sm text-amber-800">
                        {{ $jurnal->catatan_reviewer }}
                    </p>
                    @if ($jurnal->reviewer)
                        <p class="mt-3 font-label-sm text-label-sm text-amber-700">
                            {{ $jurnal->reviewer->name }} ·
                            {{ $jurnal->reviewed_at?->format('d M Y H:i') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="space-y-6">
            @if ($jurnal->dokumentasi)
                <div class="rounded-2xl border border-gray-200 bg-white p-6">
                    <h2 class="mb-3 font-title-md text-title-md font-bold text-gray-900">Dokumentasi</h2>
                    <ul class="space-y-2">
                        @foreach ($jurnal->dokumentasi as $path)
                            <li>
                                <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($path) }}"
                                    target="_blank" rel="noopener noreferrer"
                                    class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 font-body-sm text-body-sm text-blue-600 hover:bg-blue-50">
                                    <x-icon name="attach_file" class="h-4 w-4 shrink-0" />
                                    <span class="truncate">{{ basename($path) }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="rounded-2xl border border-gray-200 bg-white p-6">
                <h2 class="mb-4 font-title-md text-title-md font-bold text-gray-900">Diskusi</h2>

                <div class="max-h-80 space-y-3 overflow-y-auto pr-1">
                    @forelse ($jurnal->komentar->sortBy('id') as $k)
                        <div
                            class="rounded-xl p-3 {{ $k->user_id === auth()->id() ? 'bg-blue-50' : 'bg-gray-50' }}">
                            <p class="font-label-sm text-label-sm font-semibold text-gray-700">
                                {{ $k->user?->name ?? 'Pengguna dihapus' }}
                            </p>
                            <p class="mt-0.5 font-body-sm text-body-sm text-gray-700">
                                {{ $k->komentar }}
                            </p>
                            <p class="mt-1 font-label-sm text-label-sm text-gray-400">
                                {{ $k->created_at->format('d M H:i') }}
                            </p>
                        </div>
                    @empty
                        <p class="font-body-sm text-body-sm text-gray-500">
                            Belum ada komentar.
                        </p>
                    @endforelse
                </div>

                <form method="POST" action="{{ route('mahasiswa.jurnal.komentar', $jurnal) }}" class="mt-4">
                    @csrf
                    <label for="komentar" class="sr-only">Tulis komentar</label>
                    <textarea id="komentar" name="komentar" rows="3" required
                        placeholder="Tulis pertanyaan atau tanggapan..."
                        class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500"></textarea>
                    @error('komentar')
                        <p class="mt-1 font-body-sm text-body-sm text-rose-600">{{ $message }}</p>
                    @enderror
                    <button type="submit"
                        class="mt-2 inline-flex w-full items-center justify-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2.5 font-title-sm text-title-sm font-semibold text-white hover:bg-blue-700">
                        <x-icon name="send" class="h-4 w-4" /> Kirim Komentar
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>