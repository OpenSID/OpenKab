<?php

namespace App\Providers;

use App\Enums\SsoStatusEnum;
use App\Http\Transformers\IdentitasTransformer;
use App\Http\Transformers\SettingTransformer;
use App\Models\Identitas;
use App\Models\Setting;
use App\Models\User;
use App\Observers\UserObserver;
use App\Observers\VisitorObserver;
use App\Services\SsoAuditLogger;
use Exception;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use League\Fractal\Serializer\JsonApiSerializer;
use Shetabit\Visitor\Models\Visit;

class AppServiceProvider extends ServiceProvider
{
    public const HOME = '/dasbor';

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
        $this->configureObservers();
        $this->configureRateLimiting();
        $this->bootHttps();
        $this->addValidation();
        $this->addLogQuery();

        try {
            $this->shareViewIdentitas();
        } catch (Exception $e) {
            Log::error($e->getMessage(), ['exception' => $e]);
        }

        // Share data ke semua view (termasuk Pest browser test context)
        $identitasAplikasi = fractal(
            Identitas::first(),
            IdentitasTransformer::class,
            JsonApiSerializer::class
        )->toArray()['data']['attributes'];

        $settingAplikasi = collect(
            fractal(
                Setting::all(),
                SettingTransformer::class,
                JsonApiSerializer::class
            )->toArray()['data']
        )->pluck('attributes.value', 'attributes.key');

        View::share('identitasAplikasi', $identitasAplikasi);
        View::share('settingAplikasi', $settingAplikasi);
        config()->set(['app.sebutanDesa' => $identitasAplikasi['sebutan_desa'] ?? 'Desa']);
        config()->set(['app.sebutanKab' => $identitasAplikasi['sebutan_kab'] ?? 'Kabupaten']);
        config()->set(['app.kodeKabupatenApi' => $identitasAplikasi['kode_kabupaten_api'] ?? '']);
        $this->bootConfigAdminLTE($identitasAplikasi, $settingAplikasi);

        if (App::runningInConsole()) {
            activity()->disableLogging();
        }
    }

    private function shareViewIdentitas(): void
    {
        // Share data ke semua view (termasuk Pest browser test context)
        $identitasAplikasi = fractal(
            Identitas::first(),
            IdentitasTransformer::class,
            JsonApiSerializer::class
        )->toArray()['data']['attributes'];

        $settingAplikasi = collect(
            fractal(
                Setting::all(),
                SettingTransformer::class,
                JsonApiSerializer::class
            )->toArray()['data']
        )->pluck('attributes.value', 'attributes.key');

        View::share('identitasAplikasi', $identitasAplikasi);
        View::share('settingAplikasi', $settingAplikasi);
        config()->set(['app.sebutanDesa' => $identitasAplikasi['sebutan_desa'] ?? 'Desa']);
        config()->set(['app.sebutanKab' => $identitasAplikasi['sebutan_kab'] ?? 'Kabupaten']);
        config()->set(['app.kodeKabupatenApi' => $identitasAplikasi['kode_kabupaten_api'] ?? '']);
        $this->bootConfigAdminLTE($identitasAplikasi, $settingAplikasi);
    }

    protected function configureObservers(): void
    {
        User::observe(UserObserver::class);
        Visit::observe(VisitorObserver::class);
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('sso-generate', function (Request $request) {
            $key = ($request->user()?->getAuthIdentifier() ?? 'guest').':'.$request->ip();

            return Limit::perMinute((int) config('sso.rate_limit_max', 5))
                ->by($key)
                ->response(function (Request $request, array $headers) {
                    $availableIn = max(1, (int) ($headers['X-RateLimit-Reset'] ?? 1));

                    $this->logSsoRateLimitedAttempt($request);

                    return response()->json([
                        'status' => 'error',
                        'message' => 'Terlalu banyak permintaan. Coba lagi nanti.',
                        'code' => 'RATE_LIMITED',
                        'retry_after' => $availableIn,
                    ], 429, ['Retry-After' => $availableIn]);
                });
        });
    }

    /**
     * Catat percobaan yang diblokir rate limit ke log audit SSO.
     */
    protected function logSsoRateLimitedAttempt(Request $request): void
    {
        $user = $request->user();
        $desaId = (string) $request->input('desa_id', '');

        if (! $user || $desaId === '') {
            return;
        }

        try {
            app(SsoAuditLogger::class)->logAttempt(
                $user->getAuthIdentifier(),
                $desaId,
                SsoStatusEnum::FAILED,
                SsoStatusEnum::REASON_RATE_LIMITED,
                $request->ip(),
                $request->userAgent(),
            );
        } catch (\Throwable $e) {
            Log::error('Gagal mencatat attempt SSO yang di-rate-limit', ['exception' => $e]);
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
        if (strtolower($settingAplikasi->get('layout_menu')) !== 'vertikal') {
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
