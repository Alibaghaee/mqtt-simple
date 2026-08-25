<?php

declare(strict_types=1);

use Monolog\Handler\NullHandler;
use Monolog\Logger;
use MqttRealtime\Application\Subscriber;
use MqttRealtime\Infrastructure\Mqtt\MqttTransport;
use MqttRealtime\Infrastructure\Persistence\InMemoryIdempotencyStore;
use MqttRealtime\Infrastructure\Realtime\Broadcaster;
use MqttRealtime\Infrastructure\Resilience\CircuitBreaker;
use Ramsey\Uuid\Uuid;

it('does not broadcast an already processed event twice', function (): void {
    $eventId = Uuid::uuid7()->toString();
    $payload = json_encode([
        'event_id' => $eventId,
        'sequence' => 1,
        'timestamp' => '2026/08/25 14:30:01',
        'value' => 25,
    ], JSON_THROW_ON_ERROR);

    $mqtt = new class ($payload) implements MqttTransport {
        public function __construct(private string $payload)
        {
        }

        public function connect(): void
        {
        }

        public function disconnect(): void
        {
        }

        public function publish(string $topic, string $payload, int $qos): void
        {
        }
        public function subscribe(string $topic, int $qos, callable $handler): void
        {
            $handler($topic, $this->payload);
            $handler($topic, $this->payload);
        }
        public function loop(): void
        {
        }

        public function interrupt(): void
        {
        }
    };

    $broadcaster = new class () implements Broadcaster {
        public int $calls = 0;
        public function broadcast(string $channel, string $event, array $payload): void
        {
            $this->calls++;
        }
    };

    $logger = new Logger('test');
    $logger->pushHandler(new NullHandler());

    $subscriber = new Subscriber(
        mqtt: $mqtt,
        idempotency: new InMemoryIdempotencyStore(),
        broadcaster: $broadcaster,
        circuitBreaker: new CircuitBreaker(),
        logger: $logger,
        topic: 'test/ali',
        channel: 'mqtt.telemetry',
        event: 'telemetry.updated',
    );

    $checks = 0;
    $subscriber->run(function () use (&$checks): bool {
        return ++$checks > 1;
    });

    expect($broadcaster->calls)->toBe(1);
});
