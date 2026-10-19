<?php

use Tests\TestCase;

uses(TestCase::class)->in('Feature', 'Browser');

// Fixture bersama editor skema (KAB-02). Berisi SALINAN PERSIS respons API
// Satu Data yang nyata — `openapi-fondasi.yaml` v0.1.0 tidak cocok dengan
// implementasi. Lihat specs/004-pengelola-skema-editor/contracts/skema-editor-api.md §6a.
//
// PSR-4 tidak meng-autoload fungsi biasa, jadi berkas ini di-require manual.
// Bukan test (tidak berakhiran `Test.php`) sehingga runner tidak mengambilnya.
require_once __DIR__.'/Feature/DataSpasial/Fixtures.php';

pest()->browser()->timeout(30000);

beforeEach(function () {
    config(['adminlte.google_fonts.allowed' => false]);
});
