<x-app-layout>
    <x-ui.page-header title="Rekap Absensi Bulanan"
        :subtitle="'Periode ' . $periode->nama" :back="route('mahasiswa.absensi.index')" />

    <form method="GET" class="mb-6 flex flex-wrap items-end gap-3">
        <x-ui.form-field label="Bulan" name="bulan" type="select" :value="$bulan" class="w-40"
            :options="collect(range(1, 12))->mapWithKeys(fn($m) => [$m => \Illuminate\Support\Carbon::create(null, $m)->format('F')])" />
        <x-ui.form-field label="Tahun" name="tahun" type="number" :value="$tahun" class="w-32" min="2020" max="2100" />
        <button type="submit"
            class="inline-flex h-[42px] items-center gap-1.5 rounded-xl bg-blue-600 px-5 font-title-sm text-title-sm font-semibold text-white hover:bg-blue-700">
            <x-icon name="search" class="h-4 w-4" /> Tampilkan
        </button>
    </form>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.stat-card label="Hari Kerja" :value="$totalHariKerja" icon="calendar_today" tone="slate" />
        <x-ui.stat-card label="Hari Hadir" :value="$hadir" icon="task_alt" tone="emerald" />
        <x-ui.stat-card label="Persentase" :value="$persentase . '%'" icon="percent" tone="blue" />
        <x-ui.stat-card label="Total Tercatat" :value="$absensi->count()" icon="list_alt" tone="cyan" />
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 rounded-2xl border border-gray-200 bg-white p-6">
            <h2 class="mb-4 font-title-md text-title-md font-bold text-gray-900">Kalender</h2>
            <div class="grid grid-cols-7 gap-1.5 text-center">
                @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $h)
                    <div class="font-label-sm text-label-sm font-semibold text-gray-500">{{ $h }}</div>
                @endforeach

                @php $offset = $awal->startOfMonth()->dayOfWeekIso - 1; @endphp
                @for ($i = 0; $i < $offset; $i++)
                    <div></div>
                @endfor

                @for ($d = 1; $d <= $awal->daysInMonth; $d++)
                    @php
                        $tgl = $awal->copy()->addDays($d - 1);
                        $rec = $absensi->firstWhere('tanggal', $tgl->toDateString())
                            ?? $absensi->first(fn($x) => $x->tanggal->isSameDay($tgl));
                        $tone = match ($rec?->status) {
                            'hadir' => 'bg-emerald-500 text-white',
                            'terlambat' => 'bg-amber-500 text-white',
                            'izin' => 'bg-blue-500 text-white',
                            'sakit' => 'bg-violet-500 text-white',
                            'alpha' => 'bg-rose-500 text-white',
                            'libur' => 'bg-slate-400 text-white',
                            default => 'bg-gray-100 text-gray-500',
                        };
                    @endphp
                    <div
                        class="flex aspect-square items-center justify-center rounded-lg text-xs font-semibold {{ $tone }}"
                        title="{{ $tgl->format('d M Y') }}{{ $rec ? ' — ' . $rec->status : '' }}">
                        {{ $d }}
                    </div>
                @endfor
            </div>

            <div class="mt-5 flex flex-wrap gap-3">
                @foreach (['Hadir' => 'bg-emerald-500', 'Terlambat' => 'bg-amber-500', 'Izin' => 'bg-blue-500', 'Sakit' => 'bg-violet-500', 'Alpha' => 'bg-rose-500', 'Libur' => 'bg-slate-400', 'Belum Absen' => 'bg-gray-100'] as $label => $warna)
                    <span class="inline-flex items-center gap-1.5 font-label-sm text-label-sm text-gray-600">
                        <span class="h-2.5 w-2.5 rounded {{ $warna }}"></span>{{ $label }}
                    </span>
                @endforeach
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6">
            <h2 class="mb-4 font-title-md text-title-md font-bold text-gray-900">Rincian per Status</h2>
            <div class="space-y-3">
                @forelse ($rekap as $status => $jumlah)
                    <div class="flex items-center justify-between gap-3">
                        <x-ui.badge :label="ucfirst($status)"
                            :tone="match($status) { 'hadir' => 'emerald', 'terlambat' => 'amber', 'izin' => 'blue', 'sakit' => 'violet', 'alpha' => 'rose', default => 'slate' }" />
                        <span class="font-title-sm text-title-sm font-semibold text-gray-900">{{ $jumlah }} hari</span>
                    </div>
                @empty
                    <x-ui.empty-state title="Tidak ada data" message="Belum ada absensi pada bulan ini." />
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>