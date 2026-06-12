<?php

use Tests\Browser\ScreenshotHelper;
use Tests\Browser\SessionState;

beforeEach(function () {
    $this->user = SessionState::loginAdminUser();
});

afterEach(function () {
    SessionState::clear();
});

// ===================== Submenu Penduduk =====================

it('penduduk: displays page correctly', function () {
    $page = SessionState::loginAndNavigate($this->user, '/penduduk')
        ->assertPathIs('/penduduk')
        ->assertSee('Data Penduduk')
        ->assertVisible('@btn-filter')
        ->assertVisible('@btn-cetak')
        ->assertVisible('@btn-excel')
        ->assertVisible('@table-penduduk');

    ScreenshotHelper::saveIfEnabled($page, 'penduduk-page');
});

it('penduduk: has at least one data row', function () {
    $page = SessionState::loginAndNavigate($this->user, '/penduduk')
        ->assertPathIs('/penduduk');

    $page->assertScript(
        "new Promise((resolve) => {
            setTimeout(() => {
                const count = document.querySelectorAll('#penduduk tbody tr').length;
                resolve(count > 0);
            }, 5000);
        })",
        true
    );
});

it('penduduk: displays Pilih Aksi dropdown', function () {
    $page = SessionState::loginAndNavigate($this->user, '/penduduk')
        ->assertPathIs('/penduduk');

    $page->assertScript(
        "new Promise((resolve) => {
            setTimeout(() => {
                const exists = document.querySelector('[data-testid=\"dropdown-pilih-aksi\"]') !== null;
                resolve(exists);
            }, 5000);
        })",
        true
    );
});

// ===================== Submenu Kesehatan =====================

it('kesehatan: displays page correctly', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/kesehatan')
        ->assertPathIs('/data-pokok/kesehatan')
        ->assertSee('Data Kependudukan dan Statistik')
        ->assertVisible('@statistik-golongan-darah')
        ->assertVisible('@statistik-status-gizi')
        ->assertVisible('@btn-cetak')
        ->assertVisible('@btn-excel')
        ->assertVisible('@table-kesehatan');

    ScreenshotHelper::saveIfEnabled($page, 'kesehatan-page');
});

it('kesehatan: renders all charts', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/kesehatan')
        ->assertPathIs('/data-pokok/kesehatan')
        ->assertVisible('@chart-golongan-darah')
        ->assertVisible('@chart-status-gizi');

    $page->assertScript(
        "new Promise((resolve) => {
            setTimeout(() => {
                const donut = document.querySelector('[data-testid=\"chart-golongan-darah\"]');
                const bar = document.querySelector('[data-testid=\"chart-status-gizi\"]');
                resolve(donut !== null && bar !== null);
            }, 3000);
        })",
        true
    );
});

it('kesehatan: has at least one data row', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/kesehatan')
        ->assertPathIs('/data-pokok/kesehatan');

    $page->assertScript(
        "new Promise((resolve) => {
            setTimeout(() => {
                const count = document.querySelectorAll('#kesehatan tbody tr').length;
                resolve(count > 0);
            }, 5000);
        })",
        true
    );
});

// ===================== Submenu Pendidikan =====================

it('pendidikan: displays page correctly', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/pendidikan')
        ->assertPathIs('/data-pokok/pendidikan')
        ->assertSee('Data Pendidikan Penduduk dan DTKS')
        ->assertVisible('@statistik-partisipasi-sekolah')
        ->assertVisible('@statistik-ijazah-tertinggi')
        ->assertVisible('@btn-cetak')
        ->assertVisible('@btn-excel')
        ->assertVisible('@table-pendidikan');

    ScreenshotHelper::saveIfEnabled($page, 'pendidikan-page');
});

it('pendidikan: renders all charts', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/pendidikan')
        ->assertPathIs('/data-pokok/pendidikan')
        ->assertVisible('@chart-partisipasi-sekolah')
        ->assertVisible('@chart-ijazah-tertinggi');

    $page->assertScript(
        "new Promise((resolve) => {
            setTimeout(() => {
                const donut = document.querySelector('[data-testid=\"chart-partisipasi-sekolah\"]');
                const bar = document.querySelector('[data-testid=\"chart-ijazah-tertinggi\"]');
                resolve(donut !== null && bar !== null);
            }, 3000);
        })",
        true
    );
});

