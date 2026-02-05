<div x-data="{ previewOpen: false, previewImage: '' }">
    <!-- Title -->
    <header class="mb-6">
        <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Acara Akan Datang</h1>
    </header>

    <!-- Content -->
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
                    {{-- location with icon --}}
                    <div class="flex items-center text-gray-600 dark:text-gray-300 text-sm mt-2 mb-4">
                        <svg class="w-4 h-4 mr-1.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span>{{ $event->location }}</span>
                    </div>
                    {{-- time with icon --}}
                    <div class="flex items-center text-gray-600 dark:text-gray-300 text-sm mb-4">
                        <svg class="w-4 h-4 mr-1.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>{{ \Carbon\Carbon::parse($event->date)->format('H:i') }} WITA</span>
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
