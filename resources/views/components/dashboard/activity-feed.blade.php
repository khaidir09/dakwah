@props(['activity'])

<div class="flex flex-col col-span-full xl:col-span-6 bg-white dark:bg-gray-800 shadow-xs rounded-xl border border-gray-200 dark:border-gray-700/60">
    <header class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/60">
        <h2 class="font-semibold text-gray-800 dark:text-gray-100">Aktivitas Terbaru</h2>
    </header>

    <div class="px-5 py-3">
        @forelse ($activity as $item)
            <div class="flex items-center gap-3 py-2 border-b border-gray-100 dark:border-gray-700/60 last:border-0">
                <span class="shrink-0 text-[10px] font-medium px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-400">
                    {{ $item['label'] }}
                </span>
                <div class="grow min-w-0">
                    @if ($item['url'])
                        <a href="{{ $item['url'] }}" class="text-sm font-medium text-gray-800 dark:text-gray-100 hover:underline truncate block">{{ $item['title'] }}</a>
                    @else
                        <div class="text-sm font-medium text-gray-800 dark:text-gray-100 truncate">{{ $item['title'] }}</div>
                    @endif
                    @if ($item['actor'])
                        <div class="text-xs text-gray-500 dark:text-gray-400">oleh {{ $item['actor'] }}</div>
                    @endif
                </div>
                <span class="shrink-0 text-xs text-gray-400 dark:text-gray-500">{{ $item['created_at']->diffForHumans() }}</span>
            </div>
        @empty
            <p class="text-sm text-gray-500 dark:text-gray-400 py-2">Belum ada aktivitas.</p>
        @endforelse
    </div>
</div>
