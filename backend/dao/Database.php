<?php

require_once __DIR__ . '/../utils/Config.php';

/**
 * Creates one shared PDO connection to the MySQL database.
 * Credentials are read from config/config.php (not committed to Git) via Config.
 */
class Database
{
    private static $connection = null;

    public static function connect()
    {
        // Reuse the connection if it was already created during this request
        if (self::$connection !== null) {
            return self::$connection;
        }

        $db = Config::get('db');

        $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['name']};charset=utf8mb4";

        self::$connection = new PDO($dsn, $db['user'], $db['password'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // SQL errors throw exceptions
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // rows come back as associative arrays
            PDO::ATTR_EMULATE_PREPARES   => false                   // use real prepared statements
        ]);

        return self::$connection;
    }
}
