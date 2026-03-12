<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\IdentitasController;
use App\Http\Controllers\Api\OpendkSynchronizeController;
use App\Http\Controllers\Api\RefreshTokenController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\TokenController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/signin', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->get('validate-token', function (Request $request) {
    $user = $request->user();

    // Check if the user has an authenticated token
    if ($user && $user->currentAccessToken()) {
        // Get the current access token
        $token = $user->currentAccessToken();

        // Fetch the abilities associated with the token
        $abilities = $token->abilities;

        return response()->json([
            'user' => $user,
            'abilities' => $abilities,
        ]);
    }

    return response()->json([
        'message' => 'No active token found.',
    ], 401);
});

Route::middleware(['auth:sanctum', 'token.anomaly'])->group(function () {
    Route::get('/token', [AuthController::class, 'token']);
    Route::post('/logout', [AuthController::class, 'logOut']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Token Management
    Route::prefix('tokens')->group(function () {
        Route::get('/', [TokenController::class, 'index']);
        Route::get('/{tokenId}', [TokenController::class, 'show']);
        Route::post('/revoke', [TokenController::class, 'revoke']);
        Route::post('/rotate', [TokenController::class, 'rotate']);
        Route::post('/revoke-all', [TokenController::class, 'revokeAll']);
        Route::post('/revoke-all-including-current', [TokenController::class, 'revokeAllIncludingCurrent']);
    });

    // Refresh Token Management (no auth:sanctum needed - uses refresh token)
    Route::prefix('refresh-token')->group(function () {
        Route::post('/refresh', [RefreshTokenController::class, 'refresh']);
        Route::post('/revoke', [RefreshTokenController::class, 'revoke']);
        Route::post('/revoke-all', [RefreshTokenController::class, 'revokeAll'])->middleware('auth:sanctum');
    });

    // Identitas - bisa diakses via session auth (admin dashboard) atau Sanctum token (API)
    Route::controller(IdentitasController::class)
        ->prefix('identitas')->group(function () {
            Route::get('/', 'index');
            Route::put('/perbarui/{id}', 'update');
            Route::post('/upload/{id}', 'upload');
            Route::post('/uploadFavicon/{id}', 'uploadFavicon');
        });

    // Pengaturan Aplikasi
    Route::prefix('pengaturan')->group(function () {
        Route::controller(TeamController::class)
            ->prefix('group')->group(function () {
                Route::get('/', 'index');
                Route::get('/show/{id}', 'show');
                Route::post('/delete', 'delete');
                Route::post('/', 'store');
                Route::put('/{id}', 'update');
                Route::get('/menu', 'menu');
                Route::get('/listModul/{id}', 'listModul');
                Route::put('/updateMenu/{id}', 'updateMenu');
            });
        Route::controller(SettingController::class)
            ->prefix('settings')->group(function () {
                Route::get('/', 'index');
                Route::put('/{id}', 'update');
            });
    });

    // Sinkronisasi OpenDK
    Route::prefix('opendk')->group(function () {
        Route::get('', [OpendkSynchronizeController::class, 'index'])->name('synchronize.opendk.index');
        Route::middleware(['abilities:synchronize-opendk-create'])->group(function () {
            Route::get('data', [OpendkSynchronizeController::class, 'getData']);
        });
    });
});
