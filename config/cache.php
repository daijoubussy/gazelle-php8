<?php

declare(strict_types=1);

/**
 * Cache Configuration
 */

return [
    'driver' => env('CACHE_DRIVER', 'memcached'),
    'prefix' => env('CACHE_PREFIX', 'gazelle:'),

    'servers' => [
        [
            env('MEMCACHED_HOST', '127.0.0.1'),
            (int) env('MEMCACHED_PORT', 11211),
        ],
    ],

    'ttl' => [
        'default' => 3600,
        'user' => 1800,
        'torrent' => 300,
        'session' => 86400,
    ],
];
