<?php

declare(strict_types=1);

namespace MqttRealtime\Infrastructure\Clock;

use MqttRealtime\Domain\ValueGenerator;

final class RandomValueGenerator implements ValueGenerator
{
    public function generate(): int
    {
        return random_int(20, 30);
    }
}
