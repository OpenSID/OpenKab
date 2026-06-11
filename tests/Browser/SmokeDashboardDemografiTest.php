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

    $page->assertScript("document.querySelectorAll('#filter_kabupaten option').length > 1", true);

    foreach ($kabupatenNames as $name) {
        $page->assertSourceInHas('#filter_kabupaten', $name);
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