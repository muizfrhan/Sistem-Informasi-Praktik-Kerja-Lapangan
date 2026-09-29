@props([
    'label',
    'name',
    'type' => 'text',
    'value' => null,
    'required' => false,
    'hint' => null,
    'error' => null,
    'options' => null,   // untuk select
    'placeholder' => null,
    'rows' => 4,
    'min' => null,
    'max' => null,
    'step' => null,
])

@php $id = 'field-' . $name; @endphp

<div {{ $attributes->merge(['class' => 'space-y-1.5']) }}>
    <label for="{{ $id }}" class="block font-body-sm text-body-sm font-semibold text-gray-700">
        {{ $label }}
        @if ($required)
            <span class="text-rose-500" aria-hidden="true">*</span>
        @endif
    </label>

    @if ($type === 'select')
        <select id="{{ $id }}" name="{{ $name }}"
            @if ($required) required @endif
            @class([
                'w-full rounded-xl border bg-white px-3 py-2.5 text-sm text-gray-900 transition-colors focus:ring-blue-500',
                'border-rose-400 focus:border-rose-500' => $error,
                'border-gray-200 focus:border-blue-500' => ! $error,
            ])
            @if (! $required)
                <option value="">-- Pilih --</option>
            @endif
            @foreach ($options as $key => $label)
                <option value="{{ $key }}" @selected((string) $value === (string) $key)>{{ $label }}</option>
            @endforeach
        </select>
    @elseif ($type === 'textarea')
        <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}"
            @if ($required) required @endif
            @if ($placeholder) placeholder="{{ $placeholder }}" @endif
            @class([
                'w-full rounded-xl border bg-white px-3 py-2.5 text-sm text-gray-900 transition-colors focus:ring-blue-500',
                'border-rose-400 focus:border-rose-500' => $error,
                'border-gray-200 focus:border-blue-500' => ! $error,
            ])>{{ old($name, $value) }}</textarea>
    @elseif ($type === 'checkbox')
        <label for="{{ $id }}"
            class="flex cursor-pointer items-center gap-2 rounded-xl border border-gray-200 bg-white px-3 py-2.5">
            <input id="{{ $id }}" type="checkbox" name="{{ $name }}" value="1"
                @checked(old($name, $value)) @if($required) required @endif
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
            <span class="font-body-sm text-body-sm text-gray-700">{{ $hint }}</span>
        </label>
    @else
        <input id="{{ $id }}" type="{{ $type }}" name="{{ $name }}"
            value="{{ old($name, $value) }}"
            @if ($required) required @endif
            @if ($placeholder) placeholder="{{ $placeholder }}" @endif
            @if ($min !== null) min="{{ $min }}" @endif
            @if ($max !== null) max="{{ $max }}" @endif
            @if ($step !== null) step="{{ $step }}" @endif
            @class([
                'w-full rounded-xl border bg-white px-3 py-2.5 text-sm text-gray-900 transition-colors focus:ring-blue-500',
                'border-rose-400 focus:border-rose-500' => $error,
                'border-gray-200 focus:border-blue-500' => ! $error,
            ])>
    @endif

    @if ($error)
        <p class="font-body-sm text-body-sm text-rose-600">{{ $error }}</p>
    @elseif ($hint && $type !== 'checkbox')
        <p class="font-label-sm text-label-sm text-gray-500">{{ $hint }}</p>
    @endif
</div>