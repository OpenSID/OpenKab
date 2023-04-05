<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BantuanController;

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
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::prefix('penduduk')->group(function () {
        Route::get('/', \App\Http\Controllers\Api\PendudukController::class);
    });

    // Statistik
    Route::prefix('statistik')->group(function () {
        // Statistik Bantuan
        Route::controller(BantuanController::class)
            ->prefix('bantuan')->group(function () {
                Route::get('/', 'index');
                Route::get('/grafik', 'grafik');
            });

        // Statistik Penduduk
        Route::controller(StatistikPendudukController::class)
            ->prefix('statistik_penduduk')->group(function () {
                Route::get('/', 'index');
                Route::get('/{id}', 'show');
            });
    });
});
