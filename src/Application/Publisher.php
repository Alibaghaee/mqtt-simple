<?php

declare(strict_types=1);

namespace MqttRealtime\Application;

use MqttRealtime\Domain\Clock;
use MqttRealtime\Domain\TelemetryMessage;
use MqttRealtime\Domain\ValueGenerator;
use MqttRealtime\Infrastructure\Mqtt\MqttTransport;
use MqttRealtime\Infrastructure\Resilience\CircuitBreaker;
use MqttRealtime\Support\Backoff;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

final class Publisher
{
    private int $sequence = 0;

    public function __construct(
        private readonly MqttTransport $mqtt,
        private readonly Clock $clock,
        private readonly ValueGenerator $valueGenerator,
        private readonly CircuitBreaker $circuitBreaker,
        private readonly string $topic,
        private readonly int $qos = 1,
        private readonly int $intervalSeconds = 1,
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    /** @param callable(): bool $shouldStop */
    public function run(callable $shouldStop): void
    {
        $attempt = 0;
        $connected = false;

        while (! $shouldStop()) {
            try {
                if (! $connected) {
                    $this->circuitBreaker->call(function (): void {
                        $this->mqtt->connect();
                    });
                    $connected = true;
                    $attempt = 0;
                }

                $startedAt = microtime(true);
                $message = $this->createMessage();

                $this->circuitBreaker->call(function () use ($message): void {
                    $this->mqtt->publish($this->topic, $message->toJson(), $this->qos);
                });

                $remaining = $this->intervalSeconds - (microtime(true) - $startedAt);
                if ($remaining > 0) {
                    usleep((int) ($remaining * 1_000_000));
                }
            } catch (\Throwable $exception) {
                $attempt++;
                $connected = false;

                $this->logger?->error('Publisher loop failed; reconnecting.', [
                    'attempt' => $attempt,
                    'exception' => $exception,
                ]);

                try {
                    $this->mqtt->disconnect();
                } catch (\Throwable $disconnectException) {
                    $this->logger?->warning('MQTT disconnect failed during recovery.', [
                        'exception' => $disconnectException,
                    ]);
                }

                Backoff::sleep($attempt);
            }
        }

        try {
            $this->mqtt->disconnect();
        } catch (\Throwable $exception) {
            $this->logger?->warning('MQTT disconnect failed during shutdown.', [
                'exception' => $exception,
            ]);
        }
    }

    private function createMessage(): TelemetryMessage
    {
        return new TelemetryMessage(
            eventId: Uuid::uuid7()->toString(),
            sequence: ++$this->sequence,
            timestamp: $this->clock->now(),
            value: $this->valueGenerator->generate(),
        );
    }
}
