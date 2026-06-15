<?php

use Tests\Browser\ScreenshotHelper;
use Tests\Browser\SessionState;

beforeEach(function () {
    $this->user = SessionState::loginAdminUser();
});

afterEach(function () {
    SessionState::clear();
});

$dataPokokPages = [
    'pangan' => [
        'path' => '/data-presisi/pangan',
        'tableSelector' => '#table-pangan',
        'chartSelector' => '#barChart',
        'hasFilterTahun' => true,
        'hasFilterStatus' => true,
        'hasCetakButton' => true,
        'hasExcelButton' => true,
        'hasDetailButton' => true,
        'hasCollapse' => false,
    ],
    'sandang' => [
        'path' => '/data-pokok/sandang',
        'tableSelector' => '#table-dtks',
        'chartSelector' => null,
        'hasFilterTahun' => true,
        'hasFilterStatus' => false,
        'hasCetakButton' => true,
        'hasExcelButton' => true,
        'hasDetailButton' => true,
        'hasCollapse' => false,
    ],
    'papan' => [
        'path' => '/satu-data/dtks/papan',
        'tableSelector' => '#table-dtks',
        'chartSelector' => null,
        'hasFilterTahun' => true,
        'hasFilterStatus' => false,
        'hasCetakButton' => true,
        'hasExcelButton' => true,
        'hasDetailButton' => true,
        'hasCollapse' => false,
    ],
    'kesehatan' => [
        'path' => '/data-pokok/kesehatan',
        'tableSelector' => '[data-testid="datatable-kesehatan"]',
        'tableTestId' => 'datatable-kesehatan',
        'chartSelector' => '#donutChart',
        'chartSelector2' => '#barChart',
        'hasFilterTahun' => false,
        'hasFilterStatus' => false,
        'hasCetakButton' => true,
        'cetakTestId' => 'bt-cetak',
        'hasExcelButton' => true,
        'excelTestId' => 'bt-excel',
        'hasDetailButton' => false,
        'hasCollapse' => false,
    ],
    'pendidikan' => [
        'path' => '/data-pokok/pendidikan',
        'tableSelector' => '[data-testid="datatable-pendidikan"]',
        'tableTestId' => 'datatable-pendidikan',
        'chartSelector' => '#donutChart',
        'chartSelector2' => '#barChart',
        'hasFilterTahun' => false,
        'hasFilterStatus' => false,
        'hasCetakButton' => true,
        'cetakTestId' => 'bt-cetak',
        'hasExcelButton' => true,
        'excelTestId' => 'bt-excel',
        'hasDetailButton' => false,
        'hasCollapse' => false,
    ],
    'ketenagakerjaan' => [
        'path' => '/data-pokok/ketenagakerjaan',
        'tableSelector' => '[data-testid="datatable-ketenagakerjaan"]',
        'tableTestId' => 'datatable-ketenagakerjaan',
        'chartSelector' => '#barChart',
        'chartSelector2' => '#donutChart',
        'hasFilterTahun' => false,
        'hasFilterStatus' => false,
        'hasCetakButton' => true,
        'cetakTestId' => 'bt-cetak',
        'hasExcelButton' => true,
        'excelTestId' => 'bt-excel',
        'hasDetailButton' => false,
        'hasCollapse' => false,
    ],
    'adat' => [
        'path' => '/data-presisi/adat',
        'tableSelector' => '#adat',
        'chartSelector' => '#pie1',
        'hasFilterTahun' => true,
        'hasFilterStatus' => true,
        'hasCetakButton' => true,
        'hasExcelButton' => true,
        'hasDetailButton' => true,
        'hasCollapse' => false,
    ],
    'agama' => [
        'path' => '/data-pokok/agama',
        'tableSelector' => '#agama',
        'chartSelector' => '#pie1',
        'hasFilterTahun' => true,
        'hasFilterStatus' => true,
        'hasCetakButton' => true,
        'hasExcelButton' => true,
        'hasDetailButton' => true,
        'hasCollapse' => false,
    ],
    'seni-budaya' => [
        'path' => '/data-presisi/seni-budaya',
        'tableSelector' => '#table-seni-budaya',
        'chartSelector' => '#barChart',
        'hasFilterTahun' => true,
        'hasFilterStatus' => true,
        'hasCetakButton' => true,
        'hasExcelButton' => true,
        'hasDetailButton' => true,
        'hasCollapse' => false,
    ],
];

