<?php

declare(strict_types=1);

namespace MqttRealtime\Infrastructure\Resilience;

enum CircuitState: string
{
    case Closed = 'closed';
    case Open = 'open';
    case HalfOpen = 'half_open';
}
