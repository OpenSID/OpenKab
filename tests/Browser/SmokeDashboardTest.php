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

$kabupatenNames = FixtureReader::kabupatenNames();
$categoriesValues = FixtureReader::categoriesValues();
$firstKabupatenKode = FixtureReader::firstKabupatenKode();

it('displays all dashboard elements', function () {
    SessionState::loginAndNavigate($this->user, '/dasbor')
        ->assertPathIs('/dasbor')
        ->assertSee('Dasbor')
        ->assertVisible('@filter-kabupaten')
        ->assertVisible('@filter-kecamatan')
        ->assertVisible('@filter-desa')
        ->assertVisible('@bt-filter')
        ->assertVisible('@summary-card-kecamatan')
        ->assertVisible('@summary-card-desa')
        ->assertVisible('@summary-card-penduduk')
        ->assertVisible('@summary-card-keluarga')
        ->assertVisible('@peta')
        ->assertVisible('@tabel-penduduk-block')
        ->assertVisible('@summary-penduduk');
});

it('loads kabupaten dropdown options from ajax', function () use ($kabupatenNames) {
    $page = SessionState::loginAndNavigate($this->user, '/dasbor')
        ->assertPathIs('/dasbor');

    $page->assertScript("document.querySelectorAll('#filter_kabupaten option').length > 1", true);

    foreach ($kabupatenNames as $name) {
        $page->assertSourceInHas('#filter_kabupaten', $name);
    }
});

it('displays correct card values from fixture', function () use ($categoriesValues) {
    $page = SessionState::loginAndNavigate($this->user, '/dasbor')
        ->assertPathIs('/dasbor');

    foreach ($categoriesValues as $key => $value) {
        $page->assertVisible("@summary-value-{$key}");
    }

    foreach ($categoriesValues as $key => $value) {
        $page->assertSeeIn("@summary-value-{$key}", $value);
    }
});

it('applies filter and elements remain visible', function () use ($firstKabupatenKode) {
    $page = SessionState::loginAndNavigate($this->user, '/dasbor')
        ->assertPathIs('/dasbor')
        ->assertVisible('@filter-kabupaten')
        ->assertVisible('@bt-filter');

    $page->script("$('#filter_kabupaten').val('{$firstKabupatenKode}').trigger('change')");
    $page->click('@bt-filter');

    $page->assertVisible('@peta')
        ->assertVisible('@tabel-penduduk-block')
        ->assertVisible('@summary-penduduk');

    ScreenshotHelper::saveIfEnabled($page, 'dashboard-filter');
});