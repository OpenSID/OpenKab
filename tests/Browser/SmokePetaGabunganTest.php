<?php

declare(strict_types=1);

use Tests\Browser\SessionState;

/**
 * Smoke test halaman Peta Gabungan (KAB-04).
 *
 * Pemanggilan API Satu Data terjadi di sisi server (PHP) sehingga tidak dapat
 * di-intercept MSW. Tanpa API hidup, halaman merender degradasi "Data sementara
 * tidak tersedia" dengan kerangka dua blok tetap utuh — itu yang diverifikasi.
 */
beforeEach(function () {
    $this->user = SessionState::loginAdminUser();
});

afterEach(function () {
    SessionState::clear();
});

it('membuka peta gabungan tanpa halaman error', function () {
    $page = SessionState::loginAndNavigate($this->user, '/dataspasial/peta');

    $page->assertPathIs('/dataspasial/peta')
        ->assertSee('Peta Gabungan')
        ->assertDontSee('Whoops')
        ->assertDontSee('Stack trace');
});

it('menampilkan pesan data sementara tidak tersedia saat API tidak hidup', function () {
    $page = SessionState::loginAndNavigate($this->user, '/dataspasial/peta');

    $page->assertSee('Data sementara tidak tersedia')
        ->assertDontSee('Stack trace');
});

it('tetap menampilkan kerangka halaman pada layar ponsel', function () {
    $page = SessionState::loginAndNavigate($this->user, '/dataspasial/peta');

    $page->resize(390, 844)
        ->assertSee('Peta Gabungan')
        ->assertScript(
            'document.documentElement.scrollWidth <= window.innerWidth + 2',
            true
        );
});
