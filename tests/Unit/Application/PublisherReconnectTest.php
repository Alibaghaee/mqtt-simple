<?php

declare(strict_types=1);

use MqttRealtime\Application\Publisher;
use MqttRealtime\Domain\Clock;
use MqttRealtime\Domain\ValueGenerator;
use MqttRealtime\Infrastructure\Mqtt\MqttTransport;
use MqttRealtime\Infrastructure\Resilience\CircuitBreaker;
use Psr\Log\NullLogger;

it('reconnects after a transient publish failure', function (): void {
    $mqtt = new class () implements MqttTransport {
        public int $connects = 0;
        public int $publishes = 0;
        private bool $failed = false;

        public function connect(): void
        {
            $this->connects++;
        }

        public function disconnect(): void
        {
        }

        public function publish(string $topic, string $payload, int $qos): void
        {
            if (! $this->failed) {
                $this->failed = true;
                throw new RuntimeException('temporary MQTT failure');
            }

            $this->publishes++;
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

    $publisher = new Publisher(
        mqtt: $mqtt,
        clock: new class () implements Clock {
            public function now(): string
            {
                return '2026/08/25 17:00:00';
            }
        },
        valueGenerator: new class () implements ValueGenerator {
            public function generate(): int
            {
                return 25;
            }
        },
        circuitBreaker: new CircuitBreaker(3, 1),
        topic: 'test/reconnect',
        qos: 1,
        intervalSeconds: 0,
        logger: new NullLogger(),
    );

    $publisher->run(fn (): bool => $mqtt->publishes >= 1);

    expect($mqtt->connects)->toBeGreaterThanOrEqual(2)
        ->and($mqtt->publishes)->toBe(1);
});
