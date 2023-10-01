<?php

namespace App\Providers;

use App\Models\CMS\Article;
use App\Models\CMS\Category;
use App\Models\CMS\Page;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dasbor';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->as('api.')
                ->group(base_path('routes/api.php'));

            Route::middleware('api')
                ->prefix('api/v1')
                ->group(base_path('routes/apiv1.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });

        $this->bootRouteParameterBinders();
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }

    /**
     * Do not remove GENERATOR_PARAMETER_BINDER if you want to use CMS generator properly
     * Check the file app/Console/Commands/Cms/Resource.php
     *
     * @return void
     */
    private function bootRouteParameterBinders()
    {
        Route::bind('aSlug', function ($slug) {
            return Article::with('category')->where('slug', $slug)->firstOrFail();
        });
        Route::bind('cSlug', function ($slug) {
            return Category::with('articles')->where('slug', $slug)->firstOrFail();
        });
        Route::bind('pSlug', function ($slug) {
            return Page::where('slug', $slug)->firstOrFail();
        });
        /** GENERATOR_PARAMETER_BINDER **/
    }
}
