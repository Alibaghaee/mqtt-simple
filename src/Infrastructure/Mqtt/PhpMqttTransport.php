<?php

declare(strict_types=1);

namespace MqttRealtime\Infrastructure\Mqtt;

use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\MqttClient;
use Psr\Log\LoggerInterface;

final class PhpMqttTransport implements MqttTransport
{
    private MqttClient $client;

    public function __construct(
        string $host,
        int $port,
        string $clientId,
        private readonly ?string $username,
        private readonly ?string $password,
        private readonly int $connectTimeout,
        private readonly int $socketTimeout,
        private readonly int $keepAlive,
        LoggerInterface $logger,
    ) {
        $this->client = new MqttClient(
            $host,
            $port,
            $clientId,
            MqttClient::MQTT_3_1_1,
            null,
            $logger,
        );
    }

    public function connect(): void
    {
        $settings = (new ConnectionSettings())
            ->setUsername($this->username)
            ->setPassword($this->password)
            ->setConnectTimeout($this->connectTimeout)
            ->setSocketTimeout($this->socketTimeout)
            ->setKeepAliveInterval($this->keepAlive)
            ->setReconnectAutomatically(false);

        $this->client->connect($settings, true);
    }

    public function disconnect(): void
    {
        if ($this->client->isConnected()) {
            $this->client->disconnect();
        }
    }

    public function publish(string $topic, string $payload, int $qos): void
    {
        $this->client->publish($topic, $payload, $qos, false);
    }

    public function subscribe(string $topic, int $qos, callable $handler): void
    {
        $this->client->subscribe($topic, $handler, $qos);
    }

    public function loop(): void
    {
        $this->client->loop(true);
    }

    public function interrupt(): void
    {
        $this->client->interrupt();
    }
}
