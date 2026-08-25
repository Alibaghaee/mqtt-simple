<?php

declare(strict_types=1);

namespace MqttRealtime\Infrastructure\Persistence;

use Predis\Client;

final class RedisIdempotencyStore implements IdempotencyStore
{
    public function __construct(private readonly Client $redis)
    {
    }

    public function has(string $key): bool
    {
        return (bool) $this->redis->exists($key);
    }

    public function markProcessed(string $key, int $ttlSeconds): void
    {
        $this->redis->set($key, '1', 'EX', $ttlSeconds);
    }
}
