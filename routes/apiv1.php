<?php

use App\Http\Controllers\Api\ArtikelController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\BantuanController;
use App\Http\Controllers\Api\BantuanKabupatenController;
use App\Http\Controllers\Api\DasborController;
use App\Http\Controllers\Api\DokumenController;
use App\Http\Controllers\Api\IdentitasController;
use App\Http\Controllers\Api\KategoriDesaController;
use App\Http\Controllers\Api\KeluargaController;
use App\Http\Controllers\Api\LaporanPendudukController;
use App\Http\Controllers\Api\OpendkSynchronizeController;
use App\Http\Controllers\Api\PendudukController;
use App\Http\Controllers\Api\PengaturanController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\SummaryController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\WebsiteController;
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
Route::get('/identitas', [IdentitasController::class, 'index']);

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

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/token', [AuthController::class, 'token']);
    Route::post('/logout', [AuthController::class, 'logOut']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Dasbor
    Route::prefix('dasbor')->group(function () {
        Route::get('/', DasborController::class);
    });    

    // Master Data Bantuan
    Route::controller(BantuanKabupatenController::class)
        ->prefix('bantuan-kabupaten')->group(function () {
            Route::get('/', 'index');
            Route::post('/tambah', 'store');
            Route::put('/perbarui/{id}', 'update');
            Route::post('/hapus', 'destroy');
        });

    // Artikel
    Route::controller(ArtikelController::class)
        ->prefix('artikel')->group(function () {
            Route::get('/', 'index');
            Route::get('/tahun', 'tahun');
        });

    // Identitas
    Route::controller(IdentitasController::class)
        ->prefix('identitas')->group(function () {
            Route::put('/perbarui/{id}', 'update');
            Route::post('/upload/{id}', 'upload');
            Route::post('/uploadFavicon/{id}', 'uploadFavicon');
        });

    // Pengaturan Aplikasi
    Route::controller(PengaturanController::class)
        ->prefix('pengaturan')->group(function () {
            Route::get('/', 'index')->name('api.pengaturan_aplikasi');
            Route::post('/update', 'update');

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
            Route::get('laporan-penduduk', [LaporanPendudukController::class, 'index']);
        });
    });    
});

// Data utama website
Route::get('data-website', WebsiteController::class);
Route::get('data-summary', SummaryController::class);

// Desa teraktif
Route::get('desa-aktif', [KategoriDesaController::class, 'index']);