<?php

use App\Http\Controllers\AdminWebController;
use App\Http\Controllers\Auth\ChangePasswordController;
use App\Http\Controllers\BantuanController;
use App\Http\Controllers\CatatanRilis;
use App\Http\Controllers\DasborController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\IdentitasController;
use App\Http\Controllers\KeluargaController;
use App\Http\Controllers\Master\BantuanKabupatenController;
use App\Http\Controllers\PendudukController;
use App\Http\Controllers\RiwayatPenggunaController;
use App\Http\Controllers\StatistikController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Web\PageController;
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

Route::middleware(['auth', 'teams_permission', 'password.weak'])->group(function () {
    Route::get('catatan-rilis', CatatanRilis::class);
    Route::get('/dasbor', [DasborController::class, 'index'])->name('dasbor');
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
        Route::resource('settings', App\Http\Controllers\SettingController::class)->except(['show', 'create'])->middleware('easyauthorize:pengaturan-settings');
    });

    Route::prefix('cms')->group(function(){
        Route::resource('categories', App\Http\Controllers\CMS\CategoryController::class)->except(['show'])->middleware('easyauthorize:website-categories');
        Route::resource('articles', App\Http\Controllers\CMS\ArticleController::class)->except(['show'])->middleware('easyauthorize:website-article');
        Route::resource('menus', App\Http\Controllers\CMS\MenuController::class)->except(['show'])->middleware('easyauthorize:website-menu');
        Route::resource('pages', App\Http\Controllers\CMS\PageController::class)->except(['show'])->middleware('easyauthorize:website-pages');
        Route::resource('slides', App\Http\Controllers\CMS\SlideController::class)->except(['show'])->middleware('easyauthorize:website-slider');
        Route::resource('downloads', App\Http\Controllers\CMS\DownloadController::class)->except(['show'])->middleware('easyauthorize:website-downloads');
        Route::get('statistik', App\Http\Controllers\CMS\StatistikPengunjungController::class)->name('cms.statistic.summary')->middleware('permission:website-statistik-read');
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
    Route::middleware(['permission:penduduk-read'])->get('penduduk/cetak', [PendudukController::class, 'cetak']);
    Route::middleware(['permission:penduduk-edit'])->get('penduduk/pindah/{id}', [PendudukController::class, 'pindah'])->name('penduduk.edit');
    Route::middleware(['permission:penduduk-read'])->resource('penduduk', PendudukController::class)->only(['index', 'show']);

    // Keluarga
    Route::middleware(['permission:penduduk-read'])->controller(KeluargaController::class)
        ->prefix('keluarga')
        ->group(function () {
            Route::middleware(['permission:penduduk-read'])->get('/detail/{no_kk}', 'show')->name('keluarga.detail');
        });

    // Bantuan
    Route::middleware(['permission:bantuan-read'])->controller(BantuanController::class)
        ->prefix('bantuan')
        ->group(function () {
            Route::get('/', 'index')->name('bantuan');
            Route::get('/cetak', 'cetak');
            Route::get('/detail/{id}', 'show')->name('bantuan.detail');
        });

    // Statistik
    Route::middleware(['permission:statistik-read'])->controller(StatistikController::class)
        ->prefix('statistik')
        ->group(function () {
            Route::middleware(['permission:statistik-penduduk-read'])->get('/penduduk', 'penduduk');
            Route::middleware(['permission:statistik-keluarga-read'])->get('/keluarga', 'keluarga');
            Route::middleware(['permission:statistik-rtm-read'])->get('/rtm', 'rtm');
            Route::middleware(['permission:statistik-bantuan-read'])->get('/bantuan', 'bantuan');
            Route::get('/cetak/{kategori}/{id}', 'cetak');
        });

    // Master Data
    Route::middleware('easyauthorize:organisasi-departemen')->resource('departments', App\Http\Controllers\DepartmentController::class)->except(['show']);
    Route::middleware('easyauthorize:organisasi-position')->resource('positions', App\Http\Controllers\PositionController::class)->except(['show']);
    Route::middleware('easyauthorize:organisasi-employee')->resource('employees', App\Http\Controllers\EmployeeController::class)->except(['show']);
    Route::middleware('permission:organisasi-chart-read')->get('orgchart', App\Http\Controllers\OrgChartController::class);

    Route::prefix('master')
        ->group(function () {
            Route::middleware(['easyauthorize:master-data-artikel'])->resource('bantuan', BantuanKabupatenController::class)->only(['index', 'create', 'edit']);
            Route::controller(AdminWebController::class)->group(function () {
                Route::middleware(['permission:master-data-artikel-read'])->get('/kategori/{parrent}', 'kategori_index')->name('master-data-artikel.kategori');
                Route::middleware(['permission:master-data-artikel-edit'])->get('/kategori/edit/{id}/{parrent}', 'kategori_edit')->name('master-data-artikel.kategori-edit');
                Route::middleware(['permission:master-data-artikel-write'])->get('/kategori/tambah/{parrent}', 'kategori_create')->name('master-data-artikel.kategori-create');
                Route::middleware(['permission:master-data-pengaturan-read'])->get('/pengaturan', 'pengaturan_index')->name('master-data.pengaturan');
            });
        });
});

Route::middleware(['website.enable', 'log.visitor'])->group(function(){
    Route::get('/', [PageController::class, 'getIndex'])->name('article');
    Route::get('a/{aSlug}', [PageController::class, 'getArticle'])->name('article');
    Route::get('p/{pSlug}', [PageController::class, 'getPage'])->name('page');
    Route::get('c/{cSlug}', [PageController::class, 'getCategory'])->name('category');
    Route::get('sitemap.xml', [PageController::class, 'getSitemap'])->name('sitemap');
});
