<?php

namespace App\Providers;

use App\Models\CivitasPendidikan;
use App\Observers\CivitasPendidikanObserver;
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
        CivitasPendidikan::observe(CivitasPendidikanObserver::class);

        if (config('app.env') !== 'local' || request()->server('HTTP_X_FORWARDED_PROTO') == 'https') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        } else {
            \Illuminate\Support\Facades\URL::forceScheme('https'); // Force globally since the app URL is https
        }
    }
}
