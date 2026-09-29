<x-app-layout>
    <x-ui.page-header title="Tugas PKL" subtitle="Daftar tugas dari pembimbing dan status pengumpulan" />

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-ui.stat-card label="Total Tugas" :value="$statistik['total']" icon="assignment" tone="blue" />
        <x-ui.stat-card label="Selesai" :value="$statistik['selesai']" icon="task_alt" tone="emerald" />
        <x-ui.stat-card label="Terlambat" :value="$statistik['terlambat']" icon="running_with_errors" tone="rose" />
    </div>

    @if ($tugas->isEmpty())
        <div class="rounded-2xl border border-gray-200 bg-white">
            <x-ui.empty-state title="Belum ada tugas"
                message="Tugas dari pembimbing akan muncul di sini." icon="assignment" />
        </div>
    @else
        <div class="space-y-4">
            @foreach ($tugas as $t)
                @php $sub = $t->submissions->first(); @endphp
                <a href="{{ route('mahasiswa.tugas.show', $t) }}"
                    class="block rounded-2xl border border-gray-200 bg-white p-5 transition-all hover:-translate-y-0.5 hover:shadow-lg">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h3 class="font-title-md text-title-md font-bold text-gray-900">
                                {{ $t->judul }}
                            </h3>
                            <p class="mt-1 font-body-sm text-body-sm text-gray-500">
                                Dari {{ $t->dosen?->nama ?? 'Pembimbing' }} ·
                                Deadline {{ $t->deadline->format('d M Y H:i') }}
                            </p>
                        </div>
                        <div class="flex shrink-0 flex-col items-end gap-2">
                            <x-ui.badge
                                :label="match($sub?->status) { 'completed' => 'Selesai', 'reviewed' => 'Direview', 'revision' => 'Perlu Revisi', 'submitted' => 'Menunggu Review', default => 'Belum Dikumpulkan' }"
                                :tone="match($sub?->status) { 'completed' => 'emerald', 'reviewed' => 'blue', 'revision' => 'rose', 'submitted' => 'amber', default => 'slate' }" />
                            @if ($t->sudahLewat() && ! $sub)
                                <x-ui.badge label="Terlambat" tone="rose" />
                            @else
                                <x-ui.badge :label="($t->deadline->diffForHumans())" tone="slate" />
                            @endif
                        </div>
                    </div>

                    <p class="mt-3 line-clamp-2 font-body-sm text-body-sm text-gray-600">
                        {{ $t->deskripsi }}
                    </p>

                    <div class="mt-4">
                        <x-ui.progress
                            :label="'Progres pengumpulan'"
                            :value="match($sub?->status) { 'completed' => 100, 'reviewed' => 100, 'revision' => 60, 'submitted' => 80, default => $t->sudahLewat() ? 100 : 30 }"
                            :tone="match($sub?->status) { 'completed' => 'emerald', 'submitted' => 'amber', 'revision' => 'rose', default => 'blue' }"
                            :showValue="false" size="sm" />
                    </div>
                </a>
            @endforeach
        </div>

        <x-ui.pagination :paginator="$tugas" />
    @endif
</x-app-layout>