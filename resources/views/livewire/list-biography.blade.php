<div>
    <!-- Search form -->
    <div>
        <form class="relative">
            <label for="feed-search-mobile" class="sr-only">Search</label>
            <input wire:model.live="search" id="feed-search-mobile" class="form-input w-full pl-9 bg-white dark:bg-gray-800" type="search" placeholder="Cari manaqib..." />
            <button class="absolute inset-0 right-auto group" type="submit" aria-label="Search">
                <svg class="shrink-0 fill-current text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-400 ml-3 mr-2" width="16" height="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
                    <path d="M7 14c-3.86 0-7-3.14-7-7s3.14-7 7-7 7 3.14 7 7-3.14 7-7 7zM7 2C4.243 2 2 4.243 2 7s2.243 5 5 5 5-2.243 5-5-2.243-5-5-5z" />
                    <path d="M15.707 14.293L13.314 11.9a8.019 8.019 0 01-1.414 1.414l2.393 2.393a.997.997 0 001.414 0 .999.999 0 000-1.414z" />
                </svg>
            </button>
        </form>
    </div>

    <!-- Middle content -->
    <div class="flex-1 mt-5">
        <div class="space-y-4">
            @foreach ($biographies as $bio)
                <div class="bg-white dark:bg-gray-800 shadow-xs rounded-xl p-5">
                    <div class="flex flex-col md:flex-row gap-4">
                        @if($bio->foto)
                            <div class="flex-shrink-0">
                                <img src="{{ asset('storage/' . $bio->foto) }}" alt="{{ $bio->name }}" class="w-full md:w-56 h-56 object-cover rounded-lg">
                            </div>
                        @endif
                        <div class="flex-1">
                            <header class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-2">
                                <a href="{{ route('manaqib-detail', $bio->slug) }}" class="hover:underline hover:text-emerald-500">
                                    {{ $bio->name }}
                                </a>
                            </header>
                            <div class="text-sm text-gray-600 dark:text-gray-400 mb-3 line-clamp-3">
                                {!! strip_tags($bio->biografi) !!}
                            </div>
                            <a href="{{ route('manaqib-detail', $bio->slug) }}" class="inline-flex items-center text-sm font-medium text-emerald-500 hover:text-emerald-600 dark:hover:text-emerald-400">
                                Selengkapnya &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="mt-8">
        {{ $biographies->links() }}
    </div>
</div>
