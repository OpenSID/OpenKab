<?php

use App\Http\Controllers\Api\DDKController;
use App\Http\Controllers\Api\DTKSController;
use App\Http\Controllers\Api\PariwisataController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\DataController;
use App\Http\Controllers\Api\DasborController;
use App\Http\Controllers\Api\ArtikelController;
use App\Http\Controllers\Api\BantuanController;
use App\Http\Controllers\Api\DokumenController;
use App\Http\Controllers\Api\SummaryController;
use App\Http\Controllers\Api\WebsiteController;
use App\Http\Controllers\Api\WilayahController;
use App\Http\Controllers\Api\KategoriController;
use App\Http\Controllers\Api\KeluargaController;
use App\Http\Controllers\Api\PendudukController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\IdentitasController;
use App\Http\Controllers\Api\StatistikController;
use App\Http\Controllers\Api\PendidikanController;
use App\Http\Controllers\Api\PengaturanController;
use App\Http\Controllers\Api\KategoriDesaController;
use App\Http\Controllers\Api\KetenagakerjaanController;
use App\Http\Controllers\Api\SuplemenController;
use App\Http\Controllers\Api\PointController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PrasaranaSaranaController;
use App\Http\Controllers\Api\BantuanKabupatenController;
use App\Http\Controllers\Api\DesaController;
use App\Http\Controllers\Api\KelembagaanController;
use App\Http\Controllers\Api\InfrastrukturController;
use App\Http\Controllers\Api\LaporanPendudukController;
use App\Http\Controllers\Api\OpendkSynchronizeController;
use App\Http\Controllers\Api\SettingController;
use Illuminate\Support\Facades\Auth;

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
// Wilayah
Route::prefix('wilayah')->group(function () {
    Route::get('desa', [WilayahController::class, 'desa']);
    Route::get('dusun', [WilayahController::class, 'dusun']);
    Route::get('rw', [WilayahController::class, 'rw']);
    Route::get('rt', [WilayahController::class, 'rt']);
    Route::get('penduduk', [WilayahController::class, 'penduduk']);
});

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

    Route::get('/pariwisata', PariwisataController::class);

    Route::get('infrastruktur', [InfrastrukturController::class, 'data']);

    // API Data Presisi
    Route::get('/ketenagakerjaan', KetenagakerjaanController::class);

    Route::get('/pendidikan', PendidikanController::class);

    Route::controller(DataController::class)
    ->prefix('data')->group(function () {
        Route::get('/kategori-statistik', 'kategoriStatistik');
        Route::get('/kesehatan', 'kesehatan');
        Route::get('/jaminan-sosial', 'jaminan_sosial');
    });

    Route::prefix('penduduk')->group(function () {
        Route::get('/', [PendudukController::class, 'index']);

        // Referensi
        Route::prefix('referensi')->group(function () {
            Route::get('sex', [PendudukController::class, 'pendudukSex']);
            Route::get('status', [PendudukController::class, 'pendudukStatus']);
            Route::get('status-dasar', [PendudukController::class, 'pendudukStatusDasar']);
        });

        Route::prefix('aksi')->group(function () {
            Route::post('pindah', [PendudukController::class, 'pindah']);
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
    Route::controller(KategoriController::class)
        ->prefix('kategori')->group(function () {
            Route::get('/', 'index');
            Route::get('/tampil', 'show');
            Route::post('/buat', 'store');
            Route::put('/perbarui/{id}', 'update');
            Route::post('/hapus', 'destroy');
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

     // Prodeskel
    Route::prefix('prodeskel')->group(function () {
        Route::prefix('ddk')->group(function () {
            Route::get('pangan', [DDKController::class, 'pangan']);
        });
        Route::prefix('potensi')->group(function () {
            Route::get('prasarana-sarana', [PrasaranaSaranaController::class, 'prasaranaSarana']);
            Route::get('kelembagaan', [KelembagaanController::class, 'kelembagaan']);
            Route::get('kelembagaan/penduduk', [KelembagaanController::class, 'kelembagaan_penduduk']);
        });
    });

    // Satu Data
    Route::prefix('satu-data')->group(function () {
        Route::get('dtks', DTKSController::class);
    });

    // Sinkronisasi OpenDK
    Route::prefix('opendk')->group(function () {
        Route::get('', [OpendkSynchronizeController::class, 'index'])->name('synchronize.opendk.index');
        Route::middleware(['abilities:synchronize-opendk-create'])->group(function () {            
            Route::get('data', [OpendkSynchronizeController::class, 'getData']);
            
            Route::get('laporan-penduduk', [LaporanPendudukController::class, 'index']);
            Route::get('/sync-penduduk-opendk', [PendudukController::class, 'syncPendudukOpenDk']);
        });        
    });
    
    Route::middleware(['abilities:synchronize-opendk-create'])->group(function () {
        Route::get('desa', [DesaController::class, 'index']);
        Route::get('opendk/bantuan', [BantuanController::class, 'syncBantuanOpenDk']);
        Route::get('opendk/bantuan/{id}', [BantuanController::class, 'getBantuanOpenDk']);
        Route::get('/opendk/bantuan-peserta', [BantuanController::class, 'syncBantuanPesertaOpenDk']);
        Route::get('/opendk/bantuan-peserta/{id}/{kode_desa}', [BantuanController::class, 'getBantuanPesertaOpenDk']);
        Route::get('/opendk/desa/{kec?}', [DesaController::class, 'all']);
    });    
});

// Statistik
Route::controller(StatistikController::class)
    ->prefix('statistik-web')->group(function () {
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
        Route::get('/get-list-coordinate', 'getListCoordinate');
        Route::get('/get-list-program', 'getListProgram');
        Route::get('/get-list-tahun', 'getListTahun');
        Route::get('/get-list-kabupaten', 'getListKabupaten');
        Route::get('/get-list-kecamatan/{id}', 'getListKecamatan');
        Route::get('/get-list-desa/{id}', 'getListDesa');
        Route::get('/get-list-coordinate', 'getListCoordinate');
        Route::get('/get-list-penerima', 'getListPenerimaBantuan');
    });

// Data
Route::controller(DataController::class)
    ->prefix('data')->group(function () {
        Route::get('/kesehatan', 'kesehatan');
        Route::get('/jaminan-sosial', 'jaminanSosial');
        Route::get('/penduduk-potensi-kelembagaan', 'pendudukPotensiKelembagaan');
    });

// Data utama website
Route::get('data-website', WebsiteController::class);
Route::get('data-summary', SummaryController::class);

// Desa teraktif
Route::get('desa-aktif', [KategoriDesaController::class, 'index']);

Route::post('/suplemen', [SuplemenController::class, 'store']);
Route::post('/suplemen/terdata/hapus', [SuplemenController::class, 'delete_multiple'])->name('suplemen-terdata.delete-multiple');
Route::post('/suplemen/update/{id}', [SuplemenController::class, 'update']);
Route::get('/suplemen', [SuplemenController::class, 'index']);
Route::get('/suplemen/terdata/{sasaran}/{id}', [SuplemenController::class, 'detail']);
Route::get('/suplemen/sasaran', [SuplemenController::class, 'sasaran']);
Route::get('/suplemen/status', [SuplemenController::class, 'status']);
Route::delete('/suplemen/hapus/{id}', [SuplemenController::class, 'destroy'])->name('suplemen.hapus');


Route::get('/point', [PointController::class, 'index']);
Route::get('/point/status', [PointController::class, 'status']);
Route::delete('/point/hapus/{id}', [PointController::class, 'destroy'])->name('point.hapus');
Route::post('/point/multiple-delete', [PointController::class, 'delete_multiple'])->name('point.delete-multiple');
Route::get('/subpoint/{id}', [PointController::class, 'detail']);
Route::post('/point', [PointController::class, 'store']);
