<?php

namespace App\Providers;

use App\Models\Config;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->bootHttps();
        $this->bootConfigFTP();
    }

    public function bootHttps()
    {
        if(config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }

    /**
     * Boot config FTP berdasarkan desa.
     *
     * @return void
     */
    protected function bootConfigFTP()
    {
        Config::get()->each(function ($item) {
            $this->app->config["filesystems.disks.ftp_{$item->id}"] = [
                'driver' => 'ftp',
                'url' => env("FTP_{$item->id}_URL", $item->website),
                'host' => env("FTP_{$item->id}_HOST"),
                'username' => env("FTP_{$item->id}_USERNAME"),
                'password' => env("FTP_{$item->id}_PASSWORD"),
                'port' => (int) env("FTP_{$item->id}_PORT"),
                'root' => env("FTP_{$item->id}_ROOT"),
                'timeout' => (int) env("FTP_{$item->id}_TIMEOUT", 30),
            ];
        });
    }
}
