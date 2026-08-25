<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', config('app.name', 'Syaikhuna'))</title>
        <meta name="description" content="@yield('meta_description', \App\Services\SeoService::DEFAULT_DESCRIPTION)">
        <meta name="author" content="@yield('meta_author', config('app.name', 'Syaikhuna'))">
        @hasSection('meta_robots')
            <meta name="robots" content="@yield('meta_robots')">
        @endif
        <link rel="canonical" href="@yield('canonical', $seo->canonical())">

        <!-- Open Graph / Facebook -->
        <meta property="og:site_name" content="{{ config('app.name', 'Syaikhuna') }}">
        <meta property="og:locale" content="id_ID">
        <meta property="og:type" content="@yield('og_type', 'website')">
        <meta property="og:url" content="@yield('canonical', $seo->canonical())">
        <meta property="og:title" content="@yield('title', config('app.name', 'Syaikhuna'))">
        <meta property="og:description" content="@yield('meta_description', \App\Services\SeoService::DEFAULT_DESCRIPTION)">
        <meta property="og:image" content="@yield('meta_image', $seo->image())">

        <!-- Twitter -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:url" content="@yield('canonical', $seo->canonical())">
        <meta name="twitter:title" content="@yield('title', config('app.name', 'Syaikhuna'))">
        <meta name="twitter:description" content="@yield('meta_description', \App\Services\SeoService::DEFAULT_DESCRIPTION)">
        <meta name="twitter:image" content="@yield('meta_image', $seo->image())">

        <!-- Favicon -->
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/apple-touch-icon.png') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/favicon-16x16.png') }}">
        <link rel="manifest" href="{{ asset('site.webmanifest') }}">

        <!-- Fonts -->
        {{-- Deklarasi @font-face ada di resources/css/app.css. crossorigin wajib pada preload font — tanpa itu berkasnya diunduh dua kali. --}}
        <link rel="preload" href="{{ asset('fonts/inter-latin.woff2') }}" as="font" type="font/woff2" crossorigin>

        <!-- Structured data -->
        {{ $schema->organization() }}
        @stack('jsonld')

        @stack('styles')
        @stack('head')

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
         <x-onesignal-script />
    </head>
    <body class="font-inter antialiased bg-gray-100 dark:bg-gray-900 text-gray-600 dark:text-gray-400">
        <!-- Page wrapper -->
        <div class="flex h-[100dvh] overflow-hidden">

            <!-- Content area -->
            <div class="relative flex flex-col flex-1 overflow-y-auto overflow-x-hidden @if($attributes['background']){{ $attributes['background'] }}@endif" x-ref="contentarea">

                <x-app.navbar-user :variant="$attributes['headerVariant']" />

                <main class="grow">
                    {{ $slot }}
                    <x-install-prompt />
                </main>

            </div>

        </div>

        @livewireScriptConfig

        @stack('scripts')
    </body>
</html>
