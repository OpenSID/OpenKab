<?php

use App\Http\Controllers\AdminWebController;
use App\Http\Controllers\Auth\ChangePasswordController;
use App\Http\Controllers\BantuanController;
use App\Http\Controllers\CatatanRilis;
use App\Http\Controllers\DasborController;
use App\Http\Controllers\DasborDemografiController;
use App\Http\Controllers\DataPokokController;
use App\Http\Controllers\DesaController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\IdentitasController;
use App\Http\Controllers\KecamatanController;
use App\Http\Controllers\KeluargaController;
use App\Http\Controllers\LaporanBulananController;
use App\Http\Controllers\Master\BantuanKabupatenController;
use App\Http\Controllers\PendudukController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\PointController;
use App\Http\Controllers\RiwayatPenggunaController;
use App\Http\Controllers\RtmController;
use App\Http\Controllers\StatistikController;
use App\Http\Controllers\SuplemenController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Web\DownloadCounterController;
use App\Http\Controllers\Web\ModuleController;
use App\Http\Controllers\Web\PageController;
use App\Http\Controllers\Web\PresisiController;
use App\Http\Controllers\Web\SearchController;
use App\Http\Middleware\KabupatenMiddleware;
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

// OTP Login Routes
Route::prefix('login')->group(function () {
    Route::get('/otp', [App\Http\Controllers\Auth\OtpLoginController::class, 'showLoginForm'])->name('otp-login.form');
    Route::post('/otp/send', [App\Http\Controllers\Auth\OtpLoginController::class, 'sendOtp'])->name('otp-login.send');
    Route::post('/otp/verify', [App\Http\Controllers\Auth\OtpLoginController::class, 'verifyOtp'])->name('otp-login.verify');
    Route::post('/otp/resend', [App\Http\Controllers\Auth\OtpLoginController::class, 'resendOtp'])->name('otp-login.resend');
});

Route::get('pengaturan/logo', [IdentitasController::class, 'logo']);

