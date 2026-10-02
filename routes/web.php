<?php

use App\Http\Controllers\AdminWebController;
use App\Http\Controllers\ApiProxyController;
use App\Http\Controllers\Auth\ChangePasswordController;
use App\Http\Controllers\Auth\OtpLoginController;
use App\Http\Controllers\BantuanController;
use App\Http\Controllers\CatatanRilis;
use App\Http\Controllers\CMS\ArticleController;
use App\Http\Controllers\CMS\CategoryController;
use App\Http\Controllers\CMS\DownloadController;
use App\Http\Controllers\CMS\MenuController;
use App\Http\Controllers\CMS\SlideController;
use App\Http\Controllers\CMS\StatistikPengunjungController;
use App\Http\Controllers\DasborController;
use App\Http\Controllers\DasborDemografiController;
use App\Http\Controllers\DataPokokController;
use App\Http\Controllers\DataPresisiAdatController;
use App\Http\Controllers\DataPresisiAktivitasKeagamaanController;
use App\Http\Controllers\DataPresisiJaminanSosialController;
use App\Http\Controllers\DataPresisiKesehatanController;
use App\Http\Controllers\DataPresisiKetenagakerjaanController;
use App\Http\Controllers\DataPresisiLaporanController;
use App\Http\Controllers\DataPresisiPanganController;
use App\Http\Controllers\DataPresisiPapanController;
use App\Http\Controllers\DataPresisiPendidikanController;
use App\Http\Controllers\DataPresisiSandangController;
use App\Http\Controllers\DataPresisiSeniBudayaController;
use App\Http\Controllers\DDKPanganController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DesaController;
use App\Http\Controllers\DTKSController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ForcePasswordResetController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\IdentitasController;
use App\Http\Controllers\ImageProxyController;
use App\Http\Controllers\KecamatanController;
use App\Http\Controllers\KeluargaController;
use App\Http\Controllers\LaporanBulananController;
use App\Http\Controllers\LaporanDesaAktifController;
use App\Http\Controllers\LembagaController;
use App\Http\Controllers\Master\ArtikelKabupatenController;
use App\Http\Controllers\Master\ArtikelUploadController;
use App\Http\Controllers\Master\BantuanKabupatenController;
use App\Http\Controllers\OrgChartController;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\PendudukController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\PointController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\RiwayatPenggunaController;
use App\Http\Controllers\RtmController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\Sso\SsoAuditController;
use App\Http\Controllers\Sso\SsoLoginController;
use App\Http\Controllers\StatistikAdatController;
use App\Http\Controllers\StatistikAktivitasKeagamaanController;
use App\Http\Controllers\StatistikController;
use App\Http\Controllers\StatistikJaminanSosialController;
use App\Http\Controllers\StatistikKesehatanController;
use App\Http\Controllers\StatistikKetenagakerjaanController;
use App\Http\Controllers\StatistikPanganController;
use App\Http\Controllers\StatistikPapanController;
use App\Http\Controllers\StatistikPendidikanController;
use App\Http\Controllers\StatistikSandangController;
use App\Http\Controllers\StatistikSenibudayaController;
use App\Http\Controllers\SuplemenController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Web\ArtikelController;
use App\Http\Controllers\Web\DownloadCounterController;
use App\Http\Controllers\Web\ModuleController;
use App\Http\Controllers\Web\PageController;
use App\Http\Controllers\Web\PresisiController;
use App\Http\Controllers\Web\SearchController;
use App\Http\Middleware\KabupatenMiddleware;
use App\Http\Middleware\KecamatanMiddleware;
use App\Http\Middleware\WilayahMiddleware;
use App\Models\User;
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
    Route::get('/otp', [OtpLoginController::class, 'showLoginForm'])->name('otp-login.form');
    Route::post('/otp/send', [OtpLoginController::class, 'sendOtp'])->name('otp-login.send');
    Route::post('/otp/verify', [OtpLoginController::class, 'verifyOtp'])->name('otp-login.verify');
    Route::post('/otp/resend', [OtpLoginController::class, 'resendOtp'])->name('otp-login.resend');
});

Route::get('pengaturan/logo', [IdentitasController::class, 'logo']);

