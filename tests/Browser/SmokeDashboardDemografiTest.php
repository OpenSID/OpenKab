<?php

use Tests\Browser\FixtureReader;
use Tests\Browser\ScreenshotHelper;
use Tests\Browser\SessionState;

beforeEach(function () {
    $this->user = SessionState::loginAdminUser();
});

afterEach(function () {
    SessionState::clear();
});

$chartKeys = FixtureReader::demografiChartKeys();
$chartLabels = FixtureReader::demografiChartLabels();
$kabupatenNames = FixtureReader::kabupatenNames();
$firstKabupatenKode = FixtureReader::firstKabupatenKode();

it('displays all dashboard elements', function () use ($chartKeys) {
    $page = SessionState::loginAndNavigate($this->user, '/dasbor-demografi')
        ->assertPathIs('/dasbor-demografi')
        ->assertSee('Dasbor Demografi')
        ->assertVisible('@filter-kabupaten')
        ->assertVisible('@filter-kecamatan')
        ->assertVisible('@filter-desa')
        ->assertVisible('@bt-filter');

    foreach ($chartKeys as $key) {
        $page->assertVisible("@chart-item-{$key}");
    }
});

it('loads kabupaten dropdown options from ajax', function () use ($kabupatenNames) {
    $page = SessionState::loginAndNavigate($this->user, '/dasbor-demografi')
        ->assertPathIs('/dasbor-demografi');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const count = document.querySelectorAll('#filter_kabupaten option').length;
                if (count > 1) { resolve(true); } else { setTimeout(check, 200); }
            };
            check();
        })",
        true
    );

    foreach ($kabupatenNames as $name) {
        $escaped = addslashes($name);
        $page->assertScript(
            "Array.from(document.querySelectorAll('#filter_kabupaten option')).some(o => o.textContent.trim() === '{$escaped}')",
            true
        );
    }
});

it('displays correct chart titles from fixture', function () use ($chartLabels) {
    $page = SessionState::loginAndNavigate($this->user, '/dasbor-demografi')
        ->assertPathIs('/dasbor-demografi');

    foreach ($chartLabels as $key => $label) {
        $page->assertSeeIn("@chart-title-{$key}", "Komposisi {$label}");
    }
});

it('renders chart content from fixture data', function () use ($chartKeys) {
    $page = SessionState::loginAndNavigate($this->user, '/dasbor-demografi')
        ->assertPathIs('/dasbor-demografi');

    $keysJson = json_encode($chartKeys);
    $page->assertScript(
        "new Promise((resolve) => {
            const keys = {$keysJson};
            const elFor = key => document.querySelector('[data-testid=\"chart-content-' + key + '\"]');
            const hasCanvas = key => { const e = elFor(key); return e && e.querySelector('canvas'); };
            const hasLoading = key => { const e = elFor(key); return e && e.textContent.includes('Sedang memuat'); };

            const phase1 = () => {
                if (keys.some(hasLoading)) { setTimeout(phase2, 100); }
                else if (keys.every(hasCanvas)) {
                    setTimeout(() => {
                        if (keys.every(k => hasCanvas(k) && !hasLoading(k))) { resolve(true); }
                        else { setTimeout(phase2, 200); }
                    }, 2000);
                }
                else { setTimeout(phase1, 50); }
            };
            const phase2 = () => {
                if (keys.every(k => hasCanvas(k) && !hasLoading(k))) { resolve(true); }
                else { setTimeout(phase2, 200); }
            };
            phase1();
        })",
        true
    );

    foreach ($chartKeys as $key) {
        $page->assertSourceInHas("@chart-content-{$key}", "donutChart-{$key}");
    }
});

it('applies filter and all charts remain visible', function () use ($chartKeys, $firstKabupatenKode) {
    $page = SessionState::loginAndNavigate($this->user, '/dasbor-demografi')
        ->assertPathIs('/dasbor-demografi')
        ->assertVisible('@filter-kabupaten')
        ->assertVisible('@bt-filter');

    $page->script("$('#filter_kabupaten').val('{$firstKabupatenKode}').trigger('change')");
    $page->click('@bt-filter');

    foreach ($chartKeys as $key) {
        $page->assertVisible("@chart-item-{$key}");
    }

    ScreenshotHelper::saveIfEnabled($page, 'demografi-filter');
});