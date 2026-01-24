<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', config('app.name', 'Syaikhuna'))</title>
        <meta name="description" content="@yield('meta_description', 'Platform informasi jadwal majelis & acara terkini, profil ulama, dan konten islami.')">
        <meta name="keywords" content="@yield('meta_keywords', 'majelis, muallim, banjar, jadwal pengajian, syaikhuna, acara haul, wirid')">
        <meta name="author" content="@yield('meta_author', config('app.name', 'Syaikhuna'))">

        <!-- Open Graph / Facebook -->
        <meta property="og:type" content="@yield('og_type', 'website')">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:title" content="@yield('title', config('app.name', 'Syaikhuna'))">
        <meta property="og:description" content="@yield('meta_description', 'Platform informasi jadwal majelis & acara terkini, profil ulama, dan konten islami.')">
        {{-- <meta property="og:image" content="@yield('meta_image', asset('images/auth-image.jpg'))"> --}}

        <!-- Twitter -->
        <meta property="twitter:card" content="summary_large_image">
        <meta property="twitter:url" content="{{ url()->current() }}">
        <meta property="twitter:title" content="@yield('title', config('app.name', 'Syaikhuna'))">
        <meta property="twitter:description" content="@yield('meta_description', 'Platform informasi jadwal majelis & acara terkini, profil ulama, dan konten islami.')">
        {{-- <meta property="twitter:image" content="@yield('meta_image', asset('images/auth-image.jpg'))"> --}}

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
    </head>
    <body class="font-inter antialiased bg-gray-100 dark:bg-gray-900 text-gray-600 dark:text-gray-400">
        <!-- Page wrapper -->
        <div class="flex h-[100dvh] overflow-hidden">

            <!-- Content area -->
            <div class="relative flex flex-col flex-1 overflow-y-auto overflow-x-hidden @if($attributes['background']){{ $attributes['background'] }}@endif" x-ref="contentarea">

                <x-app.navbar-user :variant="$attributes['headerVariant']" />

                <main class="grow">
                    {{ $slot }}
                </main>

            </div>

        </div>

        @livewireScriptConfig

        @stack('scripts')
    </body>
</html>
