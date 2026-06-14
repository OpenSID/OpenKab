<?php

use App\Models\CMS\Article;
use App\Models\CMS\Category;
use App\Models\CMS\Page;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\ValidatePathEncoding;
use Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

return Application::configure(
    basePath: dirname(__DIR__),
)
    ->withRouting(
        then: function () {
            Route::middleware('api')
                ->prefix('api')
                ->as('api.')
                ->group(base_path('routes/api.php'));

            Route::middleware('api')
                ->prefix('api/v1')
                ->group(base_path('routes/apiv1.php'));

            Route::bind('aSlug', function ($slug) {
                return Article::with('category')->where('slug', $slug)->firstOrFail();
            });

            Route::bind('cSlug', function ($slug) {
                return Category::with('articles')->where('slug', $slug)->firstOrFail();
            });

            Route::bind('pSlug', function ($slug) {
                return Page::where('slug', $slug)->firstOrFail();
            });
        },
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->use([
            ValidatePathEncoding::class,
            InvokeDeferredCallbacks::class,
            App\Http\Middleware\TrustProxies::class,
            \Illuminate\Http\Middleware\HandleCors::class,
            App\Http\Middleware\PreventRequestsDuringMaintenance::class,
            \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
            App\Http\Middleware\TrimStrings::class,
            \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
            App\Http\Middleware\GlobalRateLimiter::class,
        ]);

        $middleware->group('web', [
            App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            App\Http\Middleware\CspExclusion::class,
            \Spatie\Csp\AddCspHeaders::class,
        ]);

        $middleware->group('api', [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        $middleware->alias([
            'auth' => App\Http\Middleware\Authenticate::class,
            'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
            'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
            'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
            'can' => \Illuminate\Auth\Middleware\Authorize::class,
            'guest' => App\Http\Middleware\RedirectIfAuthenticated::class,
            'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
            'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
            'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            'abilities' => \Laravel\Sanctum\Http\Middleware\CheckAbilities::class,
            'ability' => \Laravel\Sanctum\Http\Middleware\CheckForAnyAbility::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'teams_permission' => App\Http\Middleware\TeamsPermission::class,
            'password.weak' => App\Http\Middleware\WeakPassword::class,
            'password.expiry' => App\Http\Middleware\CheckPasswordExpiry::class,
            'website.enable' => App\Http\Middleware\WebsiteEnable::class,
            'log.visitor' => \Shetabit\Visitor\Middlewares\LogVisits::class,
            'easyauthorize' => App\Http\Middleware\EasyAuthorize::class,
            'check.presisi' => App\Http\Middleware\CheckPresisiStatus::class,
            '2fa' => App\Http\Middleware\TwoFactorMiddleware::class,
        ]);

        $middleware->statefulApi();
        $middleware->throttleApi('api');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->dontFlash([
            'current_password',
            'password',
            'password_confirmation',
        ]);
    })
    ->create();
