<?php

declare(strict_types=1);

namespace MqttRealtime\Infrastructure\Realtime;

interface Broadcaster
{
    /** @param array<string,mixed> $payload */
    public function broadcast(string $channel, string $event, array $payload): void;
}
