@props([
    'search' => null,
    'placeholder' => 'Cari...',
    'action' => null,
])

<div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
    @if ($search !== null || $action)
        <form method="GET" action="{{ $action }}" class="flex flex-wrap items-center gap-2">
            @if ($search !== null)
                <div class="relative flex-1 min-w-[200px]">
                    <label for="search-input" class="sr-only">{{ $placeholder }}</label>
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                    </svg>
                    <input id="search-input" type="search" name="search" value="{{ $search }}"
                        placeholder="{{ $placeholder }}"
                        class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-9 pr-3 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring-blue-500">
                </div>
            @endif
            {{ $filters ?? '' }}
            <button type="submit"
                class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-blue-700">
                <x-icon name="search" class="h-4 w-4" />
                <span class="sr-only sm:not-sr-only">Cari</span>
            </button>
            @if (request()->filled('search') || request()->filled('status') || request()->filled('perPage'))
                <a href="{{ url()->current() }}"
                    class="font-body-sm text-body-sm text-gray-500 hover:text-gray-800">
                    Reset
                </a>
            @endif
        </form>
    @endif

    <div class="flex flex-wrap items-center gap-2">
        {{ $tools ?? '' }}
    </div>
</div>