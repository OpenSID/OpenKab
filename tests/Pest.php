<?php

use Tests\TestCase;

uses(TestCase::class)->in('Feature', 'Browser');

pest()->browser()->timeout(30000);
