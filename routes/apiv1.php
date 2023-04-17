<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DasborController;
use App\Http\Controllers\Api\BantuanController;
use App\Http\Controllers\Api\KeluargaController;
use App\Http\Controllers\Api\PendudukController;
use App\Http\Controllers\Api\StatistikController;

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
// Route::middleware('auth:sanctum')->group(function () {
Route::get('/user', function (Request $request) {
    return $request->user();
});


Route::prefix('dasbor')->group(function () {
    Route::get('/', DasborController::class);
});

Route::prefix('penduduk')->group(function () {
    Route::get('/', PendudukController::class);
});


Route::controller(KeluargaController::class)
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
// });
