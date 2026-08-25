<?php

declare(strict_types=1);

namespace MqttRealtime\Support;

use RuntimeException;

final class Env
{
    public static function string(string $key, ?string $default = null): string
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? $default;

        if ($value === null) {
            throw new RuntimeException("Missing environment variable: {$key}");
        }

        return (string) $value;
    }

    public static function int(string $key, int $default): int
    {
        return (int) self::string($key, (string) $default);
    }

    public static function bool(string $key, bool $default): bool
    {
        return filter_var(self::string($key, $default ? 'true' : 'false'), FILTER_VALIDATE_BOOLEAN);
    }
}
