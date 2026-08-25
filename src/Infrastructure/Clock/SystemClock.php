<?php

declare(strict_types=1);

namespace MqttRealtime\Infrastructure\Clock;

use DateTimeImmutable;
use DateTimeZone;
use MqttRealtime\Domain\Clock;

final class SystemClock implements Clock
{
    public function __construct(private readonly DateTimeZone $timezone = new DateTimeZone('UTC'))
    {
    }

    public function now(): string
    {
        return (new DateTimeImmutable('now', $this->timezone))->format('Y/m/d H:i:s');
    }
}
