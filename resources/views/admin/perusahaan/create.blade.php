<x-app-layout>
    {{-- Main Form Container --}}
    <div
        class="bg-white border border-gray-200 rounded-2xl shadow-none overflow-hidden">
        {{-- Form Header --}}
        <div
            class="bg-gradient-to-r from-blue-100 to-blue-50 border-b border-gray-200 px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-semibold text-gray-900">Form Tambah Perusahaan</h3>
                    <p class="text-gray-500 text-sm mt-1">Lengkapi semua field yang diperlukan</p>
                </div>
                <div class="flex items-center space-x-2 text-gray-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-xs">* Field wajib diisi</span>
                </div>
            </div>
        </div>
        {{-- Form Body --}}
        <div class="p-8">
            <form method="POST" action="{{ route('admin.perusahaan.store') }}" class="space-y-8">
                @csrf
                {{-- Informasi Perusahaan Section --}}
                <div class="space-y-6">
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <h4 class="text-lg font-medium text-gray-900">Data Perusahaan</h4>
                    </div>
                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-2 col-span-2">
                            <x-input-label for="nama" :value="'Nama Perusahaan *'"
                                class="text-gray-700 font-medium" />
                            <div class="relative">
                                <x-text-input name="nama" id="nama" type="text"
                                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-gray-900 placeholder-gray-400 focus:border-indigo-400/50 focus:bg-white/10 transition-all duration-200"
                                    placeholder="Masukkan nama perusahaan" required autofocus />
                            </div>
                            <x-input-error :messages="$errors->get('nama')"
                                class="text-red-500 text-sm" />
                        </div>
                        <div class="space-y-2 col-span-2">
                            <x-input-label for="alamat" :value="'Alamat *'"
                                class="text-gray-700 font-medium" />
                            <div class="relative">
                                <x-text-input name="alamat" id="alamat" type="text"
                                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-gray-900 placeholder-gray-400 focus:border-indigo-400/50 focus:bg-white/10 transition-all duration-200"
                                    placeholder="Masukkan alamat perusahaan" required />
                            </div>
                            <x-input-error :messages="$errors->get('alamat')"
                                class="text-red-500 text-sm" />
                        </div>
                        <div class="space-y-2 col-span-2">
                            <x-input-label for="no_hp" :value="'No. HP *'"
                                class="text-gray-700 font-medium" />
                            <div class="relative">
                                <x-text-input name="no_hp" id="no_hp" type="text"
                                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-gray-900 placeholder-gray-400 focus:border-indigo-400/50 focus:bg-white/10 transition-all duration-200"
                                    placeholder="Masukkan nomor HP perusahaan" required />
                            </div>
                            <x-input-error :messages="$errors->get('no_hp')"
                                class="text-red-500 text-sm" />
                        </div>
                    </div>
                </div>
                {{-- Form Actions --}}
                <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                    <div class="flex items-center space-x-2 text-gray-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-sm">Pastikan semua data sudah benar sebelum menambahkan perusahaan</span>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('admin.perusahaan.index') }}"
                            class="px-6 py-3 bg-gray-100 hover:bg-gray-200 border border-gray-200 rounded-xl text-gray-700 hover:text-gray-900 transition-all duration-200 font-medium">Batal</a>
                        <button type="submit"
                            class="px-8 py-3 bg-gradient-to-r from-emerald-400 to-green-500 hover:from-emerald-500 hover:to-green-600 rounded-xl text-white font-semibold shadow-lg transition-all duration-200 flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>Tambah Perusahaan</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>