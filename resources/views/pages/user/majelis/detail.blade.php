<x-user-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 md:py-0 w-full max-w-[96rem] mx-auto">

        <div class="xl:flex">

            <!-- Left + Middle content -->
            <div class="md:flex flex-1">

                <!-- Left content -->
                <x-community.feed-left-content />

                <!-- Middle content -->
                <div class="flex-1 md:ml-8 xl:mx-4 2xl:mx-8">
                    <div class="md:py-8">

                        <!-- Blocks -->
                        <div class="space-y-4">

                            <div class="flex justify-between items-center mb-6">
                                <!-- Title -->
                                <header>
                                    <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Detail Majelis</h1>
                                </header>

                                <div>
                                    <a class="text-sm font-medium text-emerald-500 hover:text-emerald-600 dark:hover:text-emerald-400" href="{{ route('majelis-list') }}">&lt;- Kembali</a>
                                </div>
                            </div>

                            <!-- Posts -->
                            <div 
                                class="grow flex flex-col md:translate-x-0 duration-300 ease-in-out"
                                :class="profileSidebarOpen ? 'translate-x-1/3' : 'translate-x-0'"
                            >

                                <!-- Profile background -->
                                <div class="relative h-56 bg-gray-200 dark:bg-gray-900">
                                    <img class="object-cover h-full w-full" src="{{ $assembly->gambar_large_url }}" width="979" height="220" alt="Profile background" />    
                                </div>

                                <!-- Content -->
                                <div class="relative pb-8 mt-6">

                                    <div class="flex flex-col items-center sm:flex-row sm:justify-between sm:items-end mb-6">
                                        <!-- Header -->
                                        <header class="text-center sm:text-left ">
                                            <!-- Name -->
                                            <div class="inline-flex items-start mb-2">
                                                <h1 class="text-2xl text-gray-800 dark:text-gray-100 font-bold">{{ $assembly->nama_majelis }}</h1>
                                            </div>
                                            <!-- Meta -->
                                            <div class="flex flex-wrap justify-center sm:justify-start space-x-4">
                                                <div class="flex items-center">
                                                    <svg class="fill-current shrink-0 text-gray-400 dark:text-gray-500 hidden lg:block mr-2" width="16" height="16" viewBox="0 0 16 16">
                                                        <path d="M8 8.992a2 2 0 1 1-.002-3.998A2 2 0 0 1 8 8.992Zm-.7 6.694c-.1-.1-4.2-3.696-4.2-3.796C1.7 10.69 1 8.892 1 6.994 1 3.097 4.1 0 8 0s7 3.097 7 6.994c0 1.898-.7 3.697-2.1 4.996-.1.1-4.1 3.696-4.2 3.796-.4.3-1 .3-1.4-.1Zm-2.7-4.995L8 13.688l3.4-2.997c1-1 1.6-2.198 1.6-3.597 0-2.798-2.2-4.996-5-4.996S3 4.196 3 6.994c0 1.399.6 2.698 1.6 3.697 0-.1 0-.1 0 0Z" />
                                                    </svg>
                                                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $assembly->alamat }}, {{ $assembly->village->name }}, {{ $assembly->district->name }}</span>
                                                </div>
                                            </div>
                                        </header>
                                        <!-- Actions -->
                                        <div class="flex space-x-2 mt-3">
                                            <livewire:majelis.follow-button :assembly="$assembly" />
                                            <a href="{{ $assembly->maps }}" class="btn-sm bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700/60 hover:border-gray-300 dark:hover:border-gray-600 text-gray-800 dark:text-gray-300">
                                                Maps
                                            </a>
                                        </div>
                                    </div>

                                    

                                    <!-- Tabs -->
                                    <div class="relative mb-6">
                                        <div class="absolute bottom-0 w-full h-px bg-gray-200 dark:bg-gray-700/60" aria-hidden="true"></div>
                                        <ul class="relative text-sm font-medium flex flex-nowrap -mx-4 sm:-mx-6 lg:-mx-8 overflow-x-scroll no-scrollbar">
                                            <li class="mr-6 last:mr-0 first:pl-4 sm:first:pl-6 lg:first:pl-8 last:pr-4 sm:last:pr-6 lg:last:pr-8">
                                                <a class="block pb-3 text-emerald-500 whitespace-nowrap border-b-2 border-emerald-500" href="#0">Umum</a>
                                            </li>
                                            {{-- <li class="mr-6 last:mr-0 first:pl-4 sm:first:pl-6 lg:first:pl-8 last:pr-4 sm:last:pr-6 lg:last:pr-8">
                                                <a class="block pb-3 text-gray-500 dark:text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 whitespace-nowrap" href="#0">Connections</a>
                                            </li>
                                            <li class="mr-6 last:mr-0 first:pl-4 sm:first:pl-6 lg:first:pl-8 last:pr-4 sm:last:pr-6 lg:last:pr-8">
                                                <a class="block pb-3 text-gray-500 dark:text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 whitespace-nowrap" href="#0">Contributions</a>
                                            </li> --}}
                                        </ul>
                                    </div>

                                    <!-- Profile content -->
                                    <div class="flex flex-col xl:flex-row xl:space-x-16">

                                        <!-- Main content -->
                                        <div class="flex-1 space-y-5 mb-8 xl:mb-0">
                                
                                            <!-- About Me -->
                                            <div>
                                                <h2 class="text-gray-800 dark:text-gray-100 font-semibold mb-2">Tentang Majelis</h2>
                                                <div class="text-sm space-y-2">
                                                    <p class="text-justify">{{ $assembly->deskripsi }}</p>
                                                </div>
                                            </div>
                                
                                            <!-- Departments -->
                                            <div>
                                                <h2 class="text-gray-800 dark:text-gray-100 font-semibold mb-2">Jadwal Rutinan</h2>
                                                <!-- Cards -->
                                                <div class="space-y-4">

                                                    <!-- Card -->
                                                    @foreach ($schedules as $item)
                                                        <div class="bg-white dark:bg-gray-900 p-4 border border-gray-200 dark:border-gray-700/60 rounded-lg shadow-xs">
                                                            <!-- Card header -->
                                                            <div class="flex items-center gap-2 mb-2">
                                                                <span class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ $item->nama_jadwal }}</span>
                                                                @php
                                                                    $accessColor = match($item->access) {
                                                                        'Umum' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
                                                                        'Ikhwan' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                                                                        'Akhwat' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
                                                                        default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                                                    };
                                                                @endphp
                                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $accessColor }}">
                                                                    {{ $item->access }}
                                                                </span>
                                                            </div>
                                                            <!-- Card content -->
                                                            <div class="text-sm mb-3">{!! $item->deskripsi !!}</div>
                                                            <!-- Card footer -->
                                                            <div class="flex justify-between items-center">
                                                                <!-- Link -->
                                                                <div class="text-sm font-medium text-emerald-500 hover:text-emerald-600 dark:hover:text-emerald-400">
                                                                    {{ $item->hari }}, {{ $item->waktu_formatted }} WITA
                                                                </div>
                                                                <!-- Avatars group -->
                                                                <a href="{{ route('guru-detail', $item->teacher->id) }}">
                                                                    <div class="flex items-center">
                                                                        <img class="rounded-full border-2 border-white w-8 h-8 object-cover dark:border-gray-800 box-content mr-1" src="{{ Storage::url($item->teacher->foto) }}" alt="{{ $item->teacher->name }}" /> <span class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ $item->teacher->name }}</span>
                                                                    </div>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    @endforeach

                                                </div>

                                            </div>
                                
                                            <!-- Work History -->
                                            <div x-data="{ previewOpen: false, previewImage: '' }">
                                                <h2 class="text-gray-800 dark:text-gray-100 font-semibold mb-2">Acara Akan Datang</h2>
                                                <div class="bg-white dark:bg-gray-900 p-4 border border-gray-200 dark:border-gray-700/60 rounded-lg shadow-xs">
                                                    <ul class="space-y-3">
                                                        @forelse ($upcomingEvents as $event)
                                                            <li class="sm:flex sm:items-center sm:justify-between border-b border-gray-100 dark:border-gray-700/60 pb-3 last:border-0 last:pb-0">
                                                                <div class="sm:grow flex items-center text-sm">
                                                                    <!-- Icon/Image -->
                                                                    @if($event->image)
                                                                        <div class="w-10 h-10 rounded-lg shrink-0 overflow-hidden my-2 mr-3 group relative cursor-pointer"
                                                                             @click="previewImage = '{{ Storage::url($event->image) }}'; previewOpen = true">
                                                                            <img src="{{ Storage::url($event->image) }}" alt="{{ $event->name }}" class="w-full h-full object-cover transition-opacity group-hover:opacity-90">
                                                                        </div>
                                                                    @else
                                                                        <div class="w-10 h-10 rounded-lg shrink-0 bg-emerald-500 flex items-center justify-center my-2 mr-3 text-white">
                                                                            <svg class="w-6 h-6 fill-current" viewBox="0 0 32 32">
                                                                                 <path d="M21 14a.75.75 0 0 1-.75-.75 1.5 1.5 0 0 0-1.5-1.5.75.75 0 1 1 0-1.5 1.5 1.5 0 0 0 1.5-1.5.75.75 0 1 1 1.5 0 1.5 1.5 0 0 0 1.5 1.5.75.75 0 1 1 0 1.5 1.5 1.5 0 0 0-1.5 1.5.75.75 0 0 1-.75.75Zm-7 10a1 1 0 0 1-1-1 4 4 0 0 0-4-4 1 1 0 0 1 0-2 4 4 0 0 0 4-4 1 1 0 0 1 2 0 4 4 0 0 0 4 4 1 1 0 0 1 0 2 4 4 0 0 0-4 4 1 1 0 0 1-1 1Z" />
                                                                            </svg>
                                                                        </div>
                                                                    @endif
                                                                    <!-- Position -->
                                                                    <div>
                                                                        <div class="font-medium text-gray-800 dark:text-gray-100">
                                                                            {{ $event->name }}
                                                                        </div>
                                                                        <div class="flex flex-wrap items-center space-x-2">
                                                                            <div>{{ \Carbon\Carbon::parse($event->date)->locale('id')->translatedFormat('d F Y, H:i') }}</div>
                                                                            <div class="text-gray-400 dark:text-gray-600">·</div>
                                                                            <div class="truncate max-w-[150px]">{{ $event->location }}</div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <!-- Tags/Type -->
                                                                <div class="sm:ml-2 mt-2 sm:mt-0">
                                                                    <span class="inline-flex items-center justify-center text-xs font-medium leading-5 rounded-full px-2.5 py-0.5 border border-gray-200 dark:border-gray-700/60 shadow-xs bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400">
                                                                        {{ $event->category }}
                                                                    </span>
                                                                </div>
                                                            </li>
                                                        @empty
                                                            <li class="sm:flex sm:items-center sm:justify-between">
                                                                <div class="font-medium text-gray-800 dark:text-gray-100">Belum ada</div>
                                                            </li>
                                                        @endforelse

                                                    </ul>
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
                                
                                        </div>

                                        <!-- Sidebar -->
                                        <aside class="xl:min-w-56 xl:w-56 space-y-3">
                                            <div class="text-sm">
                                                <h3 class="font-medium text-gray-800 dark:text-gray-100">Pimpinan</h3>
                                                <div>{{$assembly->teacher->name }}</div>
                                            </div>
                                            <div class="text-sm">
                                                <h3 class="font-medium text-gray-800 dark:text-gray-100">Kontak Penanggung Jawab</h3>
                                                @if ($assembly->user != null)
                                                    @if (Auth::check())
                                                        <div>{{ $assembly->user->phone }}</div>
                                                    @else
                                                        <a href="{{ route('login') }}" class="underline text-emerald-600">Login</a> untuk melihat kontak penanggung jawab
                                                    @endif
                                                @else
                                                    <div>-</div>
                                                @endif
                                            </div>

                                            @if($assembly->youtube || $assembly->instagram || $assembly->facebook || $assembly->tiktok)
                                                <div class="text-sm">
                                                    <h3 class="font-medium text-gray-800 dark:text-gray-100 mb-2">Media Sosial</h3>
                                                    <div class="flex space-x-3">
                                                        @if($assembly->youtube)
                                                            <a href="{{ Str::startsWith($assembly->youtube, 'http') ? $assembly->youtube : 'https://youtube.com/' . $assembly->youtube }}" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-red-600 transition-colors">
                                                                <span class="sr-only">YouTube</span>
                                                                <svg class="w-6 h-6 fill-current" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
                                                                    <path d="M29.41 9.2a3.55 3.55 0 0 0-2.5-2.5C24.72 6.09 16 6.09 16 6.09s-8.72 0-10.91.61a3.55 3.55 0 0 0-2.5 2.5C2 11.41 2 16 2 16s0 4.59.59 6.8a3.55 3.55 0 0 0 2.5 2.5c2.19.61 10.91.61 10.91.61s8.72 0 10.91-.61a3.55 3.55 0 0 0 2.5-2.5C30 20.59 30 16 30 16s0-4.59-.59-6.8ZM13.2 20.2v-8.4l7.28 4.2-7.28 4.2Z"/>
                                                                </svg>
                                                            </a>
                                                        @endif

                                                        @if($assembly->instagram)
                                                            <a href="{{ Str::startsWith($assembly->instagram, 'http') ? $assembly->instagram : 'https://instagram.com/' . $assembly->instagram }}" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-pink-600 transition-colors">
                                                                <span class="sr-only">Instagram</span>
                                                                <svg class="w-6 h-6 fill-current" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
                                                                    <circle cx="16" cy="16" r="4"/>
                                                                    <path d="M22.5 7.5h-13a5 5 0 0 0-5 5v13a5 5 0 0 0 5 5h13a5 5 0 0 0 5-5v-13a5 5 0 0 0-5-5Zm2.5 18a2.5 2.5 0 0 1-2.5 2.5h-13A2.5 2.5 0 0 1 7 25.5v-13A2.5 2.5 0 0 1 9.5 10h13a2.5 2.5 0 0 1 2.5 2.5ZM23.5 11a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3Zm-7.5-1a6 6 0 1 0 0 12 6 6 0 0 0 0-12Z"/>
                                                                </svg>
                                                            </a>
                                                        @endif

                                                        @if($assembly->facebook)
                                                            <a href="{{ Str::startsWith($assembly->facebook, 'http') ? $assembly->facebook : 'https://facebook.com/' . $assembly->facebook }}" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-blue-600 transition-colors">
                                                                <span class="sr-only">Facebook</span>
                                                                <svg class="w-6 h-6 fill-current" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
                                                                    <path d="M17.5 30v-9.5h3.2l.5-3.7h-3.7V14.4c0-1.1.3-1.8 1.8-1.8h1.9v-3.3c-.3 0-1.5-.1-2.8-.1-2.8 0-4.7 1.7-4.7 4.8v2.7h-3.2v3.7h3.2V30h6.1Z"/>
                                                                </svg>
                                                            </a>
                                                        @endif

                                                        @if($assembly->tiktok)
                                                            <a href="{{ Str::startsWith($assembly->tiktok, 'http') ? $assembly->tiktok : 'https://tiktok.com/' . $assembly->tiktok }}" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-black dark:hover:text-white transition-colors">
                                                                <span class="sr-only">TikTok</span>
                                                                <svg class="w-6 h-6 fill-current" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
                                                                    <path d="M23.6 11.5a6.76 6.76 0 0 1-3.6-1V6.9A10.5 10.5 0 0 0 16 7.7a7.28 7.28 0 0 0-3.1 3.2 8.35 8.35 0 0 0-1 4 8.79 8.79 0 0 0 .5 3 8.36 8.36 0 0 0 2.2 3.2 8.65 8.65 0 0 0 3.8 2 10.38 10.38 0 0 0 5 .2v-4a6.45 6.45 0 0 1-2.9.2 4.41 4.41 0 0 1-2.1-.9 4.31 4.31 0 0 1-1.3-1.7 5.09 5.09 0 0 1-.4-2.1 4.3 4.3 0 0 1 .4-1.9 4.28 4.28 0 0 1 1.2-1.5 4.67 4.67 0 0 1 1.9-.9 6.07 6.07 0 0 1 2.5.1V2.1H29v13.5a10 10 0 0 1-1.4 5 10.74 10.74 0 0 1-4.2 3.8 12.18 12.18 0 0 1-6.1 1.6 12 12 0 0 1-4.7-.9 11.59 11.59 0 0 1-3.8-2.5 12.06 12.06 0 0 1-2.6-3.8 11.75 11.75 0 0 1-1-4.7 12 12 0 0 1 .5-3.5 12.08 12.08 0 0 1 1.6-3.4 12.44 12.44 0 0 1 2.5-2.7 11.45 11.45 0 0 1 3.4-1.8 12.87 12.87 0 0 1 4.1-.7v4Z" transform="scale(0.8) translate(6, 4)"/>
                                                                </svg>
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                        </aside>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>
                </div>

            </div>

            <!-- Right content -->
            <x-community.feed-right-content />

        </div>

    </div>
</x-user-layout>
