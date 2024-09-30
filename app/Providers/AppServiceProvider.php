<?php

namespace App\Providers;

use App\Http\Transformers\IdentitasTransformer;
use App\Models\Config;
use App\Models\Identitas;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
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
        $this->addValidation();
        // daftarkan manual karena gagal install infyomlabs/adminlte-templates terkendala depedency
        View::addNamespace('adminlte-templates', resource_path('views/vendor/adminlte-templates'));
        $this->addLogQuery();

        if (App::runningInConsole()) {
            activity()->disableLogging();
        } else {
            $identitasAplikasi = fractal(
                Identitas::first(),
                IdentitasTransformer::class,
                \League\Fractal\Serializer\JsonApiSerializer::class
            )->toArray()['data']['attributes'];

            // daftarkan data identitas aplikasi disini, karena akan dipakai di hampir semua view
            View::share('identitasAplikasi', $identitasAplikasi);
            $this->bootConfigAdminLTE($identitasAplikasi);
        }
    }

    public function bootHttps()
    {
        if (config('app.env') === 'production') {
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

    protected function addValidation()
    {
        Validator::extend('valid_file', function ($attributes, $value, $parameters) {
            $contains = preg_match('/<\?php|<script|function|__halt_compiler|<html/i', File::get($value));
            if ($contains) {
                return false;
            }

            return true;
        });
    }

    private function addLogQuery()
    {
        if (config('app.debug')) {
            DB::listen(function ($query) {
                File::append(
                    storage_path('/logs/query.log'),
                    $query->sql.' ['.implode(', ', $query->bindings).']'.PHP_EOL
                );
            });
        }
    }

    protected function bootConfigAdminLTE($identitasAplikasi)
    {
        $this->app->config['adminlte.title'] = $identitasAplikasi['nama_aplikasi'];
        $this->app->config['adminlte.title_postfix'] = "| {$identitasAplikasi['sebutan_kab']}";
        $this->app->config['adminlte.logo'] = $identitasAplikasi['nama_aplikasi'];
    }
}
