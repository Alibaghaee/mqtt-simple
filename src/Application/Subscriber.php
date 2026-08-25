<?php

declare(strict_types=1);

namespace MqttRealtime\Application;

use MqttRealtime\Infrastructure\Mqtt\MqttTransport;
use MqttRealtime\Infrastructure\Persistence\IdempotencyStore;
use MqttRealtime\Infrastructure\Realtime\Broadcaster;
use MqttRealtime\Infrastructure\Resilience\CircuitBreaker;
use MqttRealtime\Support\Backoff;
use Psr\Log\LoggerInterface;

final class Subscriber
{
    public function __construct(
        private readonly MqttTransport $mqtt,
        private readonly IdempotencyStore $idempotency,
        private readonly Broadcaster $broadcaster,
        private readonly CircuitBreaker $circuitBreaker,
        private readonly LoggerInterface $logger,
        private readonly string $topic,
        private readonly string $channel,
        private readonly string $event,
        private readonly int $qos = 1,
        private readonly int $idempotencyTtl = 3600,
    ) {
    }

    /** @param callable(): bool $shouldStop */
    public function run(callable $shouldStop): void
    {
        $attempt = 0;

        while (! $shouldStop()) {
            try {
                $this->circuitBreaker->call(function (): void {
                    $this->mqtt->connect();
                });
                $attempt = 0;

                $this->mqtt->subscribe($this->topic, $this->qos, function (string $topic, string $payload): void {
                    $this->handleMessage($topic, $payload);
                });

                $this->mqtt->loop();
            } catch (\Throwable $exception) {
                $attempt++;
                $this->logger->error('Subscriber loop failed; reconnecting.', [
                    'attempt' => $attempt,
                    'exception' => $exception,
                ]);

                try {
                    $this->mqtt->disconnect();
                } catch (\Throwable $disconnectException) {
                    $this->logger->warning('MQTT disconnect failed during recovery.', [
                        'exception' => $disconnectException,
                    ]);
                }

                Backoff::sleep($attempt);
            }
        }

        $this->mqtt->disconnect();
    }

    private function handleMessage(string $topic, string $payload): void
    {
        $message = \MqttRealtime\Domain\TelemetryMessage::fromJson($payload);
        $idempotencyKey = sprintf('mqtt:event:%s', $message->eventId);

        if ($this->idempotency->has($idempotencyKey)) {
            $this->logger->info('Duplicate MQTT event ignored.', ['event_id' => $message->eventId]);

            return;
        }

        $this->circuitBreaker->call(function () use ($message, $topic): void {
            $this->broadcaster->broadcast(
                $this->channel,
                $this->event,
                [
                    'event_id' => $message->eventId,
                    'sequence' => $message->sequence,
                    'timestamp' => $message->timestamp,
                    'value' => $message->value,
                    'mqtt_topic' => $topic,
                ],
            );
        });

        $this->idempotency->markProcessed($idempotencyKey, $this->idempotencyTtl);
    }
}
