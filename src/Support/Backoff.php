<?php

declare(strict_types=1);

namespace MqttRealtime\Support;

final class Backoff
{
    public static function sleep(int $attempt, int $baseMilliseconds = 250, int $maxMilliseconds = 10_000): void
    {
        $exponent = min($maxMilliseconds, $baseMilliseconds * (2 ** min($attempt, 8)));
        $jitter = random_int(0, max(1, (int) ($exponent * 0.25)));
        usleep(($exponent + $jitter) * 1000);
    }
}
