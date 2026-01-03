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
            @if (Auth::user()->assembly != null)
                <li class="mr-0.5 md:mr-0 md:mb-0.5">
                    <a class="flex items-center px-2.5 py-2 rounded-lg whitespace-nowrap @if(Route::is('kelola-majelis.edit')){{ 'bg-linear-to-r from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }}@endif" href="{{ route('kelola-majelis.edit', Auth::user()->assembly->id) }}">
                        <svg class="shrink-0 fill-current mr-2 @if(Route::is('kelola-majelis.edit')){{ 'text-violet-500 dark:text-violet-400' }}@else{{ 'text-gray-400 dark:text-gray-500' }}@endif" width="16" height="16" viewBox="0 0 16 16">
                        <path d="M11.92 6.851c.044-.027.09-.05.137-.07.481-.275.758-.68.908-1.256.126-.55.169-.81.357-2.058.075-.498.144-.91.217-1.264-4.122.75-7.087 2.984-9.12 6.284a18.087 18.087 0 0 0-1.985 4.585 17.07 17.07 0 0 0-.354 1.506c-.05.265-.076.448-.086.535a1 1 0 0 1-1.988-.226c.056-.49.209-1.312.502-2.357a20.063 20.063 0 0 1 2.208-5.09C5.31 3.226 9.306.494 14.913.004a1 1 0 0 1 .954 1.494c-.237.414-.375.993-.567 2.267-.197 1.306-.244 1.586-.392 2.235-.285 1.094-.789 1.853-1.552 2.363-.748 3.816-3.976 5.06-8.515 4.326a1 1 0 0 1 .318-1.974c2.954.477 4.918.025 5.808-1.556-.628.085-1.335.121-2.127.121a1 1 0 1 1 0-2c1.458 0 2.434-.116 3.08-.429Z" />
                        </svg>
                        <span class="text-sm font-medium @if(Route::is('kelola-majelis.edit')){{ 'text-violet-500 dark:text-violet-400' }}@else{{ 'text-gray-600 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-200' }}@endif">Majelis</span>
                    </a>
                </li>
                <li class="mr-0.5 md:mr-0 md:mb-0.5">
                    <a class="flex items-center px-2.5 py-2 rounded-lg whitespace-nowrap @if(Route::is('kelola-jadwal-majelis*')){{ 'bg-linear-to-r from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }}@endif" href="{{ route('kelola-jadwal-majelis')}}">
                        <svg class="shrink-0 fill-current mr-2 @if(Route::is('kelola-jadwal-majelis*')){{ 'text-violet-500 dark:text-violet-400' }}@else{{ 'text-gray-400 dark:text-gray-500' }}@endif" width="16" height="16" viewBox="0 0 16 16">
                        <path d="M5 4a1 1 0 0 0 0 2h6a1 1 0 1 0 0-2H5Z" />
                        <path d="M4 0a4 4 0 0 0-4 4v8a4 4 0 0 0 4 4h8a4 4 0 0 0 4-4V4a4 4 0 0 0-4-4H4ZM2 4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V4Z" />
                        </svg>
                        <span class="text-sm font-medium @if(Route::is('kelola-jadwal-majelis*')){{ 'text-violet-500 dark:text-violet-400' }}@else{{ 'text-gray-600 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-200' }}@endif">Jadwal Majelis</span>
                    </a>
                </li>
                <li class="mr-0.5 md:mr-0 md:mb-0.5">
                    <a class="flex items-center px-2.5 py-2 rounded-lg whitespace-nowrap @if(Route::is('kelola-acara-majelis*')){{ 'bg-linear-to-r from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }}@endif" href="{{ route('kelola-acara-majelis')}}">
                        <svg class="shrink-0 fill-current mr-2 @if(Route::is('kelola-acara-majelis*')){{ 'text-violet-500 dark:text-violet-400' }}@else{{ 'text-gray-400 dark:text-gray-500' }}@endif" width="16" height="16" viewBox="0 0 16 16">
                        <path d="M5 4a1 1 0 0 0 0 2h6a1 1 0 1 0 0-2H5Z" />
                        <path d="M4 0a4 4 0 0 0-4 4v8a4 4 0 0 0 4 4h8a4 4 0 0 0 4-4V4a4 4 0 0 0-4-4H4ZM2 4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V4Z" />
                        </svg>
                        <span class="text-sm font-medium @if(Route::is('kelola-acara-majelis*')){{ 'text-violet-500 dark:text-violet-400' }}@else{{ 'text-gray-600 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-200' }}@endif">Acara Majelis</span>
                    </a>
                </li>
            @endif
            <li class="mr-0.5 md:mr-0 md:mb-0.5">
                <a class="flex items-center px-2.5 py-2 rounded-lg whitespace-nowrap @if(Route::is('notifications')){{ 'bg-linear-to-r from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }}@endif" href="{{ route('notifications') }}">
                    <svg class="shrink-0 fill-current mr-2 @if(Route::is('notifications')){{ 'text-violet-500 dark:text-violet-400' }}@else{{ 'text-gray-400 dark:text-gray-500' }}@endif" width="16" height="16" viewBox="0 0 16 16">
                        <path d="M7 0a7 7 0 0 0-7 7c0 1.202.308 2.33.84 3.316l-.789 2.368a1 1 0 0 0 1.265 1.265l2.595-.865a1 1 0 0 0-.632-1.898l-.698.233.3-.9a1 1 0 0 0-.104-.85A4.97 4.97 0 0 1 2 7a5 5 0 0 1 5-5 4.99 4.99 0 0 1 4.093 2.135 1 1 0 1 0 1.638-1.148A6.99 6.99 0 0 0 7 0Z" />
                        <path d="M11 6a5 5 0 0 0 0 10c.807 0 1.567-.194 2.24-.533l1.444.482a1 1 0 0 0 1.265-1.265l-.482-1.444A4.962 4.962 0 0 0 16 11a5 5 0 0 0-5-5Zm-3 5a3 3 0 0 1 6 0c0 .588-.171 1.134-.466 1.6a1 1 0 0 0-.115.82 1 1 0 0 0-.82.114A2.973 2.973 0 0 1 11 14a3 3 0 0 1-3-3Z" />
                    </svg>
                    <span class="text-sm font-medium @if(Route::is('notifications')){{ 'text-violet-500 dark:text-violet-400' }}@else{{ 'text-gray-600 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-200' }}@endif">Notifikasi</span>
                </a>
            </li>
            <li class="mr-0.5 md:mr-0 md:mb-0.5">
                <a class="flex items-center px-2.5 py-2 rounded-lg whitespace-nowrap @if(Route::is('apps')){{ 'bg-linear-to-r from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }}@endif" href="#">
                    <svg class="shrink-0 fill-current mr-2 @if(Route::is('apps')){{ 'text-violet-500 dark:text-violet-400' }}@else{{ 'text-gray-400 dark:text-gray-500' }}@endif" width="16" height="16" viewBox="0 0 16 16">
                        <path d="M8 3.414V6a1 1 0 1 1-2 0V1a1 1 0 0 1 1-1h5a1 1 0 0 1 0 2H9.414l6.293 6.293a1 1 0 1 1-1.414 1.414L8 3.414Zm0 9.172V10a1 1 0 1 1 2 0v5a1 1 0 0 1-1 1H4a1 1 0 0 1 0-2h2.586L.293 7.707a1 1 0 0 1 1.414-1.414L8 12.586Z" />
                    </svg>
                    <span class="text-sm font-medium @if(Route::is('apps')){{ 'text-violet-500 dark:text-violet-400' }}@else{{ 'text-gray-600 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-200' }}@endif">Aplikasi Terhubung</span>
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
                <a class="flex items-center px-2.5 py-2 rounded-lg whitespace-nowrap @if(Route::is('feedback')){{ 'bg-linear-to-r from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }}@endif" href="{{ route('feedback') }}">
                    <svg class="shrink-0 fill-current mr-2 @if(Route::is('feedback')){{ 'text-violet-500 dark:text-violet-400' }}@else{{ 'text-gray-400 dark:text-gray-500' }}@endif" width="16" height="16" viewBox="0 0 16 16">
                        <path d="M14.3.3c.4-.4 1-.4 1.4 0 .4.4.4 1 0 1.4l-8 8c-.2.2-.4.3-.7.3-.3 0-.5-.1-.7-.3-.4-.4-.4-1 0-1.4l8-8zM15 7c.6 0 1 .4 1 1 0 4.4-3.6 8-8 8s-8-3.6-8-8 3.6-8 8-8c.6 0 1 .4 1 1s-.4 1-1 1C4.7 2 2 4.7 2 8s2.7 6 6 6 6-2.7 6-6c0-.6.4-1 1-1z" />
                    </svg>
                    <span class="text-sm font-medium @if(Route::is('feedback')){{ 'text-violet-500 dark:text-violet-400' }}@else{{ 'text-gray-600 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-200' }}@endif">Berikan Ulasan</span>
                </a>
            </li>
        </ul>
    </div>
</div>