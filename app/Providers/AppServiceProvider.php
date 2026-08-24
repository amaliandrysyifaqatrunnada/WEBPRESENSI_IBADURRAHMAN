<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Repositories\Contracts\TeacherRepositoryInterface::class,
            \App\Repositories\Eloquent\TeacherRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\AttendanceRepositoryInterface::class,
            \App\Repositories\Eloquent\AttendanceRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
            (isset($_SERVER['HTTP_HOST']) && (
                str_contains($_SERVER['HTTP_HOST'], 'lhr.life') || 
                str_contains($_SERVER['HTTP_HOST'], 'localtunnel') || 
                str_contains($_SERVER['HTTP_HOST'], 'ngrok')
            ))
        ) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
