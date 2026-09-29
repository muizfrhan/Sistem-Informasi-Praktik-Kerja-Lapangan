<div {{ $attributes->merge(['class' => 'flex flex-col-reverse gap-3 border-t border-gray-200 pt-5 sm:flex-row sm:items-center sm:justify-end']) }}>
    @isset($left)
        <div class="sm:mr-auto">{{ $left }}</div>
    @endisset

    <a href="{{ $cancel }}"
        class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-5 py-2.5 font-title-sm text-title-sm font-semibold text-gray-700 transition-colors hover:bg-gray-50">
        {{ $cancelLabel ?? 'Batal' }}
    </a>

    <button type="submit"
        x-data="{}" x-bind:disabled="$el.dataset.submitting === '1'"
        onsubmit="this.dataset.submitting='1'; this.querySelector('[data-btn]')?.setAttribute('aria-busy','true'); this.querySelector('[data-btn]')?.classList.add('opacity-70','cursor-wait')"
        class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-blue-500 to-cyan-500 px-5 py-2.5 font-title-sm text-title-sm font-semibold text-white shadow-sm transition-all hover:shadow-lg disabled:opacity-70">
        <span data-btn class="inline-flex items-center gap-2">
            <x-icon name="check" class="h-4 w-4" />
            {{ $submitLabel ?? 'Simpan' }}
        </span>
    </button>
</div>