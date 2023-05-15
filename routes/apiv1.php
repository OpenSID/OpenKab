<?php

use App\Http\Controllers\Api\BantuanController;
use App\Http\Controllers\Api\DasborController;
use App\Http\Controllers\Api\DokumenController;
use App\Http\Controllers\Api\KeluargaController;
use App\Http\Controllers\Api\PendudukController;
use App\Http\Controllers\Api\StatistikController;
use App\Http\Controllers\Api\WilayahController;
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

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Dasbor
    Route::prefix('dasbor')->group(function () {
        Route::get('/', DasborController::class);
    });

    // Wilayah
    Route::prefix('wilayah')->group(function () {
        Route::get('dusun', [WilayahController::class, 'dusun']);
        Route::get('rw', [WilayahController::class, 'rw']);
        Route::get('rt', [WilayahController::class, 'rt']);
    });

    Route::prefix('penduduk')->group(function () {
        Route::get('/', [PendudukController::class, 'index']);

        // Referensi
        Route::prefix('referensi')->group(function () {
            Route::get('sex', [PendudukController::class, 'pendudukSex']);
            Route::get('status', [PendudukController::class, 'pendudukStatus']);
            Route::get('status-dasar', [PendudukController::class, 'pendudukStatusDasar']);
        });
    });

    // Dokumen
    Route::prefix('dokumen')->group(function () {
        Route::get('/', DokumenController::class);
    });

    // Keluarga
    Route::controller(KeluargaController::class)
        ->prefix('keluarga')->group(function () {
            Route::get('/show', 'show')->name('api.keluarga.detail');
        });

    // Statistik
    Route::controller(StatistikController::class)
        ->prefix('statistik')->group(function () {
            Route::get('/kategori-statistik', 'kategoriStatistik');
            Route::prefix('penduduk')->group(function () {
                Route::get('/', 'penduduk');
                Route::get('/tahun', 'refTahunPenduduk');
            });
            Route::prefix('keluarga')->group(function () {
                Route::get('/', 'keluarga');
                Route::get('/tahun', 'refTahunKeluarga');
            });
            Route::prefix('rtm')->group(function () {
                Route::get('/', 'rtm');
                Route::get('/tahun', 'refTahunRtm');
            });
            Route::get('/bantuan', 'bantuan');
            Route::get('/bantuan/tahun', [BantuanController::class, 'tahun']);
        });

    // Bantuan
    Route::controller(BantuanController::class)
        ->prefix('bantuan')->group(function () {
            Route::get('/', 'index');
            Route::get('/peserta', 'peserta');
            Route::get('/sasaran', 'sasaran');
            Route::get('/tahun', 'tahun');
            Route::get('/cetak', 'cetakBantuan');
        });

    // Master Data Kategori Artikel
    Route::controller(\App\Http\Controllers\Api\KategoriController::class)
        ->prefix('kategori')->group(function () {
            Route::get('/', 'index');
            Route::get('/tampil', 'show');
            Route::post('/buat', 'store');
            Route::put('/perbarui/{id}', 'update');
            Route::post('/hapus', 'destroy');
        });

    // Master Data Bantuan
    Route::controller(\App\Http\Controllers\Api\BantuanKabupatenController::class)
        ->prefix('bantuan-kabupaten')->group(function () {
            Route::get('/', 'index');
            Route::post('/tambah', 'store');
            Route::put('/perbarui/{id}', 'update');
            Route::post('/hapus', 'destroy');
        });

    // Artikel
    Route::controller(\App\Http\Controllers\Api\ArtikelController::class)
        ->prefix('artikel')->group(function () {
            Route::get('/', 'index');
            Route::get('/tahun', 'tahun');
        });

    // Identitas
    Route::controller(\App\Http\Controllers\Api\IdentitasController::class)
        ->prefix('identitas')->group(function () {
            Route::get('/', 'index');
            Route::put('/perbarui/{id}', 'update');
            Route::post('/upload/{id}', 'upload');
        });

    // Pengaturan Aplikasi
    Route::controller(\App\Http\Controllers\Api\PengaturanController::class)
        ->prefix('pengaturan')->group(function () {
            Route::get('/', 'index')->name('api.pengaturan_aplikasi');
            Route::post('/update', 'update');
        });
});
