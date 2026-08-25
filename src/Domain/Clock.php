<?php

declare(strict_types=1);

namespace MqttRealtime\Domain;

interface Clock
{
    public function now(): string;
}
