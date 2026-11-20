<?php

class Database
{
    private static $connection = null;

    public static function connect()
    {

        if (self::$connection !== null) {
            return self::$connection;
        }

        $configFile = __DIR__ . '/../config/config.php';
        if (!file_exists($configFile)) {
            throw new Exception('Missing config/config.php. Copy config.example.php to config.php and fill it in.');
        }
        $config = require $configFile;
        $db = $config['db'];

        $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['name']};charset=utf8mb4";

        self::$connection = new PDO($dsn, $db['user'], $db['password'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // SQL errors throw exceptions
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // rows come back as associative arrays
            PDO::ATTR_EMULATE_PREPARES   => false                   // use real prepared statements
        ]);

        return self::$connection;
    }
}
