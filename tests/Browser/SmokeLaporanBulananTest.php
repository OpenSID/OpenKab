<?php

use Illuminate\Support\Facades\Http;
use Tests\Browser\ScreenshotHelper;
use Tests\Browser\SessionState;

beforeEach(function () {
    $this->user = SessionState::loginAdminUser();

    Http::fake(function ($request) {
        $url = $request->url();

        // logPenduduk - returns a date string
        if (str_contains($url, '/api/v1/statistik/laporan-bulanan/log-penduduk')) {
            return Http::response([
                'data' => date('Y-m-01 00:00:00'),
            ], 200);
        }

        // laporan bulanan data
        if (str_contains($url, '/api/v1/statistik/laporan-bulanan')) {
            return Http::response([
                'data' => [
                    'penduduk_awal' => [
                        'WNI_L' => 12000, 'WNI_P' => 11500,
                        'WNA_L' => 10, 'WNA_P' => 5,
                        'KK_L' => 5500, 'KK_P' => 5200, 'KK' => 10700,
                    ],
                    'kelahiran' => [
                        'WNI_L' => 50, 'WNI_P' => 45,
                        'WNA_L' => 0, 'WNA_P' => 0,
                        'KK_L' => 0, 'KK_P' => 0, 'KK' => 0,
                    ],
                    'kematian' => [
                        'WNI_L' => 20, 'WNI_P' => 15,
                        'WNA_L' => 0, 'WNA_P' => 0,
                        'KK_L' => 0, 'KK_P' => 0, 'KK' => 0,
                    ],
                    'pendatang' => [
                        'WNI_L' => 30, 'WNI_P' => 25,
                        'WNA_L' => 1, 'WNA_P' => 0,
                        'KK_L' => 0, 'KK_P' => 0, 'KK' => 0,
                    ],
                    'pindah' => [
                        'WNI_L' => 15, 'WNI_P' => 10,
                        'WNA_L' => 0, 'WNA_P' => 0,
                        'KK_L' => 0, 'KK_P' => 0, 'KK' => 0,
                    ],
                    'hilang' => [
                        'WNI_L' => 5, 'WNI_P' => 3,
                        'WNA_L' => 0, 'WNA_P' => 0,
                        'KK_L' => 0, 'KK_P' => 0, 'KK' => 0,
                    ],
                    'penduduk_akhir' => [
                        'WNI_L' => 12045, 'WNI_P' => 11542,
                        'WNA_L' => 11, 'WNA_P' => 5,
                        'KK_L' => 5500, 'KK_P' => 5200, 'KK' => 10700,
                    ],
                ],
            ], 200);
        }

        // config kabupaten
        if (str_contains($url, '/api/v1/config/kabupaten')) {
            return Http::response([
                'data' => [
                    [
                        'type' => 'config',
                        'id' => '',
                        'attributes' => [
                            'nama_kabupaten' => 'KABUPATEN TEST',
                            'kode_kabupaten' => '5102',
                        ],
                    ],
                ],
            ], 200);
        }

        // config kecamatan
        if (str_contains($url, '/api/v1/config/kecamatan')) {
            return Http::response([
                'data' => [
                    [
                        'type' => 'config',
                        'id' => '',
                        'attributes' => [
                            'nama_kecamatan' => 'KECAMATAN TEST',
                            'kode_kecamatan' => '510201',
                        ],
                    ],
                ],
            ], 200);
        }

        // config desa
        if (str_contains($url, '/api/v1/config/desa')) {
            return Http::response([
                'data' => [
                    [
                        'type' => 'config',
                        'id' => '1',
                        'attributes' => [
                            'nama_desa' => 'DESA TEST',
                            'kode_desa' => '5102010001',
                        ],
                    ],
                ],
            ], 200);
        }

        // fallback: empty response for unmatched requests
        return Http::response(['data' => []], 200);
    });
});

afterEach(function () {
    SessionState::clear();
});

it('opens the laporan bulanan page', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/laporan-bulanan')
        ->assertPathIs('/statistik/laporan-bulanan')
        ->assertSee('Laporan Kependudukan Bulanan');

    ScreenshotHelper::saveIfEnabled($page, 'laporan-bulanan-page');
});

it('displays excel button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/laporan-bulanan')
        ->assertVisible('@bt-excel');

    ScreenshotHelper::saveIfEnabled($page, 'laporan-bulanan-excel-button');
});

it('displays filter kabupaten', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/laporan-bulanan')
        ->assertVisible('@filter-kabupaten');

    ScreenshotHelper::saveIfEnabled($page, 'laporan-bulanan-filter-kabupaten');
});

it('displays filter kecamatan', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/laporan-bulanan')
        ->assertVisible('@filter-kecamatan');

    ScreenshotHelper::saveIfEnabled($page, 'laporan-bulanan-filter-kecamatan');
});

it('displays filter desa', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/laporan-bulanan')
        ->assertVisible('@filter-desa');

    ScreenshotHelper::saveIfEnabled($page, 'laporan-bulanan-filter-desa');
});

it('displays filter tahun', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/laporan-bulanan')
        ->assertVisible('@filter-tahun');

    ScreenshotHelper::saveIfEnabled($page, 'laporan-bulanan-filter-tahun');
});

it('displays filter bulan', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/laporan-bulanan')
        ->assertVisible('@filter-bulan');

    ScreenshotHelper::saveIfEnabled($page, 'laporan-bulanan-filter-bulan');
});

it('displays datatable', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/laporan-bulanan');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const table = document.querySelector('[data-testid=\"datatable-laporan-bulanan\"]');
                if (table) { resolve(true); } else { setTimeout(check, 500); }
            };
            check();
        })",
        true
    );

    ScreenshotHelper::saveIfEnabled($page, 'laporan-bulanan-datatable');
});

it('has no javascript errors', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/laporan-bulanan')
        ->assertPathIs('/statistik/laporan-bulanan')
        ->assertNoJavaScriptErrors();

    ScreenshotHelper::saveIfEnabled($page, 'laporan-bulanan-no-errors');
});
