<?php

return [
    'db' => [
        'host'     => 'localhost',
        'port'     => 3306,
        'name'     => 'tracksuit_couture',
        'user'     => 'root',
        'password' => '',
    ],
    'jwt' => [
        'secret'  => 'placeholder',
        'expires' => 3600, // seconds
    ],
    'currency_api' => [
        // Frankfurter (https://frankfurter.dev): free currency rates, no API key needed.
        // If you switch to an API that needs a key, put it in 'api_key'.
        'base_url' => 'https://api.frankfurter.dev/v1',
        'api_key'  => '',
        'timeout'  => 5, // seconds
    ],
];
