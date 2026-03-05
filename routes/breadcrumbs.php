<?php

use App\Models\CMS\Article;
use App\Models\CMS\Category;
use App\Models\CMS\Download;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Identitas;
use App\Models\Position;
use App\Models\Setting;
use App\Models\Team;
use App\Models\User;
use App\Services\ArtikelService;
use App\Services\BantuanService;
use App\Services\KategoriService;
use App\Services\PendudukApiService;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;
use Illuminate\Support\Facades\Auth;

Breadcrumbs::for('master-data.pengaturan', function (BreadcrumbTrail $trail) {
    $trail->push('Pengaturan Aplikasi');
});
Breadcrumbs::for('bantuan', function (BreadcrumbTrail $trail) {
    $trail->push('Bantuan', route('bantuan'));
});
Breadcrumbs::for('bantuan.detail', function (BreadcrumbTrail $trail, $id) {
    $bantuan = (new BantuanService)->bantuan(['filter[id]' => $id])[0]?->nama ?? '-';
    $trail->parent('bantuan');
    $trail->push($bantuan);
});

Breadcrumbs::for('bantuan.index', function (BreadcrumbTrail $trail) {
    $trail->push('Master Bantuan', route('bantuan.index'));
});
Breadcrumbs::for('bantuan.create', function (BreadcrumbTrail $trail) {
    $trail->parent('bantuan.index');
    $trail->push('Baru');
});
Breadcrumbs::for('bantuan.edit', function (BreadcrumbTrail $trail, $id) {
    $bantuan = (new BantuanService)->bantuan(['filter[id]' => $id])[0]?->nama ?? '-';
    $trail->parent('bantuan.index');
    $trail->push($bantuan);
});
Breadcrumbs::for('master-data-artikel.kategori', function (BreadcrumbTrail $trail, $parent) {
    $service = (new KategoriService)->kategori($parent);
    $name = $service ? $service['attributes']['kategori'] : 'Artikel';
    $trail->push('Master Kategori '.$name, route('master-data-artikel.kategori', $parent));
});
Breadcrumbs::for('master-data-artikel.kategori-create', function (BreadcrumbTrail $trail, $parent) {
    $trail->parent('master-data-artikel.kategori', $parent);
    $trail->push('Baru');
});
Breadcrumbs::for('master-data-artikel.kategori-edit', function (BreadcrumbTrail $trail, $id, $parent) {
    $service = (new KategoriService)->kategori($id);
    $name = $service ? $service['attributes']['kategori'] : 'Artikel';
    $trail->parent('master-data-artikel.kategori', $parent);
    $trail->push($name);
});

Breadcrumbs::for('master-data-artikel.index', function (BreadcrumbTrail $trail) {
    $trail->push('Master Artikel', route('master-data-artikel.index'));
});
Breadcrumbs::for('master-data-artikel.create', function (BreadcrumbTrail $trail) {
    $trail->parent('master-data-artikel.index');
    $trail->push('Baru');
});
Breadcrumbs::for('master-data-artikel.edit', function (BreadcrumbTrail $trail, $id) {
    $trail->parent('master-data-artikel.index');
    $trail->push('Edit Artikel');
});

Breadcrumbs::for('users.index', function (BreadcrumbTrail $trail) {
    $trail->push('Pengaturan Pengguna', route('users.index'));
});
Breadcrumbs::for('users.create', function (BreadcrumbTrail $trail) {
    $trail->parent('users.index');
    $trail->push('Baru');
});
Breadcrumbs::for('users.edit', function (BreadcrumbTrail $trail, $id) {
    $user = User::find($id)?->name ?? '-';
    $trail->parent('users.index');
    $trail->push($user);
});
Breadcrumbs::for('password.change', function (BreadcrumbTrail $trail) {
    $id = Auth::id();
    $trail->push('Profil Pengguna', route('users.edit', $id));
    $trail->push('Ganti Password');
});

