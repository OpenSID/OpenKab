<?php

use Tests\Browser\ScreenshotHelper;
use Tests\Browser\SessionState;

uses()->group('pengaturan-aplikasi');

beforeEach(function () {
    $this->user = SessionState::loginAdminUser();
});

afterEach(function () {
    SessionState::clear();
});

it('opens the otp and 2fa page', function () {
    $page = SessionState::loginAndNavigate($this->user, '/pengaturan/otp')
        ->assertPathIs('/pengaturan/otp')
        ->assertSee('Pengaturan OTP & 2FA Otentikasi');

    ScreenshotHelper::saveIfEnabled($page, 'otp-2fa-page');
});

it('displays otp panel', function () {
    $page = SessionState::loginAndNavigate($this->user, '/pengaturan/otp')
        ->assertVisible('@panel-otp');

    ScreenshotHelper::saveIfEnabled($page, 'otp-panel');
});

it('displays otp status', function () {
    $page = SessionState::loginAndNavigate($this->user, '/pengaturan/otp')
        ->assertPathIs('/pengaturan/otp');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const panel = document.querySelector('[data-testid=\"panel-otp\"]');
                if (panel) {
                    const text = panel.textContent;
                    const hasStatus = text.includes('Aktif') || text.includes('Tidak Aktif');
                    resolve(hasStatus);
                } else {
                    setTimeout(check, 500);
                }
            };
            check();
        })",
        true
    );

    ScreenshotHelper::saveIfEnabled($page, 'otp-status');
});

it('displays enable or disable otp button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/pengaturan/otp')
        ->assertPathIs('/pengaturan/otp');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const enableBtn = document.querySelector('[data-testid=\"bt-enable-otp\"]');
                const disableBtn = document.querySelector('[data-testid=\"bt-disable-otp\"]');
                resolve(!!(enableBtn || disableBtn));
            };
            check();
        })",
        true
    );

    ScreenshotHelper::saveIfEnabled($page, 'otp-enable-button');
});

it('displays 2fa panel', function () {
    $page = SessionState::loginAndNavigate($this->user, '/pengaturan/otp')
        ->assertVisible('@panel-2fa');

    ScreenshotHelper::saveIfEnabled($page, '2fa-panel');
});

it('displays 2fa status', function () {
    $page = SessionState::loginAndNavigate($this->user, '/pengaturan/otp')
        ->assertPathIs('/pengaturan/otp');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const panel = document.querySelector('[data-testid=\"panel-2fa\"]');
                if (panel) {
                    const text = panel.textContent;
                    const hasStatus = text.includes('Aktif') || text.includes('Tidak Aktif');
                    resolve(hasStatus);
                } else {
                    setTimeout(check, 500);
                }
            };
            check();
        })",
        true
    );

    ScreenshotHelper::saveIfEnabled($page, '2fa-status');
});

it('displays enable or disable 2fa button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/pengaturan/otp')
        ->assertPathIs('/pengaturan/otp');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const enableBtn = document.querySelector('[data-testid=\"bt-enable-2fa\"]');
                const disableBtn = document.querySelector('[data-testid=\"bt-disable-2fa\"]');
                resolve(!!(enableBtn || disableBtn));
            };
            check();
        })",
        true
    );

    ScreenshotHelper::saveIfEnabled($page, '2fa-enable-button');
});

it('displays info sidebar panel', function () {
    $page = SessionState::loginAndNavigate($this->user, '/pengaturan/otp')
        ->assertVisible('@panel-info-sidebar');

    ScreenshotHelper::saveIfEnabled($page, 'info-sidebar-panel');
});

it('displays info content in sidebar', function () {
    $page = SessionState::loginAndNavigate($this->user, '/pengaturan/otp')
        ->assertPathIs('/pengaturan/otp')
        ->assertVisible('@card-info-keamanan')
        ->assertVisible('@card-tips-tricks');

    ScreenshotHelper::saveIfEnabled($page, 'info-sidebar-content');
});

it('has no javascript errors', function () {
    $page = SessionState::loginAndNavigate($this->user, '/pengaturan/otp')
        ->assertPathIs('/pengaturan/otp')
        ->assertNoJavaScriptErrors();

    ScreenshotHelper::saveIfEnabled($page, 'otp-2fa-no-errors');
});