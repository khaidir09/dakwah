@props(['queues', 'latestPending'])

@php
    $totalPending = collect($queues)->sum('count');
@endphp

<div class="flex flex-col col-span-full xl:col-span-6 bg-white dark:bg-gray-800 shadow-xs rounded-xl border border-gray-200 dark:border-gray-700/60">
    <header class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/60 flex items-center justify-between">
        <h2 class="font-semibold text-gray-800 dark:text-gray-100">Menunggu Moderasi</h2>
        <span @class([
            'text-xs font-medium px-2 py-0.5 rounded-full',
            'text-amber-700 bg-amber-100 dark:text-amber-400 dark:bg-amber-500/20' => $totalPending > 0,
            'text-gray-500 bg-gray-100 dark:text-gray-400 dark:bg-gray-700' => $totalPending === 0,
        ])>
            {{ $totalPending }} item
        </span>
    </header>

    <div class="p-3">
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
            @foreach ($queues as $queue)
                @if ($queue['count'] > 0)
                    <a href="{{ $queue['url'] }}"
                        class="flex flex-col px-3 py-2 rounded-lg border border-amber-200 bg-amber-50 hover:bg-amber-100 dark:border-amber-500/30 dark:bg-amber-500/10 dark:hover:bg-amber-500/20 transition">
                        <span class="text-xl font-bold text-amber-700 dark:text-amber-400">{{ $queue['count'] }}</span>
                        <span class="text-xs text-gray-600 dark:text-gray-400">{{ $queue['label'] }}</span>
                    </a>
                @else
                    <div class="flex flex-col px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700/60">
                        <span class="text-xl font-bold text-gray-400 dark:text-gray-500">0</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $queue['label'] }}</span>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    <div class="px-5 py-3 border-t border-gray-100 dark:border-gray-700/60">
        <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">Pending Terbaru</h3>

        @forelse ($latestPending as $item)
            <a href="{{ $item['url'] }}" class="flex items-center gap-3 py-2 border-b border-gray-100 dark:border-gray-700/60 last:border-0 hover:bg-gray-50 dark:hover:bg-gray-700/30 -mx-2 px-2 rounded">
                <span class="shrink-0 text-[10px] font-medium px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                    {{ $item['label'] }}
                </span>
                <div class="grow min-w-0">
                    <div class="text-sm font-medium text-gray-800 dark:text-gray-100 truncate">{{ $item['title'] }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $item['actor'] ?? '—' }}</div>
                </div>
                <span class="shrink-0 text-xs text-gray-400 dark:text-gray-500">{{ $item['created_at']->diffForHumans() }}</span>
            </a>
        @empty
            <p class="text-sm text-gray-500 dark:text-gray-400 py-2">Tidak ada kontribusi yang menunggu moderasi.</p>
        @endforelse
    </div>
</div>
