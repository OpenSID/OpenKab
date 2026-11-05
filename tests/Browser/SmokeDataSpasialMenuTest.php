<?php

declare(strict_types=1);

use Tests\Browser\ScreenshotHelper;
use Tests\Browser\SessionState;

/**
 * Smoke test menu DataSpasial (FR-008/FR-009/FR-014).
 *
 * Catatan: pemanggilan API Satu Data terjadi di sisi server (PHP), sehingga
 * TIDAK dapat di-intercept oleh MSW. Tanpa API yang hidup, halaman akan
 * merender partial "Data sementara tidak tersedia" — dan itu justru yang
 * diverifikasi test ini: degradasi anggun tetap menghasilkan halaman utuh
 * dengan sidebar, bukan halaman error.
 */
beforeEach(function () {
    $this->user = SessionState::loginAdminUser();
});

afterEach(function () {
    SessionState::clear();
});

/**
 * @return array<string, string>
 */
function dataspasialMenuLinks(): array
{
    return [
        'Skema DataSpasial' => '/dataspasial/skema',
        'Peta Gabungan' => '/dataspasial/peta',
        'Dasbor DataSpasial' => '/dataspasial/dasbor',
        'Impor Data' => '/dataspasial/impor',
        'Monitoring' => '/dataspasial/monitoring',
    ];
}

it('menampilkan grup menu DataSpasial beserta lima submenu', function () {
    $page = SessionState::loginAndNavigate($this->user, '/dasbor')
        ->assertPathIs('/dasbor');

    $page->assertSee('DataSpasial');

    // Submenu AdminLte dirender tersembunyi sampai grup diklik; karena itu
    // keberadaan link diperiksa pada DOM, bukan visibilitasnya.
    foreach (dataspasialMenuLinks() as $label => $path) {
        $page->assertScript(
            'Array.from(document.querySelectorAll(".nav-sidebar a"))
                .some(a => a.textContent.trim() === '.json_encode($label).'
                    && a.getAttribute("href").includes('.json_encode($path).'))',
            true
        );
    }

    ScreenshotHelper::saveIfEnabled($page, 'dataspasial-menu');
});

it('membuka setiap halaman DataSpasial tanpa halaman error', function (string $path, string $title) {
    $page = SessionState::loginAndNavigate($this->user, $path);

    $page->assertPathIs($path)
        ->assertSee($title);

    ScreenshotHelper::saveIfEnabled($page, 'dataspasial'.str_replace('/', '-', $path));
})->with([
    ['/dataspasial/skema', 'Skema DataSpasial'],
    ['/dataspasial/peta', 'Peta Gabungan'],
    ['/dataspasial/dasbor', 'Dasbor DataSpasial'],
    ['/dataspasial/impor', 'Impor Data'],
    ['/dataspasial/monitoring', 'Monitoring'],
]);

it('menampilkan pesan data sementara tidak tersedia alih-alih halaman error', function () {
    $page = SessionState::loginAndNavigate($this->user, '/dataspasial/peta');

    // API tidak hidup pada lingkungan uji → harus jatuh ke kondisi unavailable.
    $page->assertSee('Data sementara tidak tersedia')
        ->assertDontSee('Whoops')
        ->assertDontSee('Stack trace');

    ScreenshotHelper::saveIfEnabled($page, 'dataspasial-unavailable');
});

it('mempertahankan kerangka halaman dan navigasi saat data tidak tersedia', function () {
    $page = SessionState::loginAndNavigate($this->user, '/dataspasial/monitoring');

    // Layout admin tetap utuh: navigasi dan judul halaman masih terlihat.
    $page->assertSee('Monitoring')
        ->assertSee('DataSpasial')
        ->assertSee('Peta Gabungan');

    ScreenshotHelper::saveIfEnabled($page, 'dataspasial-unavailable-frame');
});
