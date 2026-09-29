<x-app-layout>
    {{-- Header Section --}}
    <div
        class="mb-8 bg-white border border-gray-200 rounded-2xl p-6 shadow-none">
        <div class="flex items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Verifikasi Pendaftaran PKL</h1>
                <p class="text-gray-900 text-sm">Tinjau dan ubah status pendaftaran berdasarkan hasil
                    pemeriksaan
                </p>
            </div>
        </div>
        <div class="mt-4">
            <div class="w-32 h-1 bg-gradient-to-r from-blue-500 to-cyan-400 rounded-full"></div>
        </div>
    </div>

    {{-- Pesan sukses --}}

    {{-- Area Tombol & Search --}}
    <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
        <form method="GET" action="{{ route('admin.pendaftaran.index') }}" class="flex items-center gap-3 h-[48px]">
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Cari Nama, NIM, Perusahaan, Bidang, Status..."
                class="h-[48px] px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-400 focus:outline-none text-sm w-96 text-white transition-all duration-200 shadow-sm"
                autocomplete="off">
            <button type="submit"
                class="h-[48px] w-[48px] flex items-center justify-center bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-sm font-semibold transition-all duration-200"
                aria-label="Cari">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" />
                </svg>
            </button>
            @if(request('search'))
            <a href="{{ route('admin.pendaftaran.index') }}"
                class="h-[48px] flex items-center ml-2 text-sm text-gray-500 underline">Reset</a>
            @endif
        </form>
    </div>
    {{-- End Area Tombol & Search --}}

    {{-- Tabel --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr
                        class="border-b border-gray-200 bg-gray-50">
                        <th
                            class="px-6 py-4 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">
                            No.</th>
                        <th
                            class="px-6 py-4 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">
                            Nama Mahasiswa</th>
                        <th
                            class="px-6 py-4 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">
                            Perusahaan</th>
                        <th
                            class="px-6 py-4 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">
                            Bidang PKL</th>
                        <th
                            class="px-6 py-4 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">
                            Status</th>
                        <th
                            class="px-6 py-4 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">
                            Verifikasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($pendaftaran as $index => $item)
                    <tr class="hover:bg-gray-100 transition-colors duration-200">
                        <td class="px-6 py-4 text-sm text-gray-700">
                            {{ method_exists($pendaftaran, 'firstItem') ? $pendaftaran->firstItem() + $index : $index + 1 }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900 font-medium">
                            {{ $item->mahasiswa->nama }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $item->perusahaan->nama }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $item->bidang_pkl }}</td>
                        <td class="px-6 py-4 text-sm">
                            @php
                            $statusStyles = [
                            'menunggu' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                            'diterima' => 'bg-green-100 text-green-700 border-green-200',
                            'ditolak' => 'bg-red-100 text-red-700 border-red-200',
                            ];
                            @endphp
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border {{ $statusStyles[$item->status] ?? 'bg-slate-100 text-slate-700 border-slate-200' }}">
                                @if($item->status === 'menunggu')
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                @elseif($item->status === 'diterima')
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                @elseif($item->status === 'ditolak')
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                @endif
                                {{ ucfirst($item->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <form action="{{ route('admin.pendaftaran.verifikasi', $item->id) }}" method="POST"
                                class="flex items-center gap-3">
                                @csrf
                                <select name="status"
                                    class="bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-200 min-w-[120px]">
                                    <option value=""
                                        class="bg-gray-50 text-gray-700"> Pilih
                                        Status </option>
                                    <option value="diterima"
                                        class="bg-gray-50 text-gray-700">Diterima
                                    </option>
                                    <option value="ditolak"
                                        class="bg-gray-50 text-gray-700">Ditolak
                                    </option>
                                </select>
                                <button type="submit"
                                    class="bg-blue-500/90 hover:bg-blue-600 text-white text-sm rounded-lg transition-all duration-200 border border-blue-200 hover:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-500/50 flex items-center justify-center h-[42px] min-w-[120px] px-3">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Simpan
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center px-6 py-12">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 text-gray-400 mb-4" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                <p class="text-gray-500 text-lg font-medium">Belum ada pendaftaran
                                    PKL</p>
                                <p class="text-gray-400 text-sm mt-1">Pendaftaran yang disubmit
                                    mahasiswa akan muncul
                                    di sini
                                </p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if(method_exists($pendaftaran, 'hasPages') && $pendaftaran->hasPages())
    <div class="mt-6 flex items-center justify-between">
        {{-- Info pagination --}}
        <div class="flex items-center text-sm text-gray-500">
            <span>
                Menampilkan {{ $pendaftaran->firstItem() ?? 0 }} - {{ $pendaftaran->lastItem() ?? 0 }}
                dari {{ $pendaftaran->total() }} pendaftaran
            </span>
        </div>
        {{-- Navigation pagination --}}
        <nav class="flex items-center space-x-2">
            {{-- Previous Button --}}
            @if ($pendaftaran->onFirstPage())
            <span
                class="px-3 py-2 text-sm text-gray-400 bg-gray-100 border border-gray-200 rounded-lg cursor-not-allowed">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </span>
            @else
            <a href="{{ $pendaftaran->previousPageUrl() }}"
                class="px-3 py-2 text-sm text-gray-700 bg-gray-100 hover:bg-gray-200 border border-gray-200 hover:border-gray-300 rounded-lg transition-all duration-200">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            @endif
            {{-- Page Numbers --}}
            @foreach ($pendaftaran->getUrlRange(max(1, $pendaftaran->currentPage() - 2), min($pendaftaran->lastPage(),
            $pendaftaran->currentPage() + 2)) as $page => $url)
            @if ($page == $pendaftaran->currentPage())
            <span
                class="px-3 py-2 text-sm text-white bg-blue-600/80 border border-blue-500/50 rounded-lg font-medium">
                {{ $page }}
            </span>
            @else
            <a href="{{ $url }}"
                class="px-3 py-2 text-sm text-gray-700 bg-gray-100 hover:bg-gray-200 border border-gray-200 hover:border-gray-300 rounded-lg transition-all duration-200 hover:text-gray-900">
                {{ $page }}
            </a>
            @endif
            @endforeach
            {{-- Show dots jika ada banyak halamannya --}}
            @if($pendaftaran->currentPage() < $pendaftaran->lastPage() - 2)
                <span class="px-2 py-2 text-gray-400">...</span>
                <a href="{{ $pendaftaran->url($pendaftaran->lastPage()) }}"
                    class="px-3 py-2 text-sm text-gray-700 bg-gray-100 hover:bg-gray-200 border border-gray-200 hover:border-gray-300 rounded-lg transition-all duration-200 hover:text-gray-900">
                    {{ $pendaftaran->lastPage() }}
                </a>
                @endif
                {{-- Next Button --}}
                @if ($pendaftaran->hasMorePages())
                <a href="{{ $pendaftaran->nextPageUrl() }}"
                    class="px-3 py-2 text-sm text-gray-700 bg-gray-100 hover:bg-gray-200 border border-gray-200 hover:border-gray-300 rounded-lg transition-all duration-200">
                    <svg class="w-4 h-4 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
                @else
                <span
                    class="px-3 py-2 text-sm text-gray-400 bg-gray-100 border border-gray-200 rounded-lg cursor-not-allowed">
                    <svg class="w-4 h-4 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </span>
                @endif
        </nav>
    </div>
    @endif
</x-app-layout>