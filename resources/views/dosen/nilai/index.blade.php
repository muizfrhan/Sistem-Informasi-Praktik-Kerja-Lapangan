<x-app-layout>
    {{-- Header Section --}}
    <div
        class="mb-8 bg-white border border-gray-200 rounded-2xl p-6">
        <div class="flex items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-800 mb-2">Input Nilai PKL</h1>
                <p class="text-gray-900 text-sm">Berikan penilaian Praktik Kerja Lapangan mahasiswa
                    bimbingan Anda.</p>
            </div>
        </div>
        <div class="mt-4">
            <div class="w-32 h-1 bg-gradient-to-r from-blue-500 to-cyan-400 rounded-full"></div>
        </div>
    </div>

    {{-- Pesan sukses --}}

    {{-- Main Content --}}
    <div class="space-y-4">
        @forelse ($mahasiswa as $mhs)
        <div
            class="group relative overflow-hidden rounded-xl bg-white border border-gray-200">
            {{-- Student Info Header --}}
            <div class="p-6 pb-4">
                <div class="flex items-start justify-between">
                    <div class="space-y-1">
                        <h3 class="text-xl font-semibold text-slate-800">
                            {{ $mhs->nama }}
                        </h3>
                        <div class="flex items-center space-x-4 text-sm">
                            <span
                                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700 border border-blue-200">
                                {{ $mhs->nim }}
                            </span>
                            <span class="text-slate-500">
                                Bimbingan: <span
                                    class="text-slate-700 font-medium">{{ $mhs->bimbingan_disetujui }}</span>
                            </span>
                        </div>
                    </div>

                    {{-- Status Indicator --}}
                    @if ($mhs->nilaiPkl)
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-emerald-500 rounded-full animate-pulse"></div>
                        <span class="text-xs font-medium text-emerald-700">Sudah Dinilai</span>
                    </div>
                    @else
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-amber-400 rounded-full animate-pulse"></div>
                        <span class="text-xs font-medium text-amber-700">Menunggu Nilai</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Content Section --}}
            <div class="px-6 pb-6">
                @if ($mhs->nilaiPkl)
                {{-- Display Existing Grade --}}
                <div
                    class="relative overflow-hidden rounded-lg bg-gradient-to-r from-emerald-100/80 to-green-100/80 border border-emerald-200 p-4">
                    <div
                        class="absolute inset-0 bg-gradient-to-r from-emerald-100/30 to-transparent">
                    </div>
                    <div class="relative flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div
                                class="flex-shrink-0 w-10 h-10 bg-emerald-200 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-emerald-500" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-emerald-700 font-semibold text-lg">Nilai PKL</p>
                                <p class="text-emerald-600 text-sm">Telah diinput dan tersimpan
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-3xl font-bold text-emerald-700">
                                {{ $mhs->nilaiPkl->nilai }}
                            </div>
                            <div class="text-emerald-500 text-sm">/ 100</div>
                        </div>
                    </div>
                </div>
                @else
                {{-- Grade Input Form --}}
                <form method="POST" action="{{ route('dosen.nilai.store') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="mahasiswa_id" value="{{ $mhs->id }}">

                    <div class="space-y-3">
                        <label class="block text-sm font-medium text-slate-700">
                            Input Nilai PKL <span class="text-red-600">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" name="nilai" step="0.01" min="0" max="100"
                                class="w-full px-4 py-3 bg-white backdrop-blur-sm border border-gray-200 rounded-lg text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400/50 transition-all duration-200 hover:bg-slate-100"
                                placeholder="Masukkan nilai (0-100)" required>
                        </div>
                            <p class="text-xs text-slate-500">Masukkan nilai dalam rentang 0-100 dengan
                            maksimal 2 desimal
                        </p>
                    </div>

                    <div class="pt-2">
                        <button type="submit"
                            class="w-full bg-gradient-to-r from-emerald-400 to-green-500 hover:from-emerald-500 hover:to-green-600 text-white font-semibold py-3 px-6 rounded-lg">
                            <span class="flex items-center justify-center space-x-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Simpan Nilai</span>
                            </span>
                        </button>
                    </div>
                </form>
                @endif
            </div>
        </div>
        @empty
        {{-- Empty State --}}
        <div
            class="text-center py-16 bg-white border border-gray-200 rounded-2xl p-6 shadow-lg">
            <div class="max-w-md mx-auto">
                <div
                    class="w-20 h-20 bg-slate-200/60 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87M16 7a4 4 0 11-8 0 4 4 0 018 0zM21 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-slate-700 mb-2">Tidak Ada Mahasiswa</h3>
                        <p class="text-slate-500 text-sm leading-relaxed">
                    Saat ini tidak ada mahasiswa yang memenuhi syarat untuk dinilai PKL.
                    Mahasiswa akan muncul di sini setelah bimbingan mereka disetujui sebanyak 4 kali.
                </p>
            </div>
        </div>
        @endforelse
    </div>
</x-app-layout>