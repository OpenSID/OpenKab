<?php

namespace App\Providers;

use App\Http\Transformers\IdentitasTransformer;
use App\Http\Transformers\SettingTransformer;
use App\Models\Identitas;
use App\Models\Setting;
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
        $this->addValidation();
        $this->addLogQuery();

        if (App::runningInConsole()) {
            activity()->disableLogging();
        } else {
            $identitasAplikasi = fractal(
                Identitas::first(),
                IdentitasTransformer::class,
                \League\Fractal\Serializer\JsonApiSerializer::class
            )->toArray()['data']['attributes'];

            $settingAplikasi = collect(
                fractal(
                    Setting::all(),
                    SettingTransformer::class,
                    \League\Fractal\Serializer\JsonApiSerializer::class
                )->toArray()['data']
            )->pluck('attributes.value', 'attributes.key');

            // daftarkan data identitas aplikasi disini, karena akan dipakai di hampir semua view
            View::share('identitasAplikasi', $identitasAplikasi);
            View::share('settingAplikasi', $settingAplikasi);
            config()->set(['app.sebutanDesa' => $identitasAplikasi['sebutan_desa'] ?? 'Desa']);
            config()->set(['app.sebutanKab' => $identitasAplikasi['sebutan_kab'] ?? 'Kabupaten']);
            $this->bootConfigAdminLTE($identitasAplikasi, $settingAplikasi);
        }
    }

    public function bootHttps()
    {
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
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

    protected function bootConfigAdminLTE($identitasAplikasi, $settingAplikasi)
    {
        $this->app->config['adminlte.title'] = $identitasAplikasi['nama_aplikasi'];
        $this->app->config['adminlte.title_postfix'] = "| {$identitasAplikasi['sebutan_kab']}";
        $this->app->config['adminlte.logo'] = $identitasAplikasi['nama_aplikasi'];
        if ($settingAplikasi->get('layout_menu') !== 'Vertikal') {
            $this->app->config['adminlte.layout_topnav'] = true;
            $this->app->config['adminlte.classes_content'] = 'col-12 p-3';
            $this->app->config['adminlte.classes_sidebar'] = 'sidebar-dark-primary elevation-4';
            $this->app->config['adminlte.classes_topnav'] = 'navbar-white navbar-light p-0';
            $this->app->config['adminlte.classes_topnav_nav'] = 'navbar-expand-lg';
            $this->app->config['adminlte.classes_topnav_container'] = 'container col-lg-12 p-2 pl-4';
            $this->app->config['adminlte.classes_content_header'] = 'container ml-3';
        }
    }
}
