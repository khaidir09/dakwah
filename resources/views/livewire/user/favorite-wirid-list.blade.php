<div>
    <!-- Search form -->
    <div class="mb-5">
        <form class="relative">
            <label for="feed-search-mobile" class="sr-only">Search</label>
            <input wire:model.live="search" id="feed-search-mobile" class="form-input w-full pl-9 bg-white dark:bg-gray-800" type="search" placeholder="Cari nama wirid/waktu baca" />
            <button class="absolute inset-0 right-auto group" type="submit" aria-label="Search">
                <svg class="shrink-0 fill-current text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-400 ml-3 mr-2" width="16" height="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
                    <path d="M7 14c-3.86 0-7-3.14-7-7s3.14-7 7-7 7 3.14 7 7-3.14 7-7 7zM7 2C4.243 2 2 4.243 2 7s2.243 5 5 5 5-2.243 5-5-2.243-5-5-5z" />
                    <path d="M15.707 14.293L13.314 11.9a8.019 8.019 0 01-1.414 1.414l2.393 2.393a.997.997 0 001.414 0 .999.999 0 000-1.414z" />
                </svg>
            </button>
        </form>
    </div>

    @if($wirids->isEmpty())
        <div class="flex flex-col items-center justify-center p-8 bg-white dark:bg-gray-800 shadow-xs rounded-xl text-center">
            <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Belum ada wirid favorit</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Tandai wirid sebagai favorit untuk melihatnya di sini.</p>
            <a href="{{ route('wirid-list') }}" class="mt-4 btn-sm bg-violet-500 hover:bg-violet-600 text-white">
                Jelajahi Wirid
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach ($wirids as $wirid)
                <div class="bg-white dark:bg-gray-800 shadow-xs rounded-xl p-5 flex flex-col h-full">
                    <!-- Header -->
                    <header class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-3">
                        {{ $wirid->nama }}
                    </header>
                    <!-- Body -->
                    <div class="flex-grow text-sm text-gray-800 dark:text-gray-100 space-y-3 mb-5">
                        <p class="text-justify line-clamp-3">{!! $wirid->deskripsi !!}</p>

                        {{-- Arab Preview --}}
                        <div x-data="{ expanded: false }">
                            <div class="text-right text-xl font-semibold" :class="expanded ? '' : 'truncate'" dir="rtl">
                                {!! $wirid->arab !!}
                            </div>
                            <button @click="expanded = ! expanded" class="text-xs font-medium text-emerald-600 hover:text-emerald-500 mt-1 focus:outline-none" x-text="expanded ? 'Tutup' : 'Baca Selengkapnya'"></button>
                        </div>
                    </div>
                    <!-- Footer -->
                    <footer class="mt-auto pt-4 border-t border-gray-100 dark:border-gray-700/60 flex justify-between items-center space-x-3">
                        <div class="flex items-start space-x-3">
                            <div class="text-xs inline-flex font-medium bg-green-500/20 text-green-700 rounded-full text-center px-2.5 py-1">{{ $wirid->waktu }}</div>
                        </div>
                        <div class="flex items-center space-x-2">
                             <!-- View Button (Trigger Modal or Expand) - Optional, simplified for now -->

                            <!-- Like button -->
                            <button
                                wire:click="toggleLike({{ $wirid->id }})"
                                class="flex items-center text-red-500 hover:text-red-600 tooltip"
                                title="Hapus dari Favorit"
                                >
                                <svg class="shrink-0 fill-current mr-1.5" width="16" height="16" viewBox="0 0 16 16">
                                    <path d="M14.682 2.318A4.485 4.485 0 0011.5 1 4.377 4.377 0 008 2.707 4.383 4.383 0 004.5 1a4.5 4.5 0 00-3.182 7.682L8 15l6.682-6.318a4.5 4.5 0 000-6.364zm-1.4 4.933L8 12.247l-5.285-5A2.5 2.5 0 014.5 3c1.437 0 2.312.681 3.5 2.625C9.187 3.681 10.062 3 11.5 3a2.5 2.5 0 011.785 4.251h-.003z" />
                                </svg>
                                <div class="text-sm font-medium">{{ $wirid->likes }}</div>
                            </button>
                        </div>
                    </footer>
                </div>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $wirids->links() }}
        </div>
    @endif
</div>
