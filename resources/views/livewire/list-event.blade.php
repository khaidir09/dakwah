<div>
    <!-- Page header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-5">
        <!-- Left: Title -->
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Events</h1>
        </div>

        <!-- Right: Actions -->
        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
            <!-- Search form -->
             <div class="relative">
                <label for="event-search" class="sr-only">Search</label>
                <input wire:model.live="search" id="event-search" class="form-input w-full pl-9 bg-white dark:bg-gray-800" type="search" placeholder="Search events…" />
                <button class="absolute inset-0 right-auto group" type="submit" aria-label="Search">
                    <svg class="shrink-0 fill-current text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-400 ml-3 mr-2" width="16" height="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
                        <path d="M7 14c-3.86 0-7-3.14-7-7s3.14-7 7-7 7 3.14 7 7-3.14 7-7 7zM7 2C4.243 2 2 4.243 2 7s2.243 5 5 5 5-2.243 5-5-2.243-5-5-5z" />
                        <path d="M15.707 14.293L13.314 11.9a8.019 8.019 0 01-1.414 1.414l2.393 2.393a.997.997 0 001.414 0 .999.999 0 000-1.414z" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="mb-5">
        <ul class="flex flex-wrap -m-1">
            <li class="m-1">
                <button wire:click="$set('category', null)" class="inline-flex items-center justify-center text-sm font-medium leading-5 rounded-full px-3 py-1 border {{ is_null($category) ? 'border-transparent shadow-xs bg-gray-900 text-white dark:bg-gray-100 dark:text-gray-800' : 'border-gray-200 dark:border-gray-700/60 hover:border-gray-300 dark:hover:border-gray-600 shadow-xs bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400' }} transition">View All</button>
            </li>
            @foreach(['Taklim', 'Maulid', 'Dzikir', 'Haul', 'Tabligh Akbar'] as $cat)
            <li class="m-1">
                <button wire:click="$set('category', '{{ $cat }}')" class="inline-flex items-center justify-center text-sm font-medium leading-5 rounded-full px-3 py-1 border {{ $category === $cat ? 'border-transparent shadow-xs bg-gray-900 text-white dark:bg-gray-100 dark:text-gray-800' : 'border-gray-200 dark:border-gray-700/60 hover:border-gray-300 dark:hover:border-gray-600 shadow-xs bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400' }} transition">{{ $cat }}</button>
            </li>
            @endforeach
        </ul>
    </div>

    <div class="text-sm text-gray-500 dark:text-gray-400 italic mb-4">{{ $events_count }} Events</div>

    <!-- Content -->
    <div class="grid xl:grid-cols-2 gap-6 mb-8">
        @foreach($events as $event)
        <article class="flex bg-white dark:bg-gray-800 shadow-xs rounded-xl overflow-hidden">
            <!-- Image -->
            <div class="relative block w-24 sm:w-56 xl:sidebar-expanded:w-40 2xl:sidebar-expanded:w-56 shrink-0">
                <img class="absolute object-cover object-center w-full h-full" src="{{ $event->image ? Storage::url($event->image) : asset('images/meetups-thumb-01.jpg') }}" width="220" height="236" alt="{{ $event->name }}" />
            </div>
            <!-- Content -->
            <div class="grow p-5 flex flex-col">
                <div class="grow">
                    <div class="text-sm font-semibold text-violet-500 uppercase mb-2">{{ \Carbon\Carbon::parse($event->date)->format('D d M, Y') }}</div>
                    <div class="inline-flex mb-2">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100">{{ $event->name }}</h3>
                    </div>
                    <div class="text-sm line-clamp-2">{{ $event->location }}</div>
                </div>
                <!-- Footer -->
                <div class="flex justify-between items-center mt-3">
                    <!-- Tag -->
                    <div class="text-xs inline-flex items-center font-medium border border-gray-200 dark:border-gray-700/60 text-gray-600 dark:text-gray-400 rounded-full text-center px-2.5 py-1">
                        <span>{{ $event->category }}</span>
                    </div>
                </div>
            </div>
        </article>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $events->links() }}
    </div>
</div>
