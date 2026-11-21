<?php

/**
 * Reads values from config/config.php (which is not committed to Git).
 *   Config::get('db')            -> the whole 'db' array
 *   Config::get('jwt', 'secret') -> one value
 */
class Config
{
    private static $values = null;

    public static function get($section, $key = null)
    {
        if (self::$values === null) {
            $file = __DIR__ . '/../config/config.php';
            if (!file_exists($file)) {
                throw new Exception('Missing config/config.php. Copy config.example.php to config.php and fill it in.');
            }
            self::$values = require $file;
        }

        $sectionValues = self::$values[$section] ?? [];
        return $key === null ? $sectionValues : ($sectionValues[$key] ?? null);
    }
}
