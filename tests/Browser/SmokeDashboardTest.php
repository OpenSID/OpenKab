<?php

use App\Models\User;
use Tests\Browser\ScreenshotHelper;
use Tests\Browser\SessionState;

beforeEach(function () {
    $this->user = User::firstOrCreate(
        ['email' => 'pest-dashboard@opendesa.test'],
        [
            'name' => 'Pest Dashboard User',
            'password' => 'password',
            'username' => 'pestdashboard',
        ]
    );
    SessionState::assignAdminRole($this->user);
    SessionState::saveForUser($this->user);
});

afterEach(function () {
    SessionState::clear();
});

it('can open dashboard page', function () {
    $page = SessionState::loginAs($this->user)
        ->navigate('/dasbor')
        ->assertPathIs('/dasbor')
        ->assertSee('Dasbor');

    ScreenshotHelper::saveIfEnabled($page, 'dashboard-open');
});

it('displays wilayah filter', function () {
    $page = SessionState::loginAs($this->user)
        ->navigate('/dasbor')
        ->assertVisible('@filter_kabupaten')
        ->assertVisible('@filter_kecamatan')
        ->assertVisible('@filter_desa')
        ->assertVisible('@bt_filter');

    ScreenshotHelper::saveIfEnabled($page, 'dashboard-filter');
});

it('displays summary cards', function () {
    $page = SessionState::loginAs($this->user)
        ->navigate('/dasbor')
        ->assertVisible('#summary_block')
        ->assertSee('kecamatan')
        ->assertSee('jumlah penduduk')
        ->assertSee('jumlah keluarga');

    ScreenshotHelper::saveIfEnabled($page, 'dashboard-summary');
});

it('displays map', function () {
    $page = SessionState::loginAs($this->user)
        ->navigate('/dasbor')
        ->assertVisible('#map');

    ScreenshotHelper::saveIfEnabled($page, 'dashboard-map');
});

it('displays data table', function () {
    $page = SessionState::loginAs($this->user)
        ->navigate('/dasbor')
        ->assertVisible('#tabel_penduduk_block')
        ->assertVisible('#summary-penduduk');

    ScreenshotHelper::saveIfEnabled($page, 'dashboard-table');
});

it('updates summary when filtering by kabupaten', function () {
    $page = SessionState::loginAs($this->user)
        ->navigate('/dasbor');

    $page->script('
        $("#filter_kabupaten").val("50.01").trigger("change");
        $("#bt_filter").click();
    ');

    $page->wait(3000);

    $page->assertVisible('#summary_block');

    ScreenshotHelper::saveIfEnabled($page, 'dashboard-filtered');
});

it('displays data in table', function () {
    $page = SessionState::loginAs($this->user)
        ->navigate('/dasbor')
        ->wait(3000);

    $page->assertVisible('#summary-penduduk');

    ScreenshotHelper::saveIfEnabled($page, 'dashboard-table-data');
});

it('clear filter button works', function () {
    $page = SessionState::loginAs($this->user)
        ->navigate('/dasbor');

    $page->script('
        $("#filter_kabupaten").val("50.01").trigger("change");
        $("#bt_filter").click();
    ');

    $page->wait(3000);

    $page->script('
        $("#filter_kabupaten").val(null).trigger("change");
        $("#bt_filter").click();
    ');

    $page->wait(2000);

    $page->assertVisible('#summary_block');

    ScreenshotHelper::saveIfEnabled($page, 'dashboard-clear-filter');
});