Route::middleware(['auth', 'teams_permission', 'password.weak', '2fa'])->group(function () {
    Route::get('catatan-rilis', CatatanRilis::class);
    Route::get('/dasbor', [DasborController::class, 'index'])->name('dasbor');
    Route::get('dasbor-demografi', [DasborDemografiController::class, 'index'])->name('dasbor-demografi');

    Route::get('password.change', [ChangePasswordController::class, 'showResetForm'])->name('password.change');
    Route::post('password.change', [ChangePasswordController::class, 'reset'])->name('password.change');
    Route::get('users/list', [UserController::class, 'getUsers'])->name('users.list');
    Route::get('users/status/{id}/{status}', [UserController::class, 'status'])->name('users.status');
    Route::get('users/{user}', [UserController::class, 'profile'])->name('profile.edit');
    Route::put('users/{user}', [UserController::class, 'update'])->name('profile.update');
    Route::prefix('pengaturan')->group(function () {
        Route::middleware('easyauthorize:pengaturan-users')->resource('users', UserController::class);
        Route::middleware('easyauthorize:pengaturan-identitas')->resource('identitas', IdentitasController::class)->only(['index', 'edit']);
        Route::middleware('can:pengaturan-group-read')->prefix('groups')->group(function () {
            Route::get('/', [GroupController::class, 'index'])->name('groups.index');
            Route::middleware('can:pengaturan-group-write')->get('/tambah', [GroupController::class, 'create'])->name('groups.create');
            Route::middleware('can:pengaturan-group-edit')->get('/edit/{id}', [GroupController::class, 'edit'])->name('groups.edit');
        });
        Route::resource('activities', RiwayatPenggunaController::class)->only(['index', 'show'])->middleware('easyauthorize:pengaturan-activities');
        Route::resource('settings', App\Http\Controllers\SettingController::class)->except(['show', 'create', 'delete'])->middleware('easyauthorize:pengaturan-settings');
        
        // OTP & 2FA Routes - combined into one page
        Route::prefix('otp')->group(function () {
            Route::get('/', [App\Http\Controllers\OtpController::class, 'index'])->name('otp.index');
            Route::get('/activate', [App\Http\Controllers\OtpController::class, 'activate'])->name('otp.activate');
            Route::post('/setup', [App\Http\Controllers\OtpController::class, 'setup'])->name('otp.setup');
            Route::post('/verify-activation', [App\Http\Controllers\OtpController::class, 'verifyActivation'])->name('otp.verify-activation');
            Route::post('/resend', [App\Http\Controllers\OtpController::class, 'resend'])->name('otp.resend');
            Route::post('/disable', [App\Http\Controllers\OtpController::class, 'disable'])->name('otp.disable');            
        });
        
        // 2FA Routes - for API calls from the combined page
        Route::prefix('2fa')->group(function () {
            Route::get('/', function() {
                return redirect()->route('otp.index');
            })->name('2fa.index');
            Route::get('/activate', [App\Http\Controllers\TwoFactorController::class, 'activate'])->name('2fa.activate');
            Route::post('/enable', [App\Http\Controllers\TwoFactorController::class, 'enable'])->name('2fa.enable');            
            Route::post('/verify', [App\Http\Controllers\TwoFactorController::class, 'verifyEnable'])->name('2fa.verify');
            Route::post('/disable', [App\Http\Controllers\TwoFactorController::class, 'disable'])->name('2fa.disable');
            Route::post('/resend', [App\Http\Controllers\TwoFactorController::class, 'resend'])->name('2fa.resend');
        });
    });    

    Route::prefix('cms')->group(function () {
        Route::resource('categories', App\Http\Controllers\CMS\CategoryController::class)->except(['show'])->middleware('easyauthorize:website-categories');
        Route::resource('articles', App\Http\Controllers\CMS\ArticleController::class)->except(['show'])->middleware('easyauthorize:website-article');
        Route::resource('menus', App\Http\Controllers\CMS\MenuController::class)->except(['show'])->middleware('easyauthorize:website-menu');
        Route::resource('pages', App\Http\Controllers\CMS\PageController::class)->except(['show'])->middleware('easyauthorize:website-pages');
        Route::resource('slides', App\Http\Controllers\CMS\SlideController::class)->except(['show'])->middleware('easyauthorize:website-slider');
        Route::resource('downloads', App\Http\Controllers\CMS\DownloadController::class)->except(['show'])->middleware('easyauthorize:website-downloads');
        Route::get('statistik', App\Http\Controllers\CMS\StatistikPengunjungController::class)->name('cms.statistic.summary')->middleware('permission:website-statistik-read');
    });

    Route::prefix('sesi')->group(function () {
        // Kabupaten
        Route::middleware(KabupatenMiddleware::class)->get('kabupaten/{kodeKabupaten}', function () {
            return redirect()->back();
        });

        // Kecamatan
        Route::middleware(KecamatanMiddleware::class)->get('kecamatan/{kodeKecamatan}', function () {
            return redirect()->back();
        });

        // Desa / Kelurahan
        Route::middleware(WilayahMiddleware::class)->get('desa/{kodeDesa}', function () {
            return redirect()->back();
        });

        // Hapus session berdasarkan level
        Route::get('hapus/{level}', function ($level) {
            if (in_array($level, ['kabupaten', 'kecamatan', 'desa'])) {
                session()->remove($level);

                // Jika kabupaten dihapus, hapus juga kecamatan dan desa
                if ($level === 'kabupaten') {
                    session()->remove('kecamatan');
                    session()->remove('desa');
                }

                // Jika kecamatan dihapus, hapus juga desa
                if ($level === 'kecamatan') {
                    session()->remove('desa');
                }
            }

            return redirect()->back();
        });
    });

    // Penduduk
    Route::middleware(['permission:penduduk-read'])->get('penduduk/cetak', [PendudukController::class, 'cetak']);
    Route::middleware(['permission:penduduk-edit'])->get('penduduk/pindah/{id}', [PendudukController::class, 'pindah'])->name('penduduk.edit');
    Route::middleware(['permission:penduduk-read'])->get('penduduk', [PendudukController::class, 'index'])->name('penduduk.index');
    Route::middleware(['permission:penduduk-read'])->get('penduduk/{id}', [PendudukController::class, 'show'])->name('penduduk.show');
    // Route::middleware(['permission:penduduk-read'])->resource('penduduk', PendudukController::class)->only(['index', 'show']);

    // Keluarga
    Route::middleware(['permission:penduduk-read'])->controller(KeluargaController::class)
        ->prefix('keluarga')
        ->group(function () {
            Route::get('', 'index')->name('keluarga.index');
            Route::get('cetak', 'cetak')->name('keluarga.cetak');
            Route::get('/detail/{no_kk}', 'show')->name('keluarga.detail');
        });

    // rtm
    Route::middleware(['permission:penduduk-read'])->controller(RtmController::class)
        ->prefix('rtm')
        ->group(function () {
            Route::get('', 'index')->name('rtm.index');
            Route::get('cetak', 'cetak')->name('rtm.cetak');
            Route::get('/detail/{no_kk}', 'show')->name('rtm.detail');
        });

    // Bantuan
    Route::middleware(['permission:bantuan-read'])->controller(BantuanController::class)
        ->prefix('bantuan')
        ->group(function () {
            Route::get('/', 'index')->name('bantuan');
            Route::get('/cetak', 'cetak');
            Route::get('/detail/{id}', 'show')->name('bantuan.detail');
        });

    // Lembaga routes
    Route::middleware(['permission:lembaga-read'])->prefix('lembaga')->group(function () {
        Route::get('/', [App\Http\Controllers\LembagaController::class, 'index'])->name('lembaga.index');
        Route::get('/detail', [App\Http\Controllers\LembagaController::class, 'detail'])->name('lembaga.detail');
        Route::get('/cetak', [App\Http\Controllers\LembagaController::class, 'cetak'])->name('lembaga.cetak');
    });
    // Data Pokok
    Route::middleware(['permission:datapresisi-read'])->controller(DataPokokController::class)
        ->prefix('data-pokok')
        ->group(function () {
            Route::middleware(['permission:datapokok-ketenagakerjaan-read'])->get('/ketenagakerjaan', 'ketenagakerjaan')->name('ketenagakerjaan');
            Route::middleware(['permission:datapokok-ketenagakerjaan-read'])->get('/ketenagakerjaan/cetak', 'cetakKetenagakerjaan');

            Route::middleware(['permission:datapokok-pendidikan-read'])->get('/pendidikan', 'pendidikan')->name('pendidikan');
            Route::middleware(['permission:datapokok-pendidikan-read'])->get('/pendidikan/cetak', 'cetakPendidikan')->name('pendidikan.cetak');
            Route::middleware(['permission:datapokok-pariwisata-read'])->get('/pariwisata', 'pariwisata')->name('pariwisata');
            Route::middleware(['permission:datapokok-jaminan-sosial-read'])->get('/jaminan-sosial', 'jaminanSosial')->name('jaminan-sosial');
            Route::middleware(['permission:datapokok-jaminan-sosial-read'])->get('/jaminan-sosial/detail', 'detailJaminanSosial')->name('jaminan-sosial-detail');
            Route::middleware(['permission:datapokok-jaminan-sosial-read'])->get('/jaminan-sosial/cetak', 'cetakJaminanSosial')->name('jaminan-sosial-cetak');
            Route::middleware(['permission:datapokok-kesehatan-read'])->get('/kesehatan', 'kesehatan')->name('kesehatan');
            Route::middleware(['permission:datapokok-kesehatan-read'])->get('/kesehatan/cetak', 'cetakKesehatan')->name('kesehatan.cetak');
            Route::middleware(['permission:datapokok-agama-adat-read'])->get('/agama', 'agama')->name('agama');
            Route::middleware(['permission:datapokok-agama-adat-read'])->get('/agama/detail', 'detail_agama')->name('detail_agama');
            Route::middleware(['permission:datapokok-agama-adat-read'])->get('/agama/cetak', 'cetak_agama')->name('cetak_agama');
            Route::middleware(['permission:datapokok-infrastruktur-read'])->get('/infrastruktur', 'infrastruktur')->name('infrastruktur');

            Route::middleware(['permission:datapokok-sandang-read'])->get('/sandang', 'sandang')->name('datasandang');
            Route::middleware(['permission:datapokok-sandang-read'])->get('/sandang/detail', 'detail_sandang')->name('detail_datasandang');
            Route::middleware(['permission:datapokok-sandang-read'])->get('/sandang/cetak', 'cetak_sandang')->name('cetak_datasandang');
        });

    // Statistik
    Route::middleware(['permission:statistik-read'])->controller(StatistikController::class)
        ->prefix('statistik')
        ->group(function () {
            Route::middleware(['permission:statistik-penduduk-read'])->get('/penduduk', 'penduduk');
            Route::middleware(['permission:statistik-keluarga-read'])->get('/keluarga', 'keluarga');

            Route::middleware(['permission:statistik-rtm-read'])->get('/rtm', 'rtm')->name('statistik.rtm');
            Route::get('/rtm/detail/{tipe?}/{no?}/{sex?}/{kategori}/{kategori_id}', 'detail')->name('statistik.detail');

            Route::middleware(['permission:statistik-bantuan-read'])->get('/bantuan', 'bantuan')->name('statistik.bantuan');
            Route::get('/bantuan/detail/{tipe?}/{no?}/{sex?}/{kategori}/{kategori_id}', 'detailPenduduk')->name('statistik.detail.bantuan');

            Route::get('/cetak/{kategori}/{id}', 'cetak');

            // Data > Kependudukan > Laporan Bulanan
            Route::controller(LaporanBulananController::class)
            ->middleware(['permission:statistik-laporan-bulanan-read'])
            ->prefix('laporan-bulanan')
            ->group(function () {
                Route::get('/', 'index')->name('laporan-bulanan.index');
                Route::post('/filter', 'filter')->name('laporan-bulanan.filter');
                Route::get('/detail-penduduk/{rincian}/{tipe}', 'detailPenduduk')->name('laporan-bulanan.detail-penduduk');
                Route::get('/export-excel', 'exportExcel')->name('laporan-bulanan.export-excel');
                Route::get('/export-excel-detail/{rincian}/{tipe}', 'exportExcelDetail')->name('laporan-bulanan.export-excel-detail');
            });
        });

    // Master Data
    Route::middleware('easyauthorize:organisasi-departemen')->resource('departments', App\Http\Controllers\DepartmentController::class)->except(['show']);
    Route::middleware('easyauthorize:organisasi-position')->resource('positions', App\Http\Controllers\PositionController::class)->except(['show']);
    Route::middleware('easyauthorize:organisasi-employee')->resource('employees', App\Http\Controllers\EmployeeController::class)->except(['show']);
    Route::middleware('permission:organisasi-chart-read')->get('orgchart', App\Http\Controllers\OrgChartController::class);

    Route::prefix('master')
        ->group(function () {
            Route::middleware(['easyauthorize:master-data-bantuan'])->resource('bantuan', BantuanKabupatenController::class)->only(['index', 'create', 'edit']);
            Route::controller(AdminWebController::class)->group(function () {
                Route::middleware(['permission:master-data-artikel-read'])->get('/kategori/{parrent}', 'kategori_index')->name('master-data-artikel.kategori');
                Route::middleware(['permission:master-data-artikel-edit'])->get('/kategori/edit/{id}/{parrent}', 'kategori_edit')->name('master-data-artikel.kategori-edit');
                Route::middleware(['permission:master-data-artikel-write'])->get('/kategori/tambah/{parrent}', 'kategori_create')->name('master-data-artikel.kategori-create');
                Route::middleware(['permission:master-data-pengaturan-read'])->get('/pengaturan', 'pengaturan_index')->name('master-data.pengaturan');
            });
        });

    // Satu Data
    Route::prefix('satu-data')->group(function () {
        Route::prefix('dtks')->group(function () {
            Route::get('papan', [App\Http\Controllers\DTKSController::class, 'index'])->name('satu-data.dtks.index');
            Route::get('cetak', [App\Http\Controllers\DTKSController::class, 'cetak'])->name('satu-data.dtks.cetak');
        });
    });

    Route::prefix('data-presisi')->group(function () {
        Route::prefix('laporan')->group(function () {
            Route::get('/', [App\Http\Controllers\DataPresisiLaporanController::class, 'index'])->name('laporan.data-presisi.index');
            Route::get('cetak', [App\Http\Controllers\DataPresisiLaporanController::class, 'cetak'])->name('laporan.data-presisi.cetak');
                Route::get('/perdesa', [App\Http\Controllers\DataPresisiLaporanController::class, 'perdesa'])->name('laporan.data-presisi.perdesa');
                Route::get('/cetak-perdesa', [App\Http\Controllers\DataPresisiLaporanController::class, 'cetakPerdesa'])->name('laporan.data-presisi.cetak-perdesa');
        })
        ->middleware(['permission:datapresisi-laporan-read']);

        Route::prefix('kesehatan')->group(function () {
            Route::get('/', [App\Http\Controllers\DataPresisiKesehatanController::class, 'index'])->name('data-pokok.data-presisi.index');
            Route::get('/detail', [App\Http\Controllers\DataPresisiKesehatanController::class, 'detail'])->name('data-pokok.data-presisi.detail');
            Route::get('cetak', [App\Http\Controllers\DataPresisiKesehatanController::class, 'cetak'])->name('data-pokok.data-presisi.cetak');
        })
        ->middleware(['permission:datapresisi-kesehatan-read']);

        Route::prefix('seni-budaya')->group(function () {
            Route::get('/', [App\Http\Controllers\DataPresisiSeniBudayaController::class, 'index'])->name('data-pokok.data-presisi-seni-budaya.index');
            Route::get('/detail', [App\Http\Controllers\DataPresisiSeniBudayaController::class, 'detail'])->name('data-pokok.data-presisi-seni-budaya.detail');
            Route::get('cetak', [App\Http\Controllers\DataPresisiSeniBudayaController::class, 'cetak'])->name('data-pokok.data-presisi-seni-budaya.cetak');
        })
        ->middleware(['permission:datapresisi-seni-budaya-read']);

        Route::prefix('ketenagakerjaan')->group(function () {
            Route::get('/', [App\Http\Controllers\DataPresisiKetenagakerjaanController::class, 'index'])->name('data-pokok.data-presisi-ketenagakerjaan.index');
            Route::get('/detail', [App\Http\Controllers\DataPresisiKetenagakerjaanController::class, 'detail'])->name('data-pokok.data-presisi-ketenagakerjaan.detail');
            Route::get('cetak', [App\Http\Controllers\DataPresisiKetenagakerjaanController::class, 'cetak'])->name('data-pokok.data-presisi-ketenagakerjaan.cetak');
        })
        ->middleware(['permission:datapresisi-ketenagakerjaan-read']);

        Route::prefix('pendidikan')->group(function () {
            Route::get('/', [App\Http\Controllers\DataPresisiPendidikanController::class, 'index'])->name('data-pokok.data-presisi-pendidikan.index');
            Route::get('/detail', [App\Http\Controllers\DataPresisiPendidikanController::class, 'detail'])->name('data-pokok.data-presisi-pendidikan.detail');
            Route::get('cetak', [App\Http\Controllers\DataPresisiPendidikanController::class, 'cetak'])->name('data-pokok.data-presisi-pendidikan.cetak');
        })
        ->middleware(['permission:datapresisi-pendidikan-read']);

        Route::prefix('pangan')->group(function () {
            Route::get('/', [App\Http\Controllers\DataPresisiPanganController::class, 'index'])->name('data-pokok.data-presisi-pangan.index');
            Route::get('/detail', [App\Http\Controllers\DataPresisiPanganController::class, 'detail'])->name('data-pokok.data-presisi-pangan.detail');
            Route::get('cetak', [App\Http\Controllers\DataPresisiPanganController::class, 'cetak'])->name('data-pokok.data-presisi-pangan.cetak');
        })
        ->middleware(['permission:datapresisi-pangan-read']);

        Route::prefix('adat')->group(function () {
            Route::get('/', [App\Http\Controllers\DataPresisiAdatController::class, 'index'])->name('data-pokok.data-presisi-adat.index');
            Route::get('/detail', [App\Http\Controllers\DataPresisiAdatController::class, 'detail'])->name('data-pokok.data-presisi-adat.detail');
            Route::get('cetak', [App\Http\Controllers\DataPresisiAdatController::class, 'cetak'])->name('data-pokok.data-presisi-adat.cetak');
        })
        ->middleware(['permission:datapresisi-adat-read']);

        Route::prefix('statistik')->group(function () {
            Route::get('kesehatan', [App\Http\Controllers\StatistikKesehatanController::class, 'index']);
            Route::get('pendidikan', [App\Http\Controllers\StatistikPendidikanController::class, 'index']); 
            Route::get('sandang', [App\Http\Controllers\StatistikSandangController::class, 'index']); 
            Route::get('pangan', [App\Http\Controllers\StatistikPanganController::class, 'index']); 
        });
    });

    // Prodeskel
    Route::prefix('prodeskel')->group(function () {
        Route::prefix('ddk')->group(function () {
            Route::get('pangan', [App\Http\Controllers\DDKPanganController::class, 'index'])->name('ddk.pangan');
        });
    });

    Route::get('/suplemen', [SuplemenController::class, 'index'])->name('suplemen');
    Route::get('/suplemen/form', [SuplemenController::class, 'form'])->name('suplemen.create');
    Route::get('/suplemen/rincian/{id}', [SuplemenController::class, 'detail'])->name('suplemen.detail');
    Route::get('/suplemen/daftar/{id}/{aksi}', [SuplemenController::class, 'daftar'])->name('suplemen.daftar');
    Route::get('/suplemen/ekspor/{id}', [SuplemenController::class, 'ekspor'])->name('suplemen.ekspor');
    Route::get('/suplemen/form/{id?}', [SuplemenController::class, 'form'])->name('suplemen.form');

    Route::get('/point', [PointController::class, 'index'])->name('point');
    Route::get('/point/form/', [PointController::class, 'form'])->name('point.create');
    Route::get('/point/form/{id}', [PointController::class, 'edit'])->name('point.create');
    Route::get('/point/sub/{id?}', [PointController::class, 'sub'])->name('point.create');
    Route::get('/point/rincian/{id}', [PointController::class, 'detail'])->name('point.detail');
    Route::get('/point/lock/{id}/{status}', [PointController::class, 'lock'])->name('point.lock');
    Route::post('/point/form', [PointController::class, 'store'])->name('point.create');
    Route::post('/point/update/{id}', [PointController::class, 'update'])->name('point.update');
    Route::post('/point/sub/{id?}', [PointController::class, 'store'])->name('point.create');
    Route::post('/point/form/{id}', [PointController::class, 'update'])->name('point.update');

    Route::get('/plan/{parent?}', [PlanController::class, 'index'])->name('plan');
    Route::get('/plan/ajax_lokasi_maps/{parrent}/{id}', [PlanController::class, 'ajax_lokasi_maps'])->name('plan.ajax_lokasi_maps');
    Route::get('/show/plan/ajax_lokasi_maps/{parrent}/{id}', [PlanController::class, 'show_ajax_lokasi_maps']);

    Route::resource('desa', DesaController::class)->only(['index']);
    Route::get('desa/cetak', [DesaController::class, 'cetak']);
    Route::resource('kecamatan', KecamatanController::class)->only(['index']);
    Route::get('kecamatan/cetak', [KecamatanController::class, 'cetak']);
});