Route::middleware(['auth', 'teams_permission', 'password.expiry', 'password.weak', '2fa'])->group(function () {
    Route::get('catatan-rilis', CatatanRilis::class);
    Route::get('/dasbor', [DasborController::class, 'index'])->name('dasbor');
    Route::get('dasbor-demografi', [DasborDemografiController::class, 'index'])->name('dasbor-demografi');

    // SSO OpenSID: terbitkan token akses sekali pakai untuk administrator
    Route::post('/sso/generate-session', [SsoLoginController::class, 'generateSession'])->name('sso.generate')->middleware('throttle:sso-generate');

    // SSO OpenSID: dashboard audit percobaan akses (super admin)
    Route::middleware('permission:sso-audit-read')->get('/sso/audit', [SsoAuditController::class, 'index'])->name('sso.audit');

    // Force Password Reset Routes
    Route::get('password-reset/force', [ForcePasswordResetController::class, 'showForm'])->name('password.reset.form');
    Route::post('password-reset/force', [ForcePasswordResetController::class, 'reset'])->name('password.reset.force');

    Route::get('password.change', [ChangePasswordController::class, 'showResetForm'])->name('password.change.form');
    Route::post('password.change', [ChangePasswordController::class, 'change'])->name('password.change');
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
        Route::resource('settings', SettingController::class)->except(['show', 'create', 'delete'])->middleware('easyauthorize:pengaturan-settings');

        // OTP & 2FA Routes - combined into one page
        Route::prefix('otp')->group(function () {
            Route::get('/', [OtpController::class, 'index'])->name('otp.index');
            Route::get('/activate', [OtpController::class, 'activate'])->name('otp.activate');
            Route::post('/setup', [OtpController::class, 'setup'])->name('otp.setup');
            Route::post('/verify-activation', [OtpController::class, 'verifyActivation'])->name('otp.verify-activation');
            Route::post('/resend', [OtpController::class, 'resend'])->name('otp.resend');
            Route::post('/disable', [OtpController::class, 'disable'])->name('otp.disable');
        });

        // 2FA Routes - for API calls from the combined page
        Route::prefix('2fa')->group(function () {
            Route::get('/', function () {
                return redirect()->route('otp.index');
            })->name('2fa.index');
            Route::get('/activate', [TwoFactorController::class, 'activate'])->name('2fa.activate');
            Route::post('/enable', [TwoFactorController::class, 'enable'])->name('2fa.enable');
            Route::post('/verify', [TwoFactorController::class, 'verifyEnable'])->name('2fa.verify');
            Route::post('/disable', [TwoFactorController::class, 'disable'])->name('2fa.disable');
            Route::post('/resend', [TwoFactorController::class, 'resend'])->name('2fa.resend');
        });
    });

    Route::prefix('cms')->group(function () {
        Route::resource('categories', CategoryController::class)->except(['show'])->middleware('easyauthorize:website-categories');
        Route::resource('articles', ArticleController::class)->except(['show'])->middleware('easyauthorize:website-article');
        Route::resource('menus', MenuController::class)->except(['show'])->middleware('easyauthorize:website-menu');
        Route::resource('pages', App\Http\Controllers\CMS\PageController::class)->except(['show'])->middleware('easyauthorize:website-pages');
        Route::resource('slides', SlideController::class)->except(['show'])->middleware('easyauthorize:website-slider');
        Route::resource('downloads', DownloadController::class)->except(['show'])->middleware('easyauthorize:website-downloads');
        Route::get('statistik', StatistikPengunjungController::class)->name('cms.statistic.summary')->middleware('permission:website-statistik-read');
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
            Route::get('/detail/{id}/cetak', 'cetakPeserta')->name('bantuan.detail.cetak');
            Route::get('/detail/{id}', 'show')->name('bantuan.detail');
        });

    // Lembaga routes
    Route::middleware(['permission:lembaga-read'])->prefix('lembaga')->group(function () {
        Route::get('/', [LembagaController::class, 'index'])->name('lembaga.index');
        Route::get('/detail', [LembagaController::class, 'detail'])->name('lembaga.detail');
        Route::get('/cetak', [LembagaController::class, 'cetak'])->name('lembaga.cetak');
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
            Route::middleware(['permission:datapokok-pariwisata-read'])->get('/pariwisata/cetak', 'cetakPariwisata')->name('pariwisata.cetak');
            Route::middleware(['permission:datapokok-jaminan-sosial-read'])->get('/jaminan-sosial', 'jaminanSosial')->name('jaminan-sosial');
            Route::middleware(['permission:datapokok-jaminan-sosial-read'])->get('/jaminan-sosial/detail', 'detailJaminanSosial')->name('jaminan-sosial-detail');
            Route::middleware(['permission:datapokok-jaminan-sosial-read'])->get('/jaminan-sosial/cetak', 'cetakJaminanSosial')->name('jaminan-sosial-cetak');
            Route::middleware(['permission:datapokok-kesehatan-read'])->get('/kesehatan', 'kesehatan')->name('kesehatan');
            Route::middleware(['permission:datapokok-kesehatan-read'])->get('/kesehatan/cetak', 'cetakKesehatan')->name('kesehatan.cetak');
            Route::middleware(['permission:datapokok-agama-adat-read'])->get('/agama', 'agama')->name('agama');
            Route::middleware(['permission:datapokok-agama-adat-read'])->get('/agama/detail', 'detail_agama')->name('detail_agama');
            Route::middleware(['permission:datapokok-agama-adat-read'])->get('/agama/cetak', 'cetak_agama')->name('cetak_agama');
            Route::middleware(['permission:datapokok-infrastruktur-read'])->get('/infrastruktur', 'infrastruktur')->name('infrastruktur');
            Route::middleware(['permission:datapokok-infrastruktur-read'])->get('/infrastruktur/cetak', 'cetakInfrastruktur')->name('infrastruktur.cetak');

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
    Route::middleware('easyauthorize:organisasi-departemen')->resource('departments', DepartmentController::class)->except(['show']);
    Route::middleware('easyauthorize:organisasi-position')->resource('positions', PositionController::class)->except(['show']);
    Route::middleware('easyauthorize:organisasi-employee')->resource('employees', EmployeeController::class)->except(['show']);
    Route::middleware('permission:organisasi-chart-read')->get('orgchart', OrgChartController::class);

    Route::prefix('master')
        ->group(function () {
            Route::middleware(['easyauthorize:master-data-bantuan'])->resource('bantuan', BantuanKabupatenController::class)->only(['index', 'create', 'edit']);
            Route::middleware(['easyauthorize:master-data-artikel'])->resource('artikel', ArtikelKabupatenController::class)->names('master-data-artikel')->only(['index', 'create', 'edit']);
            Route::post('artikel/upload-gambar', [ArtikelUploadController::class, 'uploadGambar'])->name('artikel.upload_gambar');
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
            Route::get('papan', [DTKSController::class, 'index'])->name('satu-data.dtks.index');
            Route::get('cetak', [DTKSController::class, 'cetak'])->name('satu-data.dtks.cetak');
        });
    });
    Route::prefix('laporan')->group(function () {
        Route::prefix('desa-aktif')->group(function () {
            Route::get('', [LaporanDesaAktifController::class, 'index'])->name('laporan.desa-aktif.index');
            Route::get('cetak', [LaporanDesaAktifController::class, 'cetak'])->name('laporan.desa-aktif.cetak');
            Route::get('export-excel', [LaporanDesaAktifController::class, 'exportExcel'])->name('laporan.desa-aktif.export-excel');
        });
    });
    Route::prefix('data-presisi')->group(function () {
        Route::prefix('laporan')->group(function () {
            Route::get('/', [DataPresisiLaporanController::class, 'index'])->name('laporan.data-presisi.index');
            Route::get('cetak', [DataPresisiLaporanController::class, 'cetak'])->name('laporan.data-presisi.cetak');
            Route::get('/perdesa', [DataPresisiLaporanController::class, 'perdesa'])->name('laporan.data-presisi.perdesa');
            Route::get('/cetak-perdesa', [DataPresisiLaporanController::class, 'cetakPerdesa'])->name('laporan.data-presisi.cetak-perdesa');
        })->middleware(['permission:datapresisi-laporan-read']);

        Route::prefix('kesehatan')->group(function () {
            Route::get('/', [DataPresisiKesehatanController::class, 'index'])->name('data-pokok.data-presisi.index');
            Route::get('/detail', [DataPresisiKesehatanController::class, 'detail'])->name('data-pokok.data-presisi.detail');
            Route::get('/detail_data', [DataPresisiKesehatanController::class, 'detailData'])->name('data-pokok.data-presisi.detail_data');
            Route::get('cetak', [DataPresisiKesehatanController::class, 'cetak'])->name('data-pokok.data-presisi.cetak');
        })
            ->middleware(['permission:datapresisi-kesehatan-read']);

        Route::prefix('seni-budaya')->group(function () {
            Route::get('/', [DataPresisiSeniBudayaController::class, 'index'])->name('data-pokok.data-presisi-seni-budaya.index');
            Route::get('/detail', [DataPresisiSeniBudayaController::class, 'detail'])->name('data-pokok.data-presisi-seni-budaya.detail');
            Route::get('/detail_data', [DataPresisiSeniBudayaController::class, 'detailData'])->name('data-pokok.data-presisi-seni-budaya.detail_data');
            Route::get('cetak', [DataPresisiSeniBudayaController::class, 'cetak'])->name('data-pokok.data-presisi-seni-budaya.cetak');
        })
            ->middleware(['permission:datapresisi-seni-budaya-read']);

        Route::prefix('ketenagakerjaan')->group(function () {
            Route::get('/', [DataPresisiKetenagakerjaanController::class, 'index'])->name('data-pokok.data-presisi-ketenagakerjaan.index');
            Route::get('/detail', [DataPresisiKetenagakerjaanController::class, 'detail'])->name('data-pokok.data-presisi-ketenagakerjaan.detail');
            Route::get('detail_data', [DataPresisiKetenagakerjaanController::class, 'detailData'])->name('data-pokok.data-presisi-ketenagakerjaan.detail_data');
            Route::get('cetak', [DataPresisiKetenagakerjaanController::class, 'cetak'])->name('data-pokok.data-presisi-ketenagakerjaan.cetak');
        })
            ->middleware(['permission:datapresisi-ketenagakerjaan-read']);

        Route::prefix('jaminan-sosial')->group(function () {
            Route::get('/detail_data', [DataPresisiJaminanSosialController::class, 'detailData'])->name('data-pokok.data-presisi-jaminan-sosial.detail_data');
        })
            ->middleware(['permission:datapresisi-jaminan-sosial-read']);
        Route::prefix('aktivitas-keagamaan')->group(function () {
            Route::get('detail_data', [DataPresisiAktivitasKeagamaanController::class, 'detailData'])->name('data-pokok.data-presisi-aktivitas-keagamaan.detail_data');
        })
            ->middleware(['permission:datapresisi-aktivitas-keagamaan-read']);

        Route::prefix('pendidikan')->group(function () {
            Route::get('/', [DataPresisiPendidikanController::class, 'index'])->name('data-pokok.data-presisi-pendidikan.index');
            Route::get('/detail', [DataPresisiPendidikanController::class, 'detail'])->name('data-pokok.data-presisi-pendidikan.detail');
            Route::get('detail_data', [DataPresisiPendidikanController::class, 'detailData'])->name('data-pokok.data-presisi-pendidikan.detail_data');
            Route::get('cetak', [DataPresisiPendidikanController::class, 'cetak'])->name('data-pokok.data-presisi-pendidikan.cetak');
        })
            ->middleware(['permission:datapresisi-pendidikan-read']);

        Route::prefix('pangan')->group(function () {
            Route::get('/', [DataPresisiPanganController::class, 'index'])->name('data-pokok.data-presisi-pangan.index');
            Route::get('/detail', [DataPresisiPanganController::class, 'detail'])->name('data-pokok.data-presisi-pangan.detail');
            Route::get('detail_data', [DataPresisiPanganController::class, 'detailData'])->name('data-pokok.data-presisi-pangan.detail_data');
            Route::get('cetak', [DataPresisiPanganController::class, 'cetak'])->name('data-pokok.data-presisi-pangan.cetak');
        })
            ->middleware(['permission:datapresisi-pangan-read']);

        Route::prefix('papan')->group(function () {
            Route::get('detail_data', [DataPresisiPapanController::class, 'detailData'])->name('data-pokok.data-presisi-papan.detail_data');
            Route::get('detail', [DataPresisiPapanController::class, 'detail'])->name('data-pokok.data-presisi-papan.detail_datasandang');
            Route::get('cetak', [DataPresisiPapanController::class, 'cetak'])->name('data-pokok.data-presisi-papan.cetak');
        })
            ->middleware(['permission:datapresisi-papan-read']);

        Route::prefix('sandang')->group(function () {
            Route::get('detail_data', [DataPresisiSandangController::class, 'detailData'])->name('data-pokok.data-presisi-sandang.detail_data');
        })
            ->middleware(['permission:datapresisi-sandang-read']);

        Route::prefix('adat')->group(function () {
            Route::get('/', [DataPresisiAdatController::class, 'index'])->name('data-pokok.data-presisi-adat.index');
            Route::get('/detail', [DataPresisiAdatController::class, 'detail'])->name('data-pokok.data-presisi-adat.detail');
            Route::get('cetak', [DataPresisiAdatController::class, 'cetak'])->name('data-pokok.data-presisi-adat.cetak');
        })
            ->middleware(['permission:datapresisi-adat-read']);

        Route::prefix('statistik')->group(function () {
            Route::get('adat', [StatistikAdatController::class, 'index']);
            Route::get('kesehatan', [StatistikKesehatanController::class, 'index']);
            Route::get('jaminan-sosial', [StatistikJaminanSosialController::class, 'index']);
            Route::get('aktivitas-keagamaan', [StatistikAktivitasKeagamaanController::class, 'index']);
            Route::get('ketenagakerjaan', [StatistikKetenagakerjaanController::class, 'index']);
            Route::get('pendidikan', [StatistikPendidikanController::class, 'index']);
            Route::get('sandang', [StatistikSandangController::class, 'index']);
            Route::get('papan', [StatistikPapanController::class, 'index']);
            Route::get('senibudaya', [StatistikSenibudayaController::class, 'index']);
            Route::get('pangan', [StatistikPanganController::class, 'index']);
        });
    });

    // Prodeskel
    Route::prefix('prodeskel')->group(function () {
        Route::prefix('ddk')->group(function () {
            Route::get('pangan', [DDKPanganController::class, 'index'])->name('ddk.pangan');
        });
    });

    Route::get('/suplemen', [SuplemenController::class, 'index'])->name('suplemen');
    Route::get('/suplemen/form', [SuplemenController::class, 'form'])->name('suplemen.create');
    Route::get('/suplemen/rincian/{id}', [SuplemenController::class, 'detail'])->name('suplemen.detail');
    Route::get('/suplemen/daftar/{id}/{aksi}', [SuplemenController::class, 'daftar'])->name('suplemen.daftar');
    Route::get('/suplemen/ekspor/{id}', [SuplemenController::class, 'ekspor'])->name('suplemen.ekspor');
    Route::get('/suplemen/form/{id?}', [SuplemenController::class, 'form'])->name('suplemen.form');

    Route::get('/point', [PointController::class, 'index'])->name('point');
    Route::get('/point/form/', [PointController::class, 'form'])->name('point.form');
    Route::get('/point/form/{id}', [PointController::class, 'edit'])->name('point.edit');
    Route::get('/point/sub/{id?}', [PointController::class, 'sub'])->name('point.sub');
    Route::get('/point/rincian/{id}', [PointController::class, 'detail'])->name('point.detail');
    Route::get('/point/lock/{id}/{status}', [PointController::class, 'lock'])->name('point.lock');
    Route::post('/point/form', [PointController::class, 'store'])->name('point.store');
    Route::post('/point/update/{id}', [PointController::class, 'update'])->name('point.update');
    Route::post('/point/sub/{id?}', [PointController::class, 'store'])->name('point.sub_store');
    Route::post('/point/form/{id}', [PointController::class, 'update'])->name('point.form_update');

    Route::get('/plan/{parent?}', [PlanController::class, 'index'])->name('plan');
    Route::get('/plan/ajax_lokasi_maps/{parrent}/{id}', [PlanController::class, 'ajax_lokasi_maps'])->name('plan.ajax_lokasi_maps');
    Route::get('/show/plan/ajax_lokasi_maps/{parrent}/{id}', [PlanController::class, 'show_ajax_lokasi_maps']);

    Route::resource('desa', DesaController::class)->only(['index']);
    Route::get('desa/cetak', [DesaController::class, 'cetak']);
    Route::resource('kecamatan', KecamatanController::class)->only(['index']);
    Route::get('kecamatan/cetak', [KecamatanController::class, 'cetak']);

    Route::prefix('api-proxy')->group(function () {
        Route::get('get', [ApiProxyController::class, 'get'])->name('api-proxy.get');
        Route::post('post', [ApiProxyController::class, 'post'])->name('api-proxy.post');
        Route::get('clear-cache', [ApiProxyController::class, 'clearCache'])->name('api-proxy.clear-cache');
    });
});

