<div>
    @if($isFollowing)
        <button wire:click="toggleFollow" class="btn-sm bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700/60 hover:border-gray-300 dark:hover:border-gray-600 text-gray-800 dark:text-gray-300">
            <span>Mengikuti</span>
            <svg class="w-3 h-3 shrink-0 ml-2 fill-current text-emerald-500" viewBox="0 0 12 12">
                <path d="M10.28 1.28L3.989 7.575 1.695 5.28A1 1 0 00.28 6.695l3 3a1 1 0 001.414 0l7-7A1 1 0 0010.28 1.28z" />
            </svg>
        </button>
    @else
        <button wire:click="toggleFollow" class="btn-sm bg-gray-900 text-gray-100 hover:bg-gray-800 dark:bg-gray-100 dark:text-gray-800 dark:hover:bg-white">
            Ikuti
        </button>
    @endif
</div>
