<?php

namespace App\Providers;

use App\Events\DailyStatisticsCalculated;
use App\Listeners\NotifyAdminsStatisticsCalculated;
use Illuminate\Support\Facades\Event;
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
        // Registrar event listeners
        Event::listen(
            DailyStatisticsCalculated::class,
            NotifyAdminsStatisticsCalculated::class
        );
    }
}
