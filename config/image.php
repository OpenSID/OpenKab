<?php

return [

    'driver' => env('IMAGE_DRIVER', \Intervention\Image\Drivers\Gd\Driver::class),

    'options' => [
        'autoOrientation' => true,
        'decodeAnimation' => true,
        'blendingColor' => 'ffffff',
        'strip' => false,
    ],

];
