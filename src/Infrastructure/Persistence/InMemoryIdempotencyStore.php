<?php

declare(strict_types=1);

namespace MqttRealtime\Infrastructure\Persistence;

final class InMemoryIdempotencyStore implements IdempotencyStore
{
    /** @var array<string,int> */
    private array $keys = [];

    public function has(string $key): bool
    {
        $this->purgeExpired();

        return isset($this->keys[$key]);
    }

    public function markProcessed(string $key, int $ttlSeconds): void
    {
        $this->purgeExpired();
        $this->keys[$key] = time() + $ttlSeconds;
    }

    private function purgeExpired(): void
    {
        $now = time();

        foreach ($this->keys as $key => $expiresAt) {
            if ($expiresAt <= $now) {
                unset($this->keys[$key]);
            }
        }
    }
}
