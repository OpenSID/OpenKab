<?php

use Tests\Browser\SessionState;
use Tests\TestCase;

uses(TestCase::class)->in('Feature', 'Browser');

pest()->browser()->timeout(30000);

beforeAll(function () {
    SessionState::startMockServer();
});

afterAll(function () {
    SessionState::stopMockServer();
});
