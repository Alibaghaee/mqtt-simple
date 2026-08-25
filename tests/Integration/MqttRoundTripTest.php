<?php

declare(strict_types=1);

use MqttRealtime\Infrastructure\Mqtt\MqttTransportFactory;
use MqttRealtime\Support\ContainerFactory;
use Psr\Log\NullLogger;
use Ramsey\Uuid\Uuid;

it('delivers a message through the real local MQTT broker', function (): void {
    $config = ContainerFactory::config();
    $factory = new MqttTransportFactory($config['mqtt'], new NullLogger());
    $topic = sprintf('test/integration/%s', Uuid::uuid7()->toString());
    $received = null;

    $subscriber = $factory->create('integration-subscriber');
    $publisher = $factory->create('integration-publisher');

    try {
        $subscriber->connect();
        $subscriber->subscribe($topic, 1, function (string $receivedTopic, string $payload) use (&$received, $subscriber): void {
            $received = [$receivedTopic, $payload];
            $subscriber->interrupt();
        });

        $publisher->connect();
        $payload = json_encode([
            'event_id' => Uuid::uuid7()->toString(),
            'sequence' => 1,
            'timestamp' => '2026/08/25 17:00:00',
            'value' => 25,
        ], JSON_THROW_ON_ERROR);
        $publisher->publish($topic, $payload, 1);

        $subscriber->loop();

        expect($received)->not->toBeNull()
            ->and($received[0])->toBe($topic)
            ->and(json_decode($received[1], true, 512, JSON_THROW_ON_ERROR))->toMatchArray([
                'sequence' => 1,
                'value' => 25,
            ]);
    } finally {
        $publisher->disconnect();
        $subscriber->disconnect();
    }
})->group('integration');
