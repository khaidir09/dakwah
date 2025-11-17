@foreach ($assemblies as $assembly)
    <!-- Card 2 -->
    <div class="col-span-full md:col-span-6 lg:col-span-4 bg-white dark:bg-gray-800 shadow-xs rounded-xl overflow-hidden">
        <div class="flex flex-col h-full">
            <!-- Image -->
            <div class="relative">
                <img class="w-full" src="{{ asset('images/applications-image-22.jpg') }}" width="301" height="226" alt="Application 22" />
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
                        <div class="text-sm">{{ $assembly->alamat }}</div>
                    </header>
                </div>
                <!-- Rating and price -->
                <div class="flex flex-wrap justify-between items-center">
                    <!-- Rating -->
                    <div class="flex items-center space-x-2 mr-2">
                        <!-- Stars -->
                        <div class="flex space-x-1">
                            <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 24 24"><rect x="6" width="2" height="5" fill="rgba(132, 112, 255, 1)" stroke-width="0" data-color="color-2"></rect><rect x="16" width="2" height="5" fill="rgba(132, 112, 255, 1)" stroke-width="0" data-color="color-2"></rect><path d="m20,3H4c-1.654,0-3,1.346-3,3v12c0,1.654,1.346,3,3,3h16c1.654,0,3-1.346,3-3V6c0-1.654-1.346-3-3-3Zm0,16H4c-.551,0-1-.448-1-1v-9h18v9c0,.552-.449,1-1,1Z" stroke-width="0" fill="rgba(132, 112, 255, 1)"></path></svg>
                        </div>
                        <!-- Rate -->
                        <div class="inline-flex text-sm font-medium text-violet-600">{{ $assembly->schedule_count }} Jadwal Rutinan</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endforeach