<div>
    <!-- Search form -->
    <div class="mb-6">
        <form class="relative">
            <label for="feed-search-mobile" class="sr-only">Search</label>
            <input wire:model.live="search" id="feed-search-mobile" class="form-input w-full pl-9 bg-white dark:bg-gray-800" type="search" placeholder="Cari judul/kategori video" />
            <button class="absolute inset-0 right-auto group" type="submit" aria-label="Search">
                <svg class="shrink-0 fill-current text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-400 ml-3 mr-2" width="16" height="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
                    <path d="M7 14c-3.86 0-7-3.14-7-7s3.14-7 7-7 7 3.14 7 7-3.14 7-7 7zM7 2C4.243 2 2 4.243 2 7s2.243 5 5 5 5-2.243 5-5-2.243-5-5-5z" />
                    <path d="M15.707 14.293L13.314 11.9a8.019 8.019 0 01-1.414 1.414l2.393 2.393a.997.997 0 001.414 0 .999.999 0 000-1.414z" />
                </svg>
            </button>
        </form>
    </div>

    <!-- Filters -->
    <div class="mb-4 border-b border-gray-200 dark:border-gray-700/60">
        <ul class="text-sm font-medium flex flex-nowrap -mx-4 sm:-mx-6 lg:-mx-8 overflow-x-scroll no-scrollbar">
            <li class="pb-3 mr-6 last:mr-0 first:pl-4 sm:first:pl-6 lg:first:pl-8 last:pr-4 sm:last:pr-6 lg:last:pr-8">
                <button
                    wire:click="$set('category', null)"
                    class="whitespace-nowrap {{ is_null($category) ? 'text-emerald-500' : 'text-gray-500 dark:text-gray-400 hover:text-gray-600 dark:hover:text-gray-300' }}"
                >
                    Semua
                </button>
            </li>
            @foreach(['Taklim', 'Maulid', 'Dzikir', 'Haul', 'Tabligh Akbar'] as $cat)
            <li class="pb-3 mr-6 last:mr-0 first:pl-4 sm:first:pl-6 lg:first:pl-8 last:pr-4 sm:last:pr-6 lg:last:pr-8">
                <button
                    wire:click="$set('category', '{{ $cat }}')"
                    class="whitespace-nowrap {{ $category === $cat ? 'text-emerald-500' : 'text-gray-500 dark:text-gray-400 hover:text-gray-600 dark:hover:text-gray-300' }}"
                >
                    {{ $cat }}
                </button>
            </li>
            @endforeach
        </ul>
    </div>

    <!-- Page content -->
    <div>
        <div class="mt-8">
            <div class="grid grid-cols-12 gap-6">
                @foreach ($videos as $video)
                    <div class="col-span-full lg:col-span-6 bg-white dark:bg-gray-800 shadow-xs rounded-xl overflow-hidden">
                        <div class="flex flex-col h-full">
                            <!-- Image -->
                            <iframe class="w-full" height="315" src="{{ $video->link }}" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                            <!-- Card Content -->
                            <div class="grow flex flex-col p-5">
                                <!-- Card body -->
                                <div class="grow">
                                    <!-- Header -->
                                    <header class="mb-3">
                                        <h3 class="text-lg text-gray-800 dark:text-gray-100 font-semibold">{{ $video->title }}</h3>
                                    </header>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="mt-8">
        {{ $videos->links() }}
    </div>
</div>