<?php

use App\Http\Controllers\AdminWebController;
use App\Http\Controllers\BantuanController;
use App\Http\Controllers\DasborController;
use App\Http\Controllers\GroupController;
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

Route::get('pengaturan/logo', [IdentitasController::class, 'logo']);

Route::middleware(['auth', 'teams_permission'])->group(function () {
    Route::get('/', [DasborController::class, 'index'])->name('dasbor');

    Route::middleware(['role:pengaturan-users','permission:pengaturan-users-read'])->get('users/list', [UserController::class, 'getUsers'])->name('users.list');
    Route::middleware(['role:pengaturan-users','permission:pengaturan-users-edit'])->get('users/status/{id}/{status}', [UserController::class, 'status'])->name('users.status');
    Route::prefix('pengaturan')->group(function () {
        Route::middleware(['role:pengaturan-users','permission:pengaturan-users-read'])->resource('users', UserController::class);
        Route::middleware(['role:pengaturan-identitas', 'permission:pengaturan-identitas-read'])->resource('identitas', IdentitasController::class)->only(['index', 'edit']);
        Route::middleware(['role:pengaturan-group', 'permission:pengaturan-group-read'])->prefix('groups')->group(function () {
            Route::get('/', [GroupController::class, 'index']);
            Route::middleware(['permission:pengaturan-group-create'])->get('/tambah', [GroupController::class, 'create']);
            Route::middleware(['permission:pengaturan-group-edit'])->get('/edit/{id}', [GroupController::class, 'edit']);
        });

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
    Route::middleware(['role:penduduk', 'permission:penduduk-read'])->get('penduduk/cetak', [PendudukController::class, 'cetak']);
    Route::middleware(['role:penduduk', 'permission:penduduk-edit'])->get('penduduk/pindah/{id}', [PendudukController::class, 'pindah']);
    Route::middleware(['role:penduduk', 'permission:penduduk-read'])->resource('penduduk', PendudukController::class)->only(['index', 'show']);

    // Keluarga
    Route::middleware(['role:penduduk'])->controller(KeluargaController::class)
        ->prefix('keluarga')
        ->group(function () {
            Route::middleware(['permission:penduduk-read'])->get('/detail/{no_kk}', 'show')->name('keluarga.detail');
        });

    // Bantuan
    Route::middleware(['role:bantuan', 'permission:bantuan-read'])->controller(BantuanController::class)
        ->prefix('bantuan')
        ->group(function () {
            Route::get('/', 'index');
            Route::get('/cetak', 'cetak');
            Route::get('/detail/{id}', 'show');
        });

    // Statistik
    Route::middleware(['role:statistik', 'permission:statistik-read'])->controller(StatistikController::class)
        ->prefix('statistik')
        ->group(function () {
            Route::middleware(['role:statistik-penduduk', 'permission:statistik-penduduk-read'])->get('/penduduk', 'penduduk');
            Route::middleware(['role:statistik-keluarga', 'permission:statistik-keluarga-read'])->get('/keluarga', 'keluarga');
            Route::middleware(['role:statistik-rtm', 'permission:statistik-rtm-read'])->get('/rtm', 'rtm');
            Route::middleware(['role:statistik-bantuan', 'permission:statistik-bantuan-read'])->get('/bantuan', 'bantuan');
            Route::middleware(['role:statistik'])->get('/cetak/{kategori}/{id}', 'cetak');
        });

    // Master Data
    Route::middleware(['role:master-data', 'permission:master-data-read'])->controller(AdminWebController::class)
        ->prefix('master')
        ->group(function () {
            Route::middleware(['role:master-data-artikel', 'permission:master-data-artikel-read'])->get('/kategori/{parrent}', 'kategori_index');
            Route::middleware(['role:master-data-artikel', 'permission:master-data-artikel-read'])->get('/kategori/edit/{id}/{parrent}', 'kategori_edit');
            Route::middleware(['role:master-data-artikel', 'permission:master-data-artikel-read'])->get('/kategori/tambah/{parrent}', 'kategori_create');
            Route::middleware(['role:master-data-pengaturan', 'permission:master-data-pengaturan-read'])->get('/pengaturan', 'pengaturan_index');
            Route::middleware(['role:master-data-bantuan', 'permission:master-data-artikel-read'])->resource('bantuan', BantuanKabupatenController::class)->only(['index', 'create', 'edit']);
        });
});
