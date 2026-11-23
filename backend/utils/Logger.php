<?php

/**
 * Writes lines to backend/logs/info.log and backend/logs/error.log.
 * Example line: [2026-11-21 14:05:12] ERROR Currency API failed: timeout after 5s
 *
 * Milestone 3 uses it for unhandled errors and external API calls.
 * Milestone 4 adds request logging middleware on top of it.
 */
class Logger
{
    public static function info($message)
    {
        self::write('info.log', 'INFO', $message);
    }

    public static function error($message)
    {
        self::write('error.log', 'ERROR', $message);
    }

    private static function write($file, $level, $message)
    {
        $line = '[' . date('Y-m-d H:i:s') . "] $level $message" . PHP_EOL;
        // FILE_APPEND adds to the end of the file, LOCK_EX stops two requests writing at the same time
        @file_put_contents(__DIR__ . '/../logs/' . $file, $line, FILE_APPEND | LOCK_EX);
    }
}