// 2FA Challenge Routes (tanpa middleware 2fa untuk menghindari loop)
Route::middleware('auth')->prefix('2fa')->group(function () {
    Route::get('/challenge', [App\Http\Controllers\TwoFactorController::class, 'showChallenge'])->name('2fa.challenge');
    Route::post('/challenge', [App\Http\Controllers\TwoFactorController::class, 'verifyChallenge'])->name('2fa.challenge.verify');
});

Route::prefix('presisi')->middleware('check.presisi')->group(function () {
    Route::get('/', [PresisiController::class, 'index'])->name('presisi.index');
    Route::get('/kependudukan', [PresisiController::class, 'kependudukan'])->name('presisi.kependudukan');

    Route::get('/rtm', [PresisiController::class, 'rtm'])->name('presisi.rtm');
    Route::get('/statistik-rtm', [PresisiController::class, 'rtm'])->name('presisi.rtm');
    Route::get('/keluarga', [PresisiController::class, 'keluarga'])->name('presisi.keluarga');
    Route::get('/statistik-keluarga', [PresisiController::class, 'keluarga'])->name('presisi.keluarga');

    Route::get('/kesehatan', [PresisiController::class, 'kesehatan'])->name('presisi.kesehatan');
    Route::get('/kesehatan/{kuartal}/{tahun}/{id}/{kabupaten?}/{kecamatan?}/{desa?}', [PresisiController::class, 'kesehatan']);
    Route::get('/bantuan', [PresisiController::class, 'bantuan'])->name('presisi.bantuan');
    Route::get('/statistik-bantuan', [PresisiController::class, 'bantuan'])->name('presisi.bantuan');
    Route::get('/geo-spasial', [PresisiController::class, 'geoSpasial'])->name('presisi.geo-spasial');
});

