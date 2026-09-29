<x-app-layout>
    <x-ui.page-header title="Absensi PKL"
        :subtitle="'Periode ' . $periode->nama . ' · ' . $periode->tanggal_mulai->format('d M Y') . ' s/d ' . $periode->tanggal_selesai->format('d M Y')">
        <x-slot:actions>
            <a href="{{ route('mahasiswa.absensi.rekap') }}"
                class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 px-4 py-2.5 font-title-sm text-title-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
                <x-icon name="calendar_month" class="h-4 w-4" />
                Rekap Bulanan
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.alert tone="info" :dismissible="false" title="Aturan absensi">
        Batas jam masuk <strong>{{ \App\Models\Setting::get('pkl_jam_masuk', '08:00') }}</strong> dengan toleransi
        {{ \App\Models\Setting::get('pkl_toleransi_telat', 15) }} menit.
        @if (! $periode->sedangBerjalan())
            <strong class="block mt-1 text-amber-600">Saat ini di luar rentang tanggal periode, absensi dinonaktifkan.</strong>
        @endif
    </x-ui.alert>

    {{-- Kartu aksi check in / out --}}
    <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div
            class="lg:col-span-2 rounded-2xl border border-gray-200 bg-white p-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="font-label-sm text-label-sm uppercase tracking-wide text-gray-500">
                        Status hari ini
                    </p>
                    <p class="mt-1 font-headline-md text-headline-md font-bold text-gray-900">
                        {{ now()->format('l, d F Y') }}
                    </p>
                    <p class="font-body-sm text-body-sm text-gray-500">
                        Waktu server: <span id="jam-sekarang">{{ now()->format('H:i:s') }}</span> WIB
                    </p>
                </div>
                <div class="text-right">
                    @if ($hariIni)
                        <x-ui.badge :label="$hariIni->status === 'terlambat' ? 'Terlambat' : ucfirst($hariIni->status)"
                            :tone="$hariIni->status === 'terlambat' ? 'amber' : ($hariIni->status === 'hadir' ? 'emerald' : 'slate')" />
                        <p class="mt-2 font-title-sm text-title-sm text-gray-700">
                            Masuk {{ $hariIni->jam_masuk ? substr($hariIni->jam_masuk, 0, 5) : '-' }}
                        </p>
                        <p class="font-body-sm text-body-sm text-gray-500">
                            Pulang {{ $hariIni->jam_pulang ? substr($hariIni->jam_pulang, 0, 5) : '-' }}
                            @if ($hariIni->durasi_menit) · {{ intdiv($hariIni->durasi_menit, 60) }}j {{ $hariIni->durasi_menit % 60 }}m @endif
                        </p>
                    @else
                        <x-ui.badge label="Belum Absen" tone="slate" />
                        <p class="mt-2 font-body-sm text-body-sm text-gray-500">
                            Silakan check-in
                        </p>
                    @endif
                </div>
            </div>

            @if ($izinAktif)
                <div class="mt-4">
                    <x-ui.alert tone="warning" :dismissible="false">
                        Anda sedang dalam ({{ $izinAktif->jenis }}) yang disetujui
                        s.d. {{ $izinAktif->tanggal_selesai->format('d M Y') }}.
                    </x-ui.alert>
                </div>
            @endif

            <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                @unless ($hariIni)
                    <form method="POST" action="{{ route('mahasiswa.absensi.checkin') }}" class="flex-1"
                        x-data="{ lat: null, lon: null }"
                        x-init="if (navigator.geolocation) navigator.geolocation.getCurrentPosition(p => { lat = p.coords.latitude; lon = p.coords.longitude })">
                        @csrf
                        <input type="hidden" name="latitude" x-bind:value="lat">
                        <input type="hidden" name="longitude" x-bind:value="lon">
                        <button type="submit" @disabled(!$periode->sedangBerjalan())
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 px-5 py-3 font-title-sm text-title-sm font-semibold text-white shadow-sm transition-all hover:shadow-lg disabled:cursor-not-allowed disabled:opacity-50">
                            <x-icon name="login" class="h-5 w-5" />
                            Check In Sekarang
                        </button>
                    </form>
                @endunless

                @if ($hariIni && ! $hariIni->jam_pulang)
                    <form method="POST" action="{{ route('mahasiswa.absensi.checkout') }}" class="flex-1"
                        x-data="{ lat: null, lon: null }"
                        x-init="if (navigator.geolocation) navigator.geolocation.getCurrentPosition(p => { lat = p.coords.latitude; lon = p.coords.longitude })">
                        @csrf
                        <input type="hidden" name="latitude" x-bind:value="lat">
                        <input type="hidden" name="longitude" x-bind:value="lon">
                        <button type="submit"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-rose-500 to-pink-500 px-5 py-3 font-title-sm text-title-sm font-semibold text-white shadow-sm transition-all hover:shadow-lg">
                            <x-icon name="logout" class="h-5 w-5" />
                            Check Out
                        </button>
                    </form>
                @endif

                @if ($hariIni && $hariIni->jam_pulang)
                    <div
                        class="flex-1 rounded-xl bg-gray-100 px-5 py-3 text-center font-body-sm text-body-sm text-gray-500">
                        Absensi hari ini sudah lengkap
                    </div>
                @endif
            </div>
        </div>

        <div
            class="rounded-2xl border border-gray-200 bg-white p-6">
            <h3 class="font-title-md text-title-md font-bold text-gray-900">Statistik Kehadiran</h3>
            <div class="mt-4">
                <x-ui.progress label="Persentase hadir" :value="$persentase" tone="emerald" size="lg" />
            </div>

            <dl class="mt-5 space-y-2.5">
                @foreach ([
            'hadir' => ['Hadir', 'emerald'],
            'terlambat' => ['Terlambat', 'amber'],
            'izin' => ['Izin', 'blue'],
            'sakit' => ['Sakit', 'violet'],
            'alpha' => ['Alpha', 'rose'],
            'libur' => ['Libur', 'slate'],
        ] as $key => [$label, $tone])
                    <div class="flex items-center justify-between gap-3">
                        <dt class="font-body-sm text-body-sm text-gray-600">{{ $label }}</dt>
                        <dd>
                            <x-ui.badge :label="(string)($rekap[$key] ?? 0)" :tone="$tone" />
                        </dd>
                    </div>
                @endforeach
            </dl>

            <p class="mt-4 border-t border-gray-200 pt-3 font-label-sm text-label-sm text-gray-500">
                Total {{ $totalAbsensi }} hari tercatat.
            </p>
        </div>
    </div>

    {{-- Riwayat --}}
    <div class="rounded-2xl border border-gray-200 bg-white">
        <div class="border-b border-gray-200 px-6 py-4">
            <h2 class="font-title-md text-title-md font-bold text-gray-900">Riwayat Absensi</h2>
        </div>

        @if ($riwayat->isEmpty())
            <x-ui.empty-state title="Belum ada riwayat absensi"
                message="Catatan absensi akan muncul setelah Anda melakukan check-in." icon="event_available" />
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-left">
                    <thead class="border-b border-gray-200 bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 font-label-sm text-label-sm font-semibold uppercase tracking-wider text-gray-500">Tanggal</th>
                            <th scope="col" class="px-6 py-3 font-label-sm text-label-sm font-semibold uppercase tracking-wider text-gray-500">Masuk</th>
                            <th scope="col" class="px-6 py-3 font-label-sm text-label-sm font-semibold uppercase tracking-wider text-gray-500">Pulang</th>
                            <th scope="col" class="px-6 py-3 font-label-sm text-label-sm font-semibold uppercase tracking-wider text-gray-500">Durasi</th>
                            <th scope="col" class="px-6 py-3 font-label-sm text-label-sm font-semibold uppercase tracking-wider text-gray-500">Status</th>
                            <th scope="col" class="px-6 py-3 font-label-sm text-label-sm font-semibold uppercase tracking-wider text-gray-500">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($riwayat as $a)
                            <tr class="transition-colors hover:bg-gray-50">
                                <td class="whitespace-nowrap px-6 py-4 font-body-sm text-body-sm text-gray-900">
                                    {{ $a->tanggal->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 font-body-sm text-body-sm text-gray-600">
                                    {{ $a->jam_masuk ? substr($a->jam_masuk, 0, 5) : '-' }}
                                </td>
                                <td class="px-6 py-4 font-body-sm text-body-sm text-gray-600">
                                    {{ $a->jam_pulang ? substr($a->jam_pulang, 0, 5) : '-' }}
                                </td>
                                <td class="px-6 py-4 font-body-sm text-body-sm text-gray-600">
                                    {{ $a->durasi_menit ? intdiv($a->durasi_menit, 60) . 'j ' . ($a->durasi_menit % 60) . 'm' : '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    <x-ui.badge :label="ucfirst($a->status)"
                                        :tone="match($a->status) { 'hadir' => 'emerald', 'terlambat' => 'amber', 'izin' => 'blue', 'sakit' => 'violet', 'alpha' => 'rose', default => 'slate' }" />
                                </td>
                                <td class="px-6 py-4 font-body-sm text-body-sm text-gray-500">
                                    {{ \Illuminate\Support\Str::limit($a->catatan ?? '-', 40) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <x-ui.pagination :paginator="$riwayat" class="mt-2" />
</x-app-layout>

@push('scripts')
    <script>
        (function () {
            const el = document.getElementById('jam-sekarang');
            if (!el) return;
            setInterval(() => {
                const now = new Date();
                el.textContent = now.toLocaleTimeString('id-ID', { hour12: false });
            }, 1000);
        })();
    </script>
@endpush