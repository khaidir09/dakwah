<div x-data="{ previewOpen: false, previewImage: '' }">
    <!-- Page header -->
    <div class="mb-6">
        <form class="relative">
            <label for="feed-search-mobile" class="sr-only">Search</label>
            <input wire:model.live="search" id="feed-search-mobile" class="form-input w-full pl-9 bg-white dark:bg-gray-800" type="search" placeholder="Cari nama acara" />
            <button class="absolute inset-0 right-auto group" type="submit" aria-label="Search">
                <svg class="shrink-0 fill-current text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-400 ml-3 mr-2" width="16" height="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
                    <path d="M7 14c-3.86 0-7-3.14-7-7s3.14-7 7-7 7 3.14 7 7-3.14 7-7 7zM7 2C4.243 2 2 4.243 2 7s2.243 5 5 5 5-2.243 5-5-2.243-5-5-5z" />
                    <path d="M15.707 14.293L13.314 11.9a8.019 8.019 0 01-1.414 1.414l2.393 2.393a.997.997 0 001.414 0 .999.999 0 000-1.414z" />
                </svg>
            </button>
        </form>
    </div>

    <!-- Filters -->
    <div class="mb-5">
        <ul class="flex flex-wrap -m-1">
            <li class="m-1">
                <button wire:click="$set('category', null)" class="inline-flex items-center justify-center text-sm font-medium leading-5 rounded-full px-3 py-1 border {{ is_null($category) ? 'border-transparent shadow-xs bg-gray-900 text-white dark:bg-gray-100 dark:text-gray-800' : 'border-gray-200 dark:border-gray-700/60 hover:border-gray-300 dark:hover:border-gray-600 shadow-xs bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400' }} transition">Semua</button>
            </li>
            @foreach(['Taklim', 'Maulid', 'Dzikir', 'Haul', 'Tabligh Akbar'] as $cat)
            <li class="m-1">
                <button wire:click="$set('category', '{{ $cat }}')" class="inline-flex items-center justify-center text-sm font-medium leading-5 rounded-full px-3 py-1 border {{ $category === $cat ? 'border-transparent shadow-xs bg-gray-900 text-white dark:bg-gray-100 dark:text-gray-800' : 'border-gray-200 dark:border-gray-700/60 hover:border-gray-300 dark:hover:border-gray-600 shadow-xs bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400' }} transition">{{ $cat }}</button>
            </li>
            @endforeach
        </ul>
    </div>

    <div class="text-sm text-gray-500 dark:text-gray-400 italic mb-4">{{ $events_count }} Acara</div>

    <!-- Content -->
    @if ($events->count() > 0)
        <div class="grid xl:grid-cols-2 gap-6 mb-8">
            @foreach($events as $event)
            <article class="flex flex-col sm:flex-row bg-white dark:bg-gray-800 shadow-xs rounded-xl overflow-hidden">
                <!-- Image -->
                <div class="block w-full h-48 sm:h-auto sm:w-56 xl:sidebar-expanded:w-40 2xl:sidebar-expanded:w-56 shrink-0 group relative">
                    <img class="w-full h-full cursor-pointer transition-opacity group-hover:opacity-90"
                        src="{{ Storage::url($event->image) }}"
                        width="220"
                        height="236"
                        alt="{{ $event->name }}"
                        @click.prevent="previewImage = '{{ Storage::url($event->image) }}'; previewOpen = true"
                    />
                    <!-- Search icon overlay hint -->
                    <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity bg-black bg-opacity-20 pointer-events-none">
                        <svg class="w-8 h-8 text-white drop-shadow-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                        </svg>
                    </div>
                </div>
                <!-- Content -->
                <div class="grow p-5 flex flex-col">
                    <div class="grow">
                        <div class="text-sm font-semibold text-emerald-500 uppercase mb-2">{{ \Carbon\Carbon::parse($event->date)->locale('id')->translatedFormat('D, d M Y') }}</div>
                        <div class="inline-flex mb-2">
                            <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100">{{ $event->name }}</h3>
                        </div>
                        <div class="text-sm line-clamp-2">{{ $event->village->name }}, {{ $event->district->name }}</div>
                        {{-- Lokasi --}}
                        <div class="flex items-center space-x-2 mr-2 mt-2">
                            <div class="flex space-x-1">
                                <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 24 24"> <path fill-rule="evenodd" clip-rule="evenodd" d="M13 11V19H11V11H13Z" fill="#059669"></path> <path fill-rule="evenodd" clip-rule="evenodd" d="M6 7C6 3.68629 8.68629 1 12 1C15.3137 1 18 3.68629 18 7C18 10.3137 15.3137 13 12 13C8.68629 13 6 10.3137 6 7Z" fill="#059669"></path> <path fill-rule="evenodd" clip-rule="evenodd" d="M8.1893 15.7653L7.21204 15.9774C5.78358 16.2873 4.65768 16.7189 3.91598 17.1909C3.13673 17.6867 3 18.0745 3 18.2492C3 18.3785 3.06629 18.6213 3.44935 18.961C3.83125 19.2997 4.44093 19.6504 5.28013 19.9652C6.95116 20.592 9.32677 21.0001 12 21.0001C14.6732 21.0001 17.0488 20.592 18.7199 19.9652C19.5591 19.6504 20.1687 19.2997 20.5507 18.961C20.9337 18.6213 21 18.3785 21 18.2492C21 18.0745 20.8633 17.6867 20.084 17.1909C19.3423 16.7189 18.2164 16.2873 16.788 15.9774L15.8107 15.7653L16.2348 13.8108L17.212 14.0228C18.7726 14.3614 20.1467 14.8602 21.1577 15.5035C22.1312 16.123 23 17.0355 23 18.2492C23 19.1557 22.5066 19.8996 21.8776 20.4574C21.2475 21.0162 20.3927 21.4738 19.4223 21.8378C17.474 22.5686 14.8496 23.0001 12 23.0001C9.15039 23.0001 6.52599 22.5686 4.57773 21.8378C3.60729 21.4738 2.7525 21.0162 2.12235 20.4574C1.49336 19.8996 1 19.1557 1 18.2492C1 17.0355 1.86876 16.123 2.84227 15.5035C3.85331 14.8602 5.22741 14.3614 6.78796 14.0228L7.76522 13.8108L8.1893 15.7653Z" fill="#059669" data-color="color-2"></path> </svg>
                            </div>
                            <div class="inline-flex text-sm font-medium text-emerald-600">{{ $event->location }}</div>
                        </div>
                    </div>
                    <!-- Footer -->
                    <div class="flex justify-between items-center mt-3">
                        <!-- Tag -->
                        <div class="text-xs inline-flex items-center font-medium border border-gray-200 dark:border-gray-700/60 text-gray-600 dark:text-gray-400 rounded-full text-center px-2.5 py-1">
                            <span>{{ $event->category }}</span>
                        </div>
                        {{-- Tombol maps jika ada --}}
                        @if($event->maps_link)
                        <a href="{{ $event->maps_link }}" target="_blank" class="inline-flex items-center text-xs font-medium border border-emerald-500 dark:border-emerald-400 text-emerald-600 dark:text-emerald-400 rounded-full text-center px-3 py-1 hover:bg-emerald-50 dark:hover:bg-emerald-900 transition">
                            Maps &rarr;
                        </a>
                        @endif
                    </div>
                </div>
            </article>
            @endforeach
        </div>
    @else
        <div class="flex flex-col items-center justify-center py-12 text-center">
            <svg class="w-16 h-16 text-gray-400 dark:text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Belum ada acara akan datang</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-6">Saat ini belum ada jadwal acara terbaru. Silakan lihat koleksi video pengajian kami.</p>
            <a href="{{ route('video-list') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Lihat Video
            </a>
        </div>
    @endif

    <!-- Pagination -->
    <div class="mt-8">
        {{ $events->links() }}
    </div>

    <!-- Image Preview Modal -->
    <div
        x-show="previewOpen"
        style="display: none"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-90 p-4"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <!-- Modal Content -->
        <div @click.outside="previewOpen = false" class="relative max-w-5xl w-full max-h-full flex items-center justify-center">
             <button @click="previewOpen = false" class="absolute -top-12 right-0 text-white hover:text-gray-300 focus:outline-none">
                <span class="sr-only">Close</span>
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
             </button>
             <img :src="previewImage" class="rounded shadow-2xl object-contain max-h-[85vh] w-auto" alt="Event Preview">
        </div>
    </div>
</div>
