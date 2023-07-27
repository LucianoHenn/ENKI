<?php

return  [
    'persistent_id' => env('MEMCACHED_PERSISTENT_ID'),
    'sasl' => [
        env('MEMCACHED_USERNAME'),
        env('MEMCACHED_PASSWORD'),
    ],
    'options' => [
        // Memcached::OPT_CONNECT_TIMEOUT => 2000,
    ],
    'servers' => [

        ['host' => env('MEMCACHED_HOST1', '127.0.0.1'), 'port' => env('MEMCACHED_PORT1', 11211), 'weight' => 50],
        ['host' => env('MEMCACHED_HOST2', '127.0.0.1'), 'port' => env('MEMCACHED_PORT2', 11211), 'weight' => 50]

    ],
];