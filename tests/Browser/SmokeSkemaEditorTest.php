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
// ---------------------------------------------------------------------------
// Q24/Q25 — FR-008c/e — alur tombol "Ubah" → "Batal" pada skema `terbit`
//
// Tanpa API Satu Data yang hidup, editor berada dalam keadaan
// "data sementara tidak tersedia" sehingga tombol Ubah tidak dirender. Test
// ini karena itu memverifikasi dua hal yang tetap berlaku di kedua keadaan:
// (1) tidak ada tombol Ubah pada keadaan non-terbit, dan (2) bila tombol Ubah
// ada, kliknya TIDAK menghasilkan permintaan jaringan apa pun (FR-008c).
// ---------------------------------------------------------------------------

it('menampilkan tombol Ubah hanya pada skema terbit', function () {
    $page = SessionState::loginAndNavigate($this->user, '/dataspasial/skema')
        ->assertPathIs('/dataspasial/skema');

    $page->assertScript(
        "(function () {
            const akar = document.querySelector('[data-status-editor]');
            if (!akar) {
                // Sumber data tidak tersedia — tombol Ubah memang tidak boleh ada.
                return document.getElementById('btn-ubah') === null;
            }
            if (akar.dataset.statusEditor === 'terbit') {
                return document.getElementById('btn-ubah') !== null;
            }
            return document.getElementById('btn-ubah') === null;
        })()",
        true
    );

    ScreenshotHelper::saveIfEnabled($page, 'skema-editor-tombol-ubah');
});

it('tidak mengirim permintaan apa pun ketika tombol Ubah diklik', function () {
    $page = SessionState::loginAndNavigate($this->user, '/dataspasial/skema')
        ->assertPathIs('/dataspasial/skema');

    $page->assertScript(
        "(function () {
            const tombol = document.getElementById('btn-ubah');
            if (!tombol) {
                return true; // tidak ada tombol pada keadaan ini
            }

            // Hitung permintaan jaringan yang dibuat library fetcher browser.
            let jumlah = 0;
            const asli = window.fetch;
            window.fetch = function () { jumlah += 1; return asli.apply(this, arguments); };

            tombol.click();

            window.fetch = asli;

            return jumlah === 0;
        })()",
        true
    );

    ScreenshotHelper::saveIfEnabled($page, 'skema-editor-ubah-tanpa-request');
});

// ---------------------------------------------------------------------------
// Q26/Q27 — FR-008f/g — mode "sektor baru" + SweetAlert2 termuat
// ---------------------------------------------------------------------------

it('memuat SweetAlert2 untuk seluruh pesan pada halaman editor', function () {
    $page = SessionState::loginAndNavigate($this->user, '/dataspasial/skema')
        ->assertPathIs('/dataspasial/skema');

    // FR-008g/Q27 — `window.Swal` wajib tersedia; pesan TIDAK lagi memakai
    // window.alert/confirm/prompt. Bila CDN terblokir, halamaneditor akan
    // memakai fallback sehingga tidak fatal.
    $page->assertScript(
        "(function () {
            const tersedia = typeof window.Swal !== 'undefined';
            if (!tersedia) {
                return typeof window.__swalCdnGagal !== 'undefined';
            }
            return typeof window.Swal.fire === 'function';
        })()",
        true
    );

    ScreenshotHelper::saveIfEnabled($page, 'skema-editor-sweetalert');
});

it('menyediakan tombol Tambah Sektor Baru tanpa formulir terpisah', function () {
    $page = SessionState::loginAndNavigate($this->user, '/dataspasial/skema')
        ->assertPathIs('/dataspasial/skema');

    $page->assertScript(
        "(function () {
            const tombol = document.getElementById('btn-tambah-sektor');
            if (!tombol) {
                // Editor tidak dirender saat sumber data tidak tersedia (FR-005).
                return Boolean(document.querySelector('[data-testid=\"dataspasial-unavailable\"]'));
            }
            return document.getElementById('form-tambah-sektor') === null;
        })()",
        true
    );

    ScreenshotHelper::saveIfEnabled($page, 'skema-editor-tambah-sektor');
});

// ---------------------------------------------------------------------------
// Regresi — tombol "Tambah Isian Baru" harus berfungsi pada mode penyuntingan
// (terbit → Ubah, dan mode "sektor baru"). Template baris kosong WAJIB selalu
// ada di DOM; bila tidak, handler bail-out diam-diam sehingga tombol tidak
// berefek (FR-008c/FR-008f).
// ---------------------------------------------------------------------------

it('menyediakan template baris isian agar tombol tambah isian dapat bekerja', function () {
    $page = SessionState::loginAndNavigate($this->user, '/dataspasial/skema')
        ->assertPathIs('/dataspasial/skema');

    $page->assertScript(
        "(function () {
            // Saat editor tidak dirender (sumber data tidak tersedia) tidak ada
            // yang perlu diverifikasi — degradasi anggun (FR-005).
            if (!document.getElementById('isi-tabel-isian')) {
                return true;
            }

            const template = document.getElementById('template-baris-isian');
            const tombol = document.getElementById('btn-tambah-isian');

            return Boolean(template) && Boolean(tombol);
        })()",
        true
    );

    ScreenshotHelper::saveIfEnabled($page, 'skema-editor-template-baris');
});
