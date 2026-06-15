<?php

use Tests\Browser\ScreenshotHelper;
use Tests\Browser\SessionState;

beforeEach(function () {
    $this->user = SessionState::loginAdminUser();
});

afterEach(function () {
    SessionState::clear();
});

$statistikPages = [
    'pangan' => [
        'path' => '/data-presisi/statistik/pangan',
        'judul' => 'Pangan',
        'kategoriUrl' => 'pangan/kategori-statistik',
    ],
    'sandang' => [
        'path' => '/data-presisi/statistik/sandang',
        'judul' => 'Sandang',
        'kategoriUrl' => 'sandang/kategori-statistik',
    ],
    'papan' => [
        'path' => '/data-presisi/statistik/papan',
        'judul' => 'Papan',
        'kategoriUrl' => 'papan/kategori-statistik',
    ],
    'seni-budaya' => [
        'path' => '/data-presisi/statistik/senibudaya',
        'judul' => 'Seni Budaya',
        'kategoriUrl' => 'seni-budaya/kategori-statistik',
    ],
    'pendidikan' => [
        'path' => '/data-presisi/statistik/pendidikan',
        'judul' => 'Pendidikan',
        'kategoriUrl' => 'pendidikan/kategori-statistik',
    ],
    'kesehatan' => [
        'path' => '/data-presisi/statistik/kesehatan',
        'judul' => 'Kesehatan',
        'kategoriUrl' => 'kesehatan/kategori-statistik',
    ],
    'jaminan-sosial' => [
        'path' => '/data-presisi/statistik/jaminan-sosial',
        'judul' => 'Jaminan Sosial',
        'kategoriUrl' => 'jaminan-sosial/kategori-statistik',
    ],
    'aktivitas-keagamaan' => [
        'path' => '/data-presisi/statistik/aktivitas-keagamaan',
        'judul' => 'Aktivitas Keagamaan',
        'kategoriUrl' => 'agama/kategori-statistik',
    ],
    'ketenagakerjaan' => [
        'path' => '/data-presisi/statistik/ketenagakerjaan',
        'judul' => 'Ketenagakerjaan',
        'kategoriUrl' => 'ketenagakerjaan/kategori-statistik',
    ],
    'adat' => [
        'path' => '/data-presisi/statistik/adat',
        'judul' => 'Adat',
        'kategoriUrl' => 'adat/kategori-statistik',
    ],
];

foreach ($statistikPages as $key => $config) {
    $path = $config['path'];
    $judul = $config['judul'];

    it("opens the statistik {$judul} page", function () use ($path, $judul) {
        $page = SessionState::loginAndNavigate($this->user, $path)
            ->assertPathIs($path)
            ->assertSee($judul);

        ScreenshotHelper::saveIfEnabled($page, "statistik-{$judul}-page");
    });

    it("displays kategori statistik list on {$judul}", function () use ($path) {
        $page = SessionState::loginAndNavigate($this->user, $path)
            ->assertVisible('[data-testid="daftar-statistik"]');

        $page->assertScript(
            'new Promise((resolve) => {
                const check = () => {
                    const items = document.querySelectorAll(\'[data-testid="daftar-statistik"] a\');
                    if (items.length > 0) {
                        resolve(true);
                    } else {
                        setTimeout(check, 500);
                    }
                };
                check();
            })',
            true
        );

        ScreenshotHelper::saveIfEnabled($page, "statistik-{$judul}-kategori-list");
    });

    it("displays filter tahun on {$judul}", function () use ($path) {
        $page = SessionState::loginAndNavigate($this->user, $path)
            ->assertVisible('[data-testid="filter-tahun"]');

        ScreenshotHelper::saveIfEnabled($page, "statistik-{$judul}-filter-tahun");
    });

    it("displays excel button on {$judul}", function () use ($path) {
        $page = SessionState::loginAndNavigate($this->user, $path)
            ->assertVisible('[data-testid="btn-export-excel"]');

        ScreenshotHelper::saveIfEnabled($page, "statistik-{$judul}-excel-button");
    });

    it("displays grafik button on {$judul}", function () use ($path) {
        $page = SessionState::loginAndNavigate($this->user, $path)
            ->assertVisible('[data-testid="btn-toggle-grafik"]');

        ScreenshotHelper::saveIfEnabled($page, "statistik-{$judul}-grafik-button");
    });

    it("displays chart button on {$judul}", function () use ($path) {
        $page = SessionState::loginAndNavigate($this->user, $path)
            ->assertVisible('[data-testid="btn-toggle-pie"]');

        ScreenshotHelper::saveIfEnabled($page, "statistik-{$judul}-chart-button");
    });

    it("displays datatable on {$judul}", function () use ($path) {
        $page = SessionState::loginAndNavigate($this->user, $path)
            ->assertVisible('[data-testid="datatable-statistik"]');

        $page->assertScript(
            'new Promise((resolve) => {
                const check = () => {
                    const table = document.querySelector(\'[data-testid="datatable-statistik"]\');
                    if (table) { resolve(true); } else { setTimeout(check, 500); }
                };
                check();
            })',
            true
        );

        ScreenshotHelper::saveIfEnabled($page, "statistik-{$judul}-datatable");
    });

    it("clicks a kategori statistik on {$judul}", function () use ($path) {
        $page = SessionState::loginAndNavigate($this->user, $path)
            ->assertVisible('[data-testid="daftar-statistik"]');

        $page->assertScript(
            'new Promise((resolve) => {
                const check = () => {
                    const items = document.querySelectorAll(\'[data-testid="daftar-statistik"] a\');
                    if (items.length > 0) {
                        items[0].click();
                        resolve(true);
                    } else {
                        setTimeout(check, 500);
                    }
                };
                check();
            })',
            true
        );

        $page->wait(2000);

        ScreenshotHelper::saveIfEnabled($page, "statistik-{$judul}-kategori-clicked");
    });

    it("renders bar chart on {$judul}", function () use ($path) {
        $page = SessionState::loginAndNavigate($this->user, $path)
            ->assertPathIs($path);

        $page->assertScript(
            'new Promise((resolve) => {
                const check = () => {
                    const bar = document.querySelector(\'[data-testid="chart-bar"]\');
                    const ready = bar && bar.getContext && bar.width > 0;
                    if (ready) {
                        resolve(true);
                    } else {
                        setTimeout(check, 500);
                    }
                };
                check();
            })',
            true
        );

        ScreenshotHelper::saveIfEnabled($page, "statistik-{$judul}-bar-chart");
    });

    it("renders pie chart on {$judul}", function () use ($path) {
        $page = SessionState::loginAndNavigate($this->user, $path)
            ->assertPathIs($path);

        $page->assertScript(
            'new Promise((resolve) => {
                const check = () => {
                    const donut = document.querySelector(\'[data-testid="chart-donut"]\');
                    const ready = donut && donut.getContext && donut.width > 0;
                    if (ready) {
                        resolve(true);
                    } else {
                        setTimeout(check, 500);
                    }
                };
                check();
            })',
            true
        );

        ScreenshotHelper::saveIfEnabled($page, "statistik-{$judul}-pie-chart");
    });

    it("has no javascript errors on {$judul}", function () use ($path) {
        $page = SessionState::loginAndNavigate($this->user, $path)
            ->assertPathIs($path)
            ->assertNoJavaScriptErrors();

        ScreenshotHelper::saveIfEnabled($page, "statistik-{$judul}-no-errors");
    });
}
