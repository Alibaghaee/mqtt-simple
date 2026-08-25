<?php

declare(strict_types=1);

namespace MqttRealtime\Infrastructure\Persistence;

interface IdempotencyStore
{
    public function has(string $key): bool;

    public function markProcessed(string $key, int $ttlSeconds): void;
}
