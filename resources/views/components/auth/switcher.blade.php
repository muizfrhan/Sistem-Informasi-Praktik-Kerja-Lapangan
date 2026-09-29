@props([
    'current' => null,   // 'login' | 'register' | null
])

{{-- Pemindah Masuk / Daftar — dipakai bersama oleh halaman autentikasi. --}}
<nav class="inline-flex p-1 w-full rounded-lg bg-surface-container" aria-label="Navigasi autentikasi">
    @foreach ([
        'login' => ['label' => 'Masuk', 'url' => route('login')],
        'register' => ['label' => 'Daftar', 'url' => route('register')],
    ] as $key => $tab)
        @php $isActive = $current === $key; @endphp
        <a href="{{ $tab['url'] }}" @if ($isActive) aria-current="page" @endif
            @class([
                'flex-1 h-9 inline-flex items-center justify-center rounded-md font-title-sm transition-colors',
                'bg-white text-primary shadow-level-1' => $isActive,
                'text-on-surface-variant hover:text-on-surface' => ! $isActive,
            ])>{{ $tab['label'] }}</a>
    @endforeach
</nav>
