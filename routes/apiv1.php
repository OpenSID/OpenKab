<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BantuanController;
use App\Http\Controllers\Api\PendudukController;
use App\Http\Controllers\Api\StatistikController;
use App\Http\Controllers\Api\WilayahController;

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

    Route::prefix('wilayah')->group(function () {
        Route::get('dusun', [WilayahController::class, 'dusun']);
        Route::get('rw', [WilayahController::class, 'rw']);
        Route::get('rt', [WilayahController::class, 'rt']);
    });

    Route::prefix('penduduk')->group(function () {
        Route::get('/', [PendudukController::class, 'index']);
        // referensi
        Route::prefix('referensi')->group(function () {
            Route::get('sex', [PendudukController::class, 'pendudukSex']);
            Route::get('status', [PendudukController::class, 'pendudukStatus']);
            Route::get('status-dasar', [PendudukController::class, 'pendudukStatusDasar']);
        });
    });


    Route::controller(\App\Http\Controllers\Api\KeluargaController::class)
        ->prefix('keluarga')->group(function () {
            Route::get('/show', 'show');
        });


    // Statistik
    Route::controller(StatistikController::class)
        ->prefix('statistik')->group(function () {
            Route::get('/kategori-statistik', 'kategoriStatistik');
            Route::get('/penduduk', 'penduduk');
            Route::get('/keluarga', 'keluarga');
            Route::get('/rtm', 'rtm');
            Route::get('/bantuan', 'bantuan');
        });


    Route::controller(BantuanController::class)
        ->prefix('bantuan')->group(function () {
            Route::get('/', 'index');
            Route::get('/peserta', 'peserta');
        });
});