// 2FA Challenge Routes (tanpa middleware 2fa untuk menghindari loop)
Route::middleware('auth')->prefix('2fa')->group(function () {
    Route::get('/challenge', [TwoFactorController::class, 'showChallenge'])->name('2fa.challenge');
    Route::post('/challenge', [TwoFactorController::class, 'verifyChallenge'])->name('2fa.challenge.verify');
});

Route::prefix('presisi')->middleware('check.presisi')->group(function () {
    Route::get('/', [PresisiController::class, 'index'])->name('presisi.index');
    Route::get('/kependudukan', [PresisiController::class, 'kependudukan'])->name('presisi.kependudukan');

    Route::get('/rtm', [PresisiController::class, 'rtm'])->name('presisi.rtm');
    Route::get('/statistik-rtm', [PresisiController::class, 'rtm'])->name('presisi.rtm_statistik');
    Route::get('/keluarga', [PresisiController::class, 'keluarga'])->name('presisi.keluarga');
    Route::get('/statistik-keluarga', [PresisiController::class, 'keluarga'])->name('presisi.keluarga_statistik_outer');

    Route::get('/kesehatan', [PresisiController::class, 'kesehatan'])->name('presisi.kesehatan');
    Route::get('/kesehatan/{kuartal}/{tahun}/{id}/{kabupaten?}/{kecamatan?}/{desa?}', [PresisiController::class, 'kesehatan']);
    Route::get('/bantuan', [PresisiController::class, 'bantuan'])->name('presisi.bantuan');
    Route::get('/statistik-bantuan', [PresisiController::class, 'bantuan'])->name('presisi.bantuan_statistik_outer');
    Route::get('/geo-spasial', [PresisiController::class, 'geoSpasial'])->name('presisi.geo-spasial');
});

