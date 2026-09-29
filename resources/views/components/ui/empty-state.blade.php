@props([
    'title' => 'Belum ada data',
    'message' => null,
    'icon' => 'inbox',
])

<div class="flex flex-col items-center justify-center gap-3 px-6 py-16 text-center">
    <div
        class="flex h-20 w-20 items-center justify-center rounded-full bg-gray-100">
        <x-icon :name="$icon" class="text-4xl text-gray-400" />
    </div>
    <div>
        <p class="font-title-md text-title-md font-bold text-gray-900">{{ $title }}</p>
        @if ($message)
            <p class="mt-1 font-body-sm text-body-sm text-gray-500">{{ $message }}</p>
        @endif
    </div>
    @isset($action)
        <div class="mt-2">{{ $action }}</div>
    @endisset
</div>