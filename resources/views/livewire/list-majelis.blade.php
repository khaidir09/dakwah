<div>
    <!-- Search form -->
    <div class="mb-6">
        <form class="relative">
            <label for="feed-search-mobile" class="sr-only">Search</label>
            <input wire:model.live="search" id="feed-search-mobile" class="form-input w-full pl-9 bg-white dark:bg-gray-800" type="search" placeholder="Cari nama majelis/guru" />
            <button class="absolute inset-0 right-auto group" type="submit" aria-label="Search">
                <svg class="shrink-0 fill-current text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-400 ml-3 mr-2" width="16" height="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
                    <path d="M7 14c-3.86 0-7-3.14-7-7s3.14-7 7-7 7 3.14 7 7-3.14 7-7 7zM7 2C4.243 2 2 4.243 2 7s2.243 5 5 5 5-2.243 5-5-2.243-5-5-5z" />
                    <path d="M15.707 14.293L13.314 11.9a8.019 8.019 0 01-1.414 1.414l2.393 2.393a.997.997 0 001.414 0 .999.999 0 000-1.414z" />
                </svg>
            </button>
        </form>
    </div>
    <div class="grid grid-cols-12 gap-4">
        @foreach ($assemblies as $assembly)
            <!-- Card 2 -->
            <div class="col-span-full md:col-span-6 lg:col-span-4 bg-white dark:bg-gray-800 shadow-xs rounded-xl overflow-hidden">
                <div class="flex flex-col h-full">
                    <!-- Image -->
                    <div class="relative">
                        <img class="w-full" src="{{ $assembly->gambar_thumb_url }}" width="301" height="226" alt="Application 22" />
                        <!-- Like button -->
                        {{-- <button class="absolute top-0 right-0 mt-4 mr-4">
                            <div class="text-gray-100 bg-gray-900/60 rounded-full">
                                <span class="sr-only">Like</span>
                                <svg class="h-8 w-8 fill-current" viewBox="0 0 32 32">
                                    <path d="M22.682 11.318A4.485 4.485 0 0019.5 10a4.377 4.377 0 00-3.5 1.707A4.383 4.383 0 0012.5 10a4.5 4.5 0 00-3.182 7.682L16 24l6.682-6.318a4.5 4.5 0 000-6.364zm-1.4 4.933L16 21.247l-5.285-5A2.5 2.5 0 0112.5 12c1.437 0 2.312.681 3.5 2.625C17.187 12.681 18.062 12 19.5 12a2.5 2.5 0 011.785 4.251h-.003z" />
                                </svg>
                            </div>
                        </button> --}}
                    </div>
                    <!-- Card Content -->
                    <div class="grow flex flex-col p-5">
                        <!-- Card body -->
                        <div class="grow">
                            <header class="mb-2">
                                <a href="{{ route('majelis-detail', $assembly->id) }}">
                                    <h3 class="text-lg text-gray-800 dark:text-gray-100 font-semibold mb-1">{{ $assembly->nama_majelis }}</h3>
                                </a>
                                <div class="font-semibold">Pengasuh : {{ $assembly->teacher->name }}</div>
                                <div class="flex items-center space-x-2 mr-2 mt-2">
                                    <div class="flex space-x-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 24 24"> <path fill-rule="evenodd" clip-rule="evenodd" d="M13 11V19H11V11H13Z" fill="rgba(132, 112, 255, 1)"></path> <path fill-rule="evenodd" clip-rule="evenodd" d="M6 7C6 3.68629 8.68629 1 12 1C15.3137 1 18 3.68629 18 7C18 10.3137 15.3137 13 12 13C8.68629 13 6 10.3137 6 7Z" fill="rgba(132, 112, 255, 1)"></path> <path fill-rule="evenodd" clip-rule="evenodd" d="M8.1893 15.7653L7.21204 15.9774C5.78358 16.2873 4.65768 16.7189 3.91598 17.1909C3.13673 17.6867 3 18.0745 3 18.2492C3 18.3785 3.06629 18.6213 3.44935 18.961C3.83125 19.2997 4.44093 19.6504 5.28013 19.9652C6.95116 20.592 9.32677 21.0001 12 21.0001C14.6732 21.0001 17.0488 20.592 18.7199 19.9652C19.5591 19.6504 20.1687 19.2997 20.5507 18.961C20.9337 18.6213 21 18.3785 21 18.2492C21 18.0745 20.8633 17.6867 20.084 17.1909C19.3423 16.7189 18.2164 16.2873 16.788 15.9774L15.8107 15.7653L16.2348 13.8108L17.212 14.0228C18.7726 14.3614 20.1467 14.8602 21.1577 15.5035C22.1312 16.123 23 17.0355 23 18.2492C23 19.1557 22.5066 19.8996 21.8776 20.4574C21.2475 21.0162 20.3927 21.4738 19.4223 21.8378C17.474 22.5686 14.8496 23.0001 12 23.0001C9.15039 23.0001 6.52599 22.5686 4.57773 21.8378C3.60729 21.4738 2.7525 21.0162 2.12235 20.4574C1.49336 19.8996 1 19.1557 1 18.2492C1 17.0355 1.86876 16.123 2.84227 15.5035C3.85331 14.8602 5.22741 14.3614 6.78796 14.0228L7.76522 13.8108L8.1893 15.7653Z" fill="rgba(132, 112, 255, 1)" data-color="color-2"></path> </svg>
                                    </div>
                                    <div class="inline-flex text-sm font-medium text-violet-600">{{ $assembly->village->name }}, {{ $assembly->district->name }}</div>
                                </div>
                                <div class="flex items-center space-x-2 mr-2 mt-2">
                                    <!-- Stars -->
                                    <div class="flex space-x-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 24 24"><rect x="6" width="2" height="5" fill="rgba(132, 112, 255, 1)" stroke-width="0" data-color="color-2"></rect><rect x="16" width="2" height="5" fill="rgba(132, 112, 255, 1)" stroke-width="0" data-color="color-2"></rect><path d="m20,3H4c-1.654,0-3,1.346-3,3v12c0,1.654,1.346,3,3,3h16c1.654,0,3-1.346,3-3V6c0-1.654-1.346-3-3-3Zm0,16H4c-.551,0-1-.448-1-1v-9h18v9c0,.552-.449,1-1,1Z" stroke-width="0" fill="rgba(132, 112, 255, 1)"></path></svg>
                                    </div>
                                    <!-- Rate -->
                                    <div class="inline-flex text-sm font-medium text-violet-600">{{ $assembly->schedule->count() }} Jadwal Rutinan</div>
                                </div>
                            </header>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-8">
        {{ $assemblies->links() }}
    </div>
</div>