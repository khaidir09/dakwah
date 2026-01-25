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

        @stack('styles')

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
        <!-- Page wrapper -->
        <div class="flex h-[100dvh] overflow-hidden">

            <!-- Content area -->
            <div class="relative flex flex-col flex-1 overflow-y-auto overflow-x-hidden @if($attributes['background']){{ $attributes['background'] }}@endif" x-ref="contentarea">

                <x-app.navbar-user :variant="$attributes['headerVariant']" />

                <main class="grow">
                    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-[96rem] mx-auto">

                        <div class="xl:flex">
                            <div class="md:flex flex-1">
                                <div class="flex-1">
                                <!-- Page header -->
                                    <div class="mb-8 justify-between items-center md:flex">

                                        <!-- Title -->
                                        <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Dasbor Akun</h1>

                                        <div class="mt-3 md:mt-0">
                                            <a class="btn-sm px-3 bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700/60 hover:border-gray-300 dark:hover:border-gray-600 text-gray-800 dark:text-gray-300" href="{{ route('beranda') }}">
                                                <svg class="fill-current text-gray-400 dark:text-gray-500 mr-2" width="7" height="12" viewBox="0 0 7 12">
                                                    <path d="M5.4.6 6.8 2l-4 4 4 4-1.4 1.4L0 6z" />
                                                </svg>
                                                <span>Kembali ke Halaman Utama</span>
                                            </a>
                                        </div>

                                    </div>

                                    <div class="bg-white dark:bg-gray-800 shadow-xs rounded-xl mb-8">
                                        <div class="flex flex-col md:flex-row md:-mr-px">

                                            <!-- Sidebar -->
                                            <x-settings.settings-sidebar />

                                            <!-- Panel -->
                                            {{ $slot }}

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </main>

            </div>

        </div>

        @livewireScriptConfig

        @stack('scripts')
    </body>
</html>
