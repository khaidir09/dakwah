@props(['label', 'total', 'current' => 0, 'percent' => null])

<div class="flex flex-col col-span-full sm:col-span-6 xl:col-span-3 bg-white dark:bg-gray-800 shadow-xs rounded-xl border border-gray-200 dark:border-gray-700/60">
    <div class="px-5 py-5">
        <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">{{ $label }}</h2>
        <div class="flex items-baseline gap-2">
            <span class="text-3xl font-bold text-gray-800 dark:text-gray-100">{{ number_format($total, 0, ',', '.') }}</span>

            @if (!is_null($percent))
                <span @class([
                    'text-xs font-medium px-1.5 py-0.5 rounded-full',
                    'text-green-700 bg-green-100 dark:text-green-400 dark:bg-green-500/20' => $percent >= 0,
                    'text-red-700 bg-red-100 dark:text-red-400 dark:bg-red-500/20' => $percent < 0,
                ])>
                    {{ $percent >= 0 ? '↑' : '↓' }} {{ abs($percent) }}%
                </span>
            @elseif ($current > 0)
                <span class="text-xs font-medium px-1.5 py-0.5 rounded-full text-green-700 bg-green-100 dark:text-green-400 dark:bg-green-500/20">
                    +{{ number_format($current, 0, ',', '.') }} baru
                </span>
            @endif
        </div>
        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            {{ $current > 0 ? '+' . number_format($current, 0, ',', '.') . ' dalam 30 hari terakhir' : 'Tidak ada penambahan dalam 30 hari terakhir' }}
        </div>
    </div>
</div>
