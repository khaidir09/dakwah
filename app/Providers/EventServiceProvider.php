<?php

namespace App\Providers;

use App\Http\Middleware\AuthenticateWebSession;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Laravel\Fortify\Events\PasswordUpdatedViaController;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        // Menyegarkan hash password di sesi setelah ganti password lewat rute
        // `PUT /user/password`, agar AuthenticateWebSession tidak menganggap perangkat
        // yang baru saja mengganti passwordnya sendiri sebagai perangkat basi.
        //
        // Jetstream mendaftarkan listener serupa, tetapi hanya di dalam bootInertia();
        // proyek ini memakai stack Livewire, jadi listener itu tidak pernah aktif.
        // Form Livewire Jetstream menyegarkannya sendiri, rute HTTP ini tidak.
        Event::listen(function (PasswordUpdatedViaController $event) {
            if (request()->hasSession() && Auth::guard(AuthenticateWebSession::GUARD)->check()) {
                request()->session()->put([
                    'password_hash_'.AuthenticateWebSession::GUARD => Auth::guard(AuthenticateWebSession::GUARD)->user()->getAuthPassword(),
                ]);
            }
        });
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
