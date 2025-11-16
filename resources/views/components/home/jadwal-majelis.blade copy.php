@foreach ($schedules as $schedule)
    <div class="col-span-full sm:col-span-6 xl:col-span-3 bg-white dark:bg-gray-800 shadow-xs rounded-xl">
        <div class="flex flex-col h-full">
            <!-- Card top -->
            <div class="grow p-5">
                <!-- Image + name -->
                <header>                
                    <div class="flex justify-center mb-2">
                        <a class="relative inline-flex items-start" href="#0">
                            <div class="absolute top-0 right-0 -mr-2 bg-white dark:bg-gray-700 rounded-full shadow-sm" aria-hidden="true">
                                <svg class="w-8 h-8 fill-current text-yellow-500" viewBox="0 0 32 32">
                                    <path d="M21 14.077a.75.75 0 01-.75-.75 1.5 1.5 0 00-1.5-1.5.75.75 0 110-1.5 1.5 1.5 0 001.5-1.5.75.75 0 111.5 0 1.5 1.5 0 001.5 1.5.75.75 0 010 1.5 1.5 1.5 0 00-1.5 1.5.75.75 0 01-.75.75zM14 24.077a1 1 0 01-1-1 4 4 0 00-4-4 1 1 0 110-2 4 4 0 004-4 1 1 0 012 0 4 4 0 004 4 1 1 0 010 2 4 4 0 00-4 4 1 1 0 01-1 1z" />
                                </svg>
                            </div>
                            @if($schedule->teacher->foto != null)
                                <img class="rounded-full" src="{{ asset('images/' . $schedule->teacher->foto) }}" width="64" height="64" alt="{{ $schedule->teacher->name }}" />
                            @else
                                <img class="rounded-full" src="{{ asset('images/user-64-06.jpg') }}" width="64" height="64" alt="{{ $schedule->teacher->name }}" />
                            @endif
                        </a>
                    </div>
                    <div class="text-center">
                        <a class="inline-flex text-gray-800 dark:text-gray-100 hover:text-gray-900 dark:hover:text-white" href="#0">
                            <h2 class="text-xl leading-snug justify-center font-semibold">{{ $schedule->teacher->name }}</h2>
                        </a>
                    </div>
                    <div class="flex justify-center items-center text-xs text-gray-500">{{ $schedule->hari }}, {{ $schedule->waktu }}</div>
                </header>
                <!-- Bio -->
                <div class="text-center mt-2">
                    <div class="text-sm">{{ $schedule->deskripsi}}</div>
                </div>
            </div>
            <!-- Card footer -->
            <div class="border-t">
                <a class="block text-center text-sm text-violet-500 hover:text-violet-600 dark:hover:text-violet-400 font-medium px-3 py-4" href="{{ $schedule->assembly->maps }}">
                    <div class="flex items-center justify-center">
                        <svg class="fill-current shrink-0 mr-2" width="16" height="16" viewBox="0 0 16 16">
                            <path d="M8 0C3.6 0 0 3.1 0 7s3.6 7 8 7h.6l5.4 2v-4.4c1.2-1.2 2-2.8 2-4.6 0-3.9-3.6-7-8-7zm4 10.8v2.3L8.9 12H8c-3.3 0-6-2.2-6-5s2.7-5 6-5 6 2.2 6 5c0 2.2-2 3.8-2 3.8z" />
                        </svg>
                        <span>Lokasi</span>
                    </div>
                </a>
            </div>
        </div>
    </div>
@endforeach