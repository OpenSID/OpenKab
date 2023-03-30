<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
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

Route::get('/', [HomeController::class, 'index']);

Route::middleware('auth')->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    Route::prefix('sesi')->group(function () {
        Route::middleware(WilayahMiddleware::class)->get('desa/{appKey}', function () {
            return redirect('/');
        });
        Route::get('hapus', function () {
            session()->remove('desa');

            return redirect('/');
        });
    });

    Route::prefix('penduduk')->group(function () {
        Route::get('/', [\App\Http\Controllers\PendudukController::class, 'index']);
    });
});
