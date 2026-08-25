<?php

declare(strict_types=1);

namespace MqttRealtime\Infrastructure\Mqtt;

use Psr\Log\LoggerInterface;

final class MqttTransportFactory
{
    /** @param array<string, mixed> $config */
    public function __construct(private readonly array $config, private readonly LoggerInterface $logger)
    {
    }

    public function create(string $role): MqttTransport
    {
        return new PhpMqttTransport(
            host: $this->config['host'],
            port: $this->config['port'],
            clientId: sprintf('%s-%s', $role, bin2hex(random_bytes(8))),
            username: $this->config['username'],
            password: $this->config['password'],
            connectTimeout: $this->config['connect_timeout'],
            socketTimeout: $this->config['socket_timeout'],
            keepAlive: $this->config['keep_alive'],
            logger: $this->logger,
        );
    }
}
