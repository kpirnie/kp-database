<?php

namespace KPT;

/**
 * Mock Logger class for testing
 */
class Logger
{
    private static array $logs = [];

    public static function debug(string $message, array $context = []): void
    {
        self::$logs[] = [
            'level' => 'debug',
            'message' => $message,
            'context' => $context
        ];
    }

    public static function error(string $message, array $context = []): void
    {
        self::$logs[] = [
            'level' => 'error',
            'message' => $message,
            'context' => $context
        ];
    }

    public static function getLogs(): array
    {
        return self::$logs;
    }

    public static function clearLogs(): void
    {
        self::$logs = [];
    }
}
