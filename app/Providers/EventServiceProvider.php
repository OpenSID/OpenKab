<?php

namespace App\Providers;

use App\Models\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

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
     *
     * @return void
     */
    public function boot()
    {
        Event::listen(BuildingMenu::class, function (BuildingMenu $event) {
            Config::get()->each(function ($item, $key) use ($event) {
                $event->menu->addIn('desa', [
                    'text' => $item->nama_desa,
                    'url' => "sesi/desa/{$item->kode_desa}",
                    'active' => session('desa.kode_desa') === $item->kode_desa,
                    'active' => session()->has('desa') ? session('desa.kode_desa') === $item->kode_desa : false,
                ]);
            });
        });
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
