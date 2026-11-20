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
    'external_api' => [
        // Frankfurter (currency rates) needs no API key
        'currency_base_url' => 'https://api.frankfurter.app',
        'timeout'           => 5, // seconds
    ],
];
