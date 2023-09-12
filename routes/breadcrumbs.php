<?php

use App\Models\Bantuan;
use App\Models\CMS\Category;
use App\Models\Identitas;
use App\Models\Kategori;
use App\Models\Penduduk;
use App\Models\Setting;
use App\Models\Team;
use App\Models\User;
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
    $bantuan = Bantuan::find($id)?->nama ?? '-';
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
    $bantuan = Bantuan::find($id)?->nama ?? '-';
    $trail->parent('bantuan.index');
    $trail->push($bantuan);
});
Breadcrumbs::for('master-data-artikel.kategori', function (BreadcrumbTrail $trail, $parent) {
    $name = Kategori::find($parent)?->kategori ?? 'Artikel';
    $trail->push('Master Kategori '.$name, route('master-data-artikel.kategori', $parent));
});
Breadcrumbs::for('master-data-artikel.kategori-create', function (BreadcrumbTrail $trail, $parent) {
    $trail->parent('master-data-artikel.kategori', $parent);
    $trail->push('Baru');
});
Breadcrumbs::for('master-data-artikel.kategori-edit', function (BreadcrumbTrail $trail, $id, $parent) {
    $name = Kategori::find($id)?->kategori ?? 'Artikel';
    $trail->parent('master-data-artikel.kategori', $parent);
    $trail->push($name);
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
    $penduduk = Penduduk::find($id)?->nama ?? '-';
    $trail->parent('penduduk.index');
    $trail->push($penduduk);
});

Breadcrumbs::for('categories.index', function (BreadcrumbTrail $trail) {
    $trail->push('Kategori', route('categories.index'));
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

Breadcrumbs::for('settings.index', function (BreadcrumbTrail $trail) {
    $trail->push('Setting', route('settings.index'));
});

Breadcrumbs::for('settings.edit', function (BreadcrumbTrail $trail, $id) {
    $item = Setting::find($id)?->name ?? '-';
    $trail->parent('settings.index');
    $trail->push($item);
});
