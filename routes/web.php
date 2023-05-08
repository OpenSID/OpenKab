<?php

use App\Http\Controllers\AdminWebController;
use App\Http\Controllers\BantuanController;
use App\Http\Controllers\DasborController;
use App\Http\Controllers\IdentitasController;
use App\Http\Controllers\KeluargaController;
use App\Http\Controllers\Master\BantuanKabupatenController;
use App\Http\Controllers\PendudukController;
use App\Http\Controllers\StatistikController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\KecamatanMiddleware;
use App\Http\Middleware\WilayahMiddleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth')->group(function () {
    Route::get('/', [DasborController::class, 'index'])->name('dasbor');

    Route::get('users/list', [UserController::class, 'getUsers'])->name('users.list');
    Route::get('users/status/{id}/{status}', [UserController::class, 'status'])->name('users.status');
    Route::resource('users', UserController::class);
    // Route::resource('identitas', IdentitasController::class)->only(['index', 'edit']);

    Route::prefix('sesi')->group(function () {
        // Kecamatan
        Route::middleware(KecamatanMiddleware::class)->get('kecamatan/{kodeKecamatan}', function () {
            return redirect()->back();
        });

        // Desa / Kelurahan
        Route::middleware(WilayahMiddleware::class)->get('desa/{kodeDesa}', function () {
            return redirect()->back();
        });

        Route::get('hapus', function () {
            session()->remove('kecamatan');
            session()->remove('desa');

            return redirect()->back();
        });
    });

    // Penduduk
    Route::get('penduduk/cetak', [PendudukController::class, 'cetak']);
    Route::resource('penduduk', PendudukController::class)->only(['index', 'show']);

    // Keluarga
    Route::controller(KeluargaController::class)
        ->prefix('keluarga')
        ->group(function () {
            Route::get('/detail/{no_kk}', 'show')->name('keluarga.detail');
        });

    // Bantuan
    Route::controller(BantuanController::class)
        ->prefix('bantuan')
        ->group(function () {
            Route::get('/', 'index');
            Route::get('/cetak', 'cetak');
            Route::get('/detail/{id}', 'show');
        });

    // Statistik
    Route::controller(StatistikController::class)
        ->prefix('statistik')
        ->group(function () {
            Route::get('/penduduk', 'penduduk');
            Route::get('/keluarga', 'keluarga');
            Route::get('/rtm', 'rtm');
            Route::get('/bantuan', 'bantuan');
            Route::get('/cetak/{kategori}/{id}', 'cetak');
        });

    // Master Data
    Route::controller(AdminWebController::class)
        ->prefix('master')
        ->group(function () {
            Route::get('/kategori', 'kategori_index');
            Route::get('/pengaturan', 'pengaturan_index');
            Route::resource('bantuan', BantuanKabupatenController::class)->only(['index', 'create', 'edit']);
        });
});
