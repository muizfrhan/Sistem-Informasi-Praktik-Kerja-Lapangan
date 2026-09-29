@props([
    'title',
    'subtitle' => null,
    'back' => null,
])

<div class="mb-6 bg-white border border-gray-200 rounded-2xl p-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-start gap-3">
            @if ($back)
                <a href="{{ $back }}"
                    class="mt-1 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-gray-200 text-gray-500 hover:bg-gray-50 transition-colors"
                    aria-label="Kembali">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
            @endif
            <div>
                <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">{{ $title }}</h1>
                @if ($subtitle)
                    <p class="mt-1 text-sm text-gray-600">{{ $subtitle }}</p>
                @endif
            </div>
        </div>
        @isset($actions)
            <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
        @endisset
    </div>
    <div class="mt-4 h-1 w-32 rounded-full bg-gradient-to-r from-blue-500 to-cyan-400"></div>
</div>