Breadcrumbs::for('identitas.index', function (BreadcrumbTrail $trail) {
    $trail->push('Pengaturan Identitas', route('identitas.index'));
});
Breadcrumbs::for('identitas.edit', function (BreadcrumbTrail $trail, $id) {
    $identitas = Identitas::find($id)?->nama_kabupaten ?? '-';
    $trail->parent('identitas.index');
    $trail->push($identitas);
});
Breadcrumbs::for('groups.index', function (BreadcrumbTrail $trail) {
    $trail->push('Pengaturan Grup', route('groups.index'));
});
Breadcrumbs::for('groups.create', function (BreadcrumbTrail $trail) {
    $trail->parent('groups.index');
    $trail->push('Baru');
});
Breadcrumbs::for('groups.edit', function (BreadcrumbTrail $trail, $id) {
    $group = Team::find($id)?->name ?? '-';
    $trail->parent('groups.index');
    $trail->push($group);
});
Breadcrumbs::for('penduduk.index', function (BreadcrumbTrail $trail) {
    $trail->push('Penduduk', route('penduduk.index'));
});
Breadcrumbs::for('penduduk.create', function (BreadcrumbTrail $trail) {
    $trail->parent('penduduk.index');
    $trail->push('Baru');
});
Breadcrumbs::for('penduduk.edit', function (BreadcrumbTrail $trail, $id) {
    $result = (new PendudukApiService())->penduduk([
        'filter[id]' => $id,
    ]);

    $penduduk = $result->first()?->nama ?? '-';
    $trail->parent('penduduk.index');
    $trail->push($penduduk);
});

Breadcrumbs::for('categories.index', function (BreadcrumbTrail $trail) {
    $trail->push('Artikel', route('categories.index'));
});
Breadcrumbs::for('categories.create', function (BreadcrumbTrail $trail) {
    $trail->parent('categories.index');
    $trail->push('Baru');
});
Breadcrumbs::for('categories.edit', function (BreadcrumbTrail $trail, $id) {
    $item = Category::find($id)?->slug ?? '-';
    $trail->parent('categories.index');
    $trail->push($item);
});

Breadcrumbs::for('articles.index', function (BreadcrumbTrail $trail) {
    $trail->push('Artikel', route('articles.index'));
});
Breadcrumbs::for('articles.create', function (BreadcrumbTrail $trail) {
    $trail->parent('articles.index');
    $trail->push('Baru');
});
Breadcrumbs::for('articles.edit', function (BreadcrumbTrail $trail, $id) {
    $item = Article::find($id)?->slug ?? '-';
    $trail->parent('articles.index');
});
Breadcrumbs::for('employees.index', function (BreadcrumbTrail $trail) {
    $trail->push('Pejabat Daerah', route('employees.index'));
});
Breadcrumbs::for('employees.create', function (BreadcrumbTrail $trail) {
    $trail->parent('employees.index');
    $trail->push('Baru');
});
Breadcrumbs::for('employees.edit', function (BreadcrumbTrail $trail, $id) {
    $employee = Employee::find($id)?->name ?? '-';
    $trail->parent('employees.index');
    $trail->push($employee);
});

Breadcrumbs::for('departments.index', function (BreadcrumbTrail $trail) {
    $trail->push('Departemen', route('departments.index'));
});
Breadcrumbs::for('departments.create', function (BreadcrumbTrail $trail) {
    $trail->parent('departments.index');
    $trail->push('Baru');
});
Breadcrumbs::for('departments.edit', function (BreadcrumbTrail $trail, $id) {
    $item = Department::find($id)?->name ?? '-';
    $trail->parent('departments.index');
    $trail->push($item);
});

Breadcrumbs::for('positions.index', function (BreadcrumbTrail $trail) {
    $trail->push('Jabatan', route('positions.index'));
});
Breadcrumbs::for('positions.create', function (BreadcrumbTrail $trail) {
    $trail->parent('positions.index');
    $trail->push('Baru');
});
Breadcrumbs::for('positions.edit', function (BreadcrumbTrail $trail, $id) {
    $item = Position::find($id)?->name ?? '-';
    $trail->parent('positions.index');
    $trail->push($item);
});

Breadcrumbs::for('settings.index', function (BreadcrumbTrail $trail) {
    $trail->push('Setting', route('settings.index'));
});

Breadcrumbs::for('settings.edit', function (BreadcrumbTrail $trail, $id) {
    $item = Setting::find($id)?->name ?? '-';
    $trail->parent('settings.index');
    $trail->push($item);
});

Breadcrumbs::for('downloads.index', function (BreadcrumbTrail $trail) {
    $trail->push('Unduhan', route('downloads.index'));
});
Breadcrumbs::for('downloads.create', function (BreadcrumbTrail $trail) {
    $trail->parent('downloads.index');
    $trail->push('Baru');
});
Breadcrumbs::for('downloads.edit', function (BreadcrumbTrail $trail, $id) {
    $item = Download::find($id)?->title ?? '-';
    $trail->parent('downloads.index');
    $trail->push($item);
});
