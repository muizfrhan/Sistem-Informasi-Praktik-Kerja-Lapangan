@props([
    'label' => null,
    'name',
    'icon' => null,
    'options' => [],
    'placeholder' => null,
    'hint' => null,
    'maxlength' => null,
    'required' => false,
])

@php
    /*
     * Combobox creatable: satu input text yang bisa memilih opsi yang ada
     * maupun mengetik nilai baru. Opsi = nilai yang sudah pernah dipakai,
     * bukan daftar tertutup — ketikan bebas apa pun tetap dianggap sah.
     */
    $id = 'field-' . $name;
    $messages = $errors->get($name);
    $hasError = ! empty($messages);
    $opsi = collect($options)->map(fn ($value) => (string) $value)->values()->all();
    $nilai = (string) old($name, '');
@endphp

{{-- Atribut Alpine tambahan (x-on:*, x-bind:*) diteruskan ke elemen input. --}}
<div class="space-y-1.5" x-data="combobox(@js($opsi), @js($nilai))" x-on:click.outside="closePanel()">
    @if ($label)
        <label for="{{ $id }}" class="field-label">
            {{ $label }}
            @if ($required)
                <span class="text-error align-middle" aria-hidden="true">*</span>
            @endif
        </label>
    @endif

    <div class="relative">
        @if ($icon)
            <span
                class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[18px] pointer-events-none z-10"
                aria-hidden="true">{{ $icon }}</span>
        @endif

        {{-- Input ini yang dikirim ke server: apa pun yang diketik user. --}}
        <input x-ref="input" type="text" name="{{ $name }}" id="{{ $id }}" role="combobox"
            aria-autocomplete="list" :aria-expanded="open" aria-controls="{{ $id }}-list"
            autocomplete="off" x-model="query"
            x-on:focus="openPanel()" x-on:input="onInput()" x-on:blur="onBlur()"
            x-on:keydown.down.prevent="move(1)" x-on:keydown.up.prevent="move(-1)"
            x-on:keydown.enter.prevent="accept()" x-on:keydown.escape.prevent.stop="closePanel()"
            @if ($required) required @endif
            @if ($maxlength) maxlength="{{ $maxlength }}" @endif
            @if ($placeholder) placeholder="{{ $placeholder }}" @endif
            @if ($hasError) aria-invalid="true" @endif
            @class([
                'field',
                $icon ? 'pl-10' : '',
                'pr-11',
                'field-error' => $hasError,
            ]) {{ $attributes }}>

        {{-- Panah penanda ada daftar saran (hanya saat isian kosong). --}}
        <span x-cloak x-show="value === ''"
            class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-outline text-[18px] pointer-events-none"
            aria-hidden="true">expand_more</span>

        {{-- Hapus nilai terpilih. --}}
        <button type="button" x-cloak x-show="value !== ''" x-on:mousedown.prevent x-on:click="clear()"
            class="absolute inset-y-0 right-0 pr-3 flex items-center text-outline hover:text-on-surface transition-colors"
            aria-label="Hapus isian {{ $label ?? $name }}">
            <span class="material-symbols-outlined text-[18px]" aria-hidden="true">close</span>
        </button>

        {{-- Dropdown menyatu dengan field: lebar sama, warna & border sama. --}}
        <div x-cloak x-show="open" x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
            class="absolute left-0 right-0 top-full z-30 mt-1.5 rounded-lg border border-outline-variant bg-white shadow-level-3 overflow-hidden">
            <ul x-ref="list" id="{{ $id }}-list" role="listbox"
                class="max-h-56 overflow-y-auto py-1 divide-y divide-surface-container-low">

                {{-- Opsi yang sudah ada. --}}
                <template x-for="(option, index) in filtered" :key="index + '-' + option">
                    <li>
                        <button type="button" role="option" :aria-selected="index === highlight"
                            x-on:mousedown.prevent x-on:click="pick(option)"
                            x-on:mouseenter="highlight = index"
                            class="w-full flex items-center gap-2.5 px-3.5 py-2 text-left transition-colors"
                            :class="index === highlight ? 'bg-primary-fixed text-on-surface font-medium' : 'text-on-surface-variant hover:bg-surface-container-low'">
                            <span class="material-symbols-outlined text-[18px] text-outline shrink-0"
                                aria-hidden="true">check</span>
                            <span class="min-w-0 truncate" x-text="option"></span>
                            <span class="material-symbols-outlined text-[16px] text-primary shrink-0 ml-auto"
                                x-show="value.toLowerCase() === option.toLowerCase()" aria-hidden="true">verified</span>
                        </button>
                    </li>
                </template>

                {{-- Baris "+ Gunakan ..." untuk nilai yang diketik sendiri (selalu di urutan
                     terakhir, jadi posisi baris = filtered.length saat navigasi keyboard). --}}
                <li x-show="showCustomRow">
                    <button type="button" role="option" :aria-selected="highlight === filtered.length"
                        x-on:mousedown.prevent x-on:click="pickCustom()"
                        x-on:mouseenter="highlight = filtered.length"
                        class="w-full flex items-center gap-2.5 px-3.5 py-2.5 text-left transition-colors"
                        :class="highlight === filtered.length ? 'bg-primary-fixed' : 'hover:bg-surface-container-low'">
                        <span class="material-symbols-outlined text-[18px] text-primary shrink-0"
                            aria-hidden="true">add</span>
                        <span class="min-w-0">
                            <span class="block font-title-sm text-[13px] text-primary leading-tight">Gunakan
                                &ldquo;<span x-text="customValue"></span>&rdquo;</span>
                            <span class="block font-body-sm text-[11px] text-outline leading-tight">Nilai baru
                                — tetap bisa disimpan</span>
                        </span>
                    </button>
                </li>

                {{-- Daftar kosong tanpa ketikan. --}}
                <li x-show="filtered.length === 0 && ! showCustomRow" class="px-3.5 py-2.5">
                    <p class="font-body-sm text-[12px] text-outline leading-snug">
                        Belum ada data. Ketik untuk menambahkan nilai baru.
                    </p>
                </li>
            </ul>
        </div>
    </div>

    @if ($hasError)
        <ul class="space-y-0.5 field-foot">
            @foreach ($messages as $message)
                <li class="field-error-text flex items-start gap-1.5">
                    <span class="material-symbols-outlined text-[14px] shrink-0 mt-px" aria-hidden="true">error</span>
                    <span>{{ $message }}</span>
                </li>
            @endforeach
        </ul>
    @elseif ($hint)
        <p class="field-hint field-foot">{{ $hint }}</p>
    @endif
</div>
