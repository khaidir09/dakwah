<div class="flex flex-nowrap overflow-x-scroll no-scrollbar md:block md:overflow-auto px-3 py-6 border-b md:border-b-0 md:border-r border-gray-200 dark:border-gray-700/60 min-w-60 md:space-y-3">
    <!-- Group 1 -->
    <div>
        <ul class="flex flex-nowrap md:block mr-3 md:mr-0">
            <li class="mr-0.5 md:mr-0 md:mb-0.5">
                <a class="flex items-center px-2.5 py-2 rounded-lg whitespace-nowrap @if(Route::is('pengaturan-akun')){{ 'bg-linear-to-r from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }}@endif" href="{{ route('pengaturan-akun') }}">
                    <svg class="shrink-0 fill-current mr-2 @if(Route::is('pengaturan-akun')){{ 'text-violet-500 dark:text-violet-400' }}@else{{ 'text-gray-400 dark:text-gray-500' }}@endif" width="16" height="16" viewBox="0 0 16 16">
                        <path d="M8 9a4 4 0 1 1 0-8 4 4 0 0 1 0 8Zm0-2a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm-5.143 7.91a1 1 0 1 1-1.714-1.033A7.996 7.996 0 0 1 8 10a7.996 7.996 0 0 1 6.857 3.877 1 1 0 1 1-1.714 1.032A5.996 5.996 0 0 0 8 12a5.996 5.996 0 0 0-5.143 2.91Z" />
                    </svg>
                    <span class="text-sm font-medium @if(Route::is('pengaturan-akun')){{ 'text-violet-500 dark:text-violet-400' }}@else{{ 'text-gray-600 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-200' }}@endif">Profil</span>
                </a>
            </li>
            @if(Route::is('majelis.onboarding'))
            <li class="mr-0.5 md:mr-0 md:mb-0.5">
                <a class="flex items-center px-2.5 py-2 rounded-lg whitespace-nowrap bg-linear-to-r from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]" href="#">
                    <svg class="shrink-0 fill-current mr-2 text-violet-500 dark:text-violet-400" width="16" height="16" viewBox="0 0 16 16">
                        <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5z"/>
                    </svg>
                    <span class="text-sm font-medium text-violet-500 dark:text-violet-400">Registrasi Majelis</span>
                </a>
            </li>
            @endif
            @if (Auth::user()->assembly != null)
                <li class="mr-0.5 md:mr-0 md:mb-0.5">
                    <a class="flex items-center px-2.5 py-2 rounded-lg whitespace-nowrap @if(Route::is('kelola-majelis.edit')){{ 'bg-linear-to-r from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }}@endif" href="{{ route('kelola-majelis.edit', Auth::user()->assembly->id) }}">
                        <svg class="shrink-0 @if(request()->routeIs('kelola-majelis.edit')) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif mr-2" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M12 5c-3.333 0 -6 3 -6 6v9h12v-9c0 -3 -2.667 -6 -6 -6z" />
                            <path d="M12 3l0 2" />
                            <path d="M10 20v-5c0 -1.333 1.333 -2 2 -2s2 .667 2 2v5" />
                        </svg>
                        <span class="text-sm font-medium @if(Route::is('kelola-majelis.edit')){{ 'text-violet-500 dark:text-violet-400' }}@else{{ 'text-gray-600 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-200' }}@endif">Majelis</span>
                    </a>
                </li>
                <li class="mr-0.5 md:mr-0 md:mb-0.5">
                    <a class="flex items-center px-2.5 py-2 rounded-lg whitespace-nowrap @if(Route::is('kelola-jadwal-majelis*')){{ 'bg-linear-to-r from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }}@endif" href="{{ route('kelola-jadwal-majelis')}}">
                        <svg class="shrink-0 @if(request()->routeIs('kelola-jadwal-majelis')) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif mr-2" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M11.795 21h-6.795a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v4" />
                            <path d="M18 18m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" />
                            <path d="M15 3v4" />
                            <path d="M7 3v4" />
                            <path d="M3 11h16" />
                            <path d="M18 16.5v1.5l.5 .5" />
                        </svg>
                        <span class="text-sm font-medium @if(Route::is('kelola-jadwal-majelis*')){{ 'text-violet-500 dark:text-violet-400' }}@else{{ 'text-gray-600 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-200' }}@endif">Jadwal Majelis</span>
                    </a>
                </li>
                <li class="mr-0.5 md:mr-0 md:mb-0.5">
                    <a class="flex items-center px-2.5 py-2 rounded-lg whitespace-nowrap @if(Route::is('kelola-acara-majelis*')){{ 'bg-linear-to-r from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }}@endif" href="{{ route('kelola-acara-majelis')}}">
                        <svg class="shrink-0 @if(request()->routeIs('kelola-acara-majelis*')) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif mr-2" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M15 5l0 2" />
                            <path d="M15 11l0 2" />
                            <path d="M15 17l0 2" />
                            <path d="M5 5h14a2 2 0 0 1 2 2v3a2 2 0 0 0 0 4v3a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-3a2 2 0 0 0 0 -4v-3a2 2 0 0 1 2 -2" />
                        </svg>
                        <span class="text-sm font-medium @if(Route::is('kelola-acara-majelis*')){{ 'text-violet-500 dark:text-violet-400' }}@else{{ 'text-gray-600 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-200' }}@endif">Acara Majelis</span>
                    </a>
                </li>
                {{-- show if the assembly tipe is mesjid/musholla/langgar --}}
                @if(in_array(Auth::user()->assembly->tipe, ['Mesjid', 'Musholla', 'Langgar']))
                    <li class="mr-0.5 md:mr-0 md:mb-0.5">
                        <a class="flex items-center px-2.5 py-2 rounded-lg whitespace-nowrap @if(Route::is('kelola-ramadhan*')){{ 'bg-linear-to-r from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }}@endif" href="{{ route('kelola-ramadhan.index')}}">
                            <svg class="shrink-0 @if(request()->routeIs('kelola-ramadhan*')) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif mr-2" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M15 5l0 2" />
                                <path d="M15 11l0 2" />
                                <path d="M15 17l0 2" />
                                <path d="M5 5h14a2 2 0 0 1 2 2v3a2 2 0 0 0 0 4v3a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-3a2 2 0 0 0 0 -4v-3a2 2 0 0 1 2 -2" />
                            </svg>
                            <span class="text-sm font-medium @if(Route::is('kelola-ramadhan*')){{ 'text-violet-500 dark:text-violet-400' }}@else{{ 'text-gray-600 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-200' }}@endif">Jadwal Ramadhan</span>
                        </a>
                    </li>
                @endif
            @endif
            @if(Auth::user()->foundations->isNotEmpty())
                @foreach(Auth::user()->foundations as $foundation)
                    <li class="mr-0.5 md:mr-0 md:mb-0.5">
                        <a class="flex items-center px-2.5 py-2 rounded-lg whitespace-nowrap @if(request()->routeIs('kelola-yayasan.edit') && request()->route('id') == $foundation->id){{ 'bg-linear-to-r from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }}@endif" href="{{ route('kelola-yayasan.edit', $foundation->id) }}">
                            <svg class="shrink-0 @if(request()->routeIs('kelola-yayasan.edit') && request()->route('id') == $foundation->id) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif mr-2" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M3 21l18 0" />
                                <path d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16" />
                                <path d="M9 21v-4a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v4" />
                                <path d="M10 9l4 0" />
                                <path d="M12 7l0 4" />
                            </svg>
                            <span class="text-sm font-medium @if(request()->routeIs('kelola-yayasan.edit') && request()->route('id') == $foundation->id){{ 'text-violet-500 dark:text-violet-400' }}@else{{ 'text-gray-600 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-200' }}@endif">
                                Kelola Yayasan
                            </span>
                        </a>
                    </li>
                @endforeach

                <li class="mr-0.5 md:mr-0 md:mb-0.5">
                    <a class="flex items-center px-2.5 py-2 rounded-lg whitespace-nowrap @if(Route::is('kelola-artikel*')){{ 'bg-linear-to-r from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }}@endif" href="{{ route('kelola-artikel.index') }}">
                        <svg class="shrink-0 @if(request()->routeIs('kelola-artikel*')) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif mr-2" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M3 19c3.333 0 6 3 6 6" />
                            <path d="M3 13c6.667 0 10 3.333 10 10" />
                            <path d="M3 7c10 0 14 3.333 14 10" />
                            <path d="M3 3c13.333 0 18 3.333 18 10" />
                        </svg>
                        <span class="text-sm font-medium @if(Route::is('kelola-artikel*')){{ 'text-violet-500 dark:text-violet-400' }}@else{{ 'text-gray-600 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-200' }}@endif">Artikel Ilmiah</span>
                    </a>
                </li>
            @endif
            <li class="mr-0.5 md:mr-0 md:mb-0.5">
                <a class="flex items-center px-2.5 py-2 rounded-lg whitespace-nowrap @if(Route::is('notifications')){{ 'bg-linear-to-r from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }}@endif" href="#">
                    <svg class="shrink-0 fill-current mr-2 @if(Route::is('notifications')){{ 'text-violet-500 dark:text-violet-400' }}@else{{ 'text-gray-400 dark:text-gray-500' }}@endif" width="16" height="16" viewBox="0 0 16 16">
                        <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2zM8 1.918l-.797.161A4.002 4.002 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4.002 4.002 0 0 0-3.203-3.92L8 1.917z"/>
                    </svg>
                    <span class="text-sm font-medium @if(Route::is('notifications')){{ 'text-violet-500 dark:text-violet-400' }}@else{{ 'text-gray-600 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-200' }}@endif">Notifikasi</span>
                </a>
            </li>
            {{-- <li class="mr-0.5 md:mr-0 md:mb-0.5">
                <a class="flex items-center px-2.5 py-2 rounded-lg whitespace-nowrap @if(Route::is('plans')){{ 'bg-linear-to-r from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }}@endif" href="#">                
                    <svg class="shrink-0 fill-current mr-2 @if(Route::is('plans')){{ 'text-violet-500 dark:text-violet-400' }}@else{{ 'text-gray-400 dark:text-gray-500' }}@endif" width="16" height="16" viewBox="0 0 16 16">
                        <path d="M5 9a1 1 0 1 1 0-2h6a1 1 0 0 1 0 2H5ZM1 4a1 1 0 1 1 0-2h14a1 1 0 0 1 0 2H1Zm0 10a1 1 0 0 1 0-2h14a1 1 0 0 1 0 2H1Z" />
                    </svg>
                    <span class="text-sm font-medium @if(Route::is('plans')){{ 'text-violet-500 dark:text-violet-400' }}@else{{ 'text-gray-600 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-200' }}@endif">Paket</span>
                </a>
            </li>
            <li class="mr-0.5 md:mr-0 md:mb-0.5">
                <a class="flex items-center px-2.5 py-2 rounded-lg whitespace-nowrap @if(Route::is('billing')){{ 'bg-linear-to-r from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }}@endif" href="#">
                    <svg class="shrink-0 fill-current mr-2 @if(Route::is('billing')){{ 'text-violet-500 dark:text-violet-400' }}@else{{ 'text-gray-400 dark:text-gray-500' }}@endif" width="16" height="16" viewBox="0 0 16 16">
                        <path d="M0 4a4 4 0 0 1 4-4h8a4 4 0 0 1 4 4v8a4 4 0 0 1-4 4H4a4 4 0 0 1-4-4V4Zm2 0v8a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Zm9 1a1 1 0 0 1 0 2H5a1 1 0 1 1 0-2h6Zm0 4a1 1 0 0 1 0 2H5a1 1 0 1 1 0-2h6Z" />
                    </svg>
                    <span class="text-sm font-medium @if(Route::is('billing')){{ 'text-violet-500 dark:text-violet-400' }}@else{{ 'text-gray-600 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-200' }}@endif">Pembayaran</span>
                </a>
            </li> --}}
            <li class="mr-0.5 md:mr-0 md:mb-0.5">
                <a class="flex items-center px-2.5 py-2 rounded-lg whitespace-nowrap @if(Route::is('feedback')){{ 'bg-linear-to-r from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }}@endif" href="#">
                    <svg class="shrink-0 fill-current mr-2 @if(Route::is('feedback')){{ 'text-violet-500 dark:text-violet-400' }}@else{{ 'text-gray-400 dark:text-gray-500' }}@endif" width="16" height="16" viewBox="0 0 16 16">
                        <path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/>
                    </svg>
                    <span class="text-sm font-medium @if(Route::is('feedback')){{ 'text-violet-500 dark:text-violet-400' }}@else{{ 'text-gray-600 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-200' }}@endif">Berikan Ulasan</span>
                </a>
            </li>
        </ul>
    </div>
</div>