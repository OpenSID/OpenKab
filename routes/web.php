<?php

use App\Http\Controllers\AdminWebController;
use App\Http\Controllers\Auth\ChangePasswordController;
use App\Http\Controllers\BantuanController;
use App\Http\Controllers\DasborController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\IdentitasController;
use App\Http\Controllers\KeluargaController;
use App\Http\Controllers\Master\BantuanKabupatenController;
use App\Http\Controllers\PendudukController;
use App\Http\Controllers\RiwayatPenggunaController;
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

Route::get('pengaturan/logo', [IdentitasController::class, 'logo']);

Route::middleware(['auth', 'teams_permission'])->group(function () {
    Route::get('/', [DasborController::class, 'index'])->name('dasbor');
    Route::get('password.change', [ChangePasswordController::class, 'showResetForm'])->name('password.change');
    Route::post('password.change', [ChangePasswordController::class, 'reset'])->name('password.change');

    Route::get('users/list', [UserController::class, 'getUsers'])->name('users.list');
    Route::get('users/status/{id}/{status}', [UserController::class, 'status'])->name('users.status');
    Route::prefix('pengaturan')->group(function () {
        Route::middleware(['role:pengaturan-users'])->resource('users', UserController::class);
        Route::middleware(['role:pengaturan-identitas'])->resource('identitas', IdentitasController::class)->only(['index', 'edit']);
        Route::middleware(['role:pengaturan-group'])->prefix('groups')->group(function () {
            Route::get('/', [GroupController::class, 'index'])->name('groups.index');
            Route::get('/tambah', [GroupController::class, 'create'])->name('groups.create');
            Route::get('/edit/{id}', [GroupController::class, 'edit'])->name('groups.edit');
        });
        Route::resource('activities', RiwayatPenggunaController::class)->only(['index', 'show']);

    });

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
    Route::middleware(['role:penduduk'])->get('penduduk/cetak', [PendudukController::class, 'cetak']);
    Route::middleware(['role:penduduk'])->get('penduduk/pindah/{id}', [PendudukController::class, 'pindah'])->name('penduduk.edit');
    Route::middleware(['role:penduduk'])->resource('penduduk', PendudukController::class)->only(['index', 'show']);

    // Keluarga
    Route::middleware(['role:penduduk'])->controller(KeluargaController::class)
        ->prefix('keluarga')
        ->group(function () {
            Route::get('/detail/{no_kk}', 'show')->name('keluarga.detail');
        });

    // Bantuan
    Route::middleware(['role:bantuan'])->controller(BantuanController::class)
        ->prefix('bantuan')
        ->group(function () {
            Route::get('/', 'index')->name('bantuan');
            Route::get('/cetak', 'cetak');
            Route::get('/detail/{id}', 'show')->name('bantuan.detail');
        });

    // Statistik
    Route::middleware(['role:statistik'])->controller(StatistikController::class)
        ->prefix('statistik')
        ->group(function () {
            Route::middleware(['role:statistik-penduduk'])->get('/penduduk', 'penduduk');
            Route::middleware(['role:statistik-keluarga'])->get('/keluarga', 'keluarga');
            Route::middleware(['role:statistik-rtm'])->get('/rtm', 'rtm');
            Route::middleware(['role:statistik-bantuan'])->get('/bantuan', 'bantuan');
            Route::middleware(['role:statistik'])->get('/cetak/{kategori}/{id}', 'cetak');
        });

    // Master Data
    Route::middleware(['role:master-data'])->controller(AdminWebController::class)
        ->prefix('master')
        ->group(function () {
            Route::middleware(['role:master-data-artikel'])->get('/kategori/{parrent}', 'kategori_index')->name('master-data-artikel.kategori');
            Route::middleware(['role:master-data-artikel'])->get('/kategori/edit/{id}/{parrent}', 'kategori_edit')->name('master-data-artikel.kategori-edit');
            Route::middleware(['role:master-data-artikel'])->get('/kategori/tambah/{parrent}', 'kategori_create')->name('master-data-artikel.kategori-create');
            Route::middleware(['role:master-data-pengaturan'])->get('/pengaturan', 'pengaturan_index')->name('master-data.pengaturan');
            Route::middleware(['role:master-data-bantuan'])->resource('bantuan', BantuanKabupatenController::class)->only(['index', 'create', 'edit']);
        });
});
