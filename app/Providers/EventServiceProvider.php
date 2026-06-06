<?php

namespace App\Providers;

use App\Observers\VisitorObserver;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Shetabit\Visitor\Models\Visit;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        Visit::observe(VisitorObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return true;
    }
}