foreach ($dataPokokPages as $key => $config) {
    $path = $config['path'];
    $tableSelector = $config['tableSelector'];

    it("opens the {$key} page", function () use ($path) {
        $page = SessionState::loginAndNavigate($this->user, $path)
            ->assertPathIs($path);

        ScreenshotHelper::saveIfEnabled($page, "{$key}-page");
    });

    if ($config['chartSelector']) {
        $chartSelector = $config['chartSelector'];
        $chartSelector2 = $config['chartSelector2'] ?? null;

        it("renders chart on {$key}", function () use ($path, $chartSelector, $chartSelector2) {
            $page = SessionState::loginAndNavigate($this->user, $path)
                ->assertPathIs($path);

            $page->assertScript(
                "new Promise((resolve) => {
                    const check = () => {
                        const chart1 = document.querySelector('{$chartSelector}');
                        const ready1 = chart1 && (chart1.getContext ? chart1.getContext && chart1.width > 0 : chart1.offsetWidth > 0);
                        " . ($chartSelector2 ? "
                        const chart2 = document.querySelector('{$chartSelector2}');
                        const ready2 = chart2 && (chart2.getContext ? chart2.getContext && chart2.width > 0 : chart2.offsetWidth > 0);
                        if (ready1 && ready2) { resolve(true); } else { setTimeout(check, 500); }
                        " : "
                        if (ready1) { resolve(true); } else { setTimeout(check, 500); }
                        ") . "
                    };
                    check();
                })",
                true
            );

            ScreenshotHelper::saveIfEnabled($page, "{$key}-chart");
        });
    }

    if ($config['hasFilterTahun']) {
        it("displays filter tahun on {$key}", function () use ($path) {
            $page = SessionState::loginAndNavigate($this->user, $path)
                ->assertVisible('#filter-tahun');

            ScreenshotHelper::saveIfEnabled($page, "{$key}-filter-tahun");
        });
    }

    if ($config['hasFilterStatus']) {
        it("displays filter status on {$key}", function () use ($path) {
            $page = SessionState::loginAndNavigate($this->user, $path)
                ->assertVisible('#filter-status-kelengkapan');

            ScreenshotHelper::saveIfEnabled($page, "{$key}-filter-status");
        });
    }

    if ($config['hasCetakButton']) {
        $cetakTestId = $config['cetakTestId'] ?? null;

        it("displays cetak button on {$key}", function () use ($path, $cetakTestId) {
            $page = SessionState::loginAndNavigate($this->user, $path);

            if ($cetakTestId) {
                $page->assertVisible("[data-testid=\"{$cetakTestId}\"]");
            } else {
                $page->assertScript(
                    "new Promise((resolve) => {
                        const check = () => {
                            const btn = document.querySelector('[data-testid=\"bt-cetak\"]') || document.querySelector('#cetak') || document.querySelector('button[data-print-url]');
                            resolve(!!btn);
                        };
                        check();
                    })",
                    true
                );
            }

            ScreenshotHelper::saveIfEnabled($page, "{$key}-cetak-button");
        });
    }

    if ($config['hasExcelButton']) {
        $excelTestId = $config['excelTestId'] ?? null;

        it("displays excel button on {$key}", function () use ($path, $excelTestId) {
            $page = SessionState::loginAndNavigate($this->user, $path);

            if ($excelTestId) {
                $page->assertVisible("[data-testid=\"{$excelTestId}\"]");
            } else {
                $page->assertScript(
                    "new Promise((resolve) => {
                        const check = () => {
                            const btn = document.querySelector('[data-testid=\"bt-excel\"]') || document.querySelector('#export-excel') || document.querySelector('#download-excel') || document.querySelector('button[data-download-url]');
                            resolve(!!btn);
                        };
                        check();
                    })",
                    true
                );
            }

            ScreenshotHelper::saveIfEnabled($page, "{$key}-excel-button");
        });
    }

    it("displays datatable on {$key}", function () use ($path, $tableSelector) {
        $page = SessionState::loginAndNavigate($this->user, $path)
            ->assertPathIs($path);

        $page->assertScript(
            "new Promise((resolve) => {
                const check = () => {
                    const table = document.querySelector('{$tableSelector}');
                    if (table) { resolve(true); } else { setTimeout(check, 500); }
                };
                check();
            })",
            true
        );

        ScreenshotHelper::saveIfEnabled($page, "{$key}-datatable");
    });

    it("has at least 1 data row on {$key}", function () use ($path, $tableSelector) {
        $page = SessionState::loginAndNavigate($this->user, $path)
            ->assertPathIs($path);

        $page->assertScript(
            "new Promise((resolve) => {
                const check = () => {
                    const table = document.querySelector('{$tableSelector}');
                    if (table) {
                        const rows = table.querySelectorAll('tbody tr');
                        if (rows.length > 0 && !rows[0].classList.contains('dataTables_empty')) {
                            resolve(true);
                        } else {
                            setTimeout(check, 500);
                        }
                    } else {
                        setTimeout(check, 500);
                    }
                };
                check();
            })",
            true
        );

        ScreenshotHelper::saveIfEnabled($page, "{$key}-data-rows");
    });

    if ($config['hasDetailButton']) {
        it("has detail button in data on {$key}", function () use ($path, $tableSelector) {
            $page = SessionState::loginAndNavigate($this->user, $path)
                ->assertPathIs($path);

            $page->assertScript(
                "new Promise((resolve) => {
                    const check = () => {
                        const table = document.querySelector('{$tableSelector}');
                        if (table) {
                            const rows = table.querySelectorAll('tbody tr');
                            if (rows.length > 0 && !rows[0].classList.contains('dataTables_empty')) {
                                const detailBtn = rows[0].querySelector('[data-button=\"Detail\"]') || rows[0].querySelector('a[href*=\"detail\"]');
                                resolve(!!detailBtn);
                            } else {
                                setTimeout(check, 500);
                            }
                        } else {
                            setTimeout(check, 500);
                        }
                    };
                    check();
                })",
                true
            );

            ScreenshotHelper::saveIfEnabled($page, "{$key}-detail-button");
        });
    }

    it("has no javascript errors on {$key}", function () use ($path) {
        $page = SessionState::loginAndNavigate($this->user, $path)
            ->assertPathIs($path)
            ->assertNoJavaScriptErrors();

        ScreenshotHelper::saveIfEnabled($page, "{$key}-no-errors");
    });
}
