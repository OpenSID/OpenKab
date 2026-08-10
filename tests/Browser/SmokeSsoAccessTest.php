<?php

use Tests\Browser\ScreenshotHelper;
use Tests\Browser\SessionState;

beforeEach(function () {
    $this->user = SessionState::loginAdminUser();
});

afterEach(function () {
    SessionState::clear();
});

it('menampilkan tombol Masuk ke OpenSID pada tabel desa', function () {
    $page = SessionState::loginAndNavigate($this->user, '/desa')
        ->assertPathIs('/desa');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const btns = document.querySelectorAll('.btn-sso-opensid').length;
                if (btns > 0) { resolve(true); } else { setTimeout(check, 200); }
            };
            check();
        })",
        true
    );

    $page->assertVisible('.btn-sso-opensid >> nth=0');
    $page->assertSee('Masuk ke OpenSID');

    ScreenshotHelper::saveIfEnabled($page, 'sso-desa-button');
});

it('klik tombol memicu request generate-session dan menampilkan pesan generik bila gagal', function () {
    $page = SessionState::loginAndNavigate($this->user, '/desa')
        ->assertPathIs('/desa');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const btns = document.querySelectorAll('.btn-sso-opensid').length;
                if (btns > 0) { resolve(true); } else { setTimeout(check, 200); }
            };
            check();
        })",
        true
    );

    $page->click('.btn-sso-opensid >> nth=0');

    // Sesi smoke tanpa 2fa_verified → endpoint menolak dengan pesan generik.
    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const popup = document.querySelector('.swal2-popup');
                if (popup) { resolve(true); } else { setTimeout(check, 200); }
            };
            check();
        })",
        true
    );
    $page->assertVisible('.swal2-popup');
});
