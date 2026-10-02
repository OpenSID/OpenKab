<?php

use App\Http\Middleware\Authenticate;
use App\Http\Middleware\CheckPasswordExpiry;
use App\Http\Middleware\CheckPresisiStatus;
use App\Http\Middleware\CspExclusion;
use App\Http\Middleware\EasyAuthorize;
use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\GlobalRateLimiter;
use App\Http\Middleware\PreventRequestsDuringMaintenance;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\SsoCallbackAuth;
use App\Http\Middleware\SsoIpWhitelist;
use App\Http\Middleware\TeamsPermission;
use App\Http\Middleware\TrimStrings;
use App\Http\Middleware\TrustProxies;
use App\Http\Middleware\TwoFactorMiddleware;
use App\Http\Middleware\VerifyCsrfToken;
use App\Http\Middleware\WeakPassword;
use App\Http\Middleware\WebsiteEnable;
use App\Models\CMS\Article;
use App\Models\CMS\Category;
use App\Models\CMS\Page;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Middleware\SetCacheHeaders;
use Illuminate\Http\Middleware\ValidatePathEncoding;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\ValidateSignature;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Shetabit\Visitor\Middlewares\LogVisits;
use Spatie\Csp\AddCspHeaders;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

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
            TrustProxies::class,
            HandleCors::class,
            PreventRequestsDuringMaintenance::class,
            ValidatePostSize::class,
            TrimStrings::class,
            ConvertEmptyStringsToNull::class,
            GlobalRateLimiter::class,
        ]);

        $middleware->group('web', [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
            CspExclusion::class,
            AddCspHeaders::class,
        ]);

        $middleware->group('api', [
            EnsureFrontendRequestsAreStateful::class,
            'throttle:api',
            SubstituteBindings::class,
        ]);

        $middleware->alias([
            'auth' => Authenticate::class,
            'auth.basic' => AuthenticateWithBasicAuth::class,
            'auth.session' => AuthenticateSession::class,
            'cache.headers' => SetCacheHeaders::class,
            'can' => Authorize::class,
            'guest' => RedirectIfAuthenticated::class,
            'password.confirm' => RequirePassword::class,
            'signed' => ValidateSignature::class,
            'throttle' => ThrottleRequests::class,
            'verified' => EnsureEmailIsVerified::class,
            'abilities' => CheckAbilities::class,
            'ability' => CheckForAnyAbility::class,
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'teams_permission' => TeamsPermission::class,
            'password.weak' => WeakPassword::class,
            'password.expiry' => CheckPasswordExpiry::class,
            'website.enable' => WebsiteEnable::class,
            'log.visitor' => LogVisits::class,
            'easyauthorize' => EasyAuthorize::class,
            'check.presisi' => CheckPresisiStatus::class,
            '2fa' => TwoFactorMiddleware::class,
            'sso.callback' => SsoCallbackAuth::class,
            'sso.ip-whitelist' => SsoIpWhitelist::class,
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
