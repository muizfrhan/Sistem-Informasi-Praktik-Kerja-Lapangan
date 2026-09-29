@props(['items' => [], 'empty' => 'Belum ada aktivitas.'])

@if (count($items) === 0)
    <x-ui.empty-state title="{{ $empty }}" icon="history" />
@else
    <ol class="relative ml-3 border-l border-gray-200">
        @foreach ($items as $item)
            @php
                $warna = $item['warna'] ?? 'blue';
                $dot = [
                    'blue' => 'bg-blue-500',
                    'emerald' => 'bg-emerald-500',
                    'amber' => 'bg-amber-500',
                    'rose' => 'bg-rose-500',
                    'violet' => 'bg-violet-500',
                    'cyan' => 'bg-cyan-500',
                ][$warna] ?? 'bg-blue-500';
            @endphp
            <li class="mb-6 ml-6 last:mb-0">
                <span
                    class="absolute -left-[7px] mt-1.5 h-3.5 w-3.5 rounded-full border-2 border-white border-slate-900 {{ $dot }}"></span>
                <p class="font-title-sm text-title-sm font-semibold text-gray-900">{{ $item['judul'] }}</p>
                @if (!empty($item['detail']))
                    <p class="mt-0.5 font-body-sm text-body-sm text-gray-600">{{ $item['detail'] }}</p>
                @endif
                @if (!empty($item['waktu']))
                    <p class="mt-1 font-label-sm text-label-sm text-gray-400">
                        {{ \Illuminate\Support\Carbon::parse($item['waktu'])->format('d M Y H:i') }}
                    </p>
                @endif
            </li>
        @endforeach
    </ol>
@endif