Route::middleware(['website.enable', 'log.visitor'])->group(function () {
    Route::get('/', [PageController::class, 'getIndex'])->name('web.index');
    Route::get('a/{aSlug}', [PageController::class, 'getArticle'])->name('article');
    Route::get('p/{pSlug}', [PageController::class, 'getPage'])->name('page');
    Route::get('c/{cSlug}', [PageController::class, 'getCategory'])->name('category');
    Route::get('sitemap.xml', [PageController::class, 'getSitemap'])->name('sitemap');
    Route::get('search', SearchController::class)->name('web.search');
    Route::get('module/{moduleName}', ModuleController::class)->name('web.module');
    Route::post('download/{download}', DownloadCounterController::class)->name('web.download.counter');
});

Route::get('/module/penduduk/{id}', [PresisiController::class, 'kependudukan'])->name('presisi.kependudukan');
Route::get('/statistik-penduduk', [PresisiController::class, 'kependudukan'])->name('presisi.kependudukan');
Route::get('/module/rtm/{id}', [PresisiController::class, 'rtm'])->name('presisi.rtm');
Route::get('/module/bantuan/{id}', [PresisiController::class, 'bantuan'])->name('presisi.bantuan');
Route::get('/statistik-bantuan', [PresisiController::class, 'bantuan'])->name('presisi.bantuan');
Route::get('/module/keluarga/{id}', [PresisiController::class, 'keluarga'])->name('presisi.keluarga');
Route::get('/statistik-keluarga', [PresisiController::class, 'keluarga'])->name('presisi.keluarga');
Route::get('/module/kesehatan/{id}', [PresisiController::class, 'kesehatan'])->name('presisi.kesehatan');
Route::get('/statistik-kesehatan', [PresisiController::class, 'kesehatan'])->name('presisi.kesehatan');
