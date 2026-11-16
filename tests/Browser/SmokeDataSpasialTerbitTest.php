<?php

declare(strict_types=1);

use Tests\Browser\ScreenshotHelper;
use Tests\Browser\SessionState;

/**
 * Smoke test layar terbitan (KAB-03 / US1).
 *
 * ⚠️ Pemanggilan API Satu Data terjadi di sisi server (PHP) sehingga **tidak**
 * dapat di-intercept MSW. Tanpa API hidup, dialog akan menampilkan "data
 * sementara tidak tersedia" — dan itu justru yang diverifikasi di sini:
 * degradasi anggun menghasilkan dialog utuh, bukan halaman error.
 *
 * Verifikasi numerically (ringkasan perubahan, diff versi, tabel adopsi)
 * dilakukan di test feature dengan `Http::fake()` — lihat
 * `tests/Feature/DataSpasial/SkemaTerbitKonfirmasiTest.php`.
 */
beforeEach(function () {
    $this->user = SessionState::loginAdminUser();
});

afterEach(function () {
    SessionState::clear();
});

it('merender kerangka layar terbitan di halaman skema', function () {
    $page = SessionState::loginAndNavigate($this->user, '/dataspasial/skema')
        ->assertPathIs('/dataspasial/skema');

    // Kerangka dialog selalu ada walau sumber data bermasalah (FR-046).
    $page->assertScript(
        'document.querySelectorAll(\'[data-testid="layar-terbitan"]\').length === 1',
        true
    );

    ScreenshotHelper::saveIfEnabled($page, 'dataspasial-terbitan-kerangka');
});

it('memuat skrip layar terbitan tanpa error javascript', function () {
    $page = SessionState::loginAndNavigate($this->user, '/dataspasial/skema')
        ->assertPathIs('/dataspasial/skema')
        ->assertNoJavaScriptErrors();

    ScreenshotHelper::saveIfEnabled($page, 'dataspasial-terbitan-no-js-error');
});

it('menyediakan seluruh bagian layar terbitan pada halaman skema', function () {
    $page = SessionState::loginAndNavigate($this->user, '/dataspasial/skema')
        ->assertPathIs('/dataspasial/skema');

    // Kelima bagian dirender bersama (dialog disembunyikan AdminLTE sampai
    // tombol Terbitan ditekan) sehingga uptime tidak bergantung pada API.
    foreach ([
        'terbitan-isi',
        'ringkasan-perubahan',
        'peringatan-dampak',
        'riwayat-versi',
        'tabel-adopsi',
        'terbitan-footer',
        'catatan-perubahan',
        'btn-kirim-terbit',
    ] as $bagian) {
        $page->assertScript(
            'document.querySelectorAll(\'[data-testid="'.$bagian.'"]\').length === 1',
            true
        );
    }

    ScreenshotHelper::saveIfEnabled($page, 'dataspasial-terbitan-bagian');
});

it('tidak mengirim permintaan terbitan hanya karena dialog dibuka', function () {
    $page = SessionState::loginAndNavigate($this->user, '/dataspasial/skema')
        ->assertPathIs('/dataspasial/skema');

    // FR-046 - membuka/menutup dialog tidak pernah menyentuh endpoint terbitan.
    $page->assertScript(
        '(() => {'
        .'window.__k03 = { n: 0 };'
        .'const asli = window.fetch.bind(window);'
        .'window.fetch = function (url, ...sisa) {'
        .'if (String(url).includes("/dataspasial/skema/terbit")) { window.__k03.n += 1; }'
        .'return asli(url, ...sisa);'
        .'};'
        .'return true;'
        .'})()',
        true
    );

    // Tombol nonaktif bila sumber data tidak tersedia (FR-005); dialog tetap
    // boleh dibuka lewat API modal bila aktif.
    $page->assertScript(
        'new Promise((resolve) => {'
        .'const tombol = document.getElementById("btn-terbitan");'
        .'const buka = document.querySelector(\'[data-testid="layar-terbitan"]\');'
        .'if (tombol && !tombol.disabled) { tombol.click(); }'
        .'else if (buka && window.jQuery) { window.jQuery(buka).modal("show"); }'
        .'setTimeout(() => {'
        .'const batal = document.querySelector(\'[data-testid="btn-batal-terbit"]\');'
        .'if (batal) { batal.click(); }'
        .'setTimeout(() => resolve(window.__k03.n === 0), 700);'
        .'}, 700);'
        .'})',
        true
    );

    ScreenshotHelper::saveIfEnabled($page, 'dataspasial-terbitan-tanpa-request');
});
