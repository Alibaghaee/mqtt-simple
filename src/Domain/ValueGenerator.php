<?php

declare(strict_types=1);

namespace MqttRealtime\Domain;

interface ValueGenerator
{
    public function generate(): int;
}