Route::middleware(['website.enable', 'log.visitor'])->group(function () {
    Route::get('/', [PageController::class, 'getIndex'])->name('web.index');
    Route::get('artikel/terbaru', [ArtikelController::class, 'terbaru'])->name('web.artikel.terbaru');
    Route::get('artikel-opensid', [ArtikelController::class, 'index'])->name('web.artikel.index');
    Route::get('artikel-opensid/{id}', [ArtikelController::class, 'show'])->name('web.artikel.show');
    Route::get('a/{aSlug}', [PageController::class, 'getArticle'])->name('article');
    Route::get('p/{pSlug}', [PageController::class, 'getPage'])->name('page');
    Route::get('c/{cSlug}', [PageController::class, 'getCategory'])->name('category');
    Route::get('sitemap.xml', [PageController::class, 'getSitemap'])->name('sitemap');
    Route::get('search', SearchController::class)->name('web.search');
    Route::get('module/{moduleName}', ModuleController::class)->name('web.module');
    Route::post('download/{download}', DownloadCounterController::class)->name('web.download.counter');
});

Route::get('/module/penduduk/{id}', [PresisiController::class, 'kependudukan'])->name('presisi.kependudukan_module');
Route::get('/statistik-penduduk', [PresisiController::class, 'kependudukan'])->name('presisi.kependudukan_statistik');
Route::get('/module/rtm/{id}', [PresisiController::class, 'rtm'])->name('presisi.rtm_module');
Route::get('/module/bantuan/{id}', [PresisiController::class, 'bantuan'])->name('presisi.bantuan_module');
Route::get('/statistik-bantuan', [PresisiController::class, 'bantuan'])->name('presisi.bantuan_statistik');
Route::get('/module/keluarga/{id}', [PresisiController::class, 'keluarga'])->name('presisi.keluarga_module');
Route::get('/statistik-keluarga', [PresisiController::class, 'keluarga'])->name('presisi.keluarga_statistik');
Route::get('/module/kesehatan/{id}', [PresisiController::class, 'kesehatan'])->name('presisi.kesehatan_module');
Route::get('/statistik-kesehatan', [PresisiController::class, 'kesehatan'])->name('presisi.kesehatan_statistik');

// Image proxy route with rate limiting
Route::middleware('throttle:30,1')->get('/image-proxy', [ImageProxyController::class, 'proxy'])->name('image.proxy');

// Pest Browser Testing - Quick login route (hanya untuk testing)
if (app()->environment('testing')) {
    Route::get('/_pest/login/{userId}', function ($userId) {
        $user = User::findOrFail($userId);
        Auth::login($user);

        return response(status: 200);
    })->middleware('web');
}
