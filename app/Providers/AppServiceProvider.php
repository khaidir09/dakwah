<?php

namespace App\Providers;

use App\Services\SeoService;
use App\Services\StructuredDataService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SeoService::class);
        $this->app->singleton(StructuredDataService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Dipakai layout publik dan setiap @section meta di halaman publik.
        View::share('seo', $this->app->make(SeoService::class));
        View::share('schema', $this->app->make(StructuredDataService::class));
    }
}
