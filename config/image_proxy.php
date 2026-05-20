<?php

return [    

    'max_cache_bytes' => 1024 * 1024 * 5, // 5MB

    'default_timeout' => 10,

    'cache_ttl' => 3600,

    'enabled' => env('IMAGE_PROXY_ENABLED', true),
];