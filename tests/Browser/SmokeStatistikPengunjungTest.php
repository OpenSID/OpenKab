<?php

use Tests\Browser\ScreenshotHelper;
use Tests\Browser\SessionState;

uses()->group('pengaturan-web');

beforeEach(function () {
    $this->user = SessionState::loginAdminUser();
});

afterEach(function () {
    SessionState::clear();
});

it('opens the statistik pengunjung page', function () {
    $page = SessionState::loginAndNavigate($this->user, '/cms/statistik')
        ->assertPathIs('/cms/statistik')
        ->assertSee('Data Statistik Pengunjung');

    ScreenshotHelper::saveIfEnabled($page, 'statistik-pengunjung-page');
});

it('displays panel jumlah kunjungan berdasarkan gawai', function () {
    $page = SessionState::loginAndNavigate($this->user, '/cms/statistik')
        ->assertPathIs('/cms/statistik')
        ->assertVisible('@chart-device-visitor');

    ScreenshotHelper::saveIfEnabled($page, 'statistik-device-visitor');
});

it('displays panel jumlah kunjungan harian', function () {
    $page = SessionState::loginAndNavigate($this->user, '/cms/statistik')
        ->assertPathIs('/cms/statistik')
        ->assertVisible('@chart-visitor-daily');

    ScreenshotHelper::saveIfEnabled($page, 'statistik-visitor-daily');
});

it('displays panel jumlah kunjungan berdasarkan url', function () {
    $page = SessionState::loginAndNavigate($this->user, '/cms/statistik')
        ->assertPathIs('/cms/statistik')
        ->assertVisible('@chart-visitor-post');

    ScreenshotHelper::saveIfEnabled($page, 'statistik-visitor-post');
});

it('renders all charts successfully', function () {
    $page = SessionState::loginAndNavigate($this->user, '/cms/statistik')
        ->assertPathIs('/cms/statistik');

    $page->assertScript(
        'new Promise(function(resolve) {
            var attempts = 0;
            var maxAttempts = 20;
            function check() {
                attempts++;
                var d = document.querySelector("[data-testid=\"chart-device-visitor\"]");
                var dy = document.querySelector("[data-testid=\"chart-visitor-daily\"]");
                var p = document.querySelector("[data-testid=\"chart-visitor-post\"]");
                var allRendered = d && dy && p && d.children.length > 0 && dy.children.length > 0 && p.children.length > 0;
                if (allRendered) {
                    resolve(true);
                } else if (attempts >= maxAttempts) {
                    resolve(false);
                } else {
                    setTimeout(check, 500);
                }
            }
            check();
        })',
        true
    );

    ScreenshotHelper::saveIfEnabled($page, 'statistik-charts-rendered');
});
