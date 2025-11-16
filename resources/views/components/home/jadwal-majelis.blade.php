@foreach ($schedules as $schedule)
    <div class="col-span-full sm:col-span-6 xl:col-span-4 bg-white dark:bg-gray-800 shadow-xs rounded-xl">
        <div class="flex flex-col h-full">
            <!-- Card top -->
            <div class="grow p-5">
                <div class="flex justify-between items-start">
                    <!-- Image + name -->
                    <header>                
                        <div class="flex mb-2">
                            <a class="relative inline-flex items-start mr-5" href="#0">
                                @if($schedule->teacher->foto != null)
                                    <img class="rounded-full" src="{{ asset('images/' . $schedule->teacher->foto) }}" width="64" height="64" alt="{{ $schedule->teacher->name }}" />
                                @else
                                    <img class="rounded-full" src="{{ asset('images/user-64-06.jpg') }}" width="64" height="64" alt="{{ $schedule->teacher->name }}" />
                                @endif
                            </a>
                            <div class="mt-1 pr-1">
                                <a class="inline-flex text-gray-800 dark:text-gray-100 hover:text-gray-900 dark:hover:text-white" href="#0">
                                    <h2 class="text-xl leading-snug justify-center font-semibold">{{ $schedule->teacher->name }}</h2>
                                </a>
                                <div>{{ $schedule->nama_jadwal }}</div>
                            </div>
                        </div>
                    </header>          
                </div>
                <!-- Bio -->
                <div class="mt-2">
                    <div class="text-sm">{{ $schedule->deskripsi }}</div>
                </div>
            </div>
            <!-- Card footer -->
            <div class="border-t border-gray-100 dark:border-gray-700/60">
                <div class="flex divide-x divide-gray-100 dark:divide-gray-700/60">
                    <a class="block flex-1 text-center text-sm text-violet-500 hover:text-violet-600 dark:hover:text-violet-400 font-medium px-3 py-4" href="{{ $schedule->assembly->maps }}">
                        <div class="flex items-center justify-center">
                            <svg class="mr-2" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 24 24"> <path fill-rule="evenodd" clip-rule="evenodd" d="M13 11V19H11V11H13Z" fill="rgba(132, 112, 255, 1)"></path> <path fill-rule="evenodd" clip-rule="evenodd" d="M6 7C6 3.68629 8.68629 1 12 1C15.3137 1 18 3.68629 18 7C18 10.3137 15.3137 13 12 13C8.68629 13 6 10.3137 6 7Z" fill="rgba(132, 112, 255, 1)"></path> <path fill-rule="evenodd" clip-rule="evenodd" d="M8.1893 15.7653L7.21204 15.9774C5.78358 16.2873 4.65768 16.7189 3.91598 17.1909C3.13673 17.6867 3 18.0745 3 18.2492C3 18.3785 3.06629 18.6213 3.44935 18.961C3.83125 19.2997 4.44093 19.6504 5.28013 19.9652C6.95116 20.592 9.32677 21.0001 12 21.0001C14.6732 21.0001 17.0488 20.592 18.7199 19.9652C19.5591 19.6504 20.1687 19.2997 20.5507 18.961C20.9337 18.6213 21 18.3785 21 18.2492C21 18.0745 20.8633 17.6867 20.084 17.1909C19.3423 16.7189 18.2164 16.2873 16.788 15.9774L15.8107 15.7653L16.2348 13.8108L17.212 14.0228C18.7726 14.3614 20.1467 14.8602 21.1577 15.5035C22.1312 16.123 23 17.0355 23 18.2492C23 19.1557 22.5066 19.8996 21.8776 20.4574C21.2475 21.0162 20.3927 21.4738 19.4223 21.8378C17.474 22.5686 14.8496 23.0001 12 23.0001C9.15039 23.0001 6.52599 22.5686 4.57773 21.8378C3.60729 21.4738 2.7525 21.0162 2.12235 20.4574C1.49336 19.8996 1 19.1557 1 18.2492C1 17.0355 1.86876 16.123 2.84227 15.5035C3.85331 14.8602 5.22741 14.3614 6.78796 14.0228L7.76522 13.8108L8.1893 15.7653Z" fill="rgba(132, 112, 255, 1)" data-color="color-2"></path> </svg>
                            <span>{{ $schedule->assembly->nama_majelis }}</span>
                        </div>
                    </a>
                    <a class="block flex-1 text-center text-sm text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-200 font-medium px-3 py-4 group" href="#">
                        <div class="flex items-center justify-center">
                        <svg class="mr-2" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 24 24"><rect x="6" width="2" height="5" fill="rgba(75, 85, 99, 1)" stroke-width="0" data-color="color-2"></rect><rect x="16" width="2" height="5" fill="rgba(75, 85, 99, 1)" stroke-width="0" data-color="color-2"></rect><path d="m20,3H4c-1.654,0-3,1.346-3,3v12c0,1.654,1.346,3,3,3h16c1.654,0,3-1.346,3-3V6c0-1.654-1.346-3-3-3Zm0,16H4c-.551,0-1-.448-1-1v-9h18v9c0,.552-.449,1-1,1Z" stroke-width="0" fill="rgba(75, 85, 99, 1)"></path></svg>
                            <span>{{ $schedule->hari }}, {{ $schedule->waktu }}</span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endforeach