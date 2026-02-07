<?php

namespace App\Providers;

use App\Models\Library;
use App\Observers\LibraryObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Library::observe(LibraryObserver::class);
    }
}
