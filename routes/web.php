<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DasborController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\WilayahMiddleware;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes([
    'register' => false,
    'verify' => true,
]);

Route::get('/', [DasborController::class, 'index']);

Route::middleware('auth')->group(function () {
    Route::get('/home', [DasborController::class, 'index'])->name('home');

    Route::get('users/list', [UserController::class, 'getUsers'])->name('users.list');
    Route::get('users/status/{id}/{status}', [UserController::class, 'status'])->name('users.status');
    Route::resource('users', UserController::class);
    Route::prefix('sesi')->group(function () {
        Route::middleware(WilayahMiddleware::class)->get('desa/{kodeDesa}', function () {
            return Redirect::back();
        });
        Route::get('hapus', function () {
            session()->remove('desa');

            return Redirect::back();
        });
    });

    Route::resource('penduduk', \App\Http\Controllers\PendudukController::class)->only(['index', 'show']);

    Route::controller(\App\Http\Controllers\BantuanController::class)
        ->prefix('bantuan')
        ->group(function () {
            Route::get('/', 'index');
            Route::get('/{id}', 'show');
        });

    // Statistik
    Route::controller(\App\Http\Controllers\StatistikController::class)
        ->prefix('statistik')
        ->group(function () {
            Route::get('/penduduk', 'penduduk');
            Route::get('/keluarga', 'keluarga');
            Route::get('/rtm', 'rtm');
            Route::get('/bantuan', 'bantuan');
            Route::get('/cetak/{kategori}/{id}', 'cetak');
        });
});
