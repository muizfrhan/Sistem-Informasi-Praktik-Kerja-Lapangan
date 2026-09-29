<x-app-layout>
    <x-ui.page-header title="Pengajuan Izin" subtitle="Ajukan izin, sakit, atau keperluan keluarga">
        <x-slot:actions>
            <a href="{{ route('mahasiswa.izin.create') }}"
                class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-blue-500 to-cyan-500 px-5 py-2.5 font-title-sm text-title-sm font-semibold text-white shadow-sm hover:shadow-lg">
                <x-icon name="add" class="h-4 w-4" /> Ajukan Izin
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="rounded-2xl border border-gray-200 bg-white">
        @if ($izin->isEmpty())
            <x-ui.empty-state title="Belum ada pengajuan izin"
                message="Ajukan izin bila Anda tidak dapat hadir karena hal yang tidak dapat dihindari." icon="event_busy">
                <x-slot:action>
                    <a href="{{ route('mahasiswa.izin.create') }}"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-5 py-2.5 font-title-sm text-title-sm font-semibold text-white hover:bg-blue-700">
                        <x-icon name="add" class="h-4 w-4" /> Ajukan Sekarang
                    </a>
                </x-slot:action>
            </x-ui.empty-state>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left">
                    <thead class="border-b border-gray-200 bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 font-label-sm text-label-sm font-semibold uppercase tracking-wider text-gray-500">Jenis</th>
                            <th scope="col" class="px-6 py-3 font-label-sm text-label-sm font-semibold uppercase tracking-wider text-gray-500">Periode</th>
                            <th scope="col" class="px-6 py-3 font-label-sm text-label-sm font-semibold uppercase tracking-wider text-gray-500">Alasan</th>
                            <th scope="col" class="px-6 py-3 font-label-sm text-label-sm font-semibold uppercase tracking-wider text-gray-500">Status</th>
                            <th scope="col" class="px-6 py-3 font-label-sm text-label-sm font-semibold uppercase tracking-wider text-gray-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($izin as $i)
                            <tr class="align-top transition-colors hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <x-ui.badge :label="ucfirst($i->jenis)"
                                        :tone="match($i->jenis) { 'izin' => 'blue', 'sakit' => 'violet', default => 'cyan' }" />
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 font-body-sm text-body-sm text-gray-600">
                                    {{ $i->tanggal_mulai->format('d M Y') }}<br>
                                    <span class="text-gray-400">s/d {{ $i->tanggal_selesai->format('d M Y') }}</span>
                                    <span class="block text-gray-400">({{ $i->hari }} hari)</span>
                                </td>
                                <td class="px-6 py-4 font-body-sm text-body-sm text-gray-700">
                                    {{ \Illuminate\Support\Str::limit($i->alasan, 90) }}
                                    @if ($i->lampiran)
                                        <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($i->lampiran) }}"
                                            target="_blank" rel="noopener noreferrer"
                                            class="mt-1 inline-flex items-center gap-1 text-blue-600 hover:underline">
                                            <x-icon name="attach_file" class="h-3.5 w-3.5" /> Lampiran
                                        </a>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <x-ui.badge
                                        :label="match($i->status) { 'pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', default => ucfirst($i->status) }"
                                        :tone="match($i->status) { 'approved' => 'emerald', 'rejected' => 'rose', default => 'amber' }" />
                                    @if ($i->alasan_keputusan)
                                        <p class="mt-1 max-w-[200px] font-label-sm text-label-sm text-gray-500">
                                            {{ $i->alasan_keputusan }}
                                        </p>
                                    @endif
                                    @if ($i->processor)
                                        <p class="mt-1 font-label-sm text-label-sm text-gray-400">
                                            {{ $i->processor->name }} · {{ $i->diproses_at?->format('d M H:i') }}
                                        </p>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if ($i->status === 'pending')
                                        <form method="POST" action="{{ route('mahasiswa.izin.destroy', $i) }}"
                                            x-data="confirmForm({ title: 'Batalkan pengajuan izin?', text: 'Pengajuan izin yang masih diproses akan dibatalkan dan tidak dapat dilanjutkan.', confirmText: 'Ya, batalkan', cancelText: 'Kembali', danger: true })"
                                            x-on:submit="submit($event)">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center gap-1 rounded-lg border border-rose-200 px-3 py-1.5 font-body-sm text-body-sm font-semibold text-rose-600 hover:bg-rose-50">
                                                <x-icon name="close" class="h-3.5 w-3.5" /> Batalkan
                                            </button>
                                        </form>
                                    @else
                                        <span class="font-body-sm text-body-sm text-gray-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <x-ui.pagination :paginator="$izin" />
</x-app-layout>