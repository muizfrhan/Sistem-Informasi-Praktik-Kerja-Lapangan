@props([
    'label' => null,
    'name',
    'type' => 'text',
    'value' => null,
    'icon' => null,
    'placeholder' => null,
    'hint' => null,
    'autocomplete' => null,
    'maxlength' => null,
    'inputmode' => null,
    'required' => false,
    'suffix' => null,
    'link' => null,
    'linkLabel' => null,
    'padded' => false,
    'options' => null,
    'emptyLabel' => null,
])

@php
    $id = 'field-' . $name;
    $messages = $errors->get($name);
    $hasError = ! empty($messages);
    $isSelect = $type === 'select';
@endphp

{{-- Atribut Alpine (x-model, x-on:*, x-bind:*) diteruskan ke elemen input. --}}
<div class="space-y-1.5">
    @if ($label || $suffix || $link)
        <div class="flex items-center justify-between gap-3">
            @if ($label)
                <label for="{{ $id }}" class="field-label">
                    {{ $label }}
                    @if ($required)
                        <span class="text-error align-middle" aria-hidden="true">*</span>
                    @endif
                </label>
            @else
                <span></span>
            @endif
            @if ($link)
                <a href="{{ $link }}" class="font-label-md text-primary hover:underline shrink-0">
                    {{ $linkLabel ?? 'Lupa?' }}
                </a>
            @elseif ($suffix)
                <span class="font-label-sm text-[11px] text-outline shrink-0">{{ $suffix }}</span>
            @endif
        </div>
    @endif

    <div class="relative">
        @if ($icon)
            <span
                class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[18px] pointer-events-none z-10"
                aria-hidden="true">{{ $icon }}</span>
        @endif

        @if ($isSelect)
            {{-- pr-10 wajib: ruang untuk panah dropdown kustom, supaya opsi
                 panjang tidak menimpanya. Padding kiri pl-10 hanya bila ada
                 ikon, supaya ikon & teks select sejajar dengan input biasa. --}}
            <select name="{{ $name }}" id="{{ $id }}"
                @if ($required) required @endif
                @if ($hasError) aria-invalid="true" @endif
                @class([
                    'field',
                    'pr-10',
                    $icon ? 'pl-10' : '',
                    'field-error' => $hasError,
                ]) {{ $attributes }}>
                @if ($emptyLabel !== null)
                    <option value="">{{ $emptyLabel }}</option>
                @endif
                @foreach ($options ?? [] as $optionValue => $optionLabel)
                    <option value="{{ $optionValue }}"
                        @selected((string) old($name, $value) === (string) $optionValue)>
                        {{ $optionLabel }}
                    </option>
                @endforeach
            </select>
        @else
            <input type="{{ $type }}" name="{{ $name }}" id="{{ $id }}"
                @if ($required) required @endif
                @if ($placeholder) placeholder="{{ $placeholder }}" @endif
                @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
                @if ($maxlength) maxlength="{{ $maxlength }}" @endif
                @if ($inputmode) inputmode="{{ $inputmode }}" @endif
                @if ($hasError) aria-invalid="true" @endif
                value="{{ old($name, $value) }}"
                @class([
                    'field',
                    $icon ? 'pl-10' : '',
                    $padded ? 'pr-11' : 'pr-3.5',
                    'field-error' => $hasError,
                ])
                {{ $attributes }}>
        @endif

        {{ $slot }}
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
