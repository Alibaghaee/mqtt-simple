<?php

declare(strict_types=1);

use MqttRealtime\Application\Publisher;
use MqttRealtime\Domain\Clock;
use MqttRealtime\Domain\ValueGenerator;
use MqttRealtime\Infrastructure\Mqtt\MqttTransport;
use MqttRealtime\Infrastructure\Resilience\CircuitBreaker;

it('publishes a valid message and payload contract', function (): void {
    $mqtt = new class () implements MqttTransport {
        public string $payload = '';
        public int $published = 0;

        public function connect(): void
        {
        }

        public function disconnect(): void
        {
        }
        public function publish(string $topic, string $payload, int $qos): void
        {
            $this->payload = $payload;
            $this->published++;
        }
        public function subscribe(string $topic, int $qos, callable $handler): void
        {
        }

        public function loop(): void
        {
        }

        public function interrupt(): void
        {
        }
    };

    $clock = new class () implements Clock {
        public function now(): string
        {
            return '2026/08/25 14:30:01';
        }
    };

    $generator = new class () implements ValueGenerator {
        public function generate(): int
        {
            return 24;
        }
    };

    $publisher = new Publisher(
        mqtt: $mqtt,
        clock: $clock,
        valueGenerator: $generator,
        circuitBreaker: new CircuitBreaker(),
        topic: 'test/ali',
        qos: 1,
        intervalSeconds: 1,
    );

    $publisher->run(function () use ($mqtt): bool {
        return $mqtt->published >= 1;
    });

    $payload = json_decode($mqtt->payload, true, 512, JSON_THROW_ON_ERROR);

    expect($payload)->toHaveKeys(['event_id', 'sequence', 'timestamp', 'value'])
        ->and($payload['sequence'])->toBe(1)
        ->and($payload['timestamp'])->toBe('2026/08/25 14:30:01')
        ->and($payload['value'])->toBe(24);
});
