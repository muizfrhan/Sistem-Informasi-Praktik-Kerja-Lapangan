@php
    $badge = fn (string $status) => match ($status) {
        'aktif' => 'badge-success',
        'nonaktif' => 'badge-danger',
        default => 'badge-warning',
    };
    $labelStatus = fn (string $status) => match ($status) {
        'aktif' => 'Aktif',
        'nonaktif' => 'Nonaktif',
        default => 'Menunggu',
    };
@endphp

<x-app-layout>
    {{-- ================= Page header ================= --}}
    <x-ui.page-header title="Manajemen Akun Pengguna"
        subtitle="Setujui pendaftaran mandiri dan kelola status akses seluruh akun SIPKL." />

    {{-- ================= Notifikasi ================= --}}
    @php
        $notifikasi = [
            'success' => 'bg-tertiary-container text-on-tertiary-container border-[#BBF7D0] check_circle',
            'error' => 'bg-error-container text-on-error-container border-[#FECACA] error',
        ];
    @endphp
    @foreach ($notifikasi as $key => $kelas)
        @if (session($key))
            <div
                class="mb-4flexitems-centergap-2.5px-3.5py-3rounded-lgborder{{explode('',$kelas)[0]}}{{explode('',$kelas)[1]}}text-body-sm">
                <span class="material-symbols-outlined text-[18px] shrink-0" aria-hidden="true">
                    {{ explode(' ', $kelas)[2] }}
                </span>
                {{ session($key) }}
            </div>
        @endif
    @endforeach

    {{-- ================= Filter toolbar (compact: padding 1rem) ================= --}}
    <form method="GET" action="{{ route('admin.pengguna.index') }}"
        class="card card-pad mb-4 flex flex-col sm:flex-row sm:items-center gap-3">
        <div class="relative flex-1">
            <span
                class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[18px] pointer-events-none"
                aria-hidden="true">search</span>
            <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama, email, atau NIM..."
                class="field pl-10" autocomplete="off">
        </div>

        <div class="grid grid-cols-2 sm:flex gap-3">
            <select name="status" class="field sm:w-44">
                <option value="">Semua status</option>
                <option value="ditunda" @selected($status === 'ditunda')>Menunggu persetujuan</option>
                <option value="aktif" @selected($status === 'aktif')>Aktif</option>
                <option value="nonaktif" @selected($status === 'nonaktif')>Nonaktif</option>
            </select>
            <button type="submit" class="btn-primary btn-md">Terapkan</button>
        </div>

        @if ($q || $status)
            <a href="{{ route('admin.pengguna.index') }}"
                class="text-body-sm text-outline hover:text-primary transition-colors sm:ml-auto">Reset</a>
        @endif
    </form>

    {{-- Lencana antrean persetujuan --}}
    @if ($jumlahDitunda > 0)
        <a href="{{ route('admin.pengguna.index', ['status' => 'ditunda']) }}"
            class="inline-flex items-center gap-2 mb-4 badge-warning hover:brightness-95 transition">
            <span class="material-symbols-outlined text-[15px]" aria-hidden="true">pending_actions</span>
            {{ $jumlahDitunda }} akun menunggu persetujuan
        </a>
    @endif

    {{-- ================= Desktop: tabel ================= --}}
    <div class="hidden lg:block card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-body-md">
                <thead class="bg-surface-container">
                    <tr>
                        @foreach (['Pengguna', 'Identitas', 'Peran', 'Status', 'Terakhir masuk', 'Aksi'] as $kolom)
                            <th scope="col"
                                class="px-5 py-3 text-left font-label-sm text-[11px] uppercase tracking-wider text-on-surface-variant {{ $kolom === 'Aksi' ? 'text-right' : '' }}">
                                {{ $kolom }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container">
                    @forelse ($users as $user)
                        @php $mhs = $user->mahasiswa; @endphp
                        <tr class="hover:bg-primary-fixed/40 transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="w-9 h-9 shrink-0 rounded-md bg-primary text-white text-sm font-semibold flex items-center justify-center">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </span>
                                    <div class="min-w-0">
                                        <p class="font-title-sm text-on-surface truncate">{{ $user->name }}</p>
                                        <p class="text-body-sm text-[12px] text-outline truncate">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                @if ($mhs)
                                    <span class="font-mono text-[13px] text-on-surface">{{ $mhs->nim }}</span>
                                    <span class="block text-body-sm text-[12px] text-outline">
                                        {{ $mhs->kelas }} &middot; Sem {{ $mhs->semester }}
                                    </span>
                                @elseif ($user->dosen)
                                    <span class="font-mono text-[13px] text-on-surface">{{ $user->dosen->nip }}</span>
                                @else
                                    <span class="text-outline">&mdash;</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-on-surface-variant">{{ $user->labelRole() }}</td>
                            <td class="px-5 py-3.5">
                                <span class="{{ $badge($user->status) }}">{{ $labelStatus($user->status) }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-body-sm text-outline whitespace-nowrap">
                                {{ $user->last_login_at?->translatedFormat('d M Y H:i') ?? 'Belum pernah' }}
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-end gap-2">
                                    @if ($user->status !== 'aktif')
                                        <x-ui.confirm-action form="aktif-{{ $user->id }}" title="Aktifkan akun?"
                                            message="Akun {{ $user->email }} akan bisa mengakses modul sesuai perannya."
                                            confirm-text="Ya, aktifkan" :danger="false">
                                            <span class="btn-success btn-sm">Setujui</span>
                                        </x-ui.confirm-action>
                                    @endif

                                    @if ($user->status === 'aktif' && ! $user->is(auth()->user()))
                                        <x-ui.confirm-action form="nonaktif-{{ $user->id }}"
                                            title="Nonaktifkan akun?"
                                            message="Akun {{ $user->email }} langsung kehilangan akses ke seluruh modul."
                                            confirm-text="Ya, nonaktifkan">
                                            <span class="btn-danger btn-sm">Nonaktifkan</span>
                                        </x-ui.confirm-action>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        <form id="aktif-{{ $user->id }}" method="POST" class="hidden"
                            action="{{ route('admin.pengguna.status', $user) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="aktif">
                        </form>
                        <form id="nonaktif-{{ $user->id }}" method="POST" class="hidden"
                            action="{{ route('admin.pengguna.status', $user) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="nonaktif">
                        </form>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-ui.empty-state title="Belum ada akun"
                                    message="Tidak ada pengguna yang cocok dengan filter saat ini." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ================= Mobile / tablet: kartu ================= --}}
    <div class="lg:hidden space-y-3">
        @forelse ($users as $user)
            @php $mhs = $user->mahasiswa; @endphp
            <div class="card card-pad">
                <div class="flex items-start gap-3">
                    <span
                        class="w-10 h-10 shrink-0 rounded-md bg-primary text-white text-sm font-semibold flex items-center justify-center">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="font-title-sm text-on-surface break-words">{{ $user->name }}</p>
                        <p class="text-body-sm text-[12px] text-outline break-all">{{ $user->email }}</p>
                    </div>
                    <span class="{{ $badge($user->status) }} shrink-0">{{ $labelStatus($user->status) }}</span>
                </div>

                <dl class="mt-3.5 grid grid-cols-2 gap-x-4 gap-y-2.5">
                    <div class="min-w-0">
                        <dt class="font-label-sm text-[11px] uppercase text-outline">Identitas</dt>
                        <dd class="text-body-sm font-mono text-on-surface truncate">
                            {{ $mhs?->nim ?? $user->dosen?->nip ?? '—' }}
                        </dd>
                    </div>
                    <div class="min-w-0">
                        <dt class="font-label-sm text-[11px] uppercase text-outline">Peran</dt>
                        <dd class="text-body-sm text-on-surface truncate">{{ $user->labelRole() }}</dd>
                    </div>
                    @if ($mhs)
                        <div class="min-w-0">
                            <dt class="font-label-sm text-[11px] uppercase text-outline">Kelas</dt>
                            <dd class="text-body-sm text-on-surface truncate">{{ $mhs->kelas }}</dd>
                        </div>
                        <div class="min-w-0">
                            <dt class="font-label-sm text-[11px] uppercase text-outline">Semester</dt>
                            <dd class="text-body-sm text-on-surface truncate">{{ $mhs->semester }}</dd>
                        </div>
                    @endif
                </dl>

                <div class="mt-3.5 pt-3 border-t border-surface-container flex flex-wrap gap-2">
                    @if ($user->status !== 'aktif')
                        <x-ui.confirm-action form="m-aktif-{{ $user->id }}" title="Aktifkan akun?"
                            message="Akun {{ $user->email }} akan bisa mengakses modul sesuai perannya."
                            confirm-text="Ya, aktifkan" :danger="false">
                            <span class="btn-success btn-sm flex-1">Setujui</span>
                        </x-ui.confirm-action>
                    @endif
                    @if ($user->status === 'aktif' && ! $user->is(auth()->user()))
                        <x-ui.confirm-action form="m-nonaktif-{{ $user->id }}" title="Nonaktifkan akun?"
                            message="Akun {{ $user->email }} langsung kehilangan akses ke seluruh modul."
                            confirm-text="Ya, nonaktifkan">
                            <span class="btn-danger btn-sm flex-1">Nonaktifkan</span>
                        </x-ui.confirm-action>
                    @endif
                </div>
            </div>

            <form id="m-aktif-{{ $user->id }}" method="POST" class="hidden"
                action="{{ route('admin.pengguna.status', $user) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="aktif">
            </form>
            <form id="m-nonaktif-{{ $user->id }}" method="POST" class="hidden"
                action="{{ route('admin.pengguna.status', $user) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="nonaktif">
            </form>
        @empty
            <div class="card">
                <x-ui.empty-state title="Belum ada akun" message="Tidak ada pengguna yang cocok dengan filter saat ini." />
            </div>
        @endforelse
    </div>

    <x-ui.pagination :paginator="$users" class="mt-6" />
</x-app-layout>
