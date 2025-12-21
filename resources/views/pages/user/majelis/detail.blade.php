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
                                    <a class="text-sm font-medium text-violet-500 hover:text-violet-600 dark:hover:text-violet-400" href="{{ route('majelis-list') }}">&lt;- Kembali</a>
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
                                            <button class="btn-sm bg-gray-900 text-gray-100 hover:bg-gray-800 dark:bg-gray-100 dark:text-gray-800 dark:hover:bg-white">
                                                Ikuti
                                            </button>
                                            <a href="" class="btn-sm bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700/60 hover:border-gray-300 dark:hover:border-gray-600 text-gray-800 dark:text-gray-300">
                                                Maps
                                            </a>
                                        </div>
                                    </div>

                                    

                                    <!-- Tabs -->
                                    <div class="relative mb-6">
                                        <div class="absolute bottom-0 w-full h-px bg-gray-200 dark:bg-gray-700/60" aria-hidden="true"></div>
                                        <ul class="relative text-sm font-medium flex flex-nowrap -mx-4 sm:-mx-6 lg:-mx-8 overflow-x-scroll no-scrollbar">
                                            <li class="mr-6 last:mr-0 first:pl-4 sm:first:pl-6 lg:first:pl-8 last:pr-4 sm:last:pr-6 lg:last:pr-8">
                                                <a class="block pb-3 text-violet-500 whitespace-nowrap border-b-2 border-violet-500" href="#0">Umum</a>
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
                                                                <div class="text-sm font-medium text-violet-500 hover:text-violet-600 dark:hover:text-violet-400">
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
                                            <div>
                                                <h2 class="text-gray-800 dark:text-gray-100 font-semibold mb-2">Acara Akan Datang</h2>
                                                <div class="bg-white dark:bg-gray-900 p-4 border border-gray-200 dark:border-gray-700/60 rounded-lg shadow-xs">
                                                    <ul class="space-y-3">

                                                        <li class="sm:flex sm:items-center sm:justify-between">
                                                            <div class="font-medium text-gray-800 dark:text-gray-100">Belum ada</div>
                                                        </li>

                                                        <!-- Item -->
                                                        {{-- <li class="sm:flex sm:items-center sm:justify-between">
                                                            <div class="sm:grow flex items-center text-sm">
                                                                <!-- Icon -->
                                                                <div class="w-8 h-8 rounded-full shrink-0 bg-yellow-500 my-2 mr-3">
                                                                    <svg class="w-8 h-8 fill-current text-yellow-50" viewBox="0 0 32 32">
                                                                        <path d="M21 14a.75.75 0 0 1-.75-.75 1.5 1.5 0 0 0-1.5-1.5.75.75 0 1 1 0-1.5 1.5 1.5 0 0 0 1.5-1.5.75.75 0 1 1 1.5 0 1.5 1.5 0 0 0 1.5 1.5.75.75 0 1 1 0 1.5 1.5 1.5 0 0 0-1.5 1.5.75.75 0 0 1-.75.75Zm-7 10a1 1 0 0 1-1-1 4 4 0 0 0-4-4 1 1 0 0 1 0-2 4 4 0 0 0 4-4 1 1 0 0 1 2 0 4 4 0 0 0 4 4 1 1 0 0 1 0 2 4 4 0 0 0-4 4 1 1 0 0 1-1 1Z" />
                                                                    </svg>
                                                                </div>
                                                                <!-- Position -->
                                                                <div>
                                                                    <div class="font-medium text-gray-800 dark:text-gray-100">Senior Product Designer</div>
                                                                    <div class="flex flex-nowrap items-center space-x-2 whitespace-nowrap">
                                                                        <div>Remote</div>
                                                                        <div class="text-gray-400 dark:text-gray-600">·</div>
                                                                        <div>April, 2020 - Today</div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- Tags -->
                                                            <div class="sm:ml-2 mt-2 sm:mt-0">
                                                                <ul class="flex flex-wrap sm:justify-end -m-1">
                                                                    <li class="m-1">
                                                                        <button class="inline-flex items-center justify-center text-xs font-medium leading-5 rounded-full px-2.5 py-0.5 border border-gray-200 dark:border-gray-700/60 hover:border-gray-300 dark:hover:border-gray-600 shadow-xs bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 transition">Marketing</button>
                                                                    </li>
                                                                    <li class="m-1">
                                                                        <button class="inline-flex items-center justify-center text-xs font-medium leading-5 rounded-full px-2.5 py-0.5 border border-gray-200 dark:border-gray-700/60 hover:border-gray-300 dark:hover:border-gray-600 shadow-xs bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 transition">+4</button>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </li> --}}

                                                    </ul>
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
                                                <h3 class="font-medium text-gray-800 dark:text-gray-100">Kontak</h3>
                                                <div>+62</div>
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
