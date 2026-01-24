<header class="sticky top-0 before:absolute before:inset-0 before:backdrop-blur-md max-lg:before:bg-white/90 dark:max-lg:before:bg-gray-800/90 before:-z-10 z-30 {{ $variant === 'v2' || $variant === 'v3' ? 'before:bg-white after:absolute after:h-px after:inset-x-0 after:top-full after:bg-gray-200 dark:after:bg-gray-700/60 after:-z-10' : 'max-lg:shadow-xs lg:before:bg-gray-100/90 dark:lg:before:bg-gray-900/90' }} {{ $variant === 'v2' ? 'dark:before:bg-gray-800' : '' }} {{ $variant === 'v3' ? 'dark:before:bg-gray-900' : '' }}">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 {{ $variant === 'v2' || $variant === 'v3' ? '' : 'lg:border-b border-gray-200 dark:border-gray-700/60' }}">

            <a href="{{ route('beranda') }}" class="flex items-center gap-2">
                <!-- Logo Icon -->
                <svg class="text-emerald-600 fill-current w-9 h-9" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32">
                    <path d="M2 30H30V28H28V16C28 9.37 22.63 4 16 4C9.37 4 4 9.37 4 16V28H2V30ZM16 6C21.52 6 26 10.48 26 16V28H22V20C22 16.69 19.31 14 16 14C12.69 14 10 16.69 10 20V28H6V16C6 10.48 10.48 6 16 6Z"/>
                </svg>
                
                <!-- Logo Text -->
                <span class="text-2xl font-bold font-serif text-gray-800 tracking-tight dark:text-gray-100 pt-1">
                    Syaikhuna
                </span>
            </a>

            <!-- Header: Right side -->
            <div class="flex items-center space-x-3">

                <!-- Notifications button -->
                <x-dropdown-notifications align="right" />

                <!-- Info button -->
                <x-dropdown-help align="right" />

                <!-- Dark mode toggle -->
                <x-theme-toggle />                

                <!-- Divider -->
                <hr class="w-px h-6 bg-gray-200 dark:bg-gray-700/60 border-none" />

                <!-- User button -->
                @if (Auth::user())
                    <div class="relative inline-flex" x-data="{ open: false }">
                        <button
                            class="inline-flex justify-center items-center group"
                            aria-haspopup="true"
                            @click.prevent="open = !open"
                            :aria-expanded="open"                        
                        >
                            <img class="w-8 h-8 rounded-full" src="{{ Auth::user()->profile_photo_url }}" width="32" height="32" alt="{{ Auth::user()->name }}" />
                            <div class="flex items-center truncate">
                                <span class="truncate ml-2 text-sm font-medium text-gray-600 dark:text-gray-100 group-hover:text-gray-800 dark:group-hover:text-white">{{ Auth::user()->name }}</span>
                                <svg class="w-3 h-3 shrink-0 ml-1 fill-current text-gray-400 dark:text-gray-500" viewBox="0 0 12 12">
                                    <path d="M5.9 11.4L.5 6l1.4-1.4 4 4 4-4L11.3 6z" />
                                </svg>
                            </div>
                        </button>
                        <div
                            class="origin-top-right z-10 absolute top-full min-w-44 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700/60 py-1.5 rounded-lg shadow-lg overflow-hidden mt-1 right"                
                            @click.outside="open = false"
                            @keydown.escape.window="open = false"
                            x-show="open"
                            x-transition:enter="transition ease-out duration-200 transform"
                            x-transition:enter-start="opacity-0 -translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-out duration-200"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            x-cloak                    
                        >
                            <div class="pt-0.5 pb-2 px-3 mb-1 border-b border-gray-200 dark:border-gray-700/60">
                                <div class="font-medium text-gray-800 dark:text-gray-100">{{ Auth::user()->name }}</div>
                                {{-- <div class="text-xs text-gray-500 dark:text-gray-400 italic">Administrator</div> --}}
                            </div>
                            <ul>
                                <li>
                                    <a class="font-medium text-sm text-violet-500 hover:text-violet-600 dark:hover:text-violet-400 flex items-center py-1 px-3" href="{{ route('profile.show') }}" @click="open = false" @focus="open = true" @focusout="open = false">Settings</a>
                                </li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}" x-data>
                                        @csrf

                                        <a class="font-medium text-sm text-violet-500 hover:text-violet-600 dark:hover:text-violet-400 flex items-center py-1 px-3"
                                            href="{{ route('logout') }}"
                                            @click.prevent="$root.submit();"
                                            @focus="open = true"
                                            @focusout="open = false"
                                        >
                                            {{ __('Sign Out') }}
                                        </a>
                                    </form>                                
                                </li>
                            </ul>                
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn bg-gray-900 text-gray-100 hover:bg-gray-800 dark:bg-gray-100 dark:text-gray-800 dark:hover:bg-white whitespace-nowrap">Masuk</a>
                @endif

            </div>

        </div>
    </div>
</header>