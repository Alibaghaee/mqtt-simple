<?php

declare(strict_types=1);

namespace MqttRealtime\Support;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use MqttRealtime\Application\Publisher;
use MqttRealtime\Application\Subscriber;
use MqttRealtime\Infrastructure\Clock\RandomValueGenerator;
use MqttRealtime\Infrastructure\Clock\SystemClock;
use MqttRealtime\Infrastructure\Mqtt\MqttTransportFactory;
use MqttRealtime\Infrastructure\Persistence\RedisIdempotencyStore;
use MqttRealtime\Infrastructure\Realtime\SoketiBroadcaster;
use MqttRealtime\Infrastructure\Resilience\CircuitBreaker;
use Predis\Client;
use Psr\Log\LoggerInterface;

final class ContainerFactory
{
    /** @return array<string,mixed> */
    public static function config(): array
    {
        return require __DIR__ . '/../../config/config.php';
    }

    /** @param array<string, mixed> $config */
    public static function logger(array $config): LoggerInterface
    {
        $logger = new Logger('mqtt-realtime');
        $logger->pushHandler(new StreamHandler('php://stdout', $config['logging']['level']));

        return $logger;
    }

    /** @param array<string, mixed> $config */
    public static function publisher(array $config, LoggerInterface $logger): Publisher
    {
        $mqttFactory = new MqttTransportFactory($config['mqtt'], $logger);

        return new Publisher(
            mqtt: $mqttFactory->create('publisher'),
            clock: new SystemClock(new \DateTimeZone('UTC')),
            valueGenerator: new RandomValueGenerator(),
            circuitBreaker: new CircuitBreaker(
                $config['circuit_breaker']['failure_threshold'],
                $config['circuit_breaker']['recovery_timeout'],
            ),
            topic: $config['mqtt']['topic'],
            qos: $config['mqtt']['qos'],
            intervalSeconds: $config['publisher']['interval'],
            logger: $logger,
        );
    }

    /** @param array<string, mixed> $config */
    public static function subscriber(array $config, LoggerInterface $logger): Subscriber
    {
        $mqttFactory = new MqttTransportFactory($config['mqtt'], $logger);
        $redis = new Client($config['redis']['url']);

        return new Subscriber(
            mqtt: $mqttFactory->create('subscriber'),
            idempotency: new RedisIdempotencyStore($redis),
            broadcaster: new SoketiBroadcaster(
                appId: $config['realtime']['app_id'],
                key: $config['realtime']['key'],
                secret: $config['realtime']['secret'],
                host: $config['realtime']['host'],
                port: $config['realtime']['port'],
                scheme: $config['realtime']['scheme'],
            ),
            circuitBreaker: new CircuitBreaker(
                $config['circuit_breaker']['failure_threshold'],
                $config['circuit_breaker']['recovery_timeout'],
            ),
            logger: $logger,
            topic: $config['mqtt']['topic'],
            channel: $config['realtime']['channel'],
            event: $config['realtime']['event'],
            qos: $config['mqtt']['qos'],
            idempotencyTtl: $config['redis']['idempotency_ttl'],
        );
    }
}
