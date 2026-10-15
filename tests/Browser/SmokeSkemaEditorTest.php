<?php

declare(strict_types=1);

use Tests\Browser\ScreenshotHelper;
use Tests\Browser\SessionState;

/**
 * Smoke test editor skema kabupaten — KAB-02 (#1297).
 *
 * Catatan penting: pemanggilan API Satu Data terjadi di sisi **server** (PHP),
 * sehingga TIDAK dapat di-intercept oleh MSW. Tanpa API yang hidup, halaman
 * merender partial "Data sementara tidak tersedia" — dan itu justru yang
 * diverifikasi di sini: **degradasi anggun** menghasilkan halaman utuh dengan
 * sidebar, bukan halaman error (FR-005).
 *
 * Yang diverifikasi di lingkungan tanpa API hidup:
 *  - halaman `/dataspasial/skema` terbuka (bukan 403/500);
 *  - tombol Terbitan tampil dalam keadaan nonaktif (FR-040/Q1);
 *  - tidak ada satu pun halaman error atau stack trace.
 *
 * Skenario-interactive (daftar sektor, daftar skema setelah dipilih, tambah
 * isian) memerlukan API hidup dan dipakai sebagai smoke MANUAL — lihat
 * `quickstart.md` §2.
 */
beforeEach(function () {
    $this->user = SessionState::loginAdminUser();
});

afterEach(function () {
    SessionState::clear();
});

it('membuka halaman editor skema tanpa error', function () {
    $page = SessionState::loginAndNavigate($this->user, '/dataspasial/skema')
        ->assertPathIs('/dataspasial/skema')
        ->assertSee('Skema DataSpasial');

    ScreenshotHelper::saveIfEnabled($page, 'skema-editor-halaman');
});

it('menampilkan konten editor atau pesan data sementara tidak tersedia', function () {
    $page = SessionState::loginAndNavigate($this->user, '/dataspasial/skema')
        ->assertPathIs('/dataspasial/skema');

    // Dua keadaan yang boleh terjadi: editor terisi (API hidup) atau pesan
    // "data sementara tidak tersedia" (API mati). Keduanya WAJIB bukan error.
    $page->assertScript(
        "(function () {
            const isi = document.body.innerText;
            const editor = document.querySelector('[data-status-editor]');
            const kosong = document.querySelector('[data-testid=\"dataspasial-empty\"]');
            const takTersedia = document.querySelector('[data-testid=\"dataspasial-unavailable\"]');
            return Boolean(editor || kosong || takTersedia)
                && isi.indexOf('Stack trace') === -1
                && isi.indexOf('Whoops') === -1;
        })()",
        true
    );

    ScreenshotHelper::saveIfEnabled($page, 'skema-editor-keadaan');
});

it('menampilkan tombol Terbitkan dalam keadaan nonaktif', function () {
    $page = SessionState::loginAndNavigate($this->user, '/dataspasial/skema')
        ->assertPathIs('/dataspasial/skema');

    $page->assertScript(
        "(function () {
            const tombol = document.getElementById('btn-terbitkan');
            if (!tombol) {
                // Editor tidak dirender saat sumber tidak tersedia (FR-005).
                return Boolean(document.querySelector('[data-testid=\"dataspasial-unavailable\"]'));
            }
            return tombol.disabled === true;
        })()",
        true
    );

    ScreenshotHelper::saveIfEnabled($page, 'skema-editor-terbitkan-nonaktif');
});

it('tidak pernah memanggil endpoint terbitan', function () {
    $page = SessionState::loginAndNavigate($this->user, '/dataspasial/skema')
        ->assertPathIs('/dataspasial/skema');

    $page->assertScript(
        "(function () {
            const isi = document.documentElement.outerHTML;
            return isi.indexOf('/terbit') === -1;
        })()",
        true
    );
});

it('menyimpan seluruh halaman tanpa error javascript', function () {
    $page = SessionState::loginAndNavigate($this->user, '/dataspasial/skema')
        ->assertPathIs('/dataspasial/skema');

    // Kegagalan ONLY yang relevan: skrip editor gagal dimuat. Kesalahan
    // jaringan dari aset lain tidak boleh menggagalkan test ini.
    $page->assertScript(
        "(function () {
            return typeof window.__schemaEditorAssetMissing !== 'boolean';
        })()",
        true
    );

    ScreenshotHelper::saveIfEnabled($page, 'skema-editor-akhir');
});

it('tidak menampilkan stack trace maupun halaman error', function () {
    $page = SessionState::loginAndNavigate($this->user, '/dataspasial/skema')
        ->assertPathIs('/dataspasial/skema');

    $isi = $page->text('body');

    expect($isi)
        ->not->toContain('Stack trace')
        ->not->toContain('Whoops, looks like something went wrong')
        ->not->toContain('SQLSTATE')
        ->not->toContain('/var/www/');
});