it('pendidikan: has at least one data row', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/pendidikan')
        ->assertPathIs('/data-pokok/pendidikan');

    $page->assertScript(
        "new Promise((resolve) => {
            setTimeout(() => {
                const count = document.querySelectorAll('#pendidikan tbody tr').length;
                resolve(count > 0);
            }, 5000);
        })",
        true
    );
});

// ===================== Submenu Ketenagakerjaan =====================

it('ketenagakerjaan: displays page correctly', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/ketenagakerjaan')
        ->assertPathIs('/data-pokok/ketenagakerjaan')
        ->assertSee('Data Pekerjaan dan Pelatihan')
        ->assertVisible('@statistik-jumlah-penghasilan')
        ->assertVisible('@statistik-pelatihan')
        ->assertVisible('@btn-cetak')
        ->assertVisible('@btn-excel')
        ->assertVisible('@table-ketenagakerjaan');

    ScreenshotHelper::saveIfEnabled($page, 'ketenagakerjaan-page');
});

it('ketenagakerjaan: renders all charts', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/ketenagakerjaan')
        ->assertPathIs('/data-pokok/ketenagakerjaan')
        ->assertVisible('@chart-jumlah-penghasilan')
        ->assertVisible('@chart-pelatihan');

    $page->assertScript(
        "new Promise((resolve) => {
            setTimeout(() => {
                const bar = document.querySelector('[data-testid=\"chart-jumlah-penghasilan\"]');
                const donut = document.querySelector('[data-testid=\"chart-pelatihan\"]');
                resolve(bar !== null && donut !== null);
            }, 3000);
        })",
        true
    );
});

it('ketenagakerjaan: has at least one data row', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/ketenagakerjaan')
        ->assertPathIs('/data-pokok/ketenagakerjaan');

    $page->assertScript(
        "new Promise((resolve) => {
            setTimeout(() => {
                const count = document.querySelectorAll('#ketenagakerjaan tbody tr').length;
                resolve(count > 0);
            }, 5000);
        })",
        true
    );
});

// ===================== Submenu Penerima Bantuan =====================

it('bantuan: displays page correctly', function () {
    $page = SessionState::loginAndNavigate($this->user, '/bantuan')
        ->assertPathIs('/bantuan')
        ->assertSee('Data Bantuan')
        ->assertVisible('@btn-filter')
        ->assertVisible('@btn-cetak')
        ->assertVisible('@btn-excel')
        ->assertVisible('@table-bantuan');

    ScreenshotHelper::saveIfEnabled($page, 'bantuan-page');
});

it('bantuan: has at least one data row', function () {
    $page = SessionState::loginAndNavigate($this->user, '/bantuan')
        ->assertPathIs('/bantuan');

    $page->assertScript(
        "new Promise((resolve) => {
            setTimeout(() => {
                const count = document.querySelectorAll('#bantuan tbody tr').length;
                resolve(count > 0);
            }, 5000);
        })",
        true
    );
});

it('bantuan: displays Detail button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/bantuan')
        ->assertPathIs('/bantuan');

    $page->assertScript(
        "new Promise((resolve) => {
            setTimeout(() => {
                const exists = document.querySelector('[data-testid=\"btn-detail\"]') !== null;
                resolve(exists);
            }, 5000);
        })",
        true
    );
});

// ===================== Submenu Kelembagaan =====================

it('kelembagaan: displays page correctly', function () {
    $page = SessionState::loginAndNavigate($this->user, '/lembaga')
        ->assertPathIs('/lembaga')
        ->assertSee('Data Kelembagaan')
        ->assertVisible('@btn-filter')
        ->assertVisible('@btn-cetak')
        ->assertVisible('@btn-excel')
        ->assertVisible('@table-lembaga');

    ScreenshotHelper::saveIfEnabled($page, 'lembaga-page');
});

it('kelembagaan: has at least one data row', function () {
    $page = SessionState::loginAndNavigate($this->user, '/lembaga')
        ->assertPathIs('/lembaga');

    $page->assertScript(
        "new Promise((resolve) => {
            setTimeout(() => {
                const count = document.querySelectorAll('#table-lembaga tbody tr').length;
                resolve(count > 0);
            }, 5000);
        })",
        true
    );
});
