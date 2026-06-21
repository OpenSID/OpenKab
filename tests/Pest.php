<?php

use Tests\TestCase;

uses(TestCase::class)->in('Feature', 'Browser');

pest()->browser()->timeout(30000);

beforeEach(function () {
    config(['adminlte.google_fonts.allowed' => false]);
});
