<x-app-layout>
    <x-ui.page-header title="Jurnal Harian" :subtitle="'Periode ' . $periode->nama">
        <x-slot:actions>
            <a href="{{ route('mahasiswa.jurnal.create') }}"
                class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-blue-500 to-cyan-500 px-5 py-2.5 font-title-sm text-title-sm font-semibold text-white shadow-sm hover:shadow-lg">
                <x-icon name="add" class="h-4 w-4" />
                Tulis Jurnal
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-5">
        <x-ui.stat-card label="Total Jurnal" :value="$jurnal->total()" icon="menu_book" tone="blue" />
        <x-ui.stat-card label="Menunggu Review" :value="$statistik['submitted'] ?? 0" icon="hourglass_empty" tone="amber" />
        <x-ui.stat-card label="Disetujui" :value="$statistik['approved'] ?? 0" icon="task_alt" tone="emerald" />
        <x-ui.stat-card label="Perlu Revisi" :value="$statistik['revision'] ?? 0" icon="edit_note" tone="rose" />
        <x-ui.stat-card label="Draf" :value="$statistik['draft'] ?? 0" icon="edit" tone="slate" />
    </div>

    @if ($jurnal->isEmpty())
        <div class="rounded-2xl border border-gray-200 bg-white">
            <x-ui.empty-state title="Belum ada jurnal"
                message="Mulai dokumentasikan kegiatan harian Anda agar pembimbing dapat memantaunya." icon="menu_book">
                <x-slot:action>
                    <a href="{{ route('mahasiswa.jurnal.create') }}"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-5 py-2.5 font-title-sm text-title-sm font-semibold text-white hover:bg-blue-700">
                        <x-icon name="add" class="h-4 w-4" /> Tulis Jurnal Pertama
                    </a>
                </x-slot:action>
            </x-ui.empty-state>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($jurnal as $j)
                <article
                    class="rounded-2xl border border-gray-200 bg-white p-5 transition-shadow hover:shadow-md">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="font-title-md text-title-md font-bold text-gray-900">
                                    {{ $j->judul }}
                                </h3>
                                <x-ui.badge :label="$j->status_label" :tone="match($j->status) {
                                    'approved' => 'emerald',
                                    'revision' => 'rose',
                                    'submitted' => 'amber',
                                    'reviewed' => 'blue',
                                    default => 'slate',
                                }" />
                                @if ($j->revisi > 0)
                                    <x-ui.badge label="Revisi ke-{{ $j->revisi }}" tone="violet" />
                                @endif
                            </div>
                            <p class="mt-1 font-body-sm text-body-sm text-gray-500">
                                {{ $j->tanggal->format('l, d F Y') }}
                                @if ($j->jam_mulai) · {{ substr($j->jam_mulai, 0, 5) }}–{{ substr($j->jam_selesai ?? $j->jam_mulai, 0, 5) }} @endif
                            </p>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            <a href="{{ route('mahasiswa.jurnal.show', $j) }}"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50"
                                aria-label="Lihat jurnal {{ $j->judul }}" title="Lihat">
                                <x-icon name="visibility" class="h-4 w-4" />
                            </a>
                            @if (! in_array($j->status, ['approved', 'reviewed'], true))
                                <a href="{{ route('mahasiswa.jurnal.edit', $j) }}"
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50"
                                    aria-label="Ubah jurnal {{ $j->judul }}" title="Ubah">
                                    <x-icon name="edit" class="h-4 w-4" />
                                </a>
                                <form method="POST" action="{{ route('mahasiswa.jurnal.destroy', $j) }}"
                                    x-data="confirmForm({ title: 'Hapus jurnal ini?', text: 'Jurnal beserta seluruh file dokumentasinya akan dihapus permanen dan tidak dapat dibatalkan.', confirmText: 'Ya, hapus', cancelText: 'Batal', danger: true })"
                                    x-on:submit="submit($event)">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50"
                                        aria-label="Hapus jurnal {{ $j->judul }}" title="Hapus">
                                        <x-icon name="delete" class="h-4 w-4" />
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <p class="mt-3 line-clamp-3 font-body-sm text-body-sm leading-relaxed text-gray-600">
                        {{ $j->deskripsi }}
                    </p>

                    @if ($j->catatan_reviewer)
                        <div class="mt-3 rounded-xl border border-amber-200 bg-amber-50 p-3">
                            <p class="font-label-sm text-label-sm font-semibold text-amber-700">
                                Catatan Pembimbing
                            </p>
                            <p class="mt-1 font-body-sm text-body-sm text-amber-800">
                                {{ $j->catatan_reviewer }}
                            </p>
                        </div>
                    @endif

                    @if ($j->komentar->isNotEmpty())
                        <p class="mt-2 font-label-sm text-label-sm text-gray-500">
                            <x-icon name="forum" class="mr-1 inline h-3.5 w-3.5" />
                            {{ $j->komentar->count() }} komentar
                        </p>
                    @endif
                </article>
            @endforeach
        </div>

        <x-ui.pagination :paginator="$jurnal" />
    @endif
</x-app-layout>