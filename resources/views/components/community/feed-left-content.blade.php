<div class="w-full md:w-60 mb-8 md:mb-0">
    <div class="md:sticky md:top-16 md:h-[calc(100dvh-64px)] md:overflow-x-hidden md:overflow-y-auto no-scrollbar">
        <div class="md:py-8">

            <!-- Links -->
            <div class="flex flex-nowrap overflow-x-scroll no-scrollbar md:block md:overflow-auto px-4 md:space-y-3 -mx-4">
                <!-- Group 1 -->
                <div>
                    <div class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase mb-3 md:sr-only">Menu</div>
                    <ul class="flex flex-nowrap md:block mr-3 md:mr-0">
                        <li class="mr-0.5 md:mr-0 md:mb-0.5">
                            <a class="flex items-center px-2.5 py-2 rounded-lg whitespace-nowrap @if(request()->routeIs('beranda')) bg-white dark:bg-gray-800 @endif" href="{{ route('beranda') }}">
                                <svg class="shrink-0 @if(request()->routeIs('beranda')) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif mr-2" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M5 12l-2 0l9 -9l9 9l-2 0" />
                                    <path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" />
                                    <path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" />
                                </svg>
                                <span class="text-sm font-medium @if(request()->routeIs('beranda')) text-violet-500 @else text-gray-600 dark:text-gray-300 @endif">Beranda</span>
                            </a>
                        </li>
                        <li class="mr-0.5 md:mr-0 md:mb-0.5">
                             <a class="flex items-center px-2.5 py-2 rounded-lg whitespace-nowrap @if(request()->routeIs('majelis*')) bg-white dark:bg-gray-800 @endif" href="{{ route('majelis-list') }}">
                                <svg class="shrink-0 @if(request()->routeIs('majelis*')) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif mr-2" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M12 5c-3.333 0 -6 3 -6 6v9h12v-9c0 -3 -2.667 -6 -6 -6z" />
                                    <path d="M12 3l0 2" />
                                    <path d="M10 20v-5c0 -1.333 1.333 -2 2 -2s2 .667 2 2v5" />
                                </svg>
                                <span class="text-sm font-medium @if(request()->routeIs('majelis*')) text-violet-500 @else text-gray-600 dark:text-gray-300 @endif">Majelis</span>
                            </a>
                        </li>
                        <li class="mr-0.5 md:mr-0 md:mb-0.5">
                             <a class="flex items-center px-2.5 py-2 rounded-lg whitespace-nowrap @if(request()->routeIs('jadwal-majelis-list')) bg-white dark:bg-gray-800 @endif" href="{{ route('jadwal-majelis-list') }}">
                                <svg class="shrink-0 @if(request()->routeIs('jadwal-majelis-list')) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif mr-2" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M11.795 21h-6.795a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v4" />
                                    <path d="M18 18m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" />
                                    <path d="M15 3v4" />
                                    <path d="M7 3v4" />
                                    <path d="M3 11h16" />
                                    <path d="M18 16.5v1.5l.5 .5" />
                                </svg>
                                <span class="text-sm font-medium @if(request()->routeIs('jadwal-majelis-list')) text-violet-500 @else text-gray-600 dark:text-gray-300 @endif">Jadwal Majelis</span>
                            </a>
                        </li>
                        <li class="mr-0.5 md:mr-0 md:mb-0.5">
                             <a class="flex items-center px-2.5 py-2 rounded-lg whitespace-nowrap @if(request()->routeIs('guru*')) bg-white dark:bg-gray-800 @endif" href="{{ route('guru-list') }}">
                                <svg class="shrink-0 @if(request()->routeIs('guru*')) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif mr-2" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M22 9l-10 -4l-10 4l10 4l10 -4v6" />
                                    <path d="M6 10.6v5.4a6 3 0 0 0 12 0v-5.4" />
                                </svg>
                                <span class="text-sm font-medium @if(request()->routeIs('guru*')) text-violet-500 @else text-gray-600 dark:text-gray-300 @endif">Guru</span>
                            </a>
                        </li>
                        <li class="mr-0.5 md:mr-0 md:mb-0.5">
                             <a class="flex items-center px-2.5 py-2 rounded-lg whitespace-nowrap @if(request()->routeIs('video*')) bg-white dark:bg-gray-800 @endif" href="{{ route('video-list') }}">
                                <svg class="shrink-0 @if(request()->routeIs('video*')) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif mr-2" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M2 8a4 4 0 0 1 4 -4h12a4 4 0 0 1 4 4v8a4 4 0 0 1 -4 4h-12a4 4 0 0 1 -4 -4v-8z" />
                                    <path d="M10 9l5 3l-5 3z" />
                                </svg>
                                <span class="text-sm font-medium @if(request()->routeIs('video*')) text-violet-500 @else text-gray-600 dark:text-gray-300 @endif">Video</span>
                            </a>
                        </li>
                        <li class="mr-0.5 md:mr-0 md:mb-0.5">
                             <a class="flex items-center px-2.5 py-2 rounded-lg whitespace-nowrap @if(request()->routeIs('event*')) bg-white dark:bg-gray-800 @endif" href="{{ route('event-list') }}">
                                <svg class="shrink-0 @if(request()->routeIs('event*')) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif mr-2" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M15 5l0 2" />
                                    <path d="M15 11l0 2" />
                                    <path d="M15 17l0 2" />
                                    <path d="M5 5h14a2 2 0 0 1 2 2v3a2 2 0 0 0 0 4v3a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-3a2 2 0 0 0 0 -4v-3a2 2 0 0 1 2 -2" />
                                </svg>
                                <span class="text-sm font-medium @if(request()->routeIs('event*')) text-violet-500 @else text-gray-600 dark:text-gray-300 @endif">Acara</span>
                            </a>
                        </li>
                        <li class="mr-0.5 md:mr-0 md:mb-0.5">
                             <a class="flex items-center px-2.5 py-2 rounded-lg whitespace-nowrap @if(request()->routeIs('wirid*')) bg-white dark:bg-gray-800 @endif" href="{{ route('wirid-list') }}">
                                <svg class="shrink-0 @if(request()->routeIs('wirid*')) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif mr-2" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M19 4v16h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12z" />
                                    <path d="M19 16h-12a2 2 0 0 0 -2 2" />
                                    <path d="M9 8h6" />
                                </svg>
                                <span class="text-sm font-medium @if(request()->routeIs('wirid*')) text-violet-500 @else text-gray-600 dark:text-gray-300 @endif">Amalan</span>
                            </a>
                        </li>
                        <li class="mr-0.5 md:mr-0 md:mb-0.5">
                             <a class="flex items-center px-2.5 py-2 rounded-lg whitespace-nowrap @if(request()->routeIs('manaqib*')) bg-white dark:bg-gray-800 @endif" href="{{ route('manaqib-list') }}">
                                <svg class="shrink-0 @if(request()->routeIs('manaqib*')) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif mr-2" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" />
                                    <path d="M12 10m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
                                    <path d="M6.168 18.849a4 4 0 0 1 3.832 -2.849h4a4 4 0 0 1 3.834 2.855" />
                                </svg>
                                <span class="text-sm font-medium @if(request()->routeIs('manaqib*')) text-violet-500 @else text-gray-600 dark:text-gray-300 @endif">Manaqib</span>
                            </a>
                        </li>
                        <li class="mr-0.5 md:mr-0 md:mb-0.5">
                             <a class="flex items-center px-2.5 py-2 rounded-lg whitespace-nowrap @if(request()->routeIs('pustaka*')) bg-white dark:bg-gray-800 @endif" href="{{ route('pustaka-list') }}">
                                <svg class="shrink-0 @if(request()->routeIs('pustaka*')) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif mr-2" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M3 19a9 9 0 0 1 9 0a9 9 0 0 1 9 0" />
                                    <path d="M3 6a9 9 0 0 1 9 0a9 9 0 0 1 9 0" />
                                    <path d="M3 6l0 13" />
                                    <path d="M12 6l0 13" />
                                    <path d="M21 6l0 13" />
                                </svg>
                                <span class="text-sm font-medium @if(request()->routeIs('pustaka*')) text-violet-500 @else text-gray-600 dark:text-gray-300 @endif">Pustaka</span>
                            </a>
                        </li>
                        <li class="mr-0.5 md:mr-0 md:mb-0.5">
                             <a class="flex items-center px-2.5 py-2 rounded-lg whitespace-nowrap @if(request()->routeIs('tulisan*')) bg-white dark:bg-gray-800 @endif" href="{{ route('tulisan.list') }}">
                                <svg class="shrink-0 @if(request()->routeIs('tulisan*')) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif mr-2" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M3 19a9 9 0 0 1 9 0a9 9 0 0 1 9 0" />
                                    <path d="M3 6a9 9 0 0 1 9 0a9 9 0 0 1 9 0" />
                                    <path d="M3 6l0 13" />
                                    <path d="M12 6l0 13" />
                                    <path d="M21 6l0 13" />
                                </svg>
                                <span class="text-sm font-medium @if(request()->routeIs('tulisan*')) text-violet-500 @else text-gray-600 dark:text-gray-300 @endif">Tulisan</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</div>