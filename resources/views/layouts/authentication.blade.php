<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Syaikhuna</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400..700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Styles -->
        @livewireStyles        

        <script>
            if (localStorage.getItem('dark-mode') === 'false' || !('dark-mode' in localStorage)) {
                document.querySelector('html').classList.remove('dark');
                document.querySelector('html').style.colorScheme = 'light';
            } else {
                document.querySelector('html').classList.add('dark');
                document.querySelector('html').style.colorScheme = 'dark';
            }
        </script>

        <x-google-analytics />
    </head>
    <body class="font-inter antialiased bg-gray-100 dark:bg-gray-900 text-gray-600 dark:text-gray-400">

        <main class="bg-white dark:bg-gray-900">

            <div class="relative flex">

                <!-- Content -->
                <div class="w-full md:w-1/2">

                    <div class="min-h-[100dvh] h-full flex flex-col after:flex-1">

                        <!-- Header -->
                        <div class="flex-1">
                            <div class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
                                <!-- Logo -->
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
                            </div>
                        </div>

                        <div class="max-w-xl mx-auto w-full px-4 py-8">
                            {{ $slot }}
                        </div>

                    </div>

                </div>

                <!-- Image -->
                <!-- Right Side (Themed) -->
                <div class="hidden md:flex absolute top-0 bottom-0 right-0 md:w-1/2 bg-gradient-to-br from-emerald-800 to-teal-900 justify-center items-center p-12" aria-hidden="true">
                    <div class="text-white text-center relative z-10">
                        <div class="mb-4 flex justify-center">
                             <!-- Icon / Logo Placeholder -->
                             <svg class="w-20 h-20 fill-emerald-100 opacity-90" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32">
                                <path d="M2 30H30V28H28V16C28 9.37 22.63 4 16 4C9.37 4 4 9.37 4 16V28H2V30ZM16 6C21.52 6 26 10.48 26 16V28H22V20C22 16.69 19.31 14 16 14C12.69 14 10 16.69 10 20V28H6V16C6 10.48 10.48 6 16 6Z"/>
                            </svg>
                        </div>
                        <h2 class="text-4xl font-serif font-bold mb-4 tracking-wide">Syaikhuna</h2>
                        <p class="text-emerald-100 text-lg max-w-md mx-auto leading-relaxed">
                            Mudahkan pencarian jadwal majelis ilmu & akses konten islami digital secara terpusat dan terpadu.
                        </p>
                    </div>
                    <!-- Decorative Pattern -->
                    <div class="absolute inset-0 opacity-10 pointer-events-none" 
                         style="background-image: radial-gradient(circle at 2px 2px, rgba(255,255,255,0.8) 1px, transparent 0); background-size: 32px 32px;">
                    </div>
                </div>

            </div>

        </main> 

        @livewireScriptConfig
    </body>
</html>
