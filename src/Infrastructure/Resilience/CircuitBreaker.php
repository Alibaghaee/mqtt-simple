<?php

declare(strict_types=1);

namespace MqttRealtime\Infrastructure\Resilience;

use RuntimeException;

final class CircuitBreaker
{
    private CircuitState $state = CircuitState::Closed;
    private int $failures = 0;
    private ?float $openedAt = null;

    public function __construct(
        private readonly int $failureThreshold = 5,
        private readonly int $recoveryTimeoutSeconds = 10,
    ) {
        if ($failureThreshold < 1 || $recoveryTimeoutSeconds < 1) {
            throw new RuntimeException('Circuit breaker settings must be positive.');
        }
    }

    /**
     * @template T
     * @param callable(): T $operation
     * @return T
     */
    public function call(callable $operation): mixed
    {
        $this->assertAvailable();

        try {
            $result = $operation();
            $this->recordSuccess();

            return $result;
        } catch (\Throwable $exception) {
            $this->recordFailure();
            throw $exception;
        }
    }

    public function state(): CircuitState
    {
        return $this->state;
    }

    private function assertAvailable(): void
    {
        if ($this->state !== CircuitState::Open) {
            return;
        }

        if ($this->openedAt === null || (microtime(true) - $this->openedAt) < $this->recoveryTimeoutSeconds) {
            throw new RuntimeException('Circuit breaker is open.');
        }

        $this->state = CircuitState::HalfOpen;
    }

    private function recordSuccess(): void
    {
        $this->failures = 0;
        $this->openedAt = null;
        $this->state = CircuitState::Closed;
    }

    private function recordFailure(): void
    {
        $this->failures++;

        if ($this->failures >= $this->failureThreshold) {
            $this->state = CircuitState::Open;
            $this->openedAt = microtime(true);
        }
    }
}
