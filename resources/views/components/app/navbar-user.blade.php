<header class="sticky top-0 before:absolute before:inset-0 before:backdrop-blur-md max-lg:before:bg-white/90 dark:max-lg:before:bg-gray-800/90 before:-z-10 z-30 {{ $variant === 'v2' || $variant === 'v3' ? 'before:bg-white after:absolute after:h-px after:inset-x-0 after:top-full after:bg-gray-200 dark:after:bg-gray-700/60 after:-z-10' : 'max-lg:shadow-xs lg:before:bg-gray-100/90 dark:lg:before:bg-gray-900/90' }} {{ $variant === 'v2' ? 'dark:before:bg-gray-800' : '' }} {{ $variant === 'v3' ? 'dark:before:bg-gray-900' : '' }}">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 {{ $variant === 'v2' || $variant === 'v3' ? '' : 'lg:border-b border-gray-200 dark:border-gray-700/60' }}">

            <!-- Header: Left side -->
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
            <div class="flex items-center space-x-1">

                <!-- Notifications button -->
                <x-dropdown-notifications align="right" />

                <!-- Info button -->
                <x-dropdown-help align="right" />

                <!-- Dark mode toggle -->
                <x-theme-toggle />                

                <!-- Divider -->
                <hr class="w-px h-6 mr-3 bg-gray-200 dark:bg-gray-700/60 border-none" />

                <x-dropdown-user align="right" />

            </div>

        </div>
    </div>
